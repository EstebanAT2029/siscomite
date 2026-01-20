<?php
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReporteController
{
    private $model;

    public function __construct()
    {
        $this->model = new ReporteModel();
    }

    /**
     * Mostrar formulario de filtros + resultados
     * FILTRADO POR ZONA DEL USUARIO
     */
    public function index()
    {
        requireLogin();

        // 🔐 Zona del usuario logueado
        $idZona = $_SESSION["zona_activa"]["id"];

        // Solo agencias de su zona
        $agencias = $this->model->agencias($idZona);

        // ==========================
        // PAGINACIÓN
        // ==========================
        $page  = max(1, (int)($_GET["page"] ?? 1));
        $limit = 10; // cambia a 15/20 si deseas
        $offset = ($page - 1) * $limit;

        $resultados = [];
        $total = 0;
        $totalPages = 1;

        if (!empty($_GET["agencia"])) {

            $idAgencia = (int)$_GET["agencia"];

            // Total para paginar
            $total = $this->model->countReportePorAgencia($idAgencia, $idZona);
            $totalPages = max(1, (int)ceil($total / $limit));

            // Ajuste si alguien pone page muy alto
            if ($page > $totalPages) {
                $page = $totalPages;
                $offset = ($page - 1) * $limit;
            }

            // Resultados paginados
            $resultados = $this->model->reportePorAgenciaPaginado(
                $idAgencia,
                $idZona,
                $limit,
                $offset
            );
        }

        require __DIR__ . "/../views/reportes/reportes.php";
    }

    /**
     * REPORTE GENERAL EXCEL (FILTRADO POR ZONA)
     */
    public function reporteGeneralExcel()
    {
        requireLogin();

        // 🔐 Zona del usuario
        $idZona = $_SESSION["zona_activa"]["id"];

        $data = $this->model->reporteGeneral($idZona);

        if (empty($data)) {
            echo "No hay datos para exportar.";
            return;
        }

        // 1. Crear Excel
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        // 2. Encabezados
        $headers = array_keys($data[0]);
        $sheet->fromArray($headers, null, 'A1');

        // 3. Datos
        $row = 2;
        foreach ($data as $fila) {
            $values = [];
            foreach ($headers as $h) {
                $values[] = $fila[$h];
            }
            $sheet->fromArray($values, null, 'A' . $row);
            $row++;
        }

        // 4. Nombre del archivo
        $filename = "Reporte_General_Comite_" . date("Ymd_His") . ".xlsx";

        // 5. Headers HTTP
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header('Cache-Control: max-age=0');
        header('Expires: 0');
        header('Pragma: public');

        // 6. Limpiar buffer
        if (ob_get_length()) {
            ob_end_clean();
        }

        // 7. Descargar
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}