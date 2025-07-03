<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpWord\TemplateProcessor;

$tpl = new TemplateProcessor(
    __DIR__ . '/../assets/templates/Solicitud_Apertura_Curso.docx'
);

header('Content-Type: application/json');
echo json_encode($tpl->getVariables(), JSON_PRETTY_PRINT);
