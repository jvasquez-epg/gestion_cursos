<?php
// admin/views/reportes_ver_pdf.php
// Variables esperadas: $pdfUrl, $titulo
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($titulo) ?></title>
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/main.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/palette.css">
  <style>
    .dashboard-main {
      padding: 1.5rem 2rem;
    }

    .pdf-viewer {
      width: 100%;
      height: 85vh;
      border: 1px solid #ccc;
      border-radius: 8px;
    }

    .volver {
      display: inline-block;
      margin-bottom: 1rem;
      padding: 0.4rem 0.8rem;
      background-color: var(--primary-color);
      color: white;
      text-decoration: none;
      border-radius: 6px;
      font-size: 0.95rem;
      transition: background-color 0.2s ease-in-out;
    }

    .volver:hover {
      background-color: var(--primary-dark);
    }

    .volver::before {
      content: "← ";
    }

    h2 {
      margin-bottom: 1rem;
    }
  </style>
</head>
<body>

<?php include __DIR__ . '/../../components/header_main.php'; ?>
<?php include __DIR__ . '/../../components/sidebar.php'; ?>
<?php include __DIR__ . '/../../components/header_user.php'; ?>

<div class="dashboard-main">
  <a href="reportes.php" class="volver">Volver a reportes</a>
  <h2><?= htmlspecialchars($titulo) ?></h2>
  <iframe class="pdf-viewer" src="<?= htmlspecialchars($pdfUrl) ?>" frameborder="0"></iframe>
</div>

<script src="<?= BASE_URL ?>assets/js/mobile-menu.js"></script>
</body>
</html>
