<?php

class JefesAgenciaController
{
    private $model;
    private $agenciaModel;

    public function __construct()
    {
        $this->model = new JefesAgenciaModel();
        $this->agenciaModel = new AgenciaModel();
    }

    // ============================================================
    // LISTAR JEFES (FILTRADOS POR LA ZONA DEL USUARIO)
    // ============================================================
    public function index()
    {
        // Zona asignada al usuario logueado
        $idZona = $_SESSION["zona_activa"]["id"] ?? null;

        if (!$idZona) {
            exit("Error: el usuario no tiene zona asignada.");
        }

        // Cargar solo agencias de esa zona
        $agencias = $this->agenciaModel->getByZona($idZona);

        $jefes = [];

        // Si el usuario selecciona agencia → cargar jefes
        if (!empty($_GET["agencia"])) {
            $jefes = $this->model->getByAgencia($_GET["agencia"]);
        }

        require __DIR__ . "/../views/jefes/index.php";
    }

    // ============================================================
    // REGISTRAR UN NUEVO JEFE
    // ============================================================
    public function save()
    {
        $data = [
            "apellidos"  => $_POST["apellidos"],
            "nombres"    => $_POST["nombres"],
            "id_agencia" => $_POST["id_agencia"]
        ];

        $this->model->insertar($data);

        header("Location: index.php?url=jefes");
    }

    // ============================================================
    // ELIMINAR JEFE
    // ============================================================
    public function delete()
    {
        $id = $_POST["id"] ?? null;

        if (!$id) {
            echo json_encode(["success" => false]);
            return;
        }

        $ok = $this->model->eliminar($id);

        echo json_encode(["success" => $ok]);
    }
}