<?php
/**
 * Página Sobre
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
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sobre o Hotel Mucinga Nzambi</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
  
  <!-- Estilos da Navegação -->
  <?php include 'navbar-styles.php'; ?>
  
  <style>
    body {
      background: linear-gradient(to bottom, #ffffff, #fdf7e6);
      color: #005051;
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 40px 20px;
    }

    h1 {
      text-align: center;
      font-size: 42px;
      color: #005051;
      font-weight: 700;
    }

    .subtitulo {
      text-align: center;
      font-size: 18px;
      margin-top: 10px;
      color: #444;
    }

    .section {
      margin-top: 50px;
    }

    .section h2 {
      font-size: 24px;
      color: #005051;
      font-weight: 700;
    }

    .historia-container {
      display: flex;
      flex-wrap: wrap;
      gap: 40px;
      align-items: flex-start;
      margin-top: 30px;
    }

    .historia-text {
      flex: 1 1 55%;
      font-size: 16px;
      line-height: 1.6;
    }

    .historia-img {
      flex: 1 1 40%;
    }

    .historia-img img {
      width: 100%;
      border-radius: 10px;
      box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }

    .comodidades-estatisticas {
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
      gap: 40px;
      margin-top: 40px;
    }

    .comodidades-wrapper {
      flex: 1 1 55%;
    }

    .estatisticas {
      flex: 1 1 40%;
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 20px;
    }

    .comodidades ul {
      margin-top: 15px;
      padding: 0;
      font-size: 16px;
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 10px 20px;
    }

    .comodidades li {
      list-style: none;
      padding: 8px 0;
      position: relative;
      padding-left: 25px;
    }

    .comodidades li:before {
      content: "✓";
      position: absolute;
      left: 0;
      color: #F28D00;
      font-weight: bold;
    }

    .estatistica-item {
      background: white;
      padding: 20px;
      border-radius: 10px;
      text-align: center;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .estatistica-numero {
      font-size: 2.5rem;
      font-weight: 700;
      color: #F28D00;
      display: block;
    }

    .estatistica-texto {
      font-size: 0.9rem;
      color: #666;
      margin-top: 5px;
    }

    .missao-valores {
      display: flex;
      flex-wrap: wrap;
      gap: 40px;
      margin-top: 40px;
    }

    .missao, .valores {
      flex: 1 1 45%;
      background: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .valores ul {
      list-style: none;
      padding: 0;
    }

    .valores li {
      padding: 10px 0;
      border-bottom: 1px solid #eee;
    }

    .valores li:last-child {
      border-bottom: none;
    }

    @media (max-width: 768px) {
      .historia-container,
      .comodidades-estatisticas,
      .missao-valores {
        flex-direction: column;
      }
      
      .estatisticas {
        grid-template-columns: 1fr;
      }
      
      .comodidades ul {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>

  <!-- Incluir Navegação -->
  <?php include 'navbar.php'; ?>

  <div class="container">
    <h1>Sobre o Hotel Mucinga Nzambi</h1>
    <p class="subtitulo">Descubra nossa história e compromisso com a excelência</p>

    <div class="section">
      <h2>Nossa História</h2>
      <div class="historia-container">
        <div class="historia-text">
          <p>Fundado em 1998, o Hotel Mucinga Nzambi nasceu da visão de criar um espaço de hospedagem que combinasse o luxo internacional com a calorosa tradição angolana. Localizado no coração de Luanda, nosso hotel tem sido um marco de excelência na hospitalidade por mais de 25 anos.</p>
          <p>O nome "Mucinga Nzambi" significa "Presente de Deus" em kimbundu, refletindo nossa missão de oferecer uma experiência divina aos nossos hóspedes. Desde a sua inauguração, o hotel tem sido palco de importantes eventos políticos, empresariais e culturais da capital angolana.</p>
          <p>Com uma equipe dedicada e apaixonada, continuamos a evoluir e inovar, sempre mantendo os mais altos padrões de qualidade e serviço que nos tornaram referência em Luanda.</p>
        </div>
        <div class="historia-img">
          <img src="../imagens/slide1.jpg" alt="Exterior do Hotel">
        </div>
      </div>
    </div>

    <div class="section">
      <h2>Comodidades e Estatísticas</h2>
      <div class="comodidades-estatisticas">
        <div class="comodidades-wrapper">
          <h3>Nossas Comodidades</h3>
          <div class="comodidades">
            <ul>
              <li>150 quartos e suítes luxuosas</li>
              <li>Restaurante gourmet com culinária local e internacional</li>
              <li>Spa e centro de bem-estar completo</li>
              <li>Piscina ao ar livre com vista para a cidade</li>
              <li>Centro de fitness 24 horas</li>
              <li>Salas de conferência e eventos</li>
              <li>Concierge 24 horas</li>
              <li>Serviço de transfer para o aeroporto</li>
              <li>Wi-Fi gratuito em todas as áreas</li>
              <li>Estacionamento seguro</li>
            </ul>
          </div>
        </div>
        <div class="estatisticas">
          <div class="estatistica-item">
            <span class="estatistica-numero">150</span>
            <span class="estatistica-texto">Quartos e Suítes</span>
          </div>
          <div class="estatistica-item">
            <span class="estatistica-numero">25</span>
            <span class="estatistica-texto">Anos de Experiência</span>
          </div>
          <div class="estatistica-item">
            <span class="estatistica-numero">98%</span>
            <span class="estatistica-texto">Satisfação dos Hóspedes</span>
          </div>
          <div class="estatistica-item">
            <span class="estatistica-numero">24h</span>
            <span class="estatistica-texto">Serviço Disponível</span>
          </div>
        </div>
      </div>
    </div>

    <div class="section">
      <h2>Missão e Valores</h2>
      <div class="missao-valores">
        <div class="missao">
          <h3>Nossa Missão</h3>
          <p>Proporcionar experiências memoráveis aos nossos hóspedes, oferecendo serviços de excelência em um ambiente sofisticado e acolhedor, contribuindo para o desenvolvimento do turismo em Angola.</p>
        </div>
        <div class="valores">
          <h3>Nossos Valores</h3>
          <ul>
            <li><strong>Excelência:</strong> Buscamos sempre a perfeição em tudo que fazemos</li>
            <li><strong>Hospitalidade:</strong> Tratamos cada hóspede como família</li>
            <li><strong>Inovação:</strong> Constantemente melhoramos nossos serviços</li>
            <li><strong>Sustentabilidade:</strong> Comprometidos com o meio ambiente</li>
            <li><strong>Comunidade:</strong> Valorizamos e apoiamos nossa comunidade local</li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <!-- Incluir Footer -->
  <?php include 'footer-styles.php'; ?>
  <?php include 'footer-common.php'; ?>

  <!-- Scripts Comuns -->
  <?php include 'common-scripts.php'; ?>
</body>
</html>
