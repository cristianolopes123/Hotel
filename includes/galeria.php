<?php
/**
 * Página Galeria
 * Hotel Mucinga Nzambi
 */

define('SYSTEM_ACCESS', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/rbac.php';
require_once __DIR__ . '/helpers.php';

?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Galeria - Hotel Mucinga Nzambi</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
  
  <!-- Estilos da Navegação -->
  <?php include 'navbar-styles.php'; ?>
  
  <style>
    body {
      background-color: #FDF7E6;
      color: #005051;
      padding-top: 100px;
    }
    
    .gallery-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 40px 20px;
    }
    
    .page-title {
      text-align: center;
      font-size: 42px;
      color: #005051;
      font-weight: 700;
      margin-bottom: 40px;
    }
    
    .gallery-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 20px;
      margin-top: 30px;
    }
    
    .gallery-item {
      position: relative;
      overflow: hidden;
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      cursor: pointer;
      transition: transform 0.3s ease;
    }
    
    .gallery-item:hover {
      transform: scale(1.05);
    }
    
    .gallery-item img {
      width: 100%;
      height: 250px;
      object-fit: cover;
      display: block;
    }
  </style>
</head>
<body>
  <?php include 'navbar.php'; ?>
  
  <div class="gallery-container">
    <h1 class="page-title">Galeria</h1>
    <p class="text-center text-muted">Em breve, nossa galeria de fotos estará disponível.</p>
  </div>
  
  <?php include 'footer.php'; ?>
  
  <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>

