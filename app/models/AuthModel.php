<?php

class AuthModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection(); // mysqli
    }

    public function login($usuario, $password)
    {
        // =========================================
        // Sanitizar y normalizar
        // =========================================
        $usuario = trim(strtolower($usuario)); // evita ataques por mayúsculas
        $password = trim($password);

        if ($usuario === "" || $password === "") {
            return false;
        }

        // =========================================
        // Consulta segura — SOLO usuario
        // No revelamos si usuario existe
        // =========================================
        $sql = "SELECT id, usuario, nombres, apellidos, password, rol, estado, id_zona
                FROM usuarios 
                WHERE usuario = ? AND estado = 1
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $result = $stmt->get_result();

        // Si no existe usuario → timing attack protection
        if ($result->num_rows === 0) {
            password_verify($password, password_hash("dummy_password", PASSWORD_BCRYPT));
            $stmt->close();
            return false;
        }

        $user = $result->fetch_assoc();

        // =========================================
        // Verificar estado del usuario
        // =========================================
        if ((int)$user["estado"] !== 1) {
            $stmt->close();
            return false;
        }

        // =========================================
        // Verificación segura de contraseña (bcrypt)
        // =========================================
        if (!password_verify($password, $user["password"])) {
            $stmt->close();
            return false;
        }

        $stmt->close();
        return $user;
    }

    /* =========================================================
       NUEVO: Obtener todas las zonas asignadas a un usuario
       Tabla: usuario_zona (id_usuario, id_zona, estado)
       Tabla: zonas (id, nombre)
    ========================================================= */
    public function getZonasByUsuario($userId)
    {
        $userId = (int)$userId;

        $sql = "SELECT z.id, z.nombre
                FROM usuario_zona uz
                INNER JOIN zonas z ON z.id = uz.id_zona
                WHERE uz.id_usuario = ? AND uz.estado = 1
                ORDER BY z.nombre ASC";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return [];

        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $zonas = [];
        while ($row = $result->fetch_assoc()) {
            $zonas[] = $row;
        }

        $stmt->close();
        return $zonas;
    }

    /* =========================================================
       NUEVO: Validar que una zona pertenezca al usuario
       Retorna la zona (id, nombre) si es válida, sino false
    ========================================================= */
    public function zonaPerteneceAUsuario($userId, $zonaId)
    {
        $userId = (int)$userId;
        $zonaId = (int)$zonaId;

        $sql = "SELECT z.id, z.nombre
                FROM usuario_zona uz
                INNER JOIN zonas z ON z.id = uz.id_zona
                WHERE uz.id_usuario = ? AND uz.id_zona = ? AND uz.estado = 1
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param("ii", $userId, $zonaId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $stmt->close();
            return false;
        }

        $zona = $result->fetch_assoc();
        $stmt->close();
        return $zona;
    }
}