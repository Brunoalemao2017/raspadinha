<?php
@session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
include('./conexao.php');
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $nomeSite; ?> - Raspadinhas Online</title>
    <meta name="description" content="Raspe e ganhe prêmios incríveis! PIX na conta instantâneo.">

    <!-- Preload Critical Resources -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="assets/style/globalStyles.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" href="assets/style/christmas.css?v=<?php echo time(); ?>" />

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://cdn.jsdelivr.net/npm/notiflix@3.2.8/dist/notiflix-aio-3.2.8.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/notiflix@3.2.8/src/notiflix.min.css" rel="stylesheet">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo $urlSite; ?>">
    <meta property="og:title" content="Raspadinha Monkey - Ganhe Prêmios no PIX!">
    <meta property="og:description"
        content="A sorte está nas suas mãos! Raspe e ganhe prêmios incríveis com pagamento via PIX instantâneo.">
    <meta property="og:image" content="<?php echo $urlSite; ?><?php echo $logoSite; ?>">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?php echo $urlSite; ?>">
    <meta property="twitter:title" content="Raspadinha Monkey - Ganhe Prêmios no PIX!">
    <meta property="twitter:description"
        content="A sorte está nas suas mãos! Raspe e ganhe prêmios incríveis com pagamento via PIX instantâneo.">
    <meta property="twitter:image" content="<?php echo $urlSite; ?><?php echo $logoSite; ?>">

    <style>
        /* Loading Animation - Premium Christmas Theme */
        .loading-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: linear-gradient(135deg, #0a0a0a 0%, #1a0505 50%, #0a0a0a 100%);
            z-index: 9999;
            transition: opacity 0.5s ease, visibility 0.5s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 2rem;
        }

        /* Logo Animation */
        .loading-logo {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #D42426, #8D0801);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: #FFD700;
            box-shadow:
                0 0 40px rgba(212, 36, 38, 0.4),
                0 0 80px rgba(212, 36, 38, 0.2);
            animation: logoFloat 3s ease-in-out infinite;
            position: relative;
            overflow: hidden;
        }

        .loading-logo::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg,
                    transparent,
                    rgba(255, 255, 255, 0.1),
                    transparent);
            animation: shine 3s ease-in-out infinite;
        }

        @keyframes logoFloat {

            0%,
            100% {
                transform: translateY(0) scale(1);
            }

            50% {
                transform: translateY(-10px) scale(1.05);
            }
        }

        @keyframes shine {
            0% {
                transform: translateX(-100%) translateY(-100%) rotate(45deg);
            }

            100% {
                transform: translateX(100%) translateY(100%) rotate(45deg);
            }
        }

        /* Spinner Container */
        .loading-spinner-container {
            position: relative;
            width: 80px;
            height: 80px;
        }

        .loading-spinner {
            width: 80px;
            height: 80px;
            position: relative;
        }

        .loading-spinner::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: 4px solid rgba(212, 36, 38, 0.2);
            border-top-color: #D42426;
            border-right-color: #FFD700;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        .loading-spinner::after {
            content: '';
            position: absolute;
            top: 10px;
            left: 10px;
            width: calc(100% - 20px);
            height: calc(100% - 20px);
            border: 3px solid rgba(255, 215, 0, 0.2);
            border-bottom-color: #FFD700;
            border-left-color: #D42426;
            border-radius: 50%;
            animation: spin 1.5s linear infinite reverse;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        /* Loading Text */
        .loading-text {
            color: #ffffff;
            font-size: 1.5rem;
            font-weight: 700;
            text-align: center;
            animation: pulse 2s ease-in-out infinite;
            text-shadow: 0 0 20px rgba(255, 215, 0, 0.3);
        }

        .loading-subtext {
            color: #9ca3af;
            font-size: 0.9rem;
            text-align: center;
            margin-top: -1rem;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.6;
            }
        }

        /* Christmas Decorations */
        .loading-decoration {
            position: absolute;
            font-size: 2rem;
            opacity: 0.3;
            animation: float 4s ease-in-out infinite;
        }

        .loading-decoration:nth-child(1) {
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }

        .loading-decoration:nth-child(2) {
            top: 15%;
            right: 15%;
            animation-delay: 0.5s;
        }

        .loading-decoration:nth-child(3) {
            bottom: 20%;
            left: 15%;
            animation-delay: 1s;
        }

        .loading-decoration:nth-child(4) {
            bottom: 15%;
            right: 10%;
            animation-delay: 1.5s;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0) rotate(0deg);
            }

            50% {
                transform: translateY(-20px) rotate(10deg);
            }
        }

        /* Alternativa ainda mais simples usando apenas border-image */
        .loading-spinner-simple {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: conic-gradient(#22c55e, rgba(34, 197, 94, 0.3));
            animation: rotateSimple 1s linear infinite;
            position: relative;

            /* Máscara para criar o efeito de spinner */
            mask: radial-gradient(circle at center, transparent 18px, black 21px);
            -webkit-mask: radial-gradient(circle at center, transparent 18px, black 21px);
        }

        @keyframes rotateSimple {
            to {
                transform: rotate(360deg);
            }
        }

        /* Versão com CSS puro - mais moderna */
        .loading-spinner-modern {
            width: 50px;
            height: 50px;
            background:
                conic-gradient(from 0deg, transparent, #22c55e, transparent),
                conic-gradient(from 180deg, transparent, rgba(34, 197, 94, 0.3), transparent);
            border-radius: 50%;
            animation: rotateModern 1s linear infinite;
            position: relative;

            /* Efeito de máscara para criar o anel */
            mask: radial-gradient(circle, transparent 17px, black 20px);
            -webkit-mask: radial-gradient(circle, transparent 17px, black 20px);
        }

        @keyframes rotateModern {
            100% {
                transform: rotate(360deg);
            }
        }

        .hidden {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }

        /* Reset completo para garantir que não há interferências */
        .loading-screen * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        /* Smooth scroll */
        html {
            scroll-behavior: smooth;
        }

        /* Parallax effect */
        .parallax-element {
            transform: translateZ(0);
            will-change: transform;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out forwards;
        }

        /* Floating elements animation */
        .floating {
            animation: floating 3s ease-in-out infinite;
        }

        @keyframes floating {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        /* Glowing effect */
        .glow {
            box-shadow: 0 0 20px rgba(34, 197, 94, 0.3);
        }

        .glow:hover {
            box-shadow: 0 0 30px rgba(34, 197, 94, 0.5);
        }
    </style>
</head>

<body>
    <!-- Premium Loading Screen -->
    <div class="loading-screen" id="loadingScreen">
        <div class="loading-decoration">🎄</div>
        <div class="loading-decoration">🎁</div>
        <div class="loading-decoration">⭐</div>
        <div class="loading-decoration">🔔</div>

        <div class="loading-logo">
            🎅
        </div>

        <div class="loading-spinner-container">
            <div class="loading-spinner"></div>
        </div>

        <div class="loading-text">Carregando...</div>
        <div class="loading-subtext">Preparando a magia do Natal 🎄</div>
    </div>

    <?php include('./inc/header.php'); ?>

    <main>
        <?php include('./components/carrossel.php'); ?>

        <?php include('./components/ganhos.php'); ?>

        <?php include('./components/chamada.php'); ?>

        <?php include('./components/modals.php'); ?>

        <?php include('./components/testimonials.php'); ?>
    </main>

    <?php include('./inc/footer.php'); ?>

    <script>
        // Loading screen
        window.addEventListener('load', function () {
            const loadingScreen = document.getElementById('loadingScreen');
            setTimeout(() => {
                loadingScreen.classList.add('hidden');
            }, 1000);
        });

        // Smooth animations on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-fade-in-up');
                }
            });
        }, observerOptions);

        // Observe elements for animation
        document.addEventListener('DOMContentLoaded', function () {
            const elementsToAnimate = document.querySelectorAll('.step-item, .game-category, .prize-item');
            elementsToAnimate.forEach(el => {
                observer.observe(el);
            });
        });

        // Parallax effect for hero section
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const heroElements = document.querySelectorAll('.parallax-element');

            heroElements.forEach(element => {
                const speed = element.dataset.speed || 0.5;
                element.style.transform = `translateY(${scrolled * speed}px)`;
            });
        });

        // Add floating animation to certain elements
        document.addEventListener('DOMContentLoaded', function () {
            const floatingElements = document.querySelectorAll('.hero-visuals .gaming-item');
            floatingElements.forEach((el, index) => {
                el.style.animationDelay = `${index * 0.5}s`;
                el.classList.add('floating');
            });
        });

        // Notiflix configuration
        Notiflix.Notify.init({
            width: '300px',
            position: 'right-top',
            distance: '20px',
            opacity: 1,
            borderRadius: '12px',
            rtl: false,
            timeout: 4000,
            messageMaxLength: 110,
            backOverlay: false,
            backOverlayColor: 'rgba(0,0,0,0.5)',
            plainText: true,
            showOnlyTheLastOne: false,
            clickToClose: true,
            pauseOnHover: true,
            ID: 'NotiflixNotify',
            className: 'notiflix-notify',
            zindex: 4001,
            fontFamily: 'Inter',
            fontSize: '14px',
            cssAnimation: true,
            cssAnimationDuration: 400,
            cssAnimationStyle: 'zoom',
            closeButton: false,
            useIcon: true,
            useFontAwesome: false,
            fontAwesomeIconStyle: 'basic',
            fontAwesomeIconSize: '16px',
            success: {
                background: '#22c55e',
                textColor: '#fff',
                childClassName: 'notiflix-notify-success',
                notiflixIconColor: 'rgba(0,0,0,0.2)',
                fontAwesomeClassName: 'fas fa-check-circle',
                fontAwesomeIconColor: 'rgba(0,0,0,0.2)',
                backOverlayColor: 'rgba(34,197,94,0.2)',
            }
        });

        // Dynamic copyright year
        document.addEventListener('DOMContentLoaded', function () {
            const currentYear = new Date().getFullYear();
            const copyrightElements = document.querySelectorAll('.footer-description');
            if (copyrightElements.length > 0) {
                copyrightElements[0].innerHTML = copyrightElements[0].innerHTML.replace('2025', currentYear);
            }
        });

        // Add glow effect to interactive elements
        document.addEventListener('DOMContentLoaded', function () {
            const glowElements = document.querySelectorAll('.btn-register, .hero-cta, .game-btn');
            glowElements.forEach(el => {
                el.classList.add('glow');
            });
        });

        // Mobile menu toggle (if needed)
        function toggleMobileMenu() {
            const mobileMenu = document.querySelector('.mobile-menu');
            if (mobileMenu) {
                mobileMenu.classList.toggle('active');
            }
        }

        // Console welcome message
        console.log('%c� RaspaMonkey - Feliz Natal!', 'color: #fed000; font-size: 16px; font-weight: bold;');
        console.log('%cSistema natalino ativado!', 'color: #D42426; font-size: 12px;');

        // Christmas Snowflakes
        document.addEventListener('DOMContentLoaded', function () {
            const snowflakeCount = 50;
            const body = document.body;

            for (let i = 0; i < snowflakeCount; i++) {
                const snowflake = document.createElement('div');
                snowflake.className = 'snowflake';
                snowflake.innerHTML = '❄';
                snowflake.style.left = Math.random() * 100 + 'vw';
                snowflake.style.animationDuration = (Math.random() * 5 + 5) + 's, ' + (Math.random() * 3 + 2) + 's';
                snowflake.style.animationDelay = (Math.random() * 5) + 's';
                snowflake.style.fontSize = (Math.random() * 10 + 10) + 'px';
                snowflake.style.opacity = Math.random() * 0.7 + 0.3;
                body.appendChild(snowflake);
            }
        });
    </script>

    <!-- Performance and Analytics -->
    <script>
        // Performance monitoring
        window.addEventListener('load', function () {
            if ('performance' in window) {
                const loadTime = performance.timing.loadEventEnd - performance.timing.navigationStart;
                console.log(`Página carregada em ${loadTime}ms`);
            }
        });

        // Error handling
        window.addEventListener('error', function (e) {
            console.error('Erro na página:', e.error);
        });

        // Lazy loading for images when implemented
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        imageObserver.unobserve(img);
                    }
                });
            });

            document.querySelectorAll('img[data-src]').forEach(img => {
                imageObserver.observe(img);
            });
        }
    </script>
</body>

</html>