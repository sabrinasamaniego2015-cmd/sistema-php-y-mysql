<?php
require_once 'config/database.php';

if (isset($_SESSION['usuario_id'])) {
    header('Location: perfil.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($correo) || empty($password)) {
        $error = 'Todos los campos son obligatorios';
    } else {
        $stmt = $pdo->prepare("SELECT id, nombre, correo, password FROM usuarios WHERE correo = ?");
        $stmt->execute([$correo]);
        $usuario = $stmt->fetch();
        
        if ($usuario && password_verify($password, $usuario['password'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['usuario_correo'] = $usuario['correo'];
            
            header('Location: perfil.php');
            exit;
        } else {
            $error = 'Credenciales incorrectas';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión</title>
    <style>
        body { font-family: Arial; max-width: 400px; margin: 50px auto; padding: 20px; }
        input { width: 100%; padding: 8px; margin: 5px 0 15px; }
        button { background: #2196F3; color: white; padding: 10px; border: none; cursor: pointer; }
        .error { color: red; }
    </style>
</head>
<body>
    <h2>Iniciar Sesión</h2>
    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <label>Correo electrónico:</label>
        <input type="email" name="correo" required>
        
        <label>Contraseña:</label>
        <input type="password" name="password" required>
        
        <button type="submit">Ingresar</button>
    </form>
    <p>¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a></p>
</body>
</html>