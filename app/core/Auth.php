<?php

class Auth
{
    // tiempo por defecto: 30 min
    public const INACTIVITY_LIMIT = 1800; // 30*60

    public static function enforceInactivityTimeout(int $limitSeconds = self::INACTIVITY_LIMIT): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return; // la sesión debe iniciarse en index.php
        }

        // si no está logueado, no hacemos nada
        if (empty($_SESSION['user'])) {
            return;
        }

        $now = time();

        if (isset($_SESSION['ultima_actividad'])) {
            $inactive = $now - (int)$_SESSION['ultima_actividad'];
            if ($inactive > $limitSeconds) {
                self::logout(true); // true = por timeout
                return;
            }
        }

        // Actualiza actividad (esto se ejecuta en cada request)
        $_SESSION['ultima_actividad'] = $now;
    }

    public static function logout(bool $timeout = false): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();

        // redirige con flag timeout
        if (!headers_sent()) {
            header("Location: index.php?url=login" . ($timeout ? "&timeout=1" : ""));
            exit;
        }
    }

    public static function isLogged(): bool
    {
        return session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['user']);
    }
}