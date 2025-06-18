<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Footer Hotel</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body {
      margin: 0;
      font-family: 'Poppins', sans-serif;
      background-color: #FDF7E6;
    }

    footer {
      background-color: #005051;
      color: #fff;
      padding: 60px 40px 20px 40px;
    }

    .footer-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 40px;
      max-width: 1200px;
      margin: 0 auto;
    }

    .footer-logo {
      display: flex;
      align-items: flex-start;
      gap: 15px;
    }

    .footer-logo img {
      width: 60px;
      height: 60px;
      object-fit: contain;
    }

    .footer-logo h2 {
      margin: 0;
      font-size: 1.1rem;
      font-weight: 600;
      color: #fff;
    }

    .footer-logo span {
      font-size: 0.9rem;
      color: #ccc;
    }

    .footer-section h3 {
      color: #F28D00;
      font-size: 1rem;
      margin-bottom: 15px;
    }

    .footer-section ul {
      list-style: none;
      padding: 0;
    }

    .footer-section ul li {
      margin-bottom: 10px;
      color: #fff;
      font-size: 0.9rem;
    }

    .footer-section ul li i {
      margin-right: 8px;
      color: #F28D00;
    }

    .social-icons {
      display: flex;
      gap: 15px;
      margin-top: 15px;
    }

    .social-icons a {
      color: #ccc;
      font-size: 1.2rem;
      transition: color 0.3s;
    }

    .social-icons a:hover {
      color: #F28D00;
    }

    .newsletter input[type="email"] {
      padding: 10px;
      width: 100%;
      max-width: 250px;
      border-radius: 5px 0 0 5px;
      border: none;
      outline: none;
    }

    .newsletter button {
      padding: 10px;
      border: none;
      background-color: #F28D00;
      color: #fff;
      border-radius: 0 5px 5px 0;
      cursor: pointer;
    }

    .newsletter {
      display: flex;
      margin-top: 10px;
    }

    .footer-bottom {
      margin-top: 40px;
      text-align: center;
      font-size: 0.8rem;
      color: #ccc;
    }

    @media (max-width: 600px) {
      .footer-logo {
        flex-direction: column;
        align-items: flex-start;
      }
      .newsletter {
        flex-direction: column;
      }
      .newsletter input,
      .newsletter button {
        width: 100%;
        border-radius: 5px;
        margin: 5px 0;
      }
    }
  </style>
</head>
<body>
  <footer>
    <div class="footer-container">
      <div>
        <div class="footer-logo">
          <img src="Captura de Ecrã (147).png" alt="Logo Hotel">
          <div>
            <h2>Hotel Mucinga Nzambi</h2>
            <span>Luxury & Comfort</span>
          </div>
        </div>
        <p style="color:#ccc; font-size: 0.9rem; margin-top: 15px;">Há mais de 25 anos oferecendo a melhor hospitalidade no coração de Luanda, combinando luxo internacional com a calorosa tradição angolana.</p>
        <div class="social-icons">
          <a href="#"><i class="fab fa-facebook-f"></i></a>
          <a href="#"><i class="fab fa-instagram"></i></a>
          <a href="#"><i class="fab fa-twitter"></i></a>
          <a href="#"><i class="fab fa-linkedin-in"></i></a>
        </div>
      </div>

      <div class="footer-section">
        <h3>Links Rápidos</h3>
        <ul>
          <li>Início</li>
          <li>Quartos & Suítes</li>
          <li>Sobre o Hotel</li>
          <li>Contato</li>
          <li>Faça sua Reserva</li>
          <li>Galeria</li>
        </ul>
      </div>

      <div class="footer-section">
        <h3>Serviços</h3>
        <ul>
          <li>Restaurante Gourmet</li>
          <li>Spa & Wellness</li>
          <li>Centro de Fitness</li>
          <li>Salas de Conferência</li>
          <li>Concierge 24h</li>
          <li>Transfer Aeroporto</li>
        </ul>
      </div>

      <div class="footer-section">
        <h3>Contato</h3>
        <ul>
          <li><i class="fas fa-location-dot"></i>Rua Major Kanhangulo, 100, Luanda</li>
          <li><i class="fas fa-phone"></i>+244 923 456 789</li>
          <li><i class="fas fa-phone"></i>+244 923 456 790</li>
          <li><i class="fas fa-envelope"></i>info@hotelmucinga.ao</li>
          <li><i class="fas fa-envelope"></i>reservas@hotelmucinga.ao</li>
        </ul>
        <div class="newsletter">
          <input type="email" id="newsletter-email" placeholder="Seu email">
          <button onclick="subscribe()"><i class="fas fa-check"></i></button>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      <p>© 2024 Hotel Mucinga Nzambi. Todos os direitos reservados. &nbsp; | &nbsp;
        Política de Privacidade &nbsp; | &nbsp; Termos de Uso &nbsp; | &nbsp; Política de Cancelamento
      </p>
    </div>
  </footer>
  <script>
    function subscribe() {
      const email = document.getElementById('newsletter-email').value;
      if(email) {
        alert(`Obrigado por subscrever com o email: ${email}`);
        document.getElementById('newsletter-email').value = '';
      } else {
        alert('Por favor, insira um email válido.');
      }
    }
  </script>
</body>
</html>
