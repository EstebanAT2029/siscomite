<?php
if (!isset($_SESSION)) session_start();
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?? "SisComité" ?></title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">


    <!-- CSS global -->
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">

</head>

<body class="login-bg">
<!-- NAVBAR GLOBAL -->
<nav class="navbar navbar-expand-lg navbar-agro shadow-sm">
    <div class="container">

        <a class="navbar-brand d-flex align-items-center" href="index.php?url=dashboard">
            <div class="nav-siscomite-anim">
                <span>S</span><span>I</span><span>S</span>
                <span>C</span><span>O</span><span>M</span><span>I</span><span>T</span><span>É</span>
            </div>
            <span class="fw-semibold ms-2 text-white">Sistema de Comité de Créditos</span>
        </a>

            <div class="d-flex align-items-center">

                <?php if (!empty($_SESSION["user"])): ?>
                    <span class="navbar-user me-3">
                        <i class="bi bi-person-circle me-1"></i>
                        <?= htmlspecialchars($_SESSION["user"]["nombres"] . ' ' . $_SESSION["user"]["apellidos"]) ?>
                    </span>

                    <?php if (!empty($_SESSION["zona_activa"]["nombre"])): ?>
                        <span class="navbar-zona me-3">
                            <i class="bi bi-geo-alt-fill me-1"></i>
                            <?= htmlspecialchars($_SESSION["zona_activa"]["nombre"]) ?>
                        </span>
                    <?php endif; ?>

                    <a href="index.php?url=logout" class="btn btn-agro-logout btn-sm">
                        Cerrar Sesión
                    </a>
                <?php endif; ?>

            </div>

    </div>
</nav>



<div class="container mt-4">