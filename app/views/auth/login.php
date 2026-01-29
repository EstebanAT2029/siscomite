<?php $title = "Login - SisComité"; ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Iniciar Sesión</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">


<style>

/* Fondo institucional premium */
body.bg-light {
    background: linear-gradient(135deg, #e7f2ec 0%, #d7e9df 100%);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

/* Logo centrado */
.login-logo {
    text-align: center;
    margin-bottom: 10px;
}
.login-logo img {
    height: 55px;
    filter: drop-shadow(0 2px 2px rgba(0,0,0,0.15));
}

/* Tarjeta estilo Glass UI */
.login-card {
    width: 360px;
    margin: auto;
    padding: 28px;
    border-radius: 14px;

    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);

    box-shadow: 0 6px 20px rgba(0,0,0,0.15);
    animation: fadeIn 0.7s ease-out;
}

/* Título */
.login-title {
    font-weight: 700;
    font-size: 1.4rem;
    color: #006548;
    margin-bottom: 18px;
    text-align: center;
}

/* Inputs */
.form-control {
    border-radius: 8px !important;
    padding: 10px;
    border: 1.4px solid #cbd6d0 !important;
}
.form-control:focus {
    border-color: #006548 !important;
    box-shadow: 0 0 5px rgba(0,101,72,0.4) !important;
}

/* Password toggle */
.password-toggle {
    cursor: pointer;
    position: absolute;
    right: 12px;
    top: 52%;
    transform: translateY(-50%);
    color: #555;
    transition: 0.2s;
}
.password-toggle:hover {
    color: #006548;
}

/* Botón principal */
.btn-primary {
    background-color: #006548 !important;
    border: none !important;
    border-radius: 10px;
    padding: 10px;
    font-size: 1rem;
    font-weight: 600;
    transition: 0.25s ease;
}

.btn-primary:hover {
    background-color: #004c36 !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0,101,72,0.35);
}

/* Animación */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(15px); }
    to   { opacity: 1; transform: translateY(0); }
}
/* ===== ANIMACIÓN SISCOMITÉ ===== */

.siscomite-anim-wrapper {
    text-align: center;
    margin-bottom: 8px;
    margin-top: -8px;
}

.siscomite-anim {
    display: inline-flex;
    gap: 2px;
}

.siscomite-anim span {
    font-size: 26px;
    font-weight: 900;
    opacity: 0;
    transform: translateY(10px);
    animation: animSIS 0.55s ease-out forwards;
    font-family: "Segoe UI", sans-serif;
    color: #006548;
}

/* Colores institucionales */
.siscomite-anim span:nth-child(3),
.siscomite-anim span:nth-child(7) {
    color: #FFC107; /* amarillo */
}

.siscomite-anim span:nth-child(5) {
    color: #4EBF6D; /* verde claro */
}

/* Delays animados */
.siscomite-anim span:nth-child(1) { animation-delay: 0.05s; }
.siscomite-anim span:nth-child(2) { animation-delay: 0.10s; }
.siscomite-anim span:nth-child(3) { animation-delay: 0.15s; }
.siscomite-anim span:nth-child(4) { animation-delay: 0.20s; }
.siscomite-anim span:nth-child(5) { animation-delay: 0.25s; }
.siscomite-anim span:nth-child(6) { animation-delay: 0.30s; }
.siscomite-anim span:nth-child(7) { animation-delay: 0.35s; }
.siscomite-anim span:nth-child(8) { animation-delay: 0.40s; }
.siscomite-anim span:nth-child(9) { animation-delay: 0.45s; }

/* Movimiento */
@keyframes animSIS {
    0%   { opacity: 0; transform: translateY(12px) scale(0.95); }
    60%  { opacity: 1; transform: translateY(-3px) scale(1.05); }
    100% { opacity: 1; transform: translateY(0) scale(1); }
}
/* CONTENEDOR DEL OJO */
.password-toggle {
    position: absolute;
    top: 50%;
    right: 12px;
    transform: translateY(-50%);
    cursor: pointer;
    font-size: 1.2rem;
    color: #666;
    padding: 4px;
    border-radius: 6px;
    transition: 0.2s ease;
}

.password-toggle:hover {
    background-color: #e4ede7;
    color: #004d34;
}

/* Ícono del ojo */
.password-toggle i {
    font-size: 1.2rem;
}
.password-wrapper {
    position: relative;
}

/* Botón del ojo */
.password-toggle {
    position: absolute;
    top: 50%;                 /* Centrado vertical */
    right: 12px;              /* Separación del borde */
    transform: translateY(-50%);
    cursor: pointer;
    z-index: 20;
    padding: 4px;
    color: #666;
    transition: 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.password-toggle:hover {
    color: #004d34;
    background: #e8f2ec;
    border-radius: 4px;
}

/* Ícono */
.password-toggle i {
    font-size: 1.25rem;
}
#password {
    padding-right: 40px !important;
}
.input-group-text {
    cursor: pointer;
}

.input-group-text button {
    background: none;
}

.input-group-text i {
    font-size: 1.1rem;
}


</style>

</head>
<body class="bg-light">
<?php if (isset($_GET['timeout']) && $_GET['timeout'] == 1): ?>
  <div class="alert alert-warning">
    Tu sesión expiró por inactividad (30 minutos). Vuelve a iniciar sesión.
  </div>
<?php endif; ?>

<!-- CARD LOGIN -->
<div class="login-card">
    <div class="siscomite-anim-wrapper">
        <div class="siscomite-anim">
            <span>S</span><span>I</span><span>S</span>
            <span>C</span><span>O</span><span>M</span><span>I</span><span>T</span><span>É</span>
        </div>
    </div>

    <h4 class="login-title">Iniciar Sesión</h4>

    <form id="loginForm" action="index.php?url=login/entrar" method="POST" novalidate>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- USUARIO -->
        <div class="mb-3">
            <label class="form-label">Usuario</label>
            <input 
                type="text" 
                name="usuario" 
                id="usuario"
                class="form-control" 
                maxlength="50"
                required 
                autocomplete="off"
                pattern="[A-Za-z0-9._-]{4,50}"
                value="<?= htmlspecialchars($usuario ?? '') ?>"
                <?= (!empty($mostrarZonas) ? 'readonly' : '') ?>
            >
        </div>

        <?php if (empty($mostrarZonas)): ?>
            <!-- CONTRASEÑA (solo Paso 1) -->
            <div class="mb-3">
                <label class="form-label">Contraseña</label>

                <div class="input-group">
                    <input
                        type="password"
                        name="password"
                        class="form-control"
                        required
                        autocomplete="new-password"
                        id="passwordField"
                    >

                    <span class="input-group-text bg-white">
                        <button
                            type="button"
                            class="btn btn-sm p-0 border-0"
                            id="togglePassword"
                            tabindex="-1"
                        >
                            <i class="bi bi-eye" id="iconPass"></i>
                        </button>
                    </span>
                </div>
            </div>
        <?php else: ?>
            <!-- PASO 2: Mensaje elegante (en vez de contraseña vacía) -->
            <div class="mb-3">
                <div class="alert alert-success py-2 mb-0 d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill"></i>
                    <div>
                        <div class="fw-semibold">Usuario autenticado</div>
                        <div class="small">
                            <?= htmlspecialchars($usuario ?? '') ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($mostrarZonas) && !empty($zonas) && count($zonas) > 1): ?>
            <!-- PASO 2: ZONA -->
            <div class="mb-3">
                <label class="form-label">Seleccionar Zona</label>
                <select name="zona_id" class="form-control" required>
                    <option value="">-- Seleccione Zona--</option>
                    <?php foreach ($zonas as $z): ?>
                        <option value="<?= (int)$z['id'] ?>">
                            <?= htmlspecialchars($z['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="text-muted">Elige la zona con la que deseas ingresar.</small>
            </div>

            <input type="hidden" name="step" value="zona">
        <?php endif; ?>

        <button class="btn btn-primary w-100" type="submit">
            <?= (!empty($mostrarZonas) ? 'Continuar' : 'Ingresar') ?>
        </button>

    </form>
</div>


<script>
document.addEventListener("DOMContentLoaded", () => {
    const passField = document.getElementById("passwordField");
    const toggleBtn = document.getElementById("togglePassword");
    const icon = document.getElementById("iconPass");

    // Solo activar si existe el campo (Paso 1)
    if (passField && toggleBtn && icon) {
        toggleBtn.addEventListener("click", () => {
            if (passField.type === "password") {
                passField.type = "text";
                icon.classList.remove("bi-eye");
                icon.classList.add("bi-eye-slash");
            } else {
                passField.type = "password";
                icon.classList.remove("bi-eye-slash");
                icon.classList.add("bi-eye");
            }
        });
    }
});
</script>

</body>

</html>

<?php require __DIR__ . "/../layout/footer.php"; ?>

