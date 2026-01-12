<?php
include 'includes/config.php';

$error_message = '';

if($_POST){
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if($user && password_verify($password, $user['password'])){
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        
        if($user['role'] == 'student'){
            header('Location: dashboard/student/index.php');
        } else {
            header('Location: dashboard/organizer/index.php');
        }
        exit();
    } else {
        $error_message = 'Email atau password salah!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CampusConnect</title>
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
        }

        .login-container {
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

        .login-left {
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

        .login-left::before {
            content: '';
            position: absolute;
            width: 250px;
            height: 250px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
            top: -80px;
            right: -80px;
        }

        .login-left::after {
            content: '';
            position: absolute;
            width: 180px;
            height: 180px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
            bottom: -60px;
            left: -60px;
        }

        .login-left-content {
            position: relative;
            z-index: 1;
            text-align: center;
        }

        .login-illustration {
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

        .login-left h2 {
            font-size: 2rem;
            margin-bottom: 0.75rem;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .login-left p {
            font-size: 0.95rem;
            opacity: 0.95;
            line-height: 1.6;
        }

        .login-right {
            padding: 3rem 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-header {
            margin-bottom: 2rem;
        }

        .login-header h3 {
            font-size: 1.75rem;
            color: #1f2937;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .login-header p {
            color: #6b7280;
            font-size: 0.9rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
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

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .checkbox-wrapper input[type="checkbox"] {
            width: 16px;
            height: 16px;
            cursor: pointer;
            accent-color: #dc2626;
        }

        .checkbox-wrapper label {
            color: #6b7280;
            font-size: 0.875rem;
            cursor: pointer;
            margin: 0;
            font-weight: 500;
        }

        .forgot-password {
            color: #dc2626;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .forgot-password:hover {
            color: #b91c1c;
        }

        .btn-login {
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
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(220, 38, 38, 0.35);
        }

        .btn-login:active {
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

        .register-link {
            text-align: center;
            color: #6b7280;
            font-size: 0.9rem;
        }

        .register-link a {
            color: #dc2626;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.3s ease;
        }

        .register-link a:hover {
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

        .alert-icon {
            font-size: 1.1rem;
        }

        /* Password Toggle */
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

        /* Mobile Responsive */
        @media (max-width: 968px) {
            .login-container {
                grid-template-columns: 1fr;
                max-width: 480px;
            }

            .login-left {
                padding: 2.5rem 2rem;
            }

            .login-illustration {
                width: 100px;
                height: 100px;
                font-size: 3.5rem;
            }

            .login-left h2 {
                font-size: 1.75rem;
            }

            .login-right {
                padding: 2.5rem 2rem;
            }
        }

        @media (max-width: 480px) {
            .main-content {
                padding: 1rem 0.75rem;
                margin-top: 70px;
            }

            .login-left,
            .login-right {
                padding: 2rem 1.5rem;
            }

            .login-header h3 {
                font-size: 1.5rem;
            }

            .login-illustration {
                width: 90px;
                height: 90px;
                font-size: 3rem;
            }

            .form-options {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
            }
        }
    </style>
</head>
<body>
    <!-- Include Header -->
    <?php include 'includes/header.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <div class="login-container">
            <!-- Left Side -->
            <div class="login-left">
                <div class="login-left-content">
                    <div class="login-illustration">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h2>Welcome Back!</h2>
                    <p>Connect with campus events and opportunities. Login to continue your journey with CampusConnect.</p>
                </div>
            </div>

            <!-- Right Side -->
            <div class="login-right">
                <div class="login-header">
                    <h3>Login to Account</h3>
                    <p>Enter your credentials to access your account</p>
                </div>

                <?php if($error_message): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle alert-icon"></i>
                    <span><?php echo $error_message; ?></span>
                </div>
                <?php endif; ?>

                <form method="POST" action="">
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
                                placeholder="Enter your password" 
                                required
                                autocomplete="current-password"
                            >
                            <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                        </div>
                    </div>

                    <div class="form-options">
                        <div class="checkbox-wrapper">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">Remember me</label>
                        </div>
                        <a href="password_reset.php" class="forgot-password">Forgot Password?</a>
                    </div>

                    <button type="submit" class="btn-login">
                        Login Now
                    </button>
                </form>

                <div class="divider">
                    <span>or</span>
                </div>

                <div class="register-link">
                    Don't have an account? <a href="register.php">Register here</a>
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

        // Auto-hide error message after 5 seconds
        const alertError = document.querySelector('.alert-error');
        if (alertError) {
            setTimeout(() => {
                alertError.style.animation = 'slideUp 0.3s ease';
                alertError.style.opacity = '0';
                setTimeout(() => {
                    alertError.remove();
                }, 300);
            }, 5000);
        }
    </script>
</body>
</html>