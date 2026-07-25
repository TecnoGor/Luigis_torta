<?php
require_once __DIR__ . '/../includes/config.php';

if (isLoggedIn()) {
    header('Location: ' . ADMIN_URL . '/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $user = fetchOne('SELECT * FROM usuarios WHERE username = ? AND activo = 1', [$username]);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['nombre_completo'];
        $_SESSION['user_role'] = $user['rol'];
        header('Location: ' . ADMIN_URL . '/index.php');
        exit;
    } else {
        $error = 'Usuario o contraseña incorrectos';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body>
<div class="login-wrapper">
    <div class="login-card">
        <div style="font-size:2.5rem;margin-bottom:8px;">&#127856;</div>
        <h1><?= SITE_NAME ?></h1>
        <p class="login-sub">Panel de Administración</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= sanitize($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="username">Usuario</label>
                <input type="text" id="username" name="username" required autofocus value="<?= sanitize($username ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
        </form>

        <p style="margin-top:20px;font-size:0.8rem;color:#a1887f;">
            <a href="../" style="color:#c2185b;text-decoration:none;">&#8592; Volver al catálogo</a>
        </p>
    </div>
</div>
</body>
</html>
