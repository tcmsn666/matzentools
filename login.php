<?php
require_once __DIR__ . '/includes/bootstrap.php';
$error = '';
if (isLoggedIn()) {
    redirect(BASE_PATH . 'index.php');
}
if (isPost()) {
    if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
        $error = 'Ungültiges Formular-Token.';
    } elseif (attemptLogin(postString('username', 80), (string)($_POST['password'] ?? ''))) {
        redirect(BASE_PATH . 'index.php');
    } else {
        $error = 'Benutzername oder Passwort ist falsch.';
    }
}
?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <title>Login - MatzenTools</title>
    <link rel="stylesheet" href="<?= BASE_PATH ?>assets/css/style.css">
</head>
<body class="login-body">
<form class="login-box" method="post">
    <h1>MatzenTools</h1>
    <p>Verwaltungs-Grundgerüst</p>
    <?php if ($error): ?><div class="form-error"><?= e($error) ?></div><?php endif; ?>
    <?= csrfField() ?>
    <label>Benutzername<input name="username" autocomplete="username" required></label>
    <label>Passwort<input type="password" name="password" autocomplete="current-password" required></label>
    <button type="submit">Anmelden</button>
</form>
</body>
</html>
