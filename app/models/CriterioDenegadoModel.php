<?php

class CriterioDenegadoModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function listarActivos()
    {
        $sql = "SELECT id, codigo, descripcion
                FROM criterios_denegado
                WHERE estado = 1
                ORDER BY descripcion ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}