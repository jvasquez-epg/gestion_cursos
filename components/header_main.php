<?php
/*
 * components/header_main.php
 * Encabezado principal del sistema con logo institucional y título.
 * Elementos mostrados:
 *   - Logo de FISI (PNG)
 *   - Título descriptivo de la aplicación
 * Uso: Se incluye en todas las vistas administrativas y públicas del sistema.
 * Autor: ASI-GRUPO 5
 * Año: 2025
 */

?>
<header class="header-main">
  <div class="logo-container">
    <img src="<?= BASE_URL ?>assets/img/logo_fisi.png" alt="Logo FISI" class="logo-fisi"  style="max-height:40px; width:auto;">
    <h1 class="app-title">Gestor de cursos de Nivelación y Vacacional - FISI</h1>
  </div>
</header>

