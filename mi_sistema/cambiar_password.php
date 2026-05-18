<?php
require_once 'config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password_actual = $_POST['password_actual'] ?? '';
    $password_nueva = $_POST['password_nueva'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    
    if (empty($password_actual) || empty($password_nueva) || empty($password_confirm)) {
        $error = 'Todos los campos son obligatorios';
    } elseif ($password_nueva !== $password_confirm) {
        $error = 'La nueva contraseña y la confirmación no coinciden';
    } elseif (strlen($password_nueva) < 6) {
        $error = 'La nueva contraseña debe tener al menos 6 caracteres';
    } else {
        $stmt = $pdo->prepare("SELECT password FROM usuarios WHERE id = ?");
        $stmt->execute([$_SESSION['usuario_id']]);
        $usuario = $stmt->fetch();
        
        if ($usuario && password_verify($password_actual, $usuario['password'])) {
            $nuevo_hash = password_hash($password_nueva, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
            $stmt->execute([$nuevo_hash, $_SESSION['usuario_id']]);
            
            $mensaje = 'Contraseña actualizada correctamente';
        } else {
            $error = 'La contraseña actual es incorrecta';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cambiar Contraseña</title>
    <style>
        body { font-family: Arial; max-width: 500px; margin: 50px auto; padding: 20px; }
        input { width: 100%; padding: 8px; margin: 5px 0 15px; }
        button { background: #FF9800; color: white; padding: 10px; border: none; cursor: pointer; }
        .mensaje { color: green; }
        .error { color: red; }
        .menu { margin-bottom: 20px; }
        .menu a { margin-right: 15px; }
    </style>
</head>
<body>
    <div class="menu">
        <strong><?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></strong> |
        <a href="perfil.php">Mi Perfil</a> |
        <a href="cambiar_password.php">Cambiar Contraseña</a> |
        <a href="logout.php">Cerrar Sesión</a>
    </div>
    
    <h2>Cambiar Contraseña</h2>
    
    <?php if ($mensaje): ?>
        <div class="mensaje"><?php echo htmlspecialchars($mensaje); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <label>Contraseña actual:</label>
        <input type="password" name="password_actual" required>
        
        <label>Nueva contraseña (mínimo 6 caracteres):</label>
        <input type="password" name="password_nueva" required>
        
        <label>Confirmar nueva contraseña:</label>
        <input type="password" name="password_confirm" required>
        
        <button type="submit">Cambiar Contraseña</button>
    </form>
</body>
</html>