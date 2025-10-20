<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contato - Hotel Mucinga Nzambi</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
  
  <!-- Estilos da Navegação -->
  <?php include 'navbar-styles.php'; ?>
  
  <style>
    body {
      background-color: #FDF7E6;
      color: #005051;
    }

    .container {
      max-width: 1200px;
      margin: 40px auto;
      padding: 20px;
    }

    h1 {
      font-size: 40px;
      font-weight: 700;
      text-align: center;
      color: #005051;
      margin-bottom: 10px;
    }

    .subtitulo {
      text-align: center;
      font-size: 16px;
      margin-bottom: 40px;
      color: #444;
    }

    .contato {
      display: flex;
      flex-wrap: wrap;
      gap: 30px;
    }

    .blocos-laterais {
      flex: 1 1 350px;
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    .bloco-info {
      background: white;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      display: flex;
      align-items: flex-start;
      gap: 15px;
      transition: all 0.3s ease;
    }

    .bloco-info:hover {
      background-color: #fef3e3;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .bloco-info i {
      font-size: 28px;
      color: #F28D00;
      margin-top: 4px;
      width: 30px;
    }

    .info-text h3 {
      font-size: 16px;
      margin-bottom: 8px;
    }

    .info-text p {
      font-size: 14px;
      margin: 4px 0;
    }

    .formulario {
      flex: 2 1 600px;
      background: white;
      border-radius: 8px;
      padding: 25px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }

    .formulario h3 {
      margin-bottom: 20px;
      color: #005051;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      margin-bottom: 5px;
      font-weight: 600;
      color: #005051;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
      width: 100%;
      padding: 12px;
      border: 2px solid #e0e0e0;
      border-radius: 5px;
      font-size: 14px;
      transition: all 0.3s ease;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
      border: 2px solid #F28D00;
      outline: none;
      box-shadow: 0 0 0 3px rgba(242, 141, 0, 0.1);
    }

    .btn-enviar {
      background: linear-gradient(135deg, #F28D00 0%, #FFA726 100%);
      color: white;
      padding: 12px 30px;
      border: none;
      border-radius: 25px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(242, 141, 0, 0.3);
    }

    .btn-enviar:hover {
      background: linear-gradient(135deg, #FFA726 0%, #F28D00 100%);
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(242, 141, 0, 0.4);
    }

    @media (max-width: 768px) {
      .contato {
        flex-direction: column;
      }
      
      .formulario {
        order: -1;
      }
    }
  </style>
</head>
<body>

  <!-- Incluir Navegação -->
  <?php include 'navbar.php'; ?>

  <div class="container">
    <h1>Entre em Contato</h1>
    <p class="subtitulo">Estamos aqui para ajudar. Entre em contato conosco!</p>

    <div class="contato">
      <div class="blocos-laterais">
        <div class="bloco-info">
          <i class="fas fa-location-dot"></i>
          <div class="info-text">
            <h3>Endereço</h3>
            <p>Rua Major Kanhangulo, 100<br>Luanda, Angola</p>
          </div>
        </div>

        <div class="bloco-info">
          <i class="fas fa-phone"></i>
          <div class="info-text">
            <h3>Telefone</h3>
            <p>+244 923 456 789<br>+244 923 456 790</p>
          </div>
        </div>

        <div class="bloco-info">
          <i class="fas fa-envelope"></i>
          <div class="info-text">
            <h3>Email</h3>
            <p>info@hotelmucinga.ao<br>reservas@hotelmucinga.ao</p>
          </div>
        </div>

        <div class="bloco-info">
          <i class="fas fa-clock"></i>
          <div class="info-text">
            <h3>Horário de Funcionamento</h3>
            <p>Check-in: 14:00<br>Check-out: 12:00</p>
          </div>
        </div>
      </div>

      <div class="formulario">
        <h3>Envie sua Mensagem</h3>
        <form id="contactForm">
          <div class="form-group">
            <label for="nome">Nome Completo *</label>
            <input type="text" id="nome" name="nome" required>
          </div>

          <div class="form-group">
            <label for="email">Email *</label>
            <input type="email" id="email" name="email" required>
          </div>

          <div class="form-group">
            <label for="telefone">Telefone</label>
            <input type="tel" id="telefone" name="telefone">
          </div>

          <div class="form-group">
            <label for="assunto">Assunto *</label>
            <select id="assunto" name="assunto" required>
              <option value="">Selecione um assunto</option>
              <option value="reserva">Reserva</option>
              <option value="informacao">Informação</option>
              <option value="reclamacao">Reclamação</option>
              <option value="sugestao">Sugestão</option>
              <option value="outro">Outro</option>
            </select>
          </div>

          <div class="form-group">
            <label for="mensagem">Mensagem *</label>
            <textarea id="mensagem" name="mensagem" rows="5" required></textarea>
          </div>

          <button type="submit" class="btn-enviar">Enviar Mensagem</button>
        </form>
      </div>
    </div>
  </div>

  <!-- Incluir Footer -->
  <?php include 'footer-styles.php'; ?>
  <?php include 'footer-common.php'; ?>

  <!-- Scripts Comuns -->
  <?php include 'common-scripts.php'; ?>

  <!-- Script específico do formulário -->
  <script>
    // Form submission
    document.getElementById('contactForm').addEventListener('submit', function(e) {
      e.preventDefault();
      
      const nome = document.getElementById('nome').value;
      const email = document.getElementById('email').value;
      const assunto = document.getElementById('assunto').value;
      const mensagem = document.getElementById('mensagem').value;
      
      if(nome && email && assunto && mensagem) {
        alert('Obrigado pela sua mensagem! Entraremos em contato em breve.');
        document.getElementById('contactForm').reset();
      } else {
        alert('Por favor, preencha todos os campos obrigatórios.');
      }
    });
  </script>
</body>
</html>
