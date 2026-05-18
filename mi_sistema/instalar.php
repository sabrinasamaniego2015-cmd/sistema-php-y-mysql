<?php
$host = 'localhost';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Crear base de datos
    $pdo->exec("CREATE DATABASE IF NOT EXISTS sistema_usuarios");
    echo "✅ Base de datos 'sistema_usuarios' creada<br>";
    
    // Usar la base de datos
    $pdo->exec("USE sistema_usuarios");
    
    // Crear tabla
    $sql = "CREATE TABLE IF NOT EXISTS usuarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        cedula VARCHAR(20) NOT NULL UNIQUE,
        nombre VARCHAR(100) NOT NULL,
        correo VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sql);
    echo "✅ Tabla 'usuarios' creada<br>";
    
    echo "<br>🎉 <strong>¡INSTALACIÓN COMPLETADA!</strong><br>";
    echo "<a href='registro.php'>👉 IR A REGISTRARSE</a>";
    
} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>