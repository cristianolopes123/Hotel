<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hotel Mucinga Nzambi</title>

  <!-- Fonte Poppins -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">

  <!-- Bootstrap CSS Local -->
  <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">

  <style>
    body {
      font-family: 'Poppins', sans-serif;
      margin: 0;
      padding: 0;
      background-color: #ffffff;
    }

    .top-bar {
      background-color: #FFC107;
      color: #000;
      font-size: 0.9rem;
      padding: 5px 20px;
    }

    .navbar {
      background-color: #fff;
      padding: 8px 20px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
      transition: background-color 0.4s ease;
    }

    .navbar.scrolled {
      background-color: #f1f1f1 !important;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
    }

    .navbar-brand {
      font-weight: bold;
      font-size: 1.1rem;
      color: #000;
    }

    .navbar-nav .nav-link {
      color: #000 !important;
      font-weight: 500;
      margin-right: 10px;
      font-size: 0.95rem;
      transition: color 0.3s ease;
    }

    .navbar-nav .nav-link:hover {
      color: #FFC107 !important;
    }

    .btn-reservar, .btn-login {
      background-color: #FFC107;
      color: #000;
      font-weight: 600;
      border: none;
      padding: 6px 14px;
      margin-left: 8px;
      border-radius: 4px;
      font-size: 0.9rem;
      transition: transform 0.3s ease, background-color 0.3s ease;
    }

    .btn-reservar:hover, .btn-login:hover {
      background-color: #e0ac00;
      transform: scale(1.05);
    }

    .carousel-inner img {
      height: 550px;
      object-fit: cover;
    }

    .carousel-caption {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      text-align: center;
      width: 100%;
    }

    .carousel-caption h1 {
      font-weight: 700;
      font-size: 3.2rem;
      animation: fadeDown 1s ease;
    }

    .carousel-caption p {
      font-size: 1.3rem;
      animation: fadeUp 1.2s ease;
    }

    @keyframes fadeDown {
      from {opacity: 0; transform: translateY(-30px);}
      to {opacity: 1; transform: translateY(0);}
    }

    @keyframes fadeUp {
      from {opacity: 0; transform: translateY(30px);}
      to {opacity: 1; transform: translateY(0);}
    }

    .stars {
      color: #FFC107;
      font-size: 1.2rem;
    }

    .carousel-control-prev-icon,
    .carousel-control-next-icon {
      background-color: rgba(0, 0, 0, 0.3);
      border-radius: 50%;
      width: 36px;
      height: 36px;
    }

    .servicos {
      background: #fff;
      display: flex;
      justify-content: center;
      gap: 30px;
      align-items: center;
      padding: 25px 10px;
      flex-wrap: wrap;
      box-shadow: 0 -1px 5px rgba(0, 0, 0, 0.05);
    }

    .servico-item {
      text-align: center;
      color: #000;
      width: 110px;
    }

    .servico-item img {
      height: 35px;
      margin-bottom: 8px;
      filter: brightness(0) saturate(100%) invert(51%) sepia(89%) saturate(504%) hue-rotate(359deg) brightness(102%) contrast(102%);
    }

    .sticky-top {
      position: sticky;
      top: 0;
      z-index: 1030;
    }
  </style>
</head>
<body>

  <!-- Top Bar -->
  <div class="top-bar d-flex justify-content-between align-items-center">
    <div>
      <span>📍 Luanda, Angola</span>
      <span class="ms-3">📞 +244 923 456 789</span>
      <span class="ms-3">✉️ info@hotelmucinga.ao</span>
    </div>
  </div>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg sticky-top">
    <div class="container-fluid">
      <a class="navbar-brand d-flex align-items-center" href="#">
        <img src="../imagens/logo.png" alt="Logo" width="38" class="me-2">
        <div>
          <strong style="color:#000">Hotel Mucinga Nzambi</strong><br>
          <small style="color:#FFC107">Seu destino em Luanda</small>
        </div>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
        <ul class="navbar-nav align-items-center">
          <li class="nav-item"><a class="nav-link" href="#">Início</a></li>
          <li class="nav-item"><a class="nav-link" href="#">Quartos</a></li>
          <li class="nav-item"><a class="nav-link" href="#">Sobre</a></li>
          <li class="nav-item"><a class="nav-link" href="#">Contato</a></li>
          <li class="nav-item"><a class="btn btn-login" href="#">Login</a></li>
          <li class="nav-item"><a class="btn btn-reservar" href="#">Reservar Agora</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Carrossel -->
  <div id="carouselHotel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="4000">
    <div class="carousel-inner">
      <div class="carousel-item active">
        <img src="../imagens/slide1.jpg" class="d-block w-100" alt="Slide 1">
        <div class="carousel-caption text-white">
          <h1>Bem-vindo ao Hotel<br>Mucinga Nzambi</h1>
          <p>Elegância e conforto no coração de Luanda</p>
          <div class="stars">★★★★★ Hotel 5 Estrelas</div><br>
          <button class="btn btn-reservar">Reservar Agora</button>
        </div>
      </div>
      <div class="carousel-item">
        <img src="../imagens/slide2.jpg" class="d-block w-100" alt="Slide 2">
        <div class="carousel-caption text-white">
          <h1>Quartos Luxuosos</h1>
          <p>Conforto incomparável para sua estadia perfeita</p>
          <div class="stars">★★★★★ Hotel 5 Estrelas</div><br>
          <button class="btn btn-reservar">Reservar Agora</button>
        </div>
      </div>
      <div class="carousel-item">
        <img src="../imagens/slide3.jpg" class="d-block w-100" alt="Slide 3">
        <div class="carousel-caption text-white">
          <h1>Experiência Única</h1>
          <p>Serviços premium em um ambiente sofisticado</p>
          <div class="stars">★★★★★ Hotel 5 Estrelas</div><br>
          <button class="btn btn-reservar">Reservar Agora</button>
        </div>
      </div>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#carouselHotel" data-bs-slide="prev">
      <span class="carousel-control-prev-icon"></span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#carouselHotel" data-bs-slide="next">
      <span class="carousel-control-next-icon"></span>
    </button>
  </div>

  <!-- Serviços -->
  <section class="servicos">
    <div class="servico-item">
      <img src="icons/localizacao.svg" alt="Localização">
      <p>Centro de Luanda</p>
    </div>
    <div class="servico-item">
      <img src="icons/wifi.svg" alt="Wi-Fi">
      <p>WiFi Gratuito</p>
    </div>
    <div class="servico-item">
      <img src="icons/estacionamento.svg" alt="Estacionamento">
      <p>Estacionamento</p>
    </div>
    <div class="servico-item">
      <img src="icons/restaurante.svg" alt="Restaurante">
      <p>Restaurante</p>
    </div>
    <div class="servico-item">
      <img src="icons/piscina.svg" alt="Piscina">
      <p>Piscina</p>
    </div>
  </section>

  <!-- Bootstrap JS Local -->
  <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>

  <!-- Navbar scroll animation -->
  <script>
    window.addEventListener("scroll", function () {
      var navbar = document.querySelector(".navbar");
      navbar.classList.toggle("scrolled", window.scrollY > 50);
    });
  </script>
</body>
</html>
