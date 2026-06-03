<?php

// Ingresa aquí la contraseña que deseas convertir a BCrypt
$password = "admin123";

// Generar hash BCrypt
$hash = password_hash($password, PASSWORD_BCRYPT);

// Mostrar resultados
echo "<h3>Generador de Hash BCrypt</h3>";
echo "<p><strong>Contraseña:</strong> " . htmlspecialchars($password) . "</p>";
echo "<p><strong>Hash BCrypt:</strong></p>";
echo "<textarea rows='3' cols='100'>" . $hash . "</textarea>";

?>