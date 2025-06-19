<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sobre o Hotel Mucinga Nzambi</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body {
      margin: 0;
      font-family: 'Poppins', sans-serif;
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
      position: relative;
      padding-left: 20px;
    }

    .comodidades li::before {
      content: "\f111";
      font-family: "Font Awesome 6 Free";
      font-weight: 900;
      color: #F28D00;
      position: absolute;
      left: 0;
      font-size: 8px;
      top: 7px;
    }

    .estat-box {
      background: white;
      border-radius: 10px;
      padding: 15px;
      text-align: center;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      transition: transform 0.3s, background 0.3s;
    }

    .estat-box:hover {
      background: #f4f4f4;
      transform: translateY(-5px);
    }

    .estat-box i {
      font-size: 24px;
      color: #F28D00;
      margin-bottom: 10px;
    }

    .estat-box .numero {
      font-size: 22px;
      font-weight: 700;
      color: #005051;
    }

    .estat-box .descricao {
      font-size: 13px;
      color: #444;
    }

    .localizacao {
      display: flex;
      flex-wrap: wrap;
      gap: 40px;
      margin-top: 50px;
      align-items: flex-start;
    }

    .local-info {
      flex: 1 1 50%;
    }

    .local-info h2 {
      font-size: 22px;
      color: #005051;
    }

    .local-info p {
      margin-bottom: 10px;
      font-size: 15px;
    }

    .local-info i {
      color: #F28D00;
      margin-right: 8px;
    }

    .mapa {
      flex: 1 1 45%;
      position: relative;
    }

    .mapa iframe {
      width: 100%;
      height: 300px;
      border: none;
      border-radius: 10px;
      box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }

    .mapa button {
      position: absolute;
      bottom: 10px;
      right: 10px;
      background: #F28D00;
      color: white;
      border: none;
      padding: 8px 16px;
      border-radius: 5px;
      cursor: pointer;
      font-weight: 600;
    }

    @media (max-width: 768px) {
      .historia-container,
      .localizacao,
      .comodidades-estatisticas,
      .estatisticas {
        flex-direction: column;
      }

      .comodidades ul {
        grid-template-columns: 1fr;
      }

      .estatisticas {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>Sobre o Hotel Mucinga Nzambi</h1>
    <p class="subtitulo">Há mais de duas décadas oferecendo hospitalidade excepcional no coração de Luanda,<br>combinando tradição angolana com luxo internacional.</p>

    <div class="section historia-container">
      <div class="historia-text">
        <h2>Nossa História</h2>
        <p>Fundado em 1998, o Hotel Mucinga Nzambi nasceu do sonho de criar um refúgio de luxo que celebrasse a rica cultura angolana. Localizado estrategicamente no centro de Luanda, nosso hotel se tornou um marco de hospitalidade e elegância.</p>
        <p>Com arquitetura que mistura elementos contemporâneos e tradicionais, oferecemos uma experiência única que reflete o melhor de Angola para visitantes de todo o mundo.</p>
        <h2>Nossa Missão</h2>
        <p>Proporcionar experiências inesquecíveis através de serviços excepcionais, acomodações luxuosas e um atendimento caloroso que reflete a essência da hospitalidade angolana, superando sempre as expectativas de nossos hóspedes.</p>
      </div>
      <div class="historia-img">
        <img src="../img/pic.jpg" alt="Fachada do Hotel">
      </div>
    </div>

    <div class="section comodidades-estatisticas">
      <div class="comodidades-wrapper">
        <h2>Comodidades do Hotel</h2>
        <ul class="comodidades">
          <li>Restaurante Gourmet</li>
          <li>Spa & Wellness Center</li>
          <li>Salas de Conferência</li>
          <li>Wifi Gratuito</li>
          <li>Serviço de Quarto 24h</li>
          <li>Centro de Negócios</li>
          <li>Piscina Infinity</li>
          <li>Centro de Fitness</li>
          <li>Concierge 24h</li>
          <li>Estacionamento Valet</li>
          <li>Bar no Terraço</li>
          <li>Transfer Aeroporto</li>
        </ul>
      </div>
      <div class="estatisticas">
        <div class="estat-box">
          <i class="fas fa-calendar-check"></i>
          <div class="numero">25+</div>
          <div class="descricao">Anos de Experiência</div>
        </div>
        <div class="estat-box">
          <i class="fas fa-user-group"></i>
          <div class="numero">50K+</div>
          <div class="descricao">Hóspedes Satisfeitos</div>
        </div>
        <div class="estat-box">
          <i class="fas fa-award"></i>
          <div class="numero">15+</div>
          <div class="descricao">Prêmios Recebidos</div>
        </div>
        <div class="estat-box">
          <i class="fas fa-star"></i>
          <div class="numero">4.9</div>
          <div class="descricao">Avaliação Média</div>
        </div>
      </div>
    </div>

    <div class="section localizacao">
      <div class="local-info">
        <h2>Nossa Localização</h2>
        <p><i class="fas fa-map-marker-alt"></i> Rua Major Kanhangulo, 100<br> Ingombota, Luanda, Angola</p>
        <p><i class="fas fa-phone"></i> +244 923 456 789<br>+244 923 456 790</p>
        <p><i class="fas fa-envelope"></i> info@hotelmucinga.ao<br> reservas@hotelmucinga.ao</p>
        <p><i class="fas fa-clock"></i> Check-in: 15:00 | Check-out: 12:00</p>
      </div>
      <div class="mapa">
        <iframe id="mapa" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3970.8031550384317!2d13.240197774945413!3d-8.816733891173132!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x1a51f3ff5c83cd8f%3A0x1e2e32813f236353!2sLuanda%2C%20Angola!5e0!3m2!1spt-PT!2sao!4v1718735804081!5m2!1spt-PT!2sao" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        <button onclick="obterRotas()">Obter Rotas</button>
      </div>
    </div>
  </div>
  <script>
    const numeros = document.querySelectorAll('.numero');
    numeros.forEach((num) => {
      const final = +num.textContent.replace(/\D/g, '');
      let atual = 0;
      const incremento = Math.ceil(final / 100);
      const animar = setInterval(() => {
        atual += incremento;
        if (atual >= final) {
          num.textContent = num.textContent.includes('K') ? final + 'K+' : final + (num.textContent.includes('.') ? '' : '+');
          clearInterval(animar);
        } else {
          num.textContent = atual;
        }
      }, 30);
    });

    function obterRotas() {
      const url = 'https://www.google.com/maps/dir/?api=1&destination=-8.816733891173132,13.240197774945413';
      window.open(url, '_blank');
    }
  </script>
</body>
</html>
