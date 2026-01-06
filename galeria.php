<?php
/**
 * Galeria de Fotos
 * Hotel Mucinga Nzambi
 */

define('SYSTEM_ACCESS', true);
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/helpers.php';
require_once 'includes/auth.php';

$page_title = 'Galeria de Fotos | Hotel Mucinga Nzambi';
$usuarioLogado = Auth::isLoggedIn();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Lightbox CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css">
    
    <!-- Estilos da Navbar -->
    <?php include 'includes/navbar-styles.php'; ?>
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            color: #333;
            line-height: 1.6;
            background-color: #f9f9f9;
            padding-top: 0;
        }
        
        .gallery-section {
            padding: 80px 0;
        }
        
        .section-header {
            text-align: center;
            margin-bottom: 50px;
        }
        
        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 700;
            color: #005051;
            margin-bottom: 15px;
            position: relative;
            display: inline-block;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            width: 60px;
            height: 3px;
            background: #FFC107;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
        }
        
        .section-subtitle {
            color: #6c757d;
            max-width: 700px;
            margin: 0 auto;
            font-size: 1.1rem;
            line-height: 1.7;
        }
        
        .gallery-container {
            padding: 0 15px;
        }
        
        .gallery-item {
            position: relative;
            overflow: hidden;
            border-radius: 8px;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            height: 280px;
        }
        
        .gallery-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .gallery-item:hover img {
            transform: scale(1.05);
        }
        
        .gallery-caption {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0, 80, 81, 0.9));
            color: white;
            padding: 20px;
            transform: translateY(100%);
            transition: transform 0.3s ease;
        }
        
        .gallery-item:hover .gallery-caption {
            transform: translateY(0);
        }
        
        .gallery-caption h3 {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 5px;
            font-family: 'Playfair Display', serif;
        }
        
        .gallery-caption p {
            font-size: 0.9rem;
            opacity: 0.9;
            margin: 0;
        }
        
        .gallery-category {
            margin-bottom: 50px;
        }
        
        .category-title {
            font-size: 1.8rem;
            color: #005051;
            margin-bottom: 25px;
            padding-bottom: 10px;
            border-bottom: 2px solid #FFC107;
            display: inline-block;
            font-family: 'Playfair Display', serif;
        }
        
        .back-to-home {
            display: inline-block;
            margin-top: 30px;
            padding: 12px 25px;
            background: #005051;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .back-to-home:hover {
            background: #003d3e;
            color: white;
            transform: translateY(-2px);
        }
        
        @media (max-width: 768px) {
            .section-title {
                font-size: 2rem;
            }
            
            .gallery-item {
                height: 250px;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <section class="gallery-section">
        <div class="container">
            <div class="section-header">
                <h1 class="section-title">Nossa Galeria</h1>
                <p class="section-subtitle">Explore as belezas e comodidades exclusivas do Hotel Mucinga Nzambi. Cada imagem conta uma história de conforto e sofisticação.</p>
            </div>

            <div class="gallery-container">
                <!-- Categoria: Acomodações -->
                <div class="gallery-category">
                    <h2 class="category-title"><i class="fas fa-hotel me-2"></i>Acomodações</h2>
                    <div class="row">
                        <!-- Quarto Standard -->
                        <div class="col-md-4 col-sm-6 mb-4">
                            <div class="gallery-item">
                                <a href="https://source.unsplash.com/1200x800/?hotel-room" data-lightbox="acomodacoes" data-title="Quarto Standard">
                                    <img src="https://source.unsplash.com/600x400/?hotel-room" alt="Quarto Standard">
                                </a>
                                <div class="gallery-caption">
                                    <h3>Quarto Standard</h3>
                                    <p>Conforto e praticidade em nossos quartos bem equipados</p>
                                </div>
                            </div>
                        </div>

                        <!-- Suíte Premium -->
                        <div class="col-md-4 col-sm-6 mb-4">
                            <div class="gallery-item">
                                <a href="https://source.unsplash.com/1200x800/?luxury-hotel" data-lightbox="acomodacoes" data-title="Suíte Premium">
                                    <img src="https://source.unsplash.com/600x400/?luxury-hotel" alt="Suíte Premium">
                                </a>
                                <div class="gallery-caption">
                                    <h3>Suíte Premium</h3>
                                    <p>Experiência de luxo com vista privilegiada</p>
                                </div>
                            </div>
                        </div>

                        <!-- Suíte Presidencial -->
                        <div class="col-md-4 col-sm-6 mb-4">
                            <div class="gallery-item">
                                <a href="https://source.unsplash.com/1200x800/?luxury-suite" data-lightbox="acomodacoes" data-title="Suíte Presidencial">
                                    <img src="https://source.unsplash.com/600x400/?luxury-suite" alt="Suíte Presidencial">
                                </a>
                                <div class="gallery-caption">
                                    <h3>Suíte Presidencial</h3>
                                    <p>O máximo em luxo e sofisticação</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Categoria: Lazer -->
                <div class="gallery-category">
                    <h2 class="category-title"><i class="fas fa-umbrella-beach me-2"></i>Lazer</h2>
                    <div class="row">
                        <!-- Piscina -->
                        <div class="col-md-6 col-sm-6 mb-4">
                            <div class="gallery-item">
                                <a href="https://source.unsplash.com/1200x800/?resort-pool" data-lightbox="lazer" data-title="Piscina">
                                    <img src="https://source.unsplash.com/600x400/?resort-pool" alt="Piscina">
                                </a>
                                <div class="gallery-caption">
                                    <h3>Piscina</h3>
                                    <p>Área de lazer com piscina panorâmica</p>
                                </div>
                            </div>
                        </div>

                        <!-- SPA -->
                        <div class="col-md-6 col-sm-6 mb-4">
                            <div class="gallery-item">
                                <a href="https://source.unsplash.com/1200x800/?spa" data-lightbox="lazer" data-title="SPA & Bem-estar">
                                    <img src="https://source.unsplash.com/600x400/?spa" alt="SPA & Bem-estar">
                                </a>
                                <div class="gallery-caption">
                                    <h3>SPA & Bem-estar</h3>
                                    <p>Relaxe e renove suas energias</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Categoria: Gastronomia -->
                <div class="gallery-category">
                    <h2 class="category-title"><i class="fas fa-utensils me-2"></i>Gastronomia</h2>
                    <div class="row">
                        <!-- Restaurante -->
                        <div class="col-md-4 col-sm-6 mb-4">
                            <div class="gallery-item">
                                <a href="https://source.unsplash.com/1200x800/?restaurant" data-lightbox="gastronomia" data-title="Restaurante Principal">
                                    <img src="https://source.unsplash.com/600x400/?restaurant" alt="Restaurante Principal">
                                </a>
                                <div class="gallery-caption">
                                    <h3>Restaurante Principal</h3>
                                    <p>Sabores únicos da nossa cozinha especializada</p>
                                </div>
                            </div>
                        </div>

                        <!-- Bar -->
                        <div class="col-md-4 col-sm-6 mb-4">
                            <div class="gallery-item">
                                <a href="https://source.unsplash.com/1200x800/?bar" data-lightbox="gastronomia" data-title="Bar da Piscina">
                                    <img src="https://source.unsplash.com/600x400/?bar" alt="Bar da Piscina">
                                </a>
                                <div class="gallery-caption">
                                    <h3>Bar da Piscina</h3>
                                    <p>Drinks refrescantes e petiscos deliciosos</p>
                                </div>
                            </div>
                        </div>

                        <!-- Café da Manhã -->
                        <div class="col-md-4 col-sm-6 mb-4">
                            <div class="gallery-item">
                                <a href="https://source.unsplash.com/1200x800/?breakfast" data-lightbox="gastronomia" data-title="Café da Manhã">
                                    <img src="https://source.unsplash.com/600x400/?breakfast" alt="Café da Manhã">
                                </a>
                                <div class="gallery-caption">
                                    <h3>Café da Manhã</h3>
                                    <p>Comece o dia com o melhor da culinária local</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center">
                <a href="index.php" class="back-to-home">
                    <i class="fas fa-arrow-left me-2"></i>Voltar para a Página Inicial
                </a>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <!-- Bootstrap JS e Popper.js -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="bootstrap/js/bootstrap.min.js"></script>
    
    <!-- Lightbox JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
    
    <script>
        // Configuração do Lightbox
        lightbox.option({
            'resizeDuration': 200,
            'wrapAround': true,
            'albumLabel': 'Imagem %1 de %2',
            'fadeDuration': 300,
            'imageFadeDuration': 300
        });
        
        // Efeito de scroll suave
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            });
        });
        
        // Efeito de scroll na navbar
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    </script>
</body>
</html>
