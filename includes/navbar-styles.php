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
    padding: 0 20px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    transition: all 0.4s ease;
  }

  .navbar.scrolled {
    background-color: rgba(255, 255, 255, 0.95) !important;
    backdrop-filter: blur(10px);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
  }

  .navbar-brand {
    font-weight: bold;
    font-size: 1.1rem;
    color: #000;
    text-decoration: none;
  }

  .navbar-brand:hover {
    color: #000;
  }

  .navbar-nav .nav-link {
    color: #000 !important;
    font-weight: 500;
    margin: 0 8px;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    position: relative;
    padding: 8px 12px !important;
  }

  .navbar-nav .nav-link:hover {
    color: #FFC107 !important;
    transform: translateY(-2px);
  }

  .navbar-nav .nav-link::after {
    content: '';
    position: absolute;
    width: 0;
    height: 2px;
    bottom: 0;
    left: 50%;
    background-color: #FFC107;
    transition: all 0.3s ease;
    transform: translateX(-50%);
  }

  .navbar-nav .nav-link:hover::after {
    width: 80%;
  }

  .btn-reservar, .btn-login {
    background: linear-gradient(135deg, #FFC107 0%, #FFD54F 100%);
    color: #000;
    font-weight: 600;
    border: none;
    padding: 10px 20px;
    margin-left: 8px;
    border-radius: 25px;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
    box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3);
    position: relative;
    overflow: hidden;
  }

  .btn-reservar::before, .btn-login::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    transition: left 0.5s;
  }

  .btn-reservar:hover::before, .btn-login:hover::before {
    left: 100%;
  }

  .btn-reservar:hover, .btn-login:hover {
    background: linear-gradient(135deg, #FFD54F 0%, #FFC107 100%);
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(255, 193, 7, 0.4);
    color: #000;
    text-decoration: none;
  }

  .btn-reservar:active, .btn-login:active {
    transform: translateY(-1px);
  }

  .btn-reservar.loading, .btn-login.loading {
    background: linear-gradient(135deg, #FFD54F 0%, #FFC107 100%);
    transform: scale(0.95);
  }

  .sticky-top {
    position: sticky;
    top: 0;
    z-index: 1030;
  }

  /* Animação de entrada para os links da navegação */
  .navbar-nav .nav-item {
    opacity: 0;
    animation: fadeInUp 0.6s ease forwards;
  }

  .navbar-nav .nav-item:nth-child(1) { animation-delay: 0.1s; }
  .navbar-nav .nav-item:nth-child(2) { animation-delay: 0.2s; }
  .navbar-nav .nav-item:nth-child(3) { animation-delay: 0.3s; }
  .navbar-nav .nav-item:nth-child(4) { animation-delay: 0.4s; }
  .navbar-nav .nav-item:nth-child(5) { animation-delay: 0.5s; }
  .navbar-nav .nav-item:nth-child(6) { animation-delay: 0.6s; }

  @keyframes fadeInUp {
    from {
      opacity: 0;
      transform: translateY(20px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  @media (max-width: 991.98px) {
    .navbar-nav {
      padding: 20px 0;
    }
    
    .navbar-nav .nav-link {
      margin: 5px 0;
      padding: 10px 0 !important;
    }
    
    .btn-reservar, .btn-login {
      margin: 10px 0;
      text-align: center;
    }
  }
</style> 