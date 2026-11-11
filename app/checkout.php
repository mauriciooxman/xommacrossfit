<?php
declare(strict_types=1);

function checkout_db(array $config): PDO
{
    $db = new PDO($config['db']['dsn'], $config['db']['user'], $config['db']['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    if ($db->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'mysql') throw new RuntimeException('Se requiere MySQL/MariaDB.');
    $db->exec("SET time_zone = '+00:00'");
    return $db;
}

function checkout_plans(array $site): array
{
    $plans = [];
    foreach ($site['plans'] as $plan) {
        $id = $plan['id'] ?? 'full-day-' . $plan['classes'];
        $plans[$id] = $plan + ['id' => $id, 'title' => $site['plans_title'], 'condition' => $site['plans_condition']];
    }
    return $plans;
}

function checkout_bank_ready(array $bank): bool
{
    foreach (['holder', 'rut', 'bank', 'account_type', 'account_number', 'email'] as $field) {
        if (!is_string($bank[$field] ?? null) || trim($bank[$field]) === '') return false;
    }
    return (bool) filter_var($bank['email'], FILTER_VALIDATE_EMAIL);
}

interface CheckoutGateway
{
    public function create(string $buyOrder, string $session, int $amount, string $returnUrl): array;
    public function commit(string $token): array;
    public function status(string $token): array;
}

final class WebpayGateway implements CheckoutGateway
{
    private \Transbank\Webpay\WebpayPlus\Transaction $transaction;

    public function __construct()
    {
        require_once dirname(__DIR__) . '/vendor/autoload.php';
        // Solo credenciales oficiales incluidas en el SDK. Sin ruta a producción.
        $this->transaction = new \Transbank\Webpay\WebpayPlus\Transaction(new \Transbank\Webpay\Options(
            \Transbank\Webpay\Options::INTEGRATION_API_KEY,
            \Transbank\Webpay\WebpayPlus::INTEGRATION_COMMERCE_CODE,
            \Transbank\Webpay\Options::ENVIRONMENT_INTEGRATION,
            15
        ));
    }

    public function create(string $buyOrder, string $session, int $amount, string $returnUrl): array
    {
        $r = $this->transaction->create($buyOrder, $session, $amount, $returnUrl);
        return ['token' => $r->getToken(), 'url' => $r->getUrl()];
    }

    private function fields(object $r): array
    {
        // Lista permitida: no persistir card_detail ni datos de tarjeta.
        return ['status' => $r->getStatus(), 'amount' => $r->getAmount(), 'buy_order' => $r->getBuyOrder(),
            'session_id' => $r->getSessionId(), 'response_code' => $r->getResponseCode(),
            'authorization_code' => $r->getAuthorizationCode()];
    }

    public function commit(string $token): array { return $this->fields($this->transaction->commit($token)); }
    public function status(string $token): array { return $this->fields($this->transaction->status($token)); }
}

// Validación pura compartida por commit y consulta. Nunca confiar en campos del navegador.
function checkout_matches(array $order, array $attempt, array $response): bool
{
    return is_numeric($response['amount'] ?? null) && (float) $response['amount'] === (float) $order['amount']
        && ($response['buy_order'] ?? null) === $attempt['buy_order']
        && ($response['session_id'] ?? null) === $attempt['session_id'];
}

function checkout_outcome(array $order, array $attempt, array $response): string
{
    if (!checkout_matches($order, $attempt, $response)) return 'verification_pending';
    $status = $response['status'] ?? '';
    if ($status === 'AUTHORIZED' && ($response['response_code'] ?? null) === 0
        && is_string($response['authorization_code'] ?? null) && $response['authorization_code'] !== '') return 'approved';
    if ($status === 'FAILED') return 'rejected';
    if (in_array($status, ['REVERSED', 'NULLIFIED', 'PARTIALLY_NULLIFIED'], true)) return 'reversed';
    return 'verification_pending';
}

final class CheckoutService
{
    public function __construct(private PDO $db, private CheckoutGateway $gateway, private array $config) {}

    private function query(string $sql, array $values = []): PDOStatement
    {
        $q = $this->db->prepare($sql); $q->execute($values); return $q;
    }

    // GET_LOCK serializa creación, reintentos y retornos sin mantener una transacción SQL durante HTTP.
    private function locked(string $key, callable $work): mixed
    {
        $name = 'xomma:' . substr(hash('sha256', $key), 0, 50);
        if ((int) $this->query('SELECT GET_LOCK(?, 5)', [$name])->fetchColumn() !== 1) {
            throw new RuntimeException('Hay otra operación en curso. Espera unos segundos.');
        }
        try { return $work(); } finally { $this->query('SELECT RELEASE_LOCK(?)', [$name]); }
    }

    public function order(string $reference, string $owner): array
    {
        $order = $this->query('SELECT * FROM checkout_orders WHERE reference = ? AND owner_hash = ?', [$reference, hash('sha256', $owner)])->fetch();
        if (!$order) throw new DomainException('Orden no encontrada en este navegador.');
        return $order;
    }

    public function attempts(int $id): array
    {
        return $this->query('SELECT * FROM checkout_payment_attempts WHERE order_id = ? ORDER BY id DESC', [$id])->fetchAll();
    }

    public function createOrder(array $site, array $input, string $owner, string $requestKey): array
    {
        $plan = checkout_plans($site)[$input['plan'] ?? ''] ?? null;
        if (!$plan) throw new DomainException('Selecciona un plan válido.');
        $name = trim($input['name'] ?? ''); $email = trim($input['email'] ?? '');
        $method = $input['method'] ?? '';
        if ($name === '' || mb_strlen($name) > 200 || strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)
            || !in_array($input['student_type'] ?? '', ['new', 'existing'], true)) throw new DomainException('Revisa tu nombre, correo y tipo de alumno.');
        if (!in_array($method, ['webpay', 'transfer'], true)) throw new DomainException('Selecciona un medio de pago.');
        if ($method === 'transfer' && !checkout_bank_ready($this->config['bank'])) throw new DomainException('Transferencia no disponible: faltan datos bancarios confirmados.');
        return $this->locked('create:' . $requestKey, function () use ($plan, $name, $email, $input, $owner, $requestKey, $method) {
            $existing = $this->query('SELECT reference FROM checkout_orders WHERE request_key = ?', [$requestKey])->fetchColumn();
            if ($existing) return $this->order($existing, $owner);
            $reference = bin2hex(random_bytes(12));
            $this->query('INSERT INTO checkout_orders (reference, owner_hash, request_key, plan_id, plan_snapshot, classes, amount, customer_name, customer_email, student_type, payment_method, bank_snapshot) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)', [
                $reference, hash('sha256', $owner), $requestKey, $plan['id'], json_encode($plan, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
                $plan['classes'], $plan['price'], $name, $email, $input['student_type'], $method,
                $method === 'transfer' ? json_encode($this->config['bank'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE) : null,
            ]);
            return $this->order($reference, $owner);
        });
    }

    public function start(string $reference, string $owner): array
    {
        return $this->locked('order:' . $reference, function () use ($reference, $owner) {
            $order = $this->order($reference, $owner);
            if ($order['payment_method'] !== 'webpay' || $order['payment_status'] === 'approved') return [];
            $last = $this->attempts((int) $order['id'])[0] ?? null;
            if ($last && !in_array($last['status'], ['rejected', 'canceled', 'expired', 'reversed'], true)) {
                // Reutilizar el mismo token al repetir un POST o volver atrás.
                return $last['status'] === 'initialized' ? $last : [];
            }
            $buy = bin2hex(random_bytes(12)); $session = bin2hex(random_bytes(16));
            $this->query('INSERT INTO checkout_payment_attempts (order_id,buy_order,session_id) VALUES (?,?,?)', [$order['id'], $buy, $session]);
            $id = (int) $this->db->lastInsertId();
            $this->query("UPDATE checkout_orders SET payment_status='verification_pending' WHERE id=?", [$order['id']]);
            try {
                $r = $this->gateway->create($buy, $session, (int) $order['amount'], $this->config['base_url'] . '/checkout.php?action=return&attempt=' . $buy);
                if (!is_string($r['token'] ?? null) || !preg_match('/^[a-zA-Z0-9]{1,64}$/D', $r['token'])
                    || ($r['url'] ?? '') !== 'https://webpay3gint.transbank.cl/webpayserver/initTransaction') throw new RuntimeException('Respuesta de creación inválida.');
                $this->query("UPDATE checkout_payment_attempts SET token=?,redirect_url=?,status='initialized' WHERE id=?", [$r['token'], $r['url'], $id]);
                $this->query("UPDATE checkout_orders SET payment_status='pending' WHERE id=?", [$order['id']]);
            } catch (Throwable $e) {
                // Incluso una creación sin token puede haber llegado al proveedor. No repetir automáticamente.
                $this->query("UPDATE checkout_payment_attempts SET status='verification_pending' WHERE id=?", [$id]);
                return [];
            }
            return $this->attempts((int) $order['id'])[0];
        });
    }

    public function handleReturn(string $buy, array $input): string
    {
        $a = $this->query('SELECT * FROM checkout_payment_attempts WHERE buy_order=?', [$buy])->fetch();
        if (!$a || !$a['token']) throw new DomainException('Retorno no reconocido. Consulta tu orden desde el navegador original.');
        $token = $input['token_ws'] ?? null; $abort = $input['TBK_TOKEN'] ?? null;
        if ($token !== null && $abort !== null) throw new DomainException('Retorno ambiguo. Consulta el estado de tu orden.');
        $kind = $token !== null ? 'normal' : ($abort !== null ? 'canceled' : 'expired');
        if (($token !== null && !hash_equals($a['token'], $token)) || ($abort !== null && !hash_equals($a['token'], $abort))) throw new DomainException('Token inválido.');
        if ($kind !== 'normal') {
            foreach (['TBK_ORDEN_COMPRA' => 'buy_order', 'TBK_ID_SESION' => 'session_id'] as $field => $stored) {
                if (isset($input[$field]) && !hash_equals($a[$stored], $input[$field])) throw new DomainException('Retorno no corresponde al intento.');
            }
        }
        $order = $this->query('SELECT * FROM checkout_orders WHERE id=?', [$a['order_id']])->fetch();
        $this->reconcile($order['reference'], (int) $a['id'], $kind);
        return $order['reference'];
    }

    // Uso interno desde CLI o después de autenticar la propiedad de la orden en el controlador.
    public function reconcile(string $reference, int $attemptId, ?string $kind = null): void
    {
        $this->locked('order:' . $reference, function () use ($reference, $attemptId, $kind) {
            $order = $this->query('SELECT * FROM checkout_orders WHERE reference=?', [$reference])->fetch();
            $a = $this->query('SELECT * FROM checkout_payment_attempts WHERE id=? AND order_id=?', [$attemptId, $order['id']])->fetch();
            if (!$a || !$a['token']) return;
            if ($kind) $this->query('UPDATE checkout_payment_attempts SET return_kind=? WHERE id=?', [$kind, $a['id']]);
            // Un commit que pudo haberse enviado jamás se repite; se recupera mediante status.
            try {
                $r = $this->gateway->status($a['token']);
                $remoteStatus = $r['status'] ?? '';
                $canConfirm = ($kind ?? $a['return_kind']) === 'normal' || $remoteStatus === 'AUTHORIZED';
                if (checkout_matches($order, $a, $r) && in_array($remoteStatus, ['INITIALIZED', 'AUTHORIZED'], true) && !$a['commit_started_at'] && $canConfirm) {
                    $this->query("UPDATE checkout_payment_attempts SET commit_started_at=UTC_TIMESTAMP(6),status='verification_pending' WHERE id=?", [$a['id']]);
                    $r = $this->gateway->commit($a['token']);
                }
                $state = checkout_outcome($order, $a, $r);
                $returnKind = $kind ?? $a['return_kind'];
                if (checkout_matches($order, $a, $r) && ($r['status'] ?? '') === 'INITIALIZED' && !$a['commit_started_at'] && $returnKind === null) $state = 'initialized';
                if ($state === 'rejected' && in_array($returnKind, ['canceled', 'expired'], true)) $state = $returnKind;
                $this->query('UPDATE checkout_payment_attempts SET status=?,gateway_status=?,response_code=?,authorization_code=?,checked_at=UTC_TIMESTAMP(6) WHERE id=?', [
                    $state, substr((string) ($r['status'] ?? ''), 0, 32), $r['response_code'] ?? null,
                    isset($r['authorization_code']) ? substr($r['authorization_code'], 0, 32) : null, $a['id'],
                ]);
                // Nunca un retorno antiguo pendiente degrada otro intento aprobado.
                $approved = (int) $this->query("SELECT COUNT(*) FROM checkout_payment_attempts WHERE order_id=? AND status='approved'", [$order['id']])->fetchColumn();
                $latest = $this->attempts((int) $order['id'])[0];
                $payment = $approved ? 'approved' : ($latest['status'] === 'initialized' ? 'pending' : $latest['status']);
                $this->query('UPDATE checkout_orders SET payment_status=? WHERE id=?', [$payment, $order['id']]);
            } catch (Throwable $e) {
                if (!in_array($a['status'], ['approved', 'rejected', 'canceled', 'expired', 'reversed'], true)) {
                    $this->query("UPDATE checkout_payment_attempts SET status='verification_pending',checked_at=UTC_TIMESTAMP(6) WHERE id=?", [$a['id']]);
                    $this->query("UPDATE checkout_orders SET payment_status='verification_pending' WHERE id=? AND payment_status <> 'approved'", [$order['id']]);
                }
            }
        });
    }
}
