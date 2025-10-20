<style>
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

  .footer-section ul li a {
    color: #fff;
    text-decoration: none;
    transition: color 0.3s ease;
  }

  .footer-section ul li a:hover {
    color: #F28D00;
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
    transition: all 0.3s ease;
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background-color: rgba(255, 255, 255, 0.1);
  }

  .social-icons a:hover {
    color: #F28D00;
    background-color: rgba(242, 141, 0, 0.2);
    transform: translateY(-2px);
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
    transition: background-color 0.3s ease;
  }

  .newsletter button:hover {
    background-color: #e67e00;
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