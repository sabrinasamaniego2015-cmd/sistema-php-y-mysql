<?php
require_once 'config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$mensaje = '';
$error = '';

$stmt = $pdo->prepare("SELECT cedula, nombre, correo FROM usuarios WHERE id = ?");
$stmt->execute([$_SESSION['usuario_id']]);
$usuario = $stmt->fetch();

if (!$usuario) {
    session_destroy();
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_perfil'])) {
    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    
    if (empty($nombre) || empty($correo)) {
        $error = 'Nombre y correo son obligatorios';
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = 'Correo electrónico no válido';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE correo = ? AND id != ?");
            $stmt->execute([$correo, $_SESSION['usuario_id']]);
            
            if ($stmt->rowCount() > 0) {
                $error = 'El correo ya está en uso por otro usuario';
            } else {
                $stmt = $pdo->prepare("UPDATE usuarios SET nombre = ?, correo = ? WHERE id = ?");
                $stmt->execute([$nombre, $correo, $_SESSION['usuario_id']]);
                
                $_SESSION['usuario_nombre'] = $nombre;
                $_SESSION['usuario_correo'] = $correo;
                
                $mensaje = 'Perfil actualizado correctamente';
                
                $usuario['nombre'] = $nombre;
                $usuario['correo'] = $correo;
            }
        } catch (PDOException $e) {
            $error = 'Error al actualizar: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Perfil</title>
    <style>
        body { font-family: Arial; max-width: 600px; margin: 50px auto; padding: 20px; }
        input { width: 100%; padding: 8px; margin: 5px 0 15px; }
        button { background: #2196F3; color: white; padding: 10px; border: none; cursor: pointer; }
        .mensaje { color: green; }
        .error { color: red; }
        .menu { margin-bottom: 20px; }
        .menu a { margin-right: 15px; }
    </style>
</head>
<body>
    <div class="menu">
        <strong>Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></strong> |
        <a href="perfil.php">Mi Perfil</a> |
        <a href="cambiar_password.php">Cambiar Contraseña</a> |
        <a href="logout.php">Cerrar Sesión</a>
    </div>
    
    <h2>Mi Perfil</h2>
    
    <?php if ($mensaje): ?>
        <div class="mensaje"><?php echo htmlspecialchars($mensaje); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <label>Cédula:</label>
        <input type="text" value="<?php echo htmlspecialchars($usuario['cedula']); ?>" disabled>
        <small>(La cédula no se puede modificar)</small>
        
        <label>Nombre completo:</label>
        <input type="text" name="nombre" required value="<?php echo htmlspecialchars($usuario['nombre']); ?>">
        
        <label>Correo electrónico:</label>
        <input type="email" name="correo" required value="<?php echo htmlspecialchars($usuario['correo']); ?>">
        
        <button type="submit" name="actualizar_perfil">Actualizar Perfil</button>
    </form>
</body>
</html>