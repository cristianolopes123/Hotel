<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hotel Mucinga Nzambi</title>

  <!-- Fonte Poppins -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <!-- Bootstrap CSS Local -->
  <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">

  <!-- Estilos da Navegação -->
  <?php include 'navbar-styles.php'; ?>

  <style>
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
  gap: 40px;
  align-items: center;
  padding: 30px 10px;
  flex-wrap: wrap;
  box-shadow: 0 -1px 5px rgba(0, 0, 0, 0.05);
}

.servico-item {
  text-align: center;
  color: #000;
  width: 120px;
  transition: all 0.3s ease;
  cursor: default;
  padding: 15px 10px;
  border-radius: 10px;
}

.servico-item:hover {
  transform: translateY(-8px);
  background-color: rgba(212, 175, 55, 0.05);
  box-shadow: 0 5px 20px rgba(212, 175, 55, 0.2);
}

.servico-item i {
  font-size: 35px;
  color: #D4AF37;
  margin-bottom: 10px;
  display: block;
  transition: all 0.3s ease;
}

.servico-item:hover i {
  transform: scale(1.2) rotateY(360deg);
  color: #FFC107;
}

.servico-item p {
  margin: 0;
  font-size: 0.95rem;
  font-weight: 500;
  color: #2d2d2d;
  transition: color 0.3s ease;
}

.servico-item:hover p {
  color: #000;
  font-weight: 600;
}

@media (max-width: 768px) {
  .servicos {
    gap: 25px;
    padding: 25px 10px;
  }
  
  .servico-item {
    width: 100px;
  }
  
  .servico-item i {
    font-size: 30px;
  }
  
  .servico-item p {
    font-size: 0.85rem;
  }
}

/* Seção Nossos Quartos */
.nossos-quartos {
  background-color: #F5F1E8;
  padding: 80px 0;
}

.section-title {
  color: #005051;
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 15px;
}

.section-subtitle {
  color: #666;
  font-size: 1.05rem;
  line-height: 1.6;
  max-width: 800px;
  margin: 0 auto;
}

.quarto-card {
  background: #fff;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
  height: 100%;
  display: flex;
  flex-direction: column;
}

.quarto-card:hover {
  transform: translateY(-10px);
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
}

.quarto-img-wrapper {
  position: relative;
  width: 100%;
  height: 180px;
  overflow: hidden;
}

.quarto-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s ease;
}

.quarto-card:hover .quarto-img {
  transform: scale(1.1);
}

.price-badge {
  position: absolute;
  top: 15px;
  right: 15px;
  background: linear-gradient(135deg, #FFC107 0%, #FFD54F 100%);
  color: #000;
  padding: 8px 18px;
  border-radius: 25px;
  font-weight: 700;
  font-size: 0.95rem;
  box-shadow: 0 4px 10px rgba(255, 193, 7, 0.4);
}

.quarto-content {
  padding: 20px;
  flex: 1;
  display: flex;
  flex-direction: column;
}

.quarto-title {
  color: #005051;
  font-size: 1.3rem;
  font-weight: 700;
  margin-bottom: 8px;
}

.quarto-description {
  color: #666;
  font-size: 0.9rem;
  margin-bottom: 18px;
  line-height: 1.4;
}

.quarto-info {
  display: flex;
  justify-content: space-between;
  gap: 10px;
  margin-bottom: 18px;
  padding-bottom: 18px;
  border-bottom: 1px solid #eee;
}

.info-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 5px;
  font-size: 0.85rem;
  color: #333;
  text-align: center;
  flex: 1;
}

.info-item i {
  color: #FFC107;
  font-size: 1.1rem;
}

.info-item span {
  line-height: 1.3;
  font-weight: 500;
}

.comodidades {
  margin-bottom: 18px;
}

.comodidades-title {
  color: #005051;
  font-weight: 600;
  font-size: 0.9rem;
  margin-bottom: 10px;
}

.comodidades-list {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.comodidade-item {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  background-color: #f8f8f8;
  padding: 5px 10px;
  border-radius: 20px;
  font-size: 0.8rem;
  color: #333;
  transition: all 0.3s ease;
  white-space: nowrap;
}

.comodidade-item:hover {
  background-color: #FFC107;
  color: #fff;
  transform: translateY(-2px);
}

.comodidade-item i {
  font-size: 0.85rem;
}

.quarto-buttons {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-top: auto;
}

.btn-detalhes {
  background: linear-gradient(135deg, #FFC107 0%, #FFD54F 100%);
  color: #000;
  font-weight: 600;
  padding: 10px 20px;
  border-radius: 8px;
  text-align: center;
  text-decoration: none;
  transition: all 0.3s ease;
  border: none;
  box-shadow: 0 4px 10px rgba(255, 193, 7, 0.3);
  font-size: 0.9rem;
}

.btn-detalhes:hover {
  background: linear-gradient(135deg, #FFD54F 0%, #FFC107 100%);
  transform: translateY(-2px);
  box-shadow: 0 6px 15px rgba(255, 193, 7, 0.4);
  color: #000;
}

.btn-reservar-quarto {
  background: transparent;
  color: #005051;
  font-weight: 600;
  padding: 10px 20px;
  border-radius: 8px;
  text-align: center;
  text-decoration: none;
  transition: all 0.3s ease;
  border: 2px solid #005051;
  font-size: 0.9rem;
}

.btn-reservar-quarto:hover {
  background: #005051;
  color: #fff;
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(0, 80, 81, 0.3);
}

/* Responsividade */
@media (max-width: 991px) {
  .nossos-quartos {
    padding: 60px 0;
  }

  .section-title {
    font-size: 2rem;
  }

  .section-subtitle {
    font-size: 0.95rem;
  }
}

@media (max-width: 767px) {
  .nossos-quartos {
    padding: 50px 0;
  }

  .section-title {
    font-size: 1.75rem;
  }

  .quarto-info {
    gap: 8px;
  }

  .info-item {
    font-size: 0.8rem;
  }

  .comodidades-list {
    gap: 5px;
  }

  .comodidade-item {
    font-size: 0.75rem;
    padding: 4px 8px;
  }
}


/* <!-- CTA - Não encontrou o quarto ideal? -->*/
.cta-section {
  background: linear-gradient(135deg, #005051 0%, #006b6d 50%, #FFC107 100%);
  padding: 60px 20px;
  margin: 60px auto;
  max-width: 1320px;
  border-radius: 20px;
  position: relative;
  overflow: hidden;
}

.cta-section::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, rgba(0, 80, 81, 0.9) 0%, rgba(255, 193, 7, 0.3) 100%);
  z-index: 1;
}

.cta-content {
  position: relative;
  z-index: 2;
  text-align: center;
  max-width: 800px;
  margin: 0 auto;
}

.cta-title {
  color: #fff;
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 20px;
  text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.2);
}

.cta-subtitle {
  color: #fff;
  font-size: 1.15rem;
  margin-bottom: 30px;
  line-height: 1.6;
  text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.2);
}

.btn-cta {
  background: #fff;
  color: #005051;
  font-weight: 700;
  padding: 15px 40px;
  border-radius: 30px;
  text-decoration: none;
  display: inline-block;
  transition: all 0.3s ease;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
  font-size: 1.1rem;
  border: 3px solid #fff;
}

.btn-cta:hover {
  background: #FFC107;
  color: #000;
  transform: translateY(-3px) scale(1.05);
  box-shadow: 0 8px 30px rgba(255, 193, 7, 0.5);
  border-color: #FFC107;
}

@media (max-width: 768px) {
  .cta-section {
    padding: 60px 20px;
    margin: 40px 0 0 0;
  }

  .cta-title {
    font-size: 1.8rem;
  }

  .cta-subtitle {
    font-size: 1rem;
  }

  .btn-cta {
    padding: 12px 30px;
    font-size: 1rem;
  }
}



  </style>
</head>
<body>

  <!-- Incluir Navegação -->
  <?php include 'navbar.php'; ?>

  <!-- Carrossel -->
  <div id="carouselHotel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="4000">
    <div class="carousel-inner">
      <div class="carousel-item active">
        <img src="../imagens/slide1.jpg" class="d-block w-100" alt="Slide 1">
        <div class="carousel-caption text-white">
          <h1>Bem-vindo ao Hotel<br>Mucinga Nzambi</h1>
          <p>Elegância e conforto no coração de Luanda</p>
          <div class="stars">★★★★★ Hotel 5 Estrelas</div><br>
          <a href="#reservar" class="btn btn-reservar">Reservar Agora</a>
        </div>
      </div>
      <div class="carousel-item">
        <img src="../imagens/slide2.jpg" class="d-block w-100" alt="Slide 2">
        <div class="carousel-caption text-white">
          <h1>Quartos Luxuosos</h1>
          <p>Conforto incomparável para sua estadia perfeita</p>
          <div class="stars">★★★★★ Hotel 5 Estrelas</div><br>
          <a href="#reservar" class="btn btn-reservar">Reservar Agora</a>
        </div>
      </div>
      <div class="carousel-item">
        <img src="../imagens/slide3.jpg" class="d-block w-100" alt="Slide 3">
        <div class="carousel-caption text-white">
          <h1>Experiência Única</h1>
          <p>Serviços premium em um ambiente sofisticado</p>
          <div class="stars">★★★★★ Hotel 5 Estrelas</div><br>
          <a href="#reservar" class="btn btn-reservar">Reservar Agora</a>
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
    <i class="fas fa-map-marker-alt" style="font-size: 35px; color: #D4AF37; margin-bottom: 8px;"></i>
    <p>Centro de Luanda</p>
  </div>
  <div class="servico-item">
    <i class="fas fa-wifi" style="font-size: 35px; color: #D4AF37; margin-bottom: 8px;"></i>
    <p>WiFi Gratuito</p>
  </div>
  <div class="servico-item">
    <i class="fas fa-parking" style="font-size: 35px; color: #D4AF37; margin-bottom: 8px;"></i>
    <p>Estacionamento</p>
  </div>
  <div class="servico-item">
    <i class="fas fa-utensils" style="font-size: 35px; color: #D4AF37; margin-bottom: 8px;"></i>
    <p>Restaurante</p>
  </div>
  <div class="servico-item">
    <i class="fas fa-swimming-pool" style="font-size: 35px; color: #D4AF37; margin-bottom: 8px;"></i>
    <p>Piscina</p>
  </div>
</section>

<!-- Nossos Quartos -->
<section class="nossos-quartos">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="section-title">Nossos Quartos</h2>
      <p class="section-subtitle">Descubra o conforto e a elegância em cada um dos nossos quartos cuidadosamente<br>projetados para proporcionar uma experiência única em Luanda.</p>
    </div>

    <div class="row g-4">
      <!-- Quarto Standard -->
      <div class="col-lg-3 col-md-6">
        <div class="quarto-card">
          <div class="quarto-img-wrapper">
            <img src="../imagens/quarto-standard.jpg" class="quarto-img" alt="Quarto Standard">
            <span class="price-badge">$85/noite</span>
          </div>
          <div class="quarto-content">
            <h3 class="quarto-title">Quarto Standard</h3>
            <p class="quarto-description">Conforto e elegância em um ambiente acolhedor</p>
            
            <div class="quarto-info">
              <div class="info-item">
                <i class="fas fa-user"></i>
                <span>2<br>pessoas</span>
              </div>
              <div class="info-item">
                <i class="fas fa-bed"></i>
                <span>1<br>cama</span>
              </div>
              <div class="info-item">
                <i class="fas fa-expand-arrows-alt"></i>
                <span>25m²</span>
              </div>
            </div>

            <div class="comodidades">
              <p class="comodidades-title">Comodidades:</p>
              <div class="comodidades-list">
                <span class="comodidade-item"><i class="fas fa-wifi"></i> Wifi</span>
                <span class="comodidade-item"><i class="fas fa-tv"></i> TV</span>
                <span class="comodidade-item"><i class="fas fa-snowflake"></i> Ar Condicionado</span>
                <span class="comodidade-item"><i class="fas fa-bath"></i> Banheiro Privativo</span>
              </div>
            </div>

            <div class="quarto-buttons">
              <a href="#" class="btn btn-detalhes">Ver Detalhes</a>
              <a href="#" class="btn btn-reservar-quarto">Reservar Agora</a>
            </div>
          </div>
        </div>
      </div>

      <!-- Quarto Duplo Superior -->
      <div class="col-lg-3 col-md-6">
        <div class="quarto-card">
          <div class="quarto-img-wrapper">
            <img src="../imagens/quarto-duplo.jpg" class="quarto-img" alt="Quarto Duplo Superior">
            <span class="price-badge">$120/noite</span>
          </div>
          <div class="quarto-content">
            <h3 class="quarto-title">Quarto Duplo Superior</h3>
            <p class="quarto-description">Espaço amplo com vista panorâmica da cidade</p>
            
            <div class="quarto-info">
              <div class="info-item">
                <i class="fas fa-user"></i>
                <span>3<br>pessoas</span>
              </div>
              <div class="info-item">
                <i class="fas fa-bed"></i>
                <span>2<br>camas</span>
              </div>
              <div class="info-item">
                <i class="fas fa-expand-arrows-alt"></i>
                <span>35m²</span>
              </div>
            </div>

            <div class="comodidades">
              <p class="comodidades-title">Comodidades:</p>
              <div class="comodidades-list">
                <span class="comodidade-item"><i class="fas fa-wifi"></i> Wifi</span>
                <span class="comodidade-item"><i class="fas fa-tv"></i> TV</span>
                <span class="comodidade-item"><i class="fas fa-snowflake"></i> Ar Condicionado</span>
              </div>
            </div>

            <div class="quarto-buttons">
              <a href="#" class="btn btn-detalhes">Ver Detalhes</a>
              <a href="#" class="btn btn-reservar-quarto">Reservar Agora</a>
            </div>
          </div>
        </div>
      </div>

      <!-- Suite Executiva -->
      <div class="col-lg-3 col-md-6">
        <div class="quarto-card">
          <div class="quarto-img-wrapper">
            <img src="../imagens/suite-executiva.jpg" class="quarto-img" alt="Suite Executiva">
            <span class="price-badge">$200/noite</span>
          </div>
          <div class="quarto-content">
            <h3 class="quarto-title">Suite Executiva</h3>
            <p class="quarto-description">Luxo e sofisticação para hóspedes exigentes</p>
            
            <div class="quarto-info">
              <div class="info-item">
                <i class="fas fa-user"></i>
                <span>4<br>pessoas</span>
              </div>
              <div class="info-item">
                <i class="fas fa-bed"></i>
                <span>1<br>cama</span>
              </div>
              <div class="info-item">
                <i class="fas fa-expand-arrows-alt"></i>
                <span>45m²</span>
              </div>
            </div>

            <div class="comodidades">
              <p class="comodidades-title">Comodidades:</p>
              <div class="comodidades-list">
                <span class="comodidade-item"><i class="fas fa-wifi"></i> Wifi</span>
                <span class="comodidade-item"><i class="fas fa-tv"></i> TV 55"</span>
                <span class="comodidade-item"><i class="fas fa-snowflake"></i> Ar Condicionado</span>
                <span class="comodidade-item"><i class="fas fa-utensils"></i> Sala de Estar</span>
              </div>
            </div>

            <div class="quarto-buttons">
              <a href="#" class="btn btn-detalhes">Ver Detalhes</a>
              <a href="#" class="btn btn-reservar-quarto">Reservar Agora</a>
            </div>
          </div>
        </div>
      </div>

      <!-- Suite Presidencial -->
      <div class="col-lg-3 col-md-6">
        <div class="quarto-card">
          <div class="quarto-img-wrapper">
            <img src="../imagens/suite-presidencial.jpg" class="quarto-img" alt="Suite Presidencial">
            <span class="price-badge">$350/noite</span>
          </div>
          <div class="quarto-content">
            <h3 class="quarto-title">Suite Presidencial</h3>
            <p class="quarto-description">O auge do luxo e conforto para uma experiência inesquecível</p>
            
            <div class="quarto-info">
              <div class="info-item">
                <i class="fas fa-user"></i>
                <span>6<br>pessoas</span>
              </div>
              <div class="info-item">
                <i class="fas fa-bed"></i>
                <span>2<br>camas</span>
              </div>
              <div class="info-item">
                <i class="fas fa-expand-arrows-alt"></i>
                <span>80m²</span>
              </div>
            </div>

            <div class="comodidades">
              <p class="comodidades-title">Comodidades:</p>
              <div class="comodidades-list">
                <span class="comodidade-item"><i class="fas fa-wifi"></i> Wifi</span>
                <span class="comodidade-item"><i class="fas fa-tv"></i> TV 65"</span>
                <span class="comodidade-item"><i class="fas fa-snowflake"></i> Ar Condicionado</span>
                <span class="comodidade-item"><i class="fas fa-utensils"></i> Sala de Jantar</span>
              </div>
            </div>

            <div class="quarto-buttons">
              <a href="#" class="btn btn-detalhes">Ver Detalhes</a>
              <a href="#" class="btn btn-reservar-quarto">Reservar Agora</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CTA - Não encontrou o quarto ideal? -->
<section class="cta-section">
  <div class="container">
    <div class="cta-content">
      <h2 class="cta-title">Não encontrou o quarto ideal?</h2>
      <p class="cta-subtitle">Entre em contato conosco e ajudaremos você a encontrar a opção perfeita para sua estadia.</p>
      <a href="contato.php" class="btn btn-cta">Fale Conosco</a>
    </div>
  </div>
</section>



  <!-- Incluir Footer -->
  <?php include 'footer-styles.php'; ?>
  <?php include 'footer-common.php'; ?>

  <!-- Scripts Comuns -->
  <?php include 'common-scripts.php'; ?>
</body>
</html>
