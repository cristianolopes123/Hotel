<!-- Bootstrap JS Local -->
<script src="../bootstrap/js/bootstrap.bundle.min.js"></script>

<!-- Scripts Comuns -->
<script>
  // Navbar scroll animation
  window.addEventListener("scroll", function () {
    var navbar = document.querySelector(".navbar");
    navbar.classList.toggle("scrolled", window.scrollY > 50);
  });

  // Newsletter subscription
  function subscribe() {
    const email = document.getElementById('newsletter-email').value;
    if(email) {
      alert(`Obrigado por subscrever com o email: ${email}`);
      document.getElementById('newsletter-email').value = '';
    } else {
      alert('Por favor, insira um email válido.');
    }
  }

  // Smooth scrolling for anchor links
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        target.scrollIntoView({
          behavior: 'smooth',
          block: 'start'
        });
      }
    });
  });

  // Add loading animation to buttons
  document.querySelectorAll('.btn-reservar, .btn-login, .btn-enviar').forEach(button => {
    button.addEventListener('click', function() {
      if (!this.classList.contains('loading')) {
        this.classList.add('loading');
        this.style.pointerEvents = 'none';
        
        setTimeout(() => {
          this.classList.remove('loading');
          this.style.pointerEvents = 'auto';
        }, 1000);
      }
    });
  });
</script> 