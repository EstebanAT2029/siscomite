<?php
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Html as PhpWordHtml;
use Dompdf\Dompdf;
use Dompdf\Options;

class ComiteActaController
{
    private $comiteModel;
    private $detalleModel;
    private $vincModel;
    private $modalidadModel;
    private $agenciaModel;
    private $oficialModel;
    private $jefeModel;

    public function __construct()
    {
        $this->comiteModel    = new ComiteModel();
        $this->detalleModel   = new DetalleComiteModel();
        $this->vincModel      = new RiesgoVinculadoModel();
        $this->modalidadModel = new ModalidadComiteModel();
        $this->agenciaModel   = new AgenciaModel();
        $this->oficialModel   = new OficialesNegociosModel();
        $this->jefeModel      = new JefesAgenciaModel();
    }

    /* ============================================================
       ACTA WORD
    ============================================================ */
    public function actaWord()
    {
        try {

            $idComite  = $_GET["id"] ?? null;
            $idDetalle = $_GET["id_detalle"] ?? null;

            if (!$idComite || !$idDetalle) {
                throw new Exception("Faltan parámetros para generar el Acta en Word.");
            }

            $html = $this->buildActaHtml($idComite, $idDetalle);
            $html = $this->sanitizeHtmlForWord($html);

            $phpWord = new PhpWord();
            $section = $phpWord->addSection();

            PhpWordHtml::addHtml($section, $html, false, false);

            $fileName = "Acta_Comite_{$idComite}.docx";
            $tmp = __DIR__ . "/../../public/tmp/";
            $savePath = $tmp . $fileName;

            if (!is_dir($tmp)) mkdir($tmp, 0777, true);

            $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, "Word2007");
            $writer->save($savePath);

            header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
            header("Content-Disposition: attachment; filename=\"$fileName\"");
            header("Content-Length: " . filesize($savePath));

            readfile($savePath);
            unlink($savePath);
            exit;

        } catch (Exception $e) {
            echo "<b>Error generando acta WORD:</b><br>" . $e->getMessage();
        }
    }

    /* ============================================================
       ACTA PDF
    ============================================================ */
    public function actaPDF()
    {
        try {

            // 🔒 LIMPIAR TODO BUFFER PREVIO
            while (ob_get_level()) {
                ob_end_clean();
            }
            ob_start();

            $idComite  = $_GET["id"] ?? null;
            $idDetalle = $_GET["id_detalle"] ?? null;

            if (!$idComite || !$idDetalle) {
                throw new Exception("Faltan parámetros para generar el PDF.");
            }

            // HTML del acta
            $html = $this->buildActaHtml($idComite, $idDetalle);

            // Limpieza CRÍTICA
            $html = preg_replace('/<meta[^>]+>/i', '', $html);
            $html = preg_replace('/<link[^>]+>/i', '', $html);
            $html = trim($html);

            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $options->set('defaultFont', 'Helvetica');

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $canvas = $dompdf->getCanvas();
            $font = $dompdf->getFontMetrics()->getFont("Helvetica", "normal");
            $canvas->page_text(520, 820, "Página {PAGE_NUM} / {PAGE_COUNT}", $font, 9);


            //LIMPIAR ANTES DE STREAM
            ob_end_clean();

            $dompdf->stream(
                "ACTA DE COMITÉ DE CRÉDITO N° {$idComite}.pdf",
                ["Attachment" => true]
            );
            exit;

        } catch (Throwable $e) {

            //NUNCA HTML AQUÍ
            while (ob_get_level()) {
                ob_end_clean();
            }

            header("Content-Type: text/plain; charset=UTF-8");
            echo "Error generando PDF:\n" . $e->getMessage();
            exit;
        }
    }
    /* ============================================================
       GENERAR HTML COMPLETO DEL ACTA
    ============================================================ */
    private function buildActaHtml($idComite, $idDetalle)
    {
        $comite    = $this->comiteModel->getById($idComite);
        $detalles  = $this->detalleModel->getByComite($idComite);
        $anioActa = date("Y", strtotime($comite["fecha"]));

        if (empty($detalles)) {
            throw new Exception("No existen casos registrados para este comité.");
        }

        // primer caso
        $d0 = $detalles[0];

        $agencia = $this->agenciaModel->getById($d0["id_agencia"]);
        $modal   = $this->modalidadModel->getByComite($idComite);

        // participantes
        $ofProp0 = $this->oficialModel->getById($d0["id_oficial_proponente"]);
        $of1     = $this->oficialModel->getById($d0["id_oficial_participante1"]);
        $of2     = $this->oficialModel->getById($d0["id_oficial_participante2"]);
        $jefe    = $this->jefeModel->getById($d0["id_jefe_agencia"]);

        // nombres
        $nombreProponente = trim(($ofProp0["apellidos"] ?? "") . " " . ($ofProp0["nombres"] ?? ""));
        $of1_nombre       = trim(($of1["apellidos"] ?? "") . " " . ($of1["nombres"] ?? ""));
        $of2_nombre       = trim(($of2["apellidos"] ?? "") . " " . ($of2["nombres"] ?? ""));
        $jefe_nombre      = trim(($jefe["apellidos"] ?? "") . " " . ($jefe["nombres"] ?? ""));
        $riesgo_nombre    = trim($_SESSION["user"]["apellidos"] . " " . $_SESSION["user"]["nombres"]);

        $fechaLarga = $this->convertirFechaLarga($comite["fecha"]);
        


        /* ======================
        TABLA DE CASOS
        ====================== */
        $tablaCasos = "";
    
        foreach ($detalles as $detalle) {

            $ofP = $this->oficialModel->getById($detalle["id_oficial_proponente"]);
            $nombreProponenteRow = trim(($ofP["apellidos"] ?? "") . " " . ($ofP["nombres"] ?? ""));

            // FILA PRINCIPAL DEL CASO
            $tablaCasos .= '
            <tr>
                <td class="col-cadena">'.htmlspecialchars($detalle['cadena']).'</td>
                <td class="col-cliente">'.htmlspecialchars($detalle['nombres']).'</td>
                <td class="col-tipo" style="text-align:center;">'.htmlspecialchars($detalle['tipo_cli']).'</td>
                <td class="col-monto">'.number_format((float)$detalle['monto'], 2).'</td>

                <td class="col-oficial">
                    '.htmlspecialchars($nombreProponenteRow).'
                    <br><i>'.htmlspecialchars($modal['modalidad_proponente'] ?? '').'</i>
                </td>

                <td class="col-tipo_credito">'.htmlspecialchars($detalle['tipo_credito']).'</td>
                <td class="col-criterio">'.htmlspecialchars($detalle['criterio_codigo'] ?? '-').'</td>

                <td class="col-resolucion" style="text-align:center;">'.htmlspecialchars($detalle['decision_desc']).'</td>
            </tr>';

            // FILA DE OBSERVACIONES (DEBAJO DEL CASO)
            if (!empty(trim($detalle['observaciones']))) {
                $tablaCasos .= '
                <tr class="fila-observaciones">
                    <td colspan="8">
                        <b>DETALLE:</b><br>
                        '.nl2br(htmlspecialchars($detalle['observaciones'])).'
                    </td>
                </tr>';
            }
        }



        /* =====================================================
           BLOQUES DE VINCULADOS (UNO POR CADA CASO)
        ===================================================== */
        $bloqueVinculados = "";

        foreach ($detalles as $detalle) {

            $vinc = $this->vincModel->getByDetalle($detalle["id"]);

            if (!empty($vinc)) {

                $filas = "";
                foreach ($vinc as $v) {
                    $filas .= "
                    <tr>
                        <td>{$v["dni"]}</td>
                        <td>{$v["nombres"]}</td>
                        <td>{$v["grado_consanguinidad"]}</td>
                        <td>{$v["domicilio_texto"]}</td>
                        <td>{$v["actividad_texto"]}</td>
                        <td>{$v["predio_texto"]}</td>
                    </tr>";
                }

                $bloqueVinculados .= "
                <h3 style='margin-top:18px;'>Vinculados del Caso {$detalle["cadena"]}</h3>
                <table border='1' cellpadding='5' cellspacing='0' width='100%'>
                    <tr style='background:#eef5e6; font-weight:bold;'>
                        <th>DNI</th>
                        <th>CLIENTE</th>
                        <th>GRADO</th>
                        <th>DIRECCIÓN</th>
                        <th>ACTIVIDAD</th>
                        <th>PREDIO</th>
                    </tr>
                    {$filas}
                </table>";
            }
        }

        /* =====================================================
           REEMPLAZOS DE LA PLANTILLA
        ===================================================== */

        $templatePath = __DIR__ . "/../plantilla/acta_template.html";
        if (!file_exists($templatePath)) {
            throw new Exception("No se encontró plantilla HTML.");
        }

        $html = file_get_contents($templatePath);

        $buscar = [
            '${correlativo}', '${anio}', '${agencia}', '${hora}', '${fecha_larga}', '${numero_casos}',
            '${tabla_casos}', '${bloque_vinculados}', '${tipo_comite}',

            '${oficial_riesgos}', '${modalidad_riesgo}',
            '${jefe_agencia}', '${modalidad_jefe}',
            '${oficial_participante1}', '${modalidad_participante1}',
            '${oficial_participante2}', '${modalidad_participante2}',

            '${oficial_proponente}', '${modalidad_proponente}',
        ];

        $reemplazar = [
            $d0["correlativo"],
            $anioActa,
            $agencia["nombre_agencia"],
            $comite["hora"],
            $fechaLarga,
            $comite["numero_casos"],

            $tablaCasos,
            $bloqueVinculados,

            $modal["tipo_comite"] ?? "",

            $riesgo_nombre,
            $modal["modalidad_riesgo"] ?? "",

            $jefe_nombre,
            $modal["modalidad_jefe"] ?? "",

            $of1_nombre,
            $modal["modalidad_participante1"] ?? "",

            $of2_nombre,
            $modal["modalidad_participante2"] ?? "",

            $nombreProponente,
            $modal["modalidad_proponente"] ?? "",
        ];

        return str_replace($buscar, $reemplazar, $html);
    }

    /* ============================================================
       SANEAR HTML
    ============================================================ */
    private function sanitizeHtmlForWord($html)
    {
        $html = str_replace("<br>", "<br />", $html);
        $html = str_replace("<br/>", "<br />", $html);
        $html = preg_replace('/<(meta|link)(.*?)>/i', '', $html);
        $html = preg_replace('/&(?!#?[a-z0-9]+;)/', '&amp;', $html);
        return $html;
    }

    /* ============================================================
       FECHA LARGA
    ============================================================ */
    private function convertirFechaLarga($fecha)
    {
        $meses = [
            "01" => "enero", "02" => "febrero", "03" => "marzo",
            "04" => "abril", "05" => "mayo", "06" => "junio",
            "07" => "julio", "08" => "agosto", "09" => "setiembre",
            "10" => "octubre", "11" => "noviembre", "12" => "diciembre",
        ];

        $p = explode("-", $fecha);
        return intval($p[2]) . " de " . $meses[$p[1]] . " del " . $p[0];
    }
}