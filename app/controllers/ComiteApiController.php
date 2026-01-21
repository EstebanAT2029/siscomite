<?php

class ComiteApiController
{
    private $model;

    public function __construct()
    {
        // Cargar el modelo correcto
        $this->model = new ComiteApiModel();
    }

    /* ================================
       LISTAR AGENCIAS
    
    public function agencias()
    {
        header("Content-Type: application/json");

        echo json_encode($this->model->getAgencias());
    }
        ================================= */
    public function agencias()
    {
        header("Content-Type: application/json");

        // Obtener zona asignada al usuario logueado
        $idZona = $_SESSION["zona_activa"]["id"] ?? null;

        if (!$idZona) {
            echo json_encode([]);
            return;
        }

        // Traer solo las agencias de su zona
        $agencias = $this->model->getAgenciasPorZona($idZona);
        echo json_encode($agencias);
    }

    /* ============================================================
       LISTAR JEFES POR AGENCIA
    ============================================================ */
    public function jefes()
    {
        header('Content-Type: application/json');

        if (!isset($_GET["agencia_id"])) {
            echo json_encode(["error" => "agencia_id requerido"]);
            return;
        }

        $id = intval($_GET["agencia_id"]);
        $jefes = $this->model->getJefesAgencia($id);

        // NORMALIZACIÓN: agregar campo "nombre"
        foreach ($jefes as &$j) {
            $j["nombre"] = trim($j["apellidos"] . " " . $j["nombres"]);
        }

        echo json_encode($jefes);
    }

    /* ================================
       LISTAR OFICIALES POR AGENCIA
    ================================= */
    /* ============================================================
       LISTAR OFICIALES POR AGENCIA
    ============================================================ */
    public function oficiales()
    {
        header('Content-Type: application/json');

        if (!isset($_GET["agencia_id"])) {
            echo json_encode(["error" => "agencia_id requerido"]);
            return;
        }

        $id = intval($_GET["agencia_id"]);
        $oficiales = $this->model->getOficialesPorAgencia($id);

        // NORMALIZACIÓN: agregar campo "nombre"
        foreach ($oficiales as &$o) {
            $o["nombre"] = trim($o["apellidos"] . " " . $o["nombres"]);
        }

        echo json_encode($oficiales);
    }
    public function criterios()
    {
        requireLogin();
        $m = new CriterioModel();
        header("Content-Type: application/json; charset=utf-8");
        echo json_encode($m->listarActivos());
    }


}