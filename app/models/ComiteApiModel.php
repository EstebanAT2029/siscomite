<?php

class ComiteApiModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAgencias()
    {
        $sql = "SELECT id, nombre_agencia FROM agencia ORDER BY nombre_agencia";
        return $this->db->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    public function getJefesAgencia($id)
    {
        $sql = "SELECT id, apellidos, nombres 
                FROM jefes_agencia 
                WHERE id_agencia = ?
                ORDER BY apellidos";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getOficialesPorAgencia($id)
    {
        $sql = "SELECT id, apellidos, nombres 
                FROM oficiales_negocios 
                WHERE id_agencia = ?
                AND estado = 'Activo'
                ORDER BY apellidos";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    public function getAgenciasPorZona($idZona)
    {
        $sql = "SELECT id, nombre_agencia 
                FROM agencia 
                WHERE id_zona = ?
                ORDER BY nombre_agencia ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $idZona);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }



}
