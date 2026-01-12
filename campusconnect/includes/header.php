<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusConnect - Bridge Between Students and Organizers</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .index-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            box-shadow: 0 2px 20px rgba(220, 38, 38, 0.08);
            transition: all 0.3s ease;
            border-bottom: 1px solid rgba(220, 38, 38, 0.1);
        }

        .index-header.scrolled {
            box-shadow: 0 4px 30px rgba(220, 38, 38, 0.12);
            background: rgba(255, 255, 255, 1);
        }
        
        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.25rem 3rem;
            transition: padding 0.3s ease;
        }
        
        .logo {
            font-size: 1.8rem;
            font-weight: 800;
            color: #dc2626;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: transform 0.3s ease;
            letter-spacing: -0.5px;
        }

        .logo:hover {
            transform: scale(1.05);
        }

        .logo::before {
            content: "🎓";
            font-size: 2rem;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-5px); }
        }
        
        .nav-links {
            display: flex;
            gap: 2.5rem;
            align-items: center;
        }
        
        .nav-links a {
            color: #374151;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            position: relative;
            transition: all 0.3s ease;
            padding: 0.5rem 0;
        }

        .nav-links a:not(.btn-outline-red):not(.btn-red)::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #dc2626, #ef4444);
            transition: width 0.3s ease;
            border-radius: 2px;
        }

        .nav-links a:not(.btn-outline-red):not(.btn-red):hover {
            color: #dc2626;
        }

        .nav-links a:not(.btn-outline-red):not(.btn-red):hover::after {
            width: 100%;
        }
        
        .btn-outline-red {
            border: 2px solid #dc2626;
            color: #dc2626;
            background: transparent;
            padding: 0.65rem 1.75rem;
            border-radius: 30px;
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 700;
            font-size: 0.9rem;
            letter-spacing: 0.3px;
        }
        
        .btn-outline-red:hover {
            background: rgba(220, 38, 38, 0.05);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(220, 38, 38, 0.2);
        }
        
        .btn-red {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: #ffffff;
            padding: 0.65rem 2rem;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 800;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(220, 38, 38, 0.3);
            position: relative;
            overflow: hidden;
            border: none;
            letter-spacing: 0.3px;
        }

        .btn-red::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn-red:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-red:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(220, 38, 38, 0.4);
        }

        .btn-red span {
            position: relative;
            z-index: 1;
        }

        /* Mobile Menu Toggle */
        .mobile-toggle {
            display: none;
            flex-direction: column;
            gap: 5px;
            cursor: pointer;
            padding: 0.5rem;
            z-index: 1001;
            background: transparent;
            border: none;
        }

        .mobile-toggle span {
            width: 28px;
            height: 3px;
            background: #dc2626;
            border-radius: 3px;
            transition: all 0.3s ease;
        }

        .mobile-toggle.active span:nth-child(1) {
            transform: rotate(45deg) translate(8px, 8px);
        }

        .mobile-toggle.active span:nth-child(2) {
            opacity: 0;
        }

        .mobile-toggle.active span:nth-child(3) {
            transform: rotate(-45deg) translate(7px, -7px);
        }

        /* Overlay for mobile menu */
        .menu-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .menu-overlay.active {
            display: block;
            opacity: 1;
        }
        
        @media (max-width: 968px) {
            .nav-container {
                padding: 1rem 2rem;
            }

            .mobile-toggle {
                display: flex;
            }

            .nav-links {
                position: fixed;
                top: 0;
                right: -100%;
                height: 100vh;
                width: 75%;
                max-width: 350px;
                background: #ffffff;
                flex-direction: column;
                gap: 0;
                padding: 6rem 2rem 2rem;
                box-shadow: -5px 0 30px rgba(0, 0, 0, 0.15);
                transition: right 0.4s cubic-bezier(0.4, 0, 0.2, 1);
                overflow-y: auto;
                z-index: 1000;
            }

            .nav-links.active {
                right: 0;
            }

            .nav-links a {
                width: 100%;
                padding: 1.25rem 0;
                border-bottom: 1px solid rgba(220, 38, 38, 0.1);
                text-align: left;
                font-size: 1rem;
            }

            .nav-links a:not(.btn-outline-red):not(.btn-red)::after {
                display: none;
            }

            .nav-links a:not(.btn-outline-red):not(.btn-red) {
                color: #374151;
            }

            .nav-links a:not(.btn-outline-red):not(.btn-red):hover {
                color: #dc2626;
                padding-left: 1rem;
                background: rgba(220, 38, 38, 0.05);
            }

            .btn-outline-red,
            .btn-red {
                margin-top: 1.5rem;
                text-align: center;
                display: block;
                border: none;
            }

            .btn-outline-red {
                background: transparent;
                border: 2px solid #dc2626;
            }

            .btn-red {
                box-shadow: 0 4px 15px rgba(220, 38, 38, 0.25);
            }
        }

        @media (max-width: 480px) {
            .logo {
                font-size: 1.5rem;
            }

            .logo::before {
                font-size: 1.7rem;
            }

            .nav-container {
                padding: 1rem 1.5rem;
            }

            .nav-links {
                width: 85%;
                padding: 5rem 1.5rem 2rem;
            }
        }

        /* Smooth scroll behavior */
        html {
            scroll-behavior: smooth;
        }
    </style>
</head>
<body>
    <div class="menu-overlay" id="menuOverlay" onclick="closeMenu()"></div>
    
    <header class="index-header">
        <div class="nav-container">
            <a href="index.php" class="logo">CampusConnect</a>
            
            <div class="mobile-toggle" onclick="toggleMenu()">
                <span></span>
                <span></span>
                <span></span>
            </div>

            <div class="nav-links" id="navLinks">
                <a href="index.php">Home</a>
                <a href="#about">About</a>
                <a href="#features">Features</a>
                <a href="login.php" class="btn-outline-red">Login</a>
                <a href="register.php" class="btn-red"><span>Register as Student</span></a>
            </div>
        </div>
    </header>

    <script>
        // Scroll effect
        window.addEventListener('scroll', function() {
            const header = document.querySelector('.index-header');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });

        // Mobile menu toggle
        function toggleMenu() {
            const navLinks = document.getElementById('navLinks');
            const mobileToggle = document.querySelector('.mobile-toggle');
            const menuOverlay = document.getElementById('menuOverlay');
            
            navLinks.classList.toggle('active');
            mobileToggle.classList.toggle('active');
            menuOverlay.classList.toggle('active');
            
            // Prevent body scroll when menu is open
            if (navLinks.classList.contains('active')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        }

        // Close menu function
        function closeMenu() {
            const navLinks = document.getElementById('navLinks');
            const mobileToggle = document.querySelector('.mobile-toggle');
            const menuOverlay = document.getElementById('menuOverlay');
            
            navLinks.classList.remove('active');
            mobileToggle.classList.remove('active');
            menuOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        // Close menu when clicking a link
        document.querySelectorAll('.nav-links a').forEach(link => {
            link.addEventListener('click', closeMenu);
        });

        // Close menu on escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeMenu();
            }
        });

        // Handle resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 968) {
                closeMenu();
            }
        });
    </script>
</body>
</html>