<?php
/*
 * Clase DocumentGenerator
 * 
 * Genera un documento PDF a partir de una plantilla DOCX, 
 * inserta un código QR con información codificada y convierte
 * el archivo final a PDF para su descarga o almacenamiento.
 * 
 * Usa PhpWord para manipulación de DOCX y DomPDF para PDF.
 * Código QR generado con chillerlan\QRCode.
 * 
 * Autor: ASI-GRUPO 5
 * Año: 2025
 */

declare(strict_types=1);

require_once __DIR__.'/../../vendor/autoload.php';

use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\Settings;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class DocumentGenerator
{
    private string $template;

    public function __construct()
    {
        $this->template = __DIR__.'/../../assets/templates/Solicitud_Apertura_Curso.docx';
    }

    /** Genera PDF con QR */
    public function generar(array $vars): string
    {
        $tpl = new TemplateProcessor($this->template);

        /* ───── Generar QR ───── */
        $qrData = [
            'nombre' => $vars['NOMBRE'],
            'codigo' => $vars['CODIGO'],
            'dni'    => $vars['DNI'],
            'curso'  => $vars['COD_CURSO'],
            'fecha'  => $vars['FECHA'],
            'hora'   => $vars['HORA'],
        ];

        $qrTemp = tempnam(sys_get_temp_dir(), 'qr_').'.png';
        $opts   = new QROptions(['outputType'=>QRCode::OUTPUT_IMAGE_PNG,'scale'=>4]);
        (new QRCode($opts))->render(json_encode($qrData,JSON_UNESCAPED_UNICODE), $qrTemp);

        /* Inyectar QR */
        $tpl->setImageValue('QR_FIRMA', [
            'path'=>$qrTemp,'width'=>150,'height'=>150,'ratio'=>true
        ]);

        /* Sustituir texto (omitimos campo hash que ya va dentro del QR) */
        foreach ($vars as $k=>$v){
            if($k==='FIRMA_HASH') continue;
            $tpl->setValue($k,$v);
        }

        /* DOCX → PDF */
        $tmpDocx = tempnam(sys_get_temp_dir(),'sol_').'.docx';
        $tpl->saveAs($tmpDocx);

        Settings::setPdfRendererName(Settings::PDF_RENDERER_DOMPDF);
        Settings::setPdfRendererPath(__DIR__.'/../../vendor/dompdf/dompdf');

        $phpWord = \PhpOffice\PhpWord\IOFactory::load($tmpDocx);
        $tmpPdf  = tempnam(sys_get_temp_dir(),'sol_').'.pdf';
        \PhpOffice\PhpWord\IOFactory::createWriter($phpWord,'PDF')->save($tmpPdf);

        $pdf = file_get_contents($tmpPdf);

        @unlink($qrTemp);
        @unlink($tmpDocx);
        @unlink($tmpPdf);

        return $pdf;
    }
}
