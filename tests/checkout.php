<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
require dirname(__DIR__) . '/app/checkout.php';

final class FakeGateway implements CheckoutGateway
{
    public array $transactions = [];
    public string $result = 'approved';
    public int $creates = 0;
    public int $commits = 0;
    public bool $offline = false;
    public bool $lostCreate = false;
    public bool $lostCommit = false;
    public function create(string $buyOrder, string $session, int $amount, string $returnUrl): array
    {
        $this->creates++;
        if ($this->lostCreate) throw new RuntimeException('Simulación: creación incierta');
        $token = bin2hex(random_bytes(32));
        $this->transactions[$token] = ['status' => 'INITIALIZED', 'amount' => $amount, 'buy_order' => $buyOrder, 'session_id' => $session, 'response_code' => null, 'authorization_code' => null];
        return ['token' => $token, 'url' => 'https://webpay3gint.transbank.cl/webpayserver/initTransaction'];
    }
    public function commit(string $token): array
    {
        $this->commits++;
        $this->transactions[$token]['status'] = $this->result === 'approved' ? 'AUTHORIZED' : 'FAILED';
        $this->transactions[$token]['response_code'] = $this->result === 'approved' ? 0 : -1;
        $this->transactions[$token]['authorization_code'] = $this->result === 'approved' ? '123456' : null;
        if ($this->lostCommit) throw new RuntimeException('Simulación: respuesta perdida');
        return $this->transactions[$token];
    }
    public function status(string $token): array
    {
        if ($this->offline) throw new RuntimeException('Simulación: sin red');
        return $this->transactions[$token];
    }
}

$checks = 0;
function verify(bool $ok, string $label): void { global $checks; $checks++; if (!$ok) throw new RuntimeException('FAIL: ' . $label); }
function rejects(callable $fn, string $label): void {
    try { $fn(); } catch (DomainException) { verify(true, $label); return; }
    verify(false, $label);
}
$config = require dirname(__DIR__) . '/config/checkout.php';
$config['db']['dsn'] = preg_replace('/dbname=[^;]+/', 'dbname=xommafitnes_checkout_test', $config['db']['dsn']);
if (!str_contains($config['db']['dsn'], 'dbname=xommafitnes_checkout_test;')) exit('Se requiere base exclusiva de pruebas.');
$db = checkout_db($config);
foreach (explode(';', file_get_contents(dirname(__DIR__) . '/database/migrations/001_checkout.sql')) as $sql) if (trim($sql)) $db->exec($sql);
$site = load_site(dirname(__DIR__)); $gateway = new FakeGateway(); $service = new CheckoutService($db, $gateway, $config);
$owner = bin2hex(random_bytes(32)); $createdIds = [];
$input = ['plan' => 'full-day-8', 'name' => 'Alumno de prueba', 'email' => 'checkout@example.invalid', 'student_type' => 'new', 'method' => 'webpay', 'price' => '1', 'amount' => '1'];
function fresh(array $overrides = []): array {
    global $service, $site, $input, $owner, $createdIds;
    $o = $service->createOrder($site, array_replace($input, $overrides), $owner, bin2hex(random_bytes(32)));
    $createdIds[] = $o['id']; return $o;
}
function saved_order(array $order): array { global $service, $owner; return $service->order($order['reference'], $owner); }
try {
    verify(count(checkout_plans($site)) === 7, 'Siete planes del catálogo');
    verify(!checkout_bank_ready($config['bank']), 'Banco incompleto deshabilitado');
    rejects(fn () => fresh(['method' => 'transfer']), 'No crear transferencia sin banco');
    rejects(fn () => fresh(['plan' => 'inventado']), 'Plan inexistente rechazado');
    rejects(fn () => fresh(['email' => 'invalido']), 'Correo inválido rechazado');
    foreach (checkout_plans($site) as $id => $plan) {
        $o = fresh(['plan' => $id]); verify((int) $o['amount'] === $plan['price'] && (int) $o['classes'] === $plan['classes'], 'Precio y clases en servidor');
    }
    $key = bin2hex(random_bytes(32));
    $o = $service->createOrder($site, $input, $owner, $key); $createdIds[] = $o['id'];
    $same = $service->createOrder($site, $input, $owner, $key);
    verify($same['id'] === $o['id'], 'POST repetido: misma orden');
    verify((int) $o['amount'] === 45000 && json_decode($o['plan_snapshot'], true)['condition'] === $site['plans_condition'], 'Manipulación de precio ignorada y snapshot guardado');
    rejects(fn () => $service->order($o['reference'], 'otro-navegador'), 'Orden protegida');
    $a = $service->start($o['reference'], $owner); $again = $service->start($o['reference'], $owner);
    verify($a['token'] === $again['token'] && count($service->attempts((int) $o['id'])) === 1, 'Repetición conserva intento y token');
    $service->reconcile($o['reference'], (int) $a['id']);
    verify($gateway->commits === 0 && saved_order($o)['payment_status'] === 'pending', 'Consulta no confirma un pago sin retorno');
    rejects(fn () => $service->handleReturn($a['buy_order'], ['token_ws' => 'incorrecto']), 'Token manipulado rechazado');
    rejects(fn () => $service->handleReturn($a['buy_order'], ['token_ws' => $a['token'], 'TBK_TOKEN' => $a['token']]), 'Retorno ambiguo rechazado');
    $service->handleReturn($a['buy_order'], ['token_ws' => $a['token']]);
    $service->handleReturn($a['buy_order'], ['token_ws' => $a['token']]);
    verify($gateway->commits === 1 && saved_order($o)['payment_status'] === 'approved', 'Aprobado y retorno repetido sin duplicar commit');
    verify(saved_order($o)['activation_status'] === 'pending', 'Pago aprobado no activa Boxmagic');
    verify($service->start($o['reference'], $owner) === [], 'No volver a cobrar orden aprobada');
    $o = fresh(); $a = $service->start($o['reference'], $owner);
    $gateway->transactions[$a['token']] = array_replace($gateway->transactions[$a['token']], ['status' => 'AUTHORIZED', 'response_code' => 0, 'authorization_code' => '123456']);
    $before = $gateway->commits;
    $service->reconcile($o['reference'], (int) $a['id']);
    verify(saved_order($o)['payment_status'] === 'approved' && $gateway->commits === $before + 1, 'Autorización recuperada sin retorno se confirma una vez');
    $gateway->result = 'rejected'; $o = fresh(); $a = $service->start($o['reference'], $owner);
    $service->handleReturn($a['buy_order'], ['token_ws' => $a['token']]);
    verify(saved_order($o)['payment_status'] === 'rejected', 'Rechazo confirmado');
    $b = $service->start($o['reference'], $owner);
    verify($a['token'] !== $b['token'] && count($service->attempts((int) $o['id'])) === 2, 'Reintento en la misma orden');
    foreach (['canceled', 'expired'] as $kind) {
        $o = fresh(); $a = $service->start($o['reference'], $owner);
        $gateway->transactions[$a['token']]['status'] = 'FAILED';
        $fields = $kind === 'canceled' ? ['TBK_TOKEN' => $a['token'], 'TBK_ORDEN_COMPRA' => $a['buy_order'], 'TBK_ID_SESION' => $a['session_id']] : [];
        $before = $gateway->commits; $service->handleReturn($a['buy_order'], $fields);
        verify(saved_order($o)['payment_status'] === $kind && $gateway->commits === $before, 'Cancelación/expiración confirmada sin commit');
    }
    $o = fresh(); $a = $service->start($o['reference'], $owner);
    $service->handleReturn($a['buy_order'], ['TBK_TOKEN' => $a['token']]);
    verify(saved_order($o)['payment_status'] === 'verification_pending', 'Cancelación sin estado final conserva incertidumbre');
    $gateway->result = 'approved'; $o = fresh(); $a = $service->start($o['reference'], $owner);
    $gateway->lostCommit = true; $before = $gateway->commits;
    $service->handleReturn($a['buy_order'], ['token_ws' => $a['token']]);
    verify(saved_order($o)['payment_status'] === 'verification_pending', 'Respuesta de commit perdida conserva pendiente');
    verify($service->start($o['reference'], $owner) === [], 'Resultado incierto bloquea reintento');
    $gateway->lostCommit = false; $service->reconcile($o['reference'], (int) $a['id']);
    verify(saved_order($o)['payment_status'] === 'approved' && $gateway->commits === $before + 1, 'Recuperación por status sin repetir commit');
    $o = fresh(); $a = $service->start($o['reference'], $owner);
    $gateway->transactions[$a['token']]['amount'] = 1; $before = $gateway->commits;
    $service->handleReturn($a['buy_order'], ['token_ws' => $a['token']]);
    verify(saved_order($o)['payment_status'] === 'verification_pending' && $gateway->commits === $before, 'Monto remoto discordante bloquea confirmación');
    $response = ['amount' => 45000, 'buy_order' => 'a', 'session_id' => 'b', 'status' => 'AUTHORIZED', 'response_code' => 0, 'authorization_code' => '123'];
    foreach (['amount' => 1, 'buy_order' => 'otro', 'session_id' => 'otra', 'response_code' => -1, 'authorization_code' => null] as $field => $value) {
        verify(checkout_outcome(['amount' => 45000], ['buy_order' => 'a', 'session_id' => 'b'], array_replace($response, [$field => $value])) === 'verification_pending', 'Validación de autorización: ' . $field);
    }
    $gateway->offline = true; $service->reconcile($o['reference'], (int) $a['id']);
    verify(saved_order($o)['payment_status'] === 'verification_pending', 'Error de comunicación no se presenta como rechazo');
    $gateway->offline = false; $gateway->lostCreate = true; $o = fresh();
    $service->start($o['reference'], $owner); $service->start($o['reference'], $owner);
    verify(count($service->attempts((int) $o['id'])) === 1 && saved_order($o)['payment_status'] === 'verification_pending', 'Creación incierta sin token no duplica intentos');
    $configured = $config;
    $configured['bank'] = ['holder' => 'TEST NO TRANSFERIR', 'rut' => 'TEST', 'bank' => 'SIMULADO', 'account_type' => 'TEST', 'account_number' => 'TEST', 'email' => 'bank@example.invalid'];
    $bankService = new CheckoutService($db, $gateway, $configured);
    $bankOrder = $bankService->createOrder($site, array_replace($input, ['method' => 'transfer']), $owner, bin2hex(random_bytes(32))); $createdIds[] = $bankOrder['id'];
    verify($bankOrder['payment_status'] === 'pending' && $bankOrder['activation_status'] === 'pending' && $bankOrder['bank_snapshot'] !== null, 'Banco completo solo produce orden pendiente en fixture aislada');
    echo "OK: $checks comprobaciones; MySQL real + pasarela SIMULADA. Sin operaciones Transbank.\n";
} finally {
    foreach ($createdIds as $id) {
        $db->prepare('DELETE FROM checkout_payment_attempts WHERE order_id=?')->execute([$id]);
        $db->prepare('DELETE FROM checkout_orders WHERE id=?')->execute([$id]);
    }
}
