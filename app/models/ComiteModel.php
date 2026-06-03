<?php

class ComiteModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function insertarComite($data)
    {
        $fecha        = $data["fecha"];
        $hora         = $data["hora"];
        $numeroCasos  = $data["numero_casos"];
        $idUsuario    = $data["id_usuario"];
        $idZona       = $data["id_zona"] ?? null;
        $idAgencia    = $data["id_agencia"];

        $sql = "
            INSERT INTO comite (
                fecha,
                hora,
                numero_casos,
                id_usuario,
                id_zona,
                id_agencia
            )
            VALUES (?, ?, ?, ?, ?, ?)
        ";

        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            throw new Exception(
                "Error preparando insertarComite: " .
                $this->db->error
            );
        }

        $stmt->bind_param(
            "ssiiii",
            $fecha,
            $hora,
            $numeroCasos,
            $idUsuario,
            $idZona,
            $idAgencia
        );

        if (!$stmt->execute()) {
            throw new Exception(
                "Error ejecutando insertarComite: " .
                $stmt->error
            );
        }

        return $stmt->insert_id;
    }

    public function getById($id)
    {
        $sql = "SELECT * FROM comite WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}