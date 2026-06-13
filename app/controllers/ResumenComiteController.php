<?php
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
    use PhpOffice\PhpSpreadsheet\Style\Fill;
    use PhpOffice\PhpSpreadsheet\Style\Border;
    use PhpOffice\PhpSpreadsheet\Style\Alignment;
class ResumenComiteController
{
    private $model;

    public function __construct()
    {
        $this->model = new ResumenComiteModel();
    }

    public function index()
    {
        require __DIR__ . "/../views/resumen/comites.php";
    }

    /* ======================================
       API ZONAS
    ====================================== */
    public function zonasUsuario()
    {
        header('Content-Type: application/json');

        $idUsuario = $_SESSION["user"]["id"];

        echo json_encode(
            $this->model->getZonasUsuario($idUsuario)
        );
    }

    /* ======================================
       API AGENCIAS
    ====================================== */
    public function agenciasZona()
    {
        header('Content-Type: application/json');

        $zonas = $_GET["zonas"] ?? "";

        if (!$zonas) {
            echo json_encode([]);
            return;
        }

        $zonas = explode(",", $zonas);

        echo json_encode(
            $this->model->getAgenciasPorZonas($zonas)
        );
    }

    /* ======================================
       API OFICIALES
    ====================================== */
    public function oficialesZona()
    {
        header('Content-Type: application/json');

        echo json_encode(
            $this->model->getUsuarios()
        );
    }

    public function data()
    {
        header('Content-Type: application/json');

        $data = json_decode(
            file_get_contents("php://input"),
            true
        );

        echo json_encode(
            $this->obtenerResumen($data)
        );
    }

    private function obtenerResumen($data)
    {
        $zonas = $data["zonas"] ?? [];

        $agencia = $data["agencia"] ?? null;

        $usuario = null;

        if (
            $_SESSION["user"]["rol"] === "admin"
            && !empty($data["usuario"])
        ) {
            $usuario = $data["usuario"];
        }

        $resultado = [];

        foreach ($data["semanas"] as $idx => $semana) {

            $resultado[$idx] =
                $this->model->contarComites(
                    $semana["inicio"],
                    $semana["fin"],
                    $zonas,
                    $agencia,
                    $usuario
                );
        }

        return $resultado;
    }

    private function construirMatriz($resultado)
    {
        $agencias = [];

        foreach ($resultado as $idx => $semana) {

            foreach ($semana as $a) {

                if (!isset($agencias[$a["id"]])) {

                    $agencias[$a["id"]] = [
                        "nombre" => $a["nombre_agencia"],
                        "s1" => 0,
                        "s2" => 0,
                        "s3" => 0,
                        "s4" => 0
                    ];
                }

                $agencias[$a["id"]]["s" . ($idx + 1)]
                    = (int)$a["total"];
            }
        }

        return $agencias;
    }

    public function exportarExcel()
        {
        $data = json_decode(
        $_POST["payload"] ?? "{}",
        true
        );

        $resultado = $this->obtenerResumen($data);

        $agencias = $this->construirMatriz($resultado);

        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('Resumen Comites');

        /* =====================================
        CABECERA
        ===================================== */

        $sheet->mergeCells('A1:F1');
        $sheet->mergeCells('A2:F2');
        $sheet->mergeCells('A3:F3');

        $sheet->setCellValue('A1', 'SISCOMITE');
        $sheet->setCellValue('A2', 'SISTEMA DE COMITÉ DE CRÉDITOS');
        $sheet->setCellValue('A3', 'RESUMEN DE COMITÉS');

        $sheet->setCellValue(
            'A5',
            'Usuario:'
        );

        $sheet->setCellValue(
            'B5',
            $_SESSION["user"]["nombres"] . ' ' .
            ($_SESSION["user"]["apellidos"] ?? '')
        );

        $sheet->setCellValue(
            'D5',
            'Fecha Generación:'
        );

        $sheet->setCellValue(
            'E5',
            date('d/m/Y H:i')
        );

        if (count($data["zonas"]) > 1) {

            $zonaTexto = "Todas";

        } else {

            $zona =
                $this->model->getNombreZona(
                    $data["zonas"][0]
                );

                $zonaTexto =
                $zona["nombre"] ?? "";
        }

        $sheet->setCellValue('A6', 'Zona:');
        $sheet->setCellValue('B6', $zonaTexto);

        $sheet->setCellValue('D6', 'Agencia:');
        $sheet->setCellValue(
            'E6',
            empty($data["agencia"])
                ? 'Todas'
                : $data["agencia"]
        );

        /* =====================================
        TITULOS
        ===================================== */

        $sheet->getStyle('A1')
            ->getFont()
            ->setBold(true)
            ->setSize(18);

        $sheet->getStyle('A2')
            ->getFont()
            ->setBold(true)
            ->setSize(13);

        $sheet->getStyle('A3')
            ->getFont()
            ->setBold(true)
            ->setSize(16);

        $sheet->getStyle('A1:A3')
            ->getAlignment()
            ->setHorizontal(
                Alignment::HORIZONTAL_CENTER
            );

        /* =====================================
        ENCABEZADOS TABLA
        ===================================== */

        $fila = 8;

        $sheet->fromArray(
            [
                'AGENCIA',
                'SEMANA 01',
                'SEMANA 02',
                'SEMANA 03',
                'SEMANA 04',
                'TOTAL'
            ],
            null,
            'A8'
        );

        $sheet->getStyle('A8:F8')
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setRGB('0B6E4F');

        $sheet->getStyle('A8:F8')
            ->getFont()
            ->setBold(true)
            ->getColor()
            ->setRGB('FFFFFF');

        $sheet->getStyle('A8:F8')
            ->getAlignment()
            ->setHorizontal(
                Alignment::HORIZONTAL_CENTER
            );

        $fila++;

        $totalS1 = 0;
        $totalS2 = 0;
        $totalS3 = 0;
        $totalS4 = 0;

        /* =====================================
        DETALLE
        ===================================== */

        foreach ($agencias as $a) {

            $total =
                $a["s1"] +
                $a["s2"] +
                $a["s3"] +
                $a["s4"];

            $sheet->fromArray(
                [
                    $a["nombre"],
                    $a["s1"],
                    $a["s2"],
                    $a["s3"],
                    $a["s4"],
                    $total
                ],
                null,
                'A' . $fila
            );

            $totalS1 += $a["s1"];
            $totalS2 += $a["s2"];
            $totalS3 += $a["s3"];
            $totalS4 += $a["s4"];

            $fila++;
        }

        /* =====================================
        TOTAL
        ===================================== */

        $totalGeneral =
            $totalS1 +
            $totalS2 +
            $totalS3 +
            $totalS4;

        $sheet->fromArray(
            [
                'TOTAL',
                $totalS1,
                $totalS2,
                $totalS3,
                $totalS4,
                $totalGeneral
            ],
            null,
            'A' . $fila
        );

        $sheet->getStyle(
            'A'.$fila.':F'.$fila
        )
        ->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()
        ->setRGB('FFF2CC');

        $sheet->getStyle(
            'A'.$fila.':F'.$fila
        )
        ->getFont()
        ->setBold(true);

        /* =====================================
        FORMATO GENERAL
        ===================================== */

        $sheet->freezePane('A9');

        $sheet->setAutoFilter(
            'A8:F8'
        );

        $sheet->getStyle(
            'A8:F'.$fila
        )
        ->getBorders()
        ->getAllBorders()
        ->setBorderStyle(
            Border::BORDER_THIN
        );

        $sheet->getStyle(
            'B9:F'.$fila
        )
        ->getAlignment()
        ->setHorizontal(
            Alignment::HORIZONTAL_CENTER
        );

        foreach (range('A', 'F') as $col) {

            $sheet
                ->getColumnDimension($col)
                ->setAutoSize(true);
        }

        /* =====================================
        PIE
        ===================================== */

        $firma = $fila + 3;

        $sheet->setCellValue(
            'A'.$firma,
            'Fecha Reporte'
        );

        $sheet->setCellValue(
            'A'.($firma + 1),
            date('d/m/Y H:i:s')
        );

        $nombreArchivo =
            'Resumen_Comites_' .
            date('Ym') .
            '.xlsx';

        header(
            'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );

        header(
            'Content-Disposition: attachment; filename="' .
            $nombreArchivo . '"'
        );

        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);

        $writer->save('php://output');

        exit;
        }

}