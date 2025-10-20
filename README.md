# Hotel Mucinga Nzambi - Website

## Descrição
Website do Hotel Mucinga Nzambi, um hotel de luxo localizado em Luanda, Angola. O projeto foi desenvolvido usando PHP, Bootstrap (local) e HTML/CSS/JavaScript.

## Estrutura do Projeto

```
mucinga/
├── includes/           # Páginas PHP principais
│   ├── index.php      # Página inicial
│   ├── sobre.php      # Página sobre o hotel
│   ├── contato.php    # Página de contato
│   ├── navbar.php     # Navegação comum
│   ├── navbar-styles.php # Estilos da navegação
│   ├── footer-common.php # Footer comum
│   ├── footer-styles.php # Estilos do footer
│   └── common-scripts.php # Scripts JavaScript comuns
├── bootstrap/         # Bootstrap local
├── imagens/          # Imagens do projeto
└── README.md         # Este arquivo
```

## Melhorias Implementadas

### 1. Navegação Unificada
- ✅ Criado arquivo `navbar.php` com navegação comum
- ✅ Criado arquivo `navbar-styles.php` com estilos da navegação
- ✅ Removido espaçamento desnecessário da navbar
- ✅ Adicionado efeito de scroll com backdrop-filter
- ✅ Implementado animações de entrada para os links

### 2. Footer Unificado
- ✅ Criado arquivo `footer-common.php` com footer comum
- ✅ Criado arquivo `footer-styles.php` com estilos do footer
- ✅ Adicionado links de navegação funcionais no footer
- ✅ Implementado newsletter com validação

### 3. Botões Melhorados
- ✅ Design mais moderno com gradientes
- ✅ Efeitos hover com animações
- ✅ Efeito de brilho (shimmer) nos botões
- ✅ Animações de loading
- ✅ Bordas arredondadas e sombras

### 4. Navegação Entre Páginas
- ✅ Links funcionais entre todas as páginas
- ✅ Navegação consistente em todo o site
- ✅ Scroll suave para âncoras
- ✅ Estado ativo para página atual

### 5. Scripts Unificados
- ✅ Criado arquivo `common-scripts.php` com funcionalidades comuns
- ✅ Animação de scroll da navbar
- ✅ Função de newsletter
- ✅ Scroll suave
- ✅ Animações de botões

### 6. Melhorias Visuais
- ✅ Design mais moderno e interativo
- ✅ Animações CSS suaves
- ✅ Efeitos hover melhorados
- ✅ Responsividade aprimorada
- ✅ Cores e tipografia consistentes

## Tecnologias Utilizadas

- **PHP**: Para estruturação das páginas
- **Bootstrap 5**: Framework CSS (versão local)
- **HTML5**: Estrutura semântica
- **CSS3**: Estilos e animações
- **JavaScript**: Interatividade
- **Font Awesome**: Ícones
- **Google Fonts**: Tipografia (Poppins)

## Como Executar

1. Certifique-se de ter um servidor web com suporte a PHP
2. Coloque os arquivos na pasta do servidor web
3. Acesse através do navegador: `http://localhost/mucinga/includes/`

## Funcionalidades

### Páginas Disponíveis
- **Início** (`index.php`): Carrossel de imagens e informações gerais
- **Sobre** (`sobre.php`): História, comodidades e valores do hotel
- **Contato** (`contato.php`): Formulário de contato e informações

### Recursos Interativos
- Navegação responsiva
- Formulário de contato funcional
- Newsletter com validação
- Animações de scroll
- Efeitos hover nos botões
- Design responsivo para mobile

## Estrutura de Arquivos Comuns

### Navegação
- `navbar.php`: HTML da navegação
- `navbar-styles.php`: Estilos CSS da navegação

### Footer
- `footer-common.php`: HTML do footer
- `footer-styles.php`: Estilos CSS do footer

### Scripts
- `common-scripts.php`: JavaScript comum para todas as páginas

## Cores do Projeto

- **Primária**: #FFC107 (Amarelo)
- **Secundária**: #005051 (Verde escuro)
- **Acento**: #F28D00 (Laranja)
- **Fundo**: #FDF7E6 (Bege claro)
- **Texto**: #000000 (Preto)

## Responsividade

O site é totalmente responsivo e funciona bem em:
- Desktop (1200px+)
- Tablet (768px - 1199px)
- Mobile (até 767px)

## Próximas Melhorias Sugeridas

1. Sistema de reservas online
2. Galeria de imagens interativa
3. Blog/notícias do hotel
4. Sistema de avaliações
5. Integração com redes sociais
6. Chat online
7. Mapa interativo da localização
8. Sistema de login para clientes 