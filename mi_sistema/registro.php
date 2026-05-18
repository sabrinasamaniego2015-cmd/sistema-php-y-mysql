<?php
require_once 'config/database.php';

$error = '';
$success = '';

// Función para validar cédula ecuatoriana
function validarCedulaEcuador($cedula) {
    // Verificar que sea 10 dígitos numéricos
    if (!preg_match("/^[0-9]{10}$/", $cedula)) {
        return false;
    }
    
    // Verificar provincia (primeros 2 dígitos deben ser entre 01 y 24)
    $provincia = intval(substr($cedula, 0, 2));
    if ($provincia < 1 || $provincia > 24) {
        return false;
    }
    
    // Algoritmo del dígito verificador
    $suma = 0;
    $coeficientes = [2, 1, 2, 1, 2, 1, 2, 1, 2];
    
    for ($i = 0; $i < 9; $i++) {
        $digito = intval($cedula[$i]);
        $producto = $digito * $coeficientes[$i];
        
        if ($producto > 9) {
            $producto -= 9;
        }
        
        $suma += $producto;
    }
    
    $decenaSuperior = ceil($suma / 10) * 10;
    $digitoVerificadorEsperado = $decenaSuperior - $suma;
    
    if ($digitoVerificadorEsperado == 10) {
        $digitoVerificadorEsperado = 0;
    }
    
    $digitoVerificadorReal = intval($cedula[9]);
    
    return $digitoVerificadorEsperado == $digitoVerificadorReal;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cedula = trim($_POST['cedula'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validaciones
    if (empty($cedula) || empty($nombre) || empty($correo) || empty($password)) {
        $error = 'Todos los campos son obligatorios';
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = 'Correo electrónico no válido';
    } elseif (!validarCedulaEcuador($cedula)) {
        $error = 'Cédula ecuatoriana no válida';
    } elseif ($password !== $confirm_password) {
        $error = 'Las contraseñas no coinciden';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres';
    } else {
        try {
            // Verificar si la cédula o correo ya existen
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE correo = ? OR cedula = ?");
            $stmt->execute([$correo, $cedula]);
            
            if ($stmt->rowCount() > 0) {
                $error = 'El correo o cédula ya está registrado';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare("INSERT INTO usuarios (cedula, nombre, correo, password) VALUES (?, ?, ?, ?)");
                $stmt->execute([$cedula, $nombre, $correo, $hashed_password]);
                
                $success = 'Usuario registrado exitosamente. <a href="login.php">Iniciar sesión</a>';
            }
        } catch (PDOException $e) {
            $error = 'Error al registrar: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Usuario</title>
    <style>
        body { font-family: Arial; max-width: 400px; margin: 50px auto; padding: 20px; }
        input { width: 100%; padding: 8px; margin: 5px 0 15px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #4CAF50; color: white; padding: 10px; border: none; cursor: pointer; border-radius: 4px; }
        button:hover { background: #45a049; }
        .error { color: red; background: #ffebee; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .success { color: green; background: #e8f5e9; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        small { color: #666; font-size: 12px; display: block; margin-top: -10px; margin-bottom: 10px; }
        .info { background: #e3f2fd; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-size: 14px; }
    </style>
</head>
<body>
    <h2>Registro de Usuario</h2>
    
    <div class="info">
        📌 <strong>Formato de cédula ecuatoriana:</strong> 10 dígitos<br>
        Ejemplo: <strong>1723456789</strong> (provincia 17)
    </div>
    
    <?php if ($error): ?>
        <div class="error">❌ <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="success">✅ <?php echo $success; ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <label>Cédula (10 dígitos):</label>
        <input type="text" name="cedula" required 
               value="<?php echo htmlspecialchars($_POST['cedula'] ?? ''); ?>"
               maxlength="10"
               onkeypress="return event.charCode >= 48 && event.charCode <= 57"
               placeholder="Ejemplo: 1723456789">
        <small>Ingrese 10 dígitos numéricos (formato ecuatoriano)</small>
        
        <label>Nombre completo:</label>
        <input type="text" name="nombre" required 
               value="<?php echo htmlspecialchars($_POST['nombre'] ?? ''); ?>"
               placeholder="Nombres y apellidos">
        
        <label>Correo electrónico:</label>
        <input type="email" name="correo" required 
               value="<?php echo htmlspecialchars($_POST['correo'] ?? ''); ?>"
               placeholder="ejemplo@correo.com">
        
        <label>Contraseña (mínimo 6 caracteres):</label>
        <input type="password" name="password" required placeholder="Mínimo 6 caracteres">
        
        <label>Confirmar contraseña:</label>
        <input type="password" name="confirm_password" required placeholder="Repita su contraseña">
        
        <button type="submit">Registrarse</button>
    </form>
    <p>¿Ya tienes cuenta? <a href="login.php">Iniciar sesión</a></p>
</body>
</html>