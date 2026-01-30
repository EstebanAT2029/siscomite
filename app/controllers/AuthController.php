<?php

class AuthController
{
    private $model;

    public function __construct()
    {
        $this->model = new AuthModel();
    }

    /* ======================================
       Mostrar formulario LOGIN
    ====================================== */
    public function loginForm()
    {
        require __DIR__ . "/../views/auth/login.php";
    }

    /* ======================================
    Procesar inicio de sesión – SEGURO
    ====================================== */
    public function login()
    {
        // ==============================
        // Paso (login o selección zona)
        // ==============================
        $step = $_POST["step"] ?? "login";

        // ==============================
        // PASO 2: Selección de zona
        // ==============================
        if ($step === "zona") {

            if (!isset($_SESSION["tmp_user"])) {
                header("Location: index.php?url=login");
                exit;
            }

            $zona_id = (int)($_POST["zona_id"] ?? 0);

            if ($zona_id <= 0) {
                $error = "Debe seleccionar una zona válida.";
                $zonas = $_SESSION["tmp_zonas"];
                $usuario = $_SESSION["tmp_user"]["usuario"];
                $mostrarZonas = true;
                require __DIR__ . "/../views/auth/login.php";
                return;
            }

            // Validar que la zona pertenezca al usuario
            $valida = $this->model->zonaPerteneceAUsuario(
                $_SESSION["tmp_user"]["id"],
                $zona_id
            );

            if (!$valida) {
                $error = "La zona seleccionada no está asignada a su usuario.";
                $zonas = $_SESSION["tmp_zonas"];
                $usuario = $_SESSION["tmp_user"]["usuario"];
                $mostrarZonas = true;
                require __DIR__ . "/../views/auth/login.php";
                return;
            }

            // Seguridad de sesión
            session_regenerate_id(true);

            // Sesión definitiva
            $_SESSION["user"] = $_SESSION["tmp_user"];
            $_SESSION["zona_activa"] = [
                "id" => $valida["id"],
                "nombre" => $valida["nombre"]
            ];

            unset($_SESSION["tmp_user"], $_SESSION["tmp_zonas"]);

            header("Location: index.php?url=dashboard");
            exit;
        }

        // ==============================
        // PASO 1: Login normal
        // ==============================
        $usuario  = trim($_POST["usuario"] ?? "");
        $password = trim($_POST["password"] ?? "");

        if ($usuario === "" || $password === "") {
            $_SESSION["login_error"] = "Usuario o contraseña incorrectos.";
            header("Location: index.php?url=login");
            exit;
        }

        // Evitar ataques de fuerza bruta
        if (!isset($_SESSION["login_attempts"])) {
            $_SESSION["login_attempts"] = 0;
        }

        if ($_SESSION["login_attempts"] >= 5) {
            sleep(2); // Anti brute force delay
            $_SESSION["login_error"] = "Demasiados intentos. Intente nuevamente en unos minutos.";
            header("Location: index.php?url=login");
            exit;
        }

        if ($_SESSION["login_attempts"] >= 5) {
            sleep(2);
            $_SESSION["login_error"] = "Demasiados intentos. Intente nuevamente en unos minutos.";
            header("Location: index.php?url=login");
            exit;
        }

        $user = $this->model->login($usuario, $password);

        if (!$user) {
            $_SESSION["login_attempts"]++;
            sleep(1);
            $_SESSION["login_error"] = "Usuario o contraseña incorrectos.";
            header("Location: index.php?url=login");
            exit;
        }

        $_SESSION["login_attempts"] = 0;

        // ==============================
        // Obtener zonas asignadas
        // ==============================
        $zonas = $this->model->getZonasByUsuario($user["id"]);

        if (count($zonas) === 0) {
            $_SESSION["login_error"] = "Usuario sin zonas asignadas.";
            header("Location: index.php?url=login");
            exit;
        }

        // ==============================
        // UNA sola zona → entra directo
        // ==============================
        if (count($zonas) === 1) {

            session_regenerate_id(true);

            $_SESSION["user"] = [
                "id"        => $user["id"],
                "usuario"   => $user["usuario"],
                "nombres"   => $user["nombres"],
                "apellidos" => $user["apellidos"],
                "rol"       => $user["rol"]
            ];

            $_SESSION["zona_activa"] = [
                "id" => $zonas[0]["id"],
                "nombre" => $zonas[0]["nombre"]
            ];

            header("Location: index.php?url=dashboard");
            exit;
        }

        // ==============================
        // MÁS de una zona → mostrar selector
        // ==============================
        $_SESSION["tmp_user"] = [
            "id"        => $user["id"],
            "usuario"   => $user["usuario"],
            "nombres"   => $user["nombres"],
            "apellidos" => $user["apellidos"],
            "rol"       => $user["rol"]
        ];

        $_SESSION["tmp_zonas"] = $zonas;

        $mostrarZonas = true;
        $usuario = $user["usuario"];
        require __DIR__ . "/../views/auth/login.php";
    }

    /* ======================================
       Cerrar sesión
    ====================================== */
    public function logout()
    {
        session_destroy();
        header("Location: index.php?url=login");
        exit;
    }
}