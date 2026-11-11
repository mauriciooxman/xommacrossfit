<!doctype html>
<html lang="es-CL">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Compra tu plan · <?= e($site['name']) ?></title><link rel="stylesheet" href="assets/css/site.css"><link rel="stylesheet" href="assets/css/checkout.css"></head>
<body class="checkout-page">
<?php if ($demoMode): ?><p class="demo-banner" role="status">Demostración · Órdenes, pagos, transferencias y envíos de correo deshabilitados.</p><?php endif; ?>
<header class="checkout-header"><a href="index.php#planes"><img src="assets/images/logo.webp" width="600" height="144" alt="Xomma Fitness — volver a planes"></a><span><?= $demoMode ? 'Vista de demostración · sin operaciones' : 'Integración · sin cobros reales' ?></span></header>
<main class="checkout-main" id="contenido">
<h1><?= $order ? 'Tu compra' : 'Compra tu plan' ?></h1>
<p class="checkout-intro"><?= $demoMode ? 'Explora los siete planes y sus resúmenes. El formulario es solo una vista previa y no recibe datos ni crea compras.' : 'Prueba local. Usa únicamente tarjetas de prueba en el formulario oficial de Webpay.' ?></p>
<?php if ($error): ?><div class="checkout-notice" role="alert"><p><?= e($error) ?></p><a class="text-link" href="checkout.php">Volver al checkout</a></div><?php endif; ?>
<?php if ($redirectAttempt): ?>
<section class="checkout-summary"><h2>Continuar a Webpay</h2><p>El pago se realiza en el formulario de Transbank. Xommafitnes no recibe datos de tarjeta.</p>
<form action="<?= e($redirectAttempt['redirect_url']) ?>" method="post"><input type="hidden" name="token_ws" value="<?= e($redirectAttempt['token']) ?>"><button class="button" type="submit">Ir a Webpay de pruebas</button></form></section>
<?php elseif ($order): $snapshot = json_decode($order['plan_snapshot'], true, 512, JSON_THROW_ON_ERROR); ?>
<div class="checkout-layout"><section class="checkout-summary"><h2><?= e($snapshot['title']) ?></h2>
<dl><div><dt>Clases mensuales</dt><dd><?= (int) $order['classes'] ?></dd></div><div><dt>Total mensual · CLP</dt><dd><?= e('$' . number_format((int) $order['amount'], 0, ',', '.')) ?></dd></div></dl>
<p><?= e($snapshot['condition']) ?></p><p>Referencia: <strong class="checkout-reference"><?= e($order['reference']) ?></strong></p>
<p>Guarda esta referencia. Los datos de la orden se consultan desde este navegador durante 30 días; si borras sus cookies, necesitarás ayuda del gimnasio.</p></section>
<section class="checkout-result"><h2>Estado de tu compra</h2>
<?php $labels = ['pending' => 'Pendiente de pago', 'initialized' => 'Pago iniciado', 'verification_pending' => 'Pendiente de verificación', 'approved' => 'Pago aprobado', 'rejected' => 'Pago rechazado', 'canceled' => 'Pago cancelado', 'expired' => 'Pago expirado', 'reversed' => 'Pago reversado o anulado']; ?>
<dl><div><dt>Pago</dt><dd><?= e($labels[$order['payment_status']] ?? 'Pendiente de verificación') ?></dd></div><div><dt>Activación en Boxmagic</dt><dd>Pendiente de confirmación del gimnasio</dd></div></dl>
<p>El gimnasio debe confirmar la habilitación del plan antes de que puedas reservar. Un pago aprobado no habilita automáticamente las reservas.</p>
<?php if ($order['payment_method'] === 'transfer'): $bank = json_decode($order['bank_snapshot'], true, 512, JSON_THROW_ON_ERROR); ?>
<h3>Instrucciones de transferencia</h3><dl><?php foreach (['holder' => 'Titular', 'rut' => 'RUT', 'bank' => 'Banco', 'account_type' => 'Tipo de cuenta', 'account_number' => 'Número de cuenta', 'email' => 'Correo'] as $field => $label): ?><div><dt><?= e($label) ?></dt><dd><?= e($bank[$field]) ?></dd></div><?php endforeach; ?></dl>
<p>Transfiere el monto exacto del resumen e incluye la referencia de la orden. La aprobación requiere verificar el abono bancario. Esta entrega no recibe comprobantes ni aprueba transferencias.</p>
<?php else: $last = $attempts[0] ?? null; ?>
<?php if ($last && in_array($last['return_kind'], ['canceled', 'expired'], true)): ?><p><?= $last['return_kind'] === 'canceled' ? 'Se recibió una cancelación.' : 'Se recibió un retorno por expiración.' ?> El estado del pago se confirma con Transbank.</p><?php endif; ?>
<?php if ($order['payment_status'] === 'verification_pending'): ?><p>No inicies otro pago hasta confirmar este resultado. Si la creación falló antes de recibir un token, será necesaria una revisión técnica.</p><?php endif; ?>
<div class="checkout-actions">
<?php if (!$last || ($order['payment_status'] !== 'approved' && in_array($last['status'], ['initialized', 'rejected', 'canceled', 'expired', 'reversed'], true))): ?>
<form method="post" action="checkout.php?action=pay&amp;order=<?= e($order['reference']) ?>"><input type="hidden" name="csrf" value="<?= e($csrf) ?>"><button class="button" type="submit"><?= !$last ? 'Pagar con Webpay de pruebas' : ($last['status'] === 'initialized' ? 'Continuar el mismo pago' : 'Reintentar con Webpay') ?></button></form>
<?php endif; ?>
<?php if ($last && $last['token']): ?><form method="post" action="checkout.php?action=check&amp;order=<?= e($order['reference']) ?>"><input type="hidden" name="csrf" value="<?= e($csrf) ?>"><button class="checkout-secondary" type="submit">Consultar estado en Transbank</button></form><?php endif; ?>
</div><?php endif; ?>
<form method="post" action="checkout.php?action=new"><input type="hidden" name="csrf" value="<?= e($csrf) ?>"><button class="checkout-secondary" type="submit">Iniciar otra compra</button></form>
</section></div>
<?php elseif ($action !== 'return' && (!$error || http_response_code() === 422)): ?>
<form method="get" action="checkout.php" class="checkout-selector"><label for="plan">Elige tus clases mensuales</label><div><select id="plan" name="plan"><?php foreach ($plans as $id => $plan): ?><option value="<?= e($id) ?>" <?= $selected === $id ? 'selected' : '' ?>><?= (int) $plan['classes'] ?> clases · <?= e('$' . number_format($plan['price'], 0, ',', '.')) ?>/mes</option><?php endforeach; ?></select><button class="checkout-secondary" type="submit">Ver resumen</button></div></form>
<?php $plan = $plans[$selected] ?? null; if ($plan): ?>
<div class="checkout-layout"><section class="checkout-summary"><h2><?= e($plan['title']) ?></h2><dl><div><dt>Clases mensuales</dt><dd><?= (int) $plan['classes'] ?></dd></div><div><dt>Total mensual · CLP</dt><dd><?= e('$' . number_format($plan['price'], 0, ',', '.')) ?></dd></div></dl><p><?= e($plan['condition']) ?></p><p>La activación en Boxmagic requiere confirmación del gimnasio antes de reservar.</p></section>
<form method="post" action="checkout.php?action=buy" class="checkout-form">
<h2>Tus datos</h2><input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="nonce" value="<?= e($nonce) ?>"><input type="hidden" name="plan" value="<?= e($selected) ?>">
<label for="name">Nombre completo</label><input id="name" name="name" autocomplete="name" maxlength="200" required value="<?= e($values['name']) ?>"<?= $demoMode ? ' disabled' : '' ?>>
<label for="email">Correo electrónico</label><input id="email" type="email" name="email" autocomplete="email" maxlength="254" required value="<?= e($values['email']) ?>"<?= $demoMode ? ' disabled' : '' ?>>
<fieldset<?= $demoMode ? ' disabled' : '' ?>><legend>¿Ya entrenas con nosotros?</legend><label><input type="radio" name="student_type" value="new" <?= $values['student_type'] === 'new' ? 'checked' : '' ?> required> Soy alumno nuevo</label><label><input type="radio" name="student_type" value="existing" <?= $values['student_type'] === 'existing' ? 'checked' : '' ?>> Soy alumno existente</label></fieldset>
<fieldset<?= $demoMode ? ' disabled' : '' ?>><legend>Medio de pago</legend><label><input type="radio" name="method" value="webpay" checked required> Tarjeta · Webpay Plus de pruebas</label><label class="<?= checkout_bank_ready($config['bank']) ? '' : 'checkout-disabled' ?>"><input type="radio" name="method" value="transfer" <?= checkout_bank_ready($config['bank']) ? '' : 'disabled' ?>> Transferencia bancaria</label><?php if (!checkout_bank_ready($config['bank'])): ?><p>Transferencia no disponible: faltan datos bancarios confirmados.</p><?php endif; ?></fieldset>
<button class="button" type="submit"<?= $demoMode ? ' disabled' : '' ?>><?= $demoMode ? 'Compra deshabilitada en demostración' : 'Crear orden y continuar' ?></button><p><?= $demoMode ? 'Esta presentación no recibe datos personales ni procesa pagos.' : 'Tu nombre y correo se usarán para identificar esta compra. Los datos de tarjeta se ingresan exclusivamente en Transbank.' ?></p>
</form></div><?php else: ?><p role="alert">Selecciona uno de los planes disponibles para continuar.</p><?php endif; ?>
<?php endif; ?>
</main><footer class="checkout-footer"><a href="index.php#planes">Volver a planes y horarios</a></footer>
</body></html>
