<?php

class OficialesNegociosController
{
    private $model;
    private $agenciaModel;

    public function __construct()
    {
        $this->model = new OficialesNegociosModel();
        $this->agenciaModel = new AgenciaModel();
    }

    // ============================================================
    // LISTADO FILTRADO POR ZONA DEL USUARIO
    // ============================================================
    public function index()
    {
        // 🔥 Zona asignada al usuario logueado
        $idZona = $_SESSION["zona_activa"]["id"] ?? null;

        if (!$idZona) {
            exit("Error: El usuario no tiene zona asignada.");
        }

        // 🔥 Cargar solo agencias de esa zona
        $agencias = $this->agenciaModel->getByZona($idZona);

        $oficiales = [];

        // Si selecciona la agencia → cargar oficiales
        if (!empty($_GET["agencia"])) {
            $oficiales = $this->model->getByAgencia($_GET["agencia"]);
        }

        require __DIR__ . "/../views/oficiales/index.php";
    }

    // ============================================================
    // REGISTRAR NUEVO OFICIAL
    // ============================================================
    public function save()
    {
        header("Content-Type: application/json");

        try {
            // 🟢 Leer JSON
            $data = json_decode(file_get_contents("php://input"), true);

            if (
                empty($data["apellidos"]) ||
                empty($data["nombres"]) ||
                empty($data["cargo"]) ||
                empty($data["id_agencia"])
            ) {
                throw new Exception("Datos incompletos");
            }

            $insert = [
                "apellidos"  => strtoupper(trim($data["apellidos"])),
                "nombres"    => strtoupper(trim($data["nombres"])),
                "cargo"      => trim($data["cargo"]),
                "id_agencia" => (int) $data["id_agencia"]
            ];

            $this->model->insertar($insert);

            echo json_encode([
                "success" => true
            ]);
            exit;

        } catch (Exception $e) {
            echo json_encode([
                "success" => false,
                "error"   => $e->getMessage()
            ]);
            exit;
        }
    }

    // ============================================================
    // CAMBIAR ESTADO (Activo / Inactivo)
    // ============================================================
    public function estado()
    {
        $id     = $_POST["id"] ?? null;
        $estado = $_POST["estado"] ?? null;

        if (!$id || !$estado) {
            echo json_encode(["success" => false, "error" => "Datos incompletos"]);
            return;
        }

        $ok = $this->model->cambiarEstado($id, $estado);
        echo json_encode(["success" => $ok]);
    }

}