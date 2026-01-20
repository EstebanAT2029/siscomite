<?php

$usuarios = [
    ["Hoyos Correa","Cristian","42281562","choyos@agrobanco.com.pe","999999999","choyos",3],
];

foreach ($usuarios as $u) {

    $hash = password_hash($u[2], PASSWORD_BCRYPT);

    echo "INSERT INTO usuarios 
    (apellidos, nombres, dni, correo, telefono, usuario, password, estado, fecha_registro, rol, id_zona)
    VALUES (
        '{$u[0]}',
        '{$u[1]}',
        '{$u[2]}',
        '{$u[3]}',
        '{$u[4]}',
        '{$u[5]}',
        '{$hash}',
        1,
        NOW(),
        'usuario',
        {$u[6]}
    );\n\n";
}