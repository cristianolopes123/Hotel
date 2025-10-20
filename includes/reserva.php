<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <title>Reserva | Hotel Mucinga Nzambi</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <!-- Bootstrap Local -->
  <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
  
  <!-- Google Fonts Poppins -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
  
  <!-- Estilos personalizados -->
  <link rel="stylesheet" href="css/estilo.css">
</head>
<body style="font-family: 'Poppins', sans-serif; background-color: #fdf7e6;">
 <style>

    body {
  font-size: 16px;
}

.card {
  border-radius: 10px;
}

 </style>
  <div class="container py-5">
    <h1 class="text-center fw-bold" style="color:#005051;">Faça sua Reserva</h1>
    <p class="text-center mb-4">Encontre a disponibilidade perfeita para suas datas e desfrute de uma experiência única no Hotel Mucinga Nzambi.</p>

    <!-- Verificar Disponibilidade -->
    <div class="card border-0 shadow mb-5">
      <div class="card-header text-white px-4 py-3" style="background: linear-gradient(90deg, #005051, #F28D00); border-top-left-radius: 10px; border-top-right-radius: 10px;">
        <h5 class="mb-0">
          <i class="bi bi-search"></i> Verificar Disponibilidade
        </h5>
        <small>Insira suas datas e preferências para encontrar os melhores quartos disponíveis.</small>
      </div>
      <div class="card-body">
        <form>
          <div class="row g-3">
            <div class="col-md-3">
              <label class="form-label">Check-in</label>
              <input type="date" class="form-control" required>
            </div>
            <div class="col-md-3">
              <label class="form-label">Check-out</label>
              <input type="date" class="form-control" required>
            </div>
            <div class="col-md-3">
              <label class="form-label">Hóspedes</label>
              <select class="form-select">
                <option selected>4 Hóspedes</option>
                <option>1 Hóspede</option>
                <option>2 Hóspedes</option>
                <option>3 Hóspedes</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Quartos</label>
              <select class="form-select">
                <option selected>4 Quartos</option>
                <option>1 Quarto</option>
                <option>2 Quartos</option>
                <option>3 Quartos</option>
              </select>
            </div>
            <div class="col-12 text-center mt-3">
              <button class="btn text-white px-4 py-2" style="background-color: #F28D00;" type="submit">
                <i class="bi bi-search"></i> Buscar Quartos Disponíveis
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Formulário de Reserva -->
    <div class="card border-0 shadow">
      <div class="card-header text-white px-4 py-3" style="background-color: #005051;">
        <h5 class="mb-0">Fazer Reserva</h5>
        <small>Preencha os dados para confirmar sua reserva.</small>
      </div>
      <div class="card-body">
        <form action="processar_reserva.php" method="POST">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Nome Completo</label>
              <input type="text" name="nome" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Telefone</label>
              <input type="tel" name="telefone" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Data de Check-in</label>
              <input type="date" name="checkin" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Data de Check-out</label>
              <input type="date" name="checkout" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Número de Hóspedes</label>
              <select name="hospedes" class="form-select">
                <option>1</option>
                <option>2</option>
                <option>3</option>
                <option selected>4</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Tipo de Quarto</label>
              <select name="quarto" class="form-select">
                <option>Simples</option>
                <option>Casal</option>
                <option selected>Luxo</option>
              </select>
            </div>
            <div class="col-12 text-center mt-3">
              <button class="btn text-white px-5 py-2" style="background-color: #005051;" type="submit">
                Confirmar Reserva
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>

  </div>
  
  <!-- Bootstrap JS Local -->
  <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
