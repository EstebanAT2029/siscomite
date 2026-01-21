<?php

class CriterioModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection(); // mysqli
    }

    public function listarActivos()
    {
        $sql = "SELECT id, codigo
                FROM criterios_comite
                WHERE estado = 1
                ORDER BY codigo ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
