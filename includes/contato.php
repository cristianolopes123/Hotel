<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contato - Hotel Mucinga Nzambi</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body {
      margin: 0;
      font-family: 'Poppins', sans-serif;
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
/* Animação ao passar o mouse nas caixas de contato */
.bloco-info {
  transition: background-color 0.3s ease, transform 0.3s ease;
}

.bloco-info:hover {
  background-color: #fef3e3;
  transform: translateY(-2px);
}

/* Realce dos inputs, selects e textarea ao foco */
input:focus,
select:focus,
textarea:focus {
  border: 2px solid #F28D00;
  outline: none;
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

    .formulario h2 {
      font-size: 22px;
      margin-bottom: 5px;
    }

    .formulario p {
      font-size: 14px;
      margin-bottom: 20px;
    }

    .form-row {
      display: flex;
      gap: 20px;
      margin-bottom: 15px;
      flex-wrap: wrap;
    }

    .form-group {
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    label {
      font-size: 14px;
      margin-bottom: 5px;
    }

    input, select, textarea {
      padding: 10px 12px;
      border: 1px solid #005051;
      border-radius: 6px;
      font-size: 14px;
      font-family: 'Poppins', sans-serif;
    }

    textarea {
      height: 120px;
      resize: none;
    }

    .btn {
      background: #F28D00;
      color: white;
      border: none;
      padding: 12px 25px;
      border-radius: 6px;
      font-size: 14px;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      transition: background 0.3s ease;
    }

    .btn i {
      font-size: 16px;
    }

    .btn:hover {
      background: #d67c00;
    }

    .rodape-ajuda {
      background: linear-gradient(to right, #005051, #F28D00);
      color: white;
      padding: 25px;
      text-align: center;
      margin-top: 30px;
      border-radius: 8px;
    }

    .rodape-ajuda h3 {
      font-size: 20px;
      margin-bottom: 8px;
    }

    .rodape-ajuda p {
      margin-bottom: 15px;
    }

    .rodape-ajuda button {
      background: white;
      color: #005051;
      border: none;
      padding: 10px 20px;
      border-radius: 6px;
      font-weight: 600;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }

    @media (max-width: 768px) {
      .contato {
        flex-direction: column;
      }

      .form-row {
        flex-direction: column;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>Entre em Contato</h1>
    <p class="subtitulo">Estamos prontos para ajudá-lo a planejar sua estadia perfeita. Entre em contato conosco através de qualquer um dos canais abaixo.</p>

    <div class="contato">
      <div class="blocos-laterais">
        <div class="bloco-info">
          <i class="fas fa-location-dot"></i>
          <div class="info-text">
            <h3>Endereço</h3>
            <p>Rua Major Kanhangulo, 100<br>Ingomobota, Luanda<br>Angola</p>
          </div>
        </div>

        <div class="bloco-info">
          <i class="fas fa-phone"></i>
          <div class="info-text">
            <h3>Telefone</h3>
            <p>+244 923 456 789<br>+244 923 456 790<br>WhatsApp: +244 923 456 789</p>
          </div>
        </div>

        <div class="bloco-info">
          <i class="fas fa-envelope"></i>
          <div class="info-text">
            <h3>Email</h3>
            <p>info@hotelmucinga.ao<br>reservas@hotelmucinga.ao<br>eventos@hotelmucinga.ao</p>
          </div>
        </div>

        <div class="bloco-info">
          <i class="fas fa-clock"></i>
          <div class="info-text">
            <h3>Horário de Atendimento</h3>
            <p>Recepção: 24h<br>Restaurante: 06:00 - 23:00<br>Spa: 09:00 - 21:00</p>
          </div>
        </div>
      </div>

      <div class="formulario">
        <h2>Envie sua Mensagem</h2>
        <p>Preencha o formulário abaixo e retornaremos o mais breve possível.</p>

        <form onsubmit="return enviarMensagem()">
          <div class="form-row">
            <div class="form-group">
              <label>Nome Completo *</label>
              <input type="text" required placeholder="Seu nome completo">
            </div>
            <div class="form-group">
              <label>Email *</label>
              <input type="email" required placeholder="seu@email.com">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Telefone</label>
              <input type="text" placeholder="+244 923 456 789">
            </div>
            <div class="form-group">
              <label>Assunto *</label>
              <select required>
                <option value="">Selecione um assunto</option>
                <option value="reserva">Reserva</option>
                <option value="eventos">Eventos</option>
                <option value="outros">Outros</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label>Mensagem *</label>
            <textarea required placeholder="Descreva sua solicitação ou dúvida..."></textarea>
          </div>

          <br>
          <button type="submit" class="btn">
            <i class="fas fa-paper-plane"></i> Enviar Mensagem
          </button>
        </form>
      </div>
    </div>

    <div class="rodape-ajuda">
      <h3>Precisa de Ajuda Imediata?</h3>
      <p>Nossa equipe está disponível 24 horas para atendê-lo</p>
      <button><i class="fas fa-phone"></i> Ligar Agora</button>
    </div>
  </div>

  <script>
    function enviarMensagem() {
      alert("Mensagem enviada com sucesso!");
      return false;
    }
  </script>
</body>
</html>
