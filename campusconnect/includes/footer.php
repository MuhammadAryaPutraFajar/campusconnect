<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Footer - CampusConnect</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Simple Elegant Footer */
        .elegant-footer {
            background: #ffffff;
            border-top: 1px solid rgba(220, 38, 38, 0.1);
            margin-top: auto;
            position: relative;
        }

        .footer-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 3rem 3rem 1.5rem;
        }

        .footer-main {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 3rem;
            padding-bottom: 2.5rem;
            border-bottom: 1px solid rgba(220, 38, 38, 0.1);
        }

        .footer-brand {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .footer-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.75rem;
            font-weight: 800;
            color: #dc2626;
            letter-spacing: -0.5px;
        }

        .footer-logo i {
            font-size: 2rem;
            color: #dc2626;
        }

        .footer-description {
            line-height: 1.7;
            color: #6b7280;
            font-size: 0.95rem;
            max-width: 350px;
        }

        .social-links {
            display: flex;
            gap: 0.75rem;
            margin-top: 0.5rem;
        }

        .social-link {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: rgba(220, 38, 38, 0.08);
            color: #dc2626;
            border-radius: 10px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .social-link:hover {
            background: #dc2626;
            color: #ffffff;
            transform: translateY(-3px);
        }

        .social-link i {
            font-size: 1rem;
        }

        .link-group h4 {
            color: #1f2937;
            margin-bottom: 1.25rem;
            font-size: 1rem;
            font-weight: 700;
        }

        .link-group ul {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .link-group a {
            color: #6b7280;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            display: inline-block;
        }

        .link-group a:hover {
            color: #dc2626;
            padding-left: 5px;
        }

        .footer-bottom {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .copyright {
            color: #6b7280;
            font-size: 0.9rem;
        }

        .copyright strong {
            color: #dc2626;
            font-weight: 700;
        }

        .footer-extra {
            color: #6b7280;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .footer-extra i {
            color: #dc2626;
            font-size: 0.85rem;
        }

        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 48px;
            height: 48px;
            background: #dc2626;
            color: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(220, 38, 38, 0.3);
            z-index: 999;
        }

        .back-to-top.show {
            opacity: 1;
            visibility: visible;
        }

        .back-to-top:hover {
            transform: translateY(-4px);
            box-shadow: 0 6px 20px rgba(220, 38, 38, 0.4);
        }

        .back-to-top i {
            font-size: 1.1rem;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .footer-container {
                padding: 2.5rem 2rem 1.5rem;
            }

            .footer-main {
                grid-template-columns: 1fr 1fr;
                gap: 2.5rem;
            }

            .footer-brand {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 768px) {
            .footer-container {
                padding: 2rem 1.5rem 1rem;
            }

            .footer-main {
                grid-template-columns: 1fr;
                gap: 2rem;
                padding-bottom: 2rem;
            }

            .footer-brand {
                grid-column: 1;
            }

            .footer-logo {
                font-size: 1.6rem;
            }

            .footer-logo i {
                font-size: 1.8rem;
            }

            .footer-bottom {
                flex-direction: column;
                text-align: center;
                gap: 0.75rem;
            }

            .back-to-top {
                bottom: 20px;
                right: 20px;
                width: 44px;
                height: 44px;
            }
        }

        @media (max-width: 480px) {
            .footer-container {
                padding: 1.5rem 1rem 1rem;
            }

            .footer-logo {
                font-size: 1.5rem;
            }

            .footer-logo i {
                font-size: 1.7rem;
            }

            .footer-description {
                font-size: 0.9rem;
            }

            .link-group h4 {
                font-size: 0.95rem;
            }

            .link-group a {
                font-size: 0.85rem;
            }

            .copyright,
            .footer-extra {
                font-size: 0.85rem;
            }

            .social-links {
                gap: 0.6rem;
            }

            .social-link {
                width: 38px;
                height: 38px;
            }
        }

        /* Smooth scroll */
        html {
            scroll-behavior: smooth;
        }
    </style>
</head>
<body>
    <!-- Simple Elegant Footer -->
    <footer class="elegant-footer">
        <div class="footer-container">
            <div class="footer-main">
                <div class="footer-brand">
                    <div class="footer-logo">
                        <i class="fas fa-graduation-cap"></i>
                        <span>CampusConnect</span>
                    </div>
                    <p class="footer-description">
                        Bridging the gap between students and organizers. Discover opportunities, 
                        manage events, and build your campus community.
                    </p>
                    <div class="social-links">
                        <a href="#" class="social-link" title="Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="social-link" title="Twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="social-link" title="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="social-link" title="LinkedIn">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                </div>

                <div class="link-group">
                    <h4>Platform</h4>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="#features">Features</a></li>
                        <li><a href="#about">About Us</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </div>

                <div class="link-group">
                    <h4>For Students</h4>
                    <ul>
                        <li><a href="register.php">Register</a></li>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="#events">Browse Events</a></li>
                        <li><a href="#opportunities">Opportunities</a></li>
                    </ul>
                </div>

                <div class="link-group">
                    <h4>Support</h4>
                    <ul>
                        <li><a href="#help">Help Center</a></li>
                        <li><a href="#faq">FAQ</a></li>
                        <li><a href="#privacy">Privacy Policy</a></li>
                        <li><a href="#terms">Terms of Service</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <p class="copyright">
                    &copy; 2025 <strong>CampusConnect</strong>. All rights reserved.
                </p>
                <div class="footer-extra">
                    <span>Made with</span>
                    <i class="fas fa-heart"></i>
                    <span>for campus communities</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <div class="back-to-top" id="backToTop" onclick="scrollToTop()">
        <i class="fas fa-arrow-up"></i>
    </div>

    <script>
        // Back to top functionality
        window.addEventListener('scroll', function() {
            const backToTop = document.getElementById('backToTop');
            if (window.scrollY > 300) {
                backToTop.classList.add('show');
            } else {
                backToTop.classList.remove('show');
            }
        });

        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }
    </script>
</body>
</html>