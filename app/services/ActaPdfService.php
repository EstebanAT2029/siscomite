<?php

use PhpOffice\PhpWord\TemplateProcessor;

class ActaPdfService
{
    public function generar($datos, $idComite)
    {
        $enc   = $datos["encabezado"];
        $casos = $datos["casos"];

        $template = new TemplateProcessor(__DIR__ . "/../../plantilla/acta_comite.docx");

        $template->setValue("correlativo", $enc["correlativo"]);
        $template->setValue("hora", substr($enc["hora"], 0, 5));
        $template->setValue("fecha_larga", $this->fechaLarga($enc["fecha"]));
        $template->setValue("agencia", $enc["nombre_agencia"]);
        $template->setValue("numero_casos", $enc["numero_casos"]);

        $template->cloneRow("cadena", count($casos));

        $i = 1;
        foreach ($casos as $c) {
            $template->setValue("cadena#{$i}", $c["cadena"]);
            $template->setValue("cliente#{$i}", $c["nombres"] . "\n" . $c["dni"]);
            $template->setValue("tipo_cli#{$i}", $c["tipo_cli"]);
            $template->setValue("criterio_codigo#{$i}", $c["criterio_codigo"]);
            $template->setValue("tipo_credito#{$i}", $c["tipo_credito"]);
            $template->setValue("monto#{$i}", number_format($c["monto"], 2));
            $template->setValue("resolucion#{$i}", strtoupper($c["decision"]));
            $template->setValue("observaciones#{$i}", $c["observaciones"]);
            $i++;
        }

        $tmpDir = __DIR__ . "/../../tmp";
        if (!is_dir($tmpDir)) mkdir($tmpDir, 0777, true);

        $docxFile = $tmpDir . "/acta_{$idComite}.docx";
        $template->saveAs($docxFile);

        PhpOffice\PhpWord\Settings::setPdfRendererName('MPDF');
        PhpOffice\PhpWord\Settings::setPdfRendererPath(__DIR__ . "/../../vendor/mpdf/mpdf");

        $phpWord = PhpOffice\PhpWord\IOFactory::load($docxFile);
        $pdfFile = $tmpDir . "/acta_{$idComite}.pdf";

        $writer = PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'PDF');
        $writer->save($pdfFile);

        header("Content-Type: application/pdf");
        readfile($pdfFile);
    }

    private function fechaLarga($fecha)
    {
        $meses = [
            "enero","febrero","marzo","abril","mayo","junio",
            "julio","agosto","septiembre","octubre","noviembre","diciembre"
        ];
        return date("d", strtotime($fecha)) . " de " . $meses[date("m", strtotime($fecha))-1] . " del " . date("Y", strtotime($fecha));
    }
}