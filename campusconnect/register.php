<?php
include 'includes/config.php';
include 'includes/security.php';

$error_message = '';
$success_message = '';

if($_POST){
    // Validate CSRF token
    if(!validate_csrf_token($_POST['csrf_token'])) {
        $error_message = "CSRF token validation failed. Please try again.";
    } else {
        $username = sanitize_input($_POST['username']);
        $email = sanitize_input($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = 'student';
        
        // Validate input
        if(strlen($username) < 3) {
            $error_message = "Username must be at least 3 characters long";
        } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = "Invalid email format";
        } elseif(strlen($_POST['password']) < 6) {
            $error_message = "Password must be at least 6 characters long";
        } else {
            $sql = "INSERT INTO users (username, email, password, role) VALUES (?,?,?,?)";
            $stmt = $pdo->prepare($sql);
            if($stmt->execute([$username, $email, $password, $role])){
                $success_message = "Registration successful! Redirecting to login...";
                header('refresh:2;url=login.php');
            } else {
                $error_message = "Registration failed. Username or email might be already taken.";
            }
        }
    }
}

$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - CampusConnect</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #fef2f2 0%, #ffffff 50%, #fef2f2 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            margin-top: 80px;
            margin-bottom: 2rem;
        }

        .register-container {
            background: white;
            border-radius: 24px;
            box-shadow: 0 8px 40px rgba(220, 38, 38, 0.08);
            overflow: hidden;
            max-width: 950px;
            width: 100%;
            display: grid;
            grid-template-columns: 45% 55%;
            border: 1px solid rgba(220, 38, 38, 0.1);
        }

        .register-left {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            padding: 3rem 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .register-left::before {
            content: '';
            position: absolute;
            width: 250px;
            height: 250px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
            top: -80px;
            right: -80px;
        }

        .register-left::after {
            content: '';
            position: absolute;
            width: 180px;
            height: 180px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
            bottom: -60px;
            left: -60px;
        }

        .register-left-content {
            position: relative;
            z-index: 1;
            text-align: center;
        }

        .register-illustration {
            width: 120px;
            height: 120px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            margin: 0 auto 1.5rem;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .register-left h2 {
            font-size: 2rem;
            margin-bottom: 0.75rem;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .register-left p {
            font-size: 0.95rem;
            opacity: 0.95;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .features-list {
            text-align: left;
            max-width: 280px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.875rem;
            font-size: 0.9rem;
            opacity: 0.95;
        }

        .feature-item i {
            font-size: 1.1rem;
            width: 20px;
        }

        .register-right {
            padding: 3rem 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .register-header {
            margin-bottom: 1.75rem;
        }

        .register-header h3 {
            font-size: 1.75rem;
            color: #1f2937;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .register-header p {
            color: #6b7280;
            font-size: 0.9rem;
        }

        .form-group {
            margin-bottom: 1.15rem;
        }

        .form-group label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
            color: #374151;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .label-icon {
            color: #dc2626;
            font-size: 0.9rem;
        }

        .input-wrapper {
            position: relative;
        }

        .form-control {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            background: #f9fafb;
            color: #1f2937;
        }

        .form-control:focus {
            outline: none;
            border-color: #dc2626;
            background: white;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.08);
        }

        .form-control::placeholder {
            color: #9ca3af;
        }

        .password-strength {
            margin-top: 0.5rem;
        }

        .strength-bar {
            height: 4px;
            background: #e5e7eb;
            border-radius: 2px;
            margin-bottom: 0.4rem;
            overflow: hidden;
        }

        .strength-fill {
            height: 100%;
            width: 0%;
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        .strength-weak { width: 33%; background: #ef4444; }
        .strength-medium { width: 66%; background: #f59e0b; }
        .strength-strong { width: 100%; background: #10b981; }

        .form-hint {
            font-size: 0.8rem;
            color: #9ca3af;
            margin-top: 0.35rem;
        }

        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #9ca3af;
            font-size: 1rem;
            transition: color 0.3s ease;
        }

        .password-toggle:hover {
            color: #dc2626;
        }

        .btn-register {
            width: 100%;
            padding: 0.95rem;
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(220, 38, 38, 0.25);
            letter-spacing: 0.3px;
            margin-top: 0.5rem;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(220, 38, 38, 0.35);
        }

        .btn-register:active {
            transform: translateY(0);
        }

        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 1.5rem 0;
            color: #9ca3af;
            font-size: 0.85rem;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e5e7eb;
        }

        .divider span {
            padding: 0 1rem;
        }

        .login-link {
            text-align: center;
            color: #6b7280;
            font-size: 0.9rem;
        }

        .login-link a {
            color: #dc2626;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.3s ease;
        }

        .login-link a:hover {
            color: #b91c1c;
            text-decoration: underline;
        }

        .alert {
            padding: 0.875rem 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            animation: slideDown 0.3s ease;
            font-size: 0.875rem;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        .alert-success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
        }

        .alert-icon {
            font-size: 1.1rem;
        }

        /* Mobile Responsive */
        @media (max-width: 968px) {
            .register-container {
                grid-template-columns: 1fr;
                max-width: 480px;
            }

            .register-left {
                padding: 2.5rem 2rem;
            }

            .register-illustration {
                width: 100px;
                height: 100px;
                font-size: 3.5rem;
            }

            .register-left h2 {
                font-size: 1.75rem;
            }

            .features-list {
                max-width: 100%;
            }

            .register-right {
                padding: 2.5rem 2rem;
            }
        }

        @media (max-width: 480px) {
            .main-content {
                padding: 1rem 0.75rem;
                margin-top: 70px;
            }

            .register-left,
            .register-right {
                padding: 2rem 1.5rem;
            }

            .register-header h3 {
                font-size: 1.5rem;
            }

            .register-illustration {
                width: 90px;
                height: 90px;
                font-size: 3rem;
            }

            .features-list {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Include Header -->
    <?php include 'includes/header.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <div class="register-container">
            <!-- Left Side -->
            <div class="register-left">
                <div class="register-left-content">
                    <div class="register-illustration">
                        <i class="fas fa-rocket"></i>
                    </div>
                    <h2>Join CampusConnect</h2>
                    <p>Start your journey with us and discover amazing campus events and opportunities!</p>
                    
                    <div class="features-list">
                        <div class="feature-item">
                            <i class="fas fa-star"></i>
                            <span>Access exclusive campus events</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-users"></i>
                            <span>Connect with organizers</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-trophy"></i>
                            <span>Track your participation</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side -->
            <div class="register-right">
                <div class="register-header">
                    <h3>Create Account</h3>
                    <p>Fill in your details to get started</p>
                </div>

                <?php if($error_message): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle alert-icon"></i>
                    <span><?php echo $error_message; ?></span>
                </div>
                <?php endif; ?>

                <?php if($success_message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle alert-icon"></i>
                    <span><?php echo $success_message; ?></span>
                </div>
                <?php endif; ?>

                <form method="POST" action="" id="registerForm">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    
                    <div class="form-group">
                        <label for="username">
                            <i class="fas fa-user label-icon"></i>
                            Username
                        </label>
                        <div class="input-wrapper">
                            <input 
                                type="text" 
                                id="username"
                                name="username" 
                                class="form-control" 
                                placeholder="Choose a username" 
                                required
                                minlength="3"
                                autocomplete="username"
                            >
                        </div>
                        <div class="form-hint">Minimum 3 characters</div>
                    </div>

                    <div class="form-group">
                        <label for="email">
                            <i class="fas fa-envelope label-icon"></i>
                            Email Address
                        </label>
                        <div class="input-wrapper">
                            <input 
                                type="email" 
                                id="email"
                                name="email" 
                                class="form-control" 
                                placeholder="your.email@example.com" 
                                required
                                autocomplete="email"
                            >
                        </div>
                        <div class="form-hint">We'll never share your email</div>
                    </div>

                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock label-icon"></i>
                            Password
                        </label>
                        <div class="input-wrapper">
                            <input 
                                type="password" 
                                id="password"
                                name="password" 
                                class="form-control" 
                                placeholder="Create a strong password" 
                                required
                                minlength="6"
                                autocomplete="new-password"
                            >
                            <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                        </div>
                        <div class="password-strength">
                            <div class="strength-bar">
                                <div class="strength-fill" id="strengthFill"></div>
                            </div>
                            <div class="form-hint" id="strengthText">Minimum 6 characters</div>
                        </div>
                    </div>

                    <button type="submit" class="btn-register">
                        Create Account
                    </button>
                </form>

                <div class="divider">
                    <span>or</span>
                </div>

                <div class="login-link">
                    Already have an account? <a href="login.php">Login here</a>
                </div>
            </div>
        </div>
    </main>

    <!-- Include Footer -->
    <?php include 'includes/footer.php'; ?>

    <script>
        // Password toggle functionality
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });

        // Password strength indicator
        const strengthFill = document.getElementById('strengthFill');
        const strengthText = document.getElementById('strengthText');

        passwordInput.addEventListener('input', function() {
            const password = this.value;
            const length = password.length;
            
            strengthFill.className = 'strength-fill';
            
            if (length === 0) {
                strengthText.textContent = 'Minimum 6 characters';
                strengthText.style.color = '#9ca3af';
            } else if (length < 6) {
                strengthFill.classList.add('strength-weak');
                strengthText.textContent = 'Weak password';
                strengthText.style.color = '#ef4444';
            } else if (length < 10) {
                strengthFill.classList.add('strength-medium');
                strengthText.textContent = 'Medium password';
                strengthText.style.color = '#f59e0b';
            } else {
                strengthFill.classList.add('strength-strong');
                strengthText.textContent = 'Strong password';
                strengthText.style.color = '#10b981';
            }
        });

        // Auto-hide alert messages after 5 seconds
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.animation = 'slideUp 0.3s ease';
                alert.style.opacity = '0';
                setTimeout(() => {
                    alert.remove();
                }, 300);
            }, 5000);
        });
    </script>
</body>
</html>