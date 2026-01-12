<?php
include 'includes/config.php';

$message = '';
$error = '';

if($_POST && isset($_POST['reset_password'])) {
    $email = $_POST['email'];
    
    // Check if email exists
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if($user) {
        // Generate reset token (in real application, send email)
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Store token in database (you'll need a password_resets table)
        // For now, we'll just show a message
        $message = "Password reset link has been sent to your email.";
    } else {
        $error = "Email not found in our system.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset - CampusConnect</title>
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

        .reset-container {
            background: white;
            border-radius: 24px;
            box-shadow: 0 8px 40px rgba(220, 38, 38, 0.08);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
            display: grid;
            grid-template-columns: 45% 55%;
            border: 1px solid rgba(220, 38, 38, 0.1);
        }

        .reset-left {
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

        .reset-left::before {
            content: '';
            position: absolute;
            width: 250px;
            height: 250px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
            top: -80px;
            right: -80px;
        }

        .reset-left::after {
            content: '';
            position: absolute;
            width: 180px;
            height: 180px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
            bottom: -60px;
            left: -60px;
        }

        .reset-left-content {
            position: relative;
            z-index: 1;
            text-align: center;
        }

        .reset-illustration {
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

        .reset-left h2 {
            font-size: 2rem;
            margin-bottom: 0.75rem;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .reset-left p {
            font-size: 0.95rem;
            opacity: 0.95;
            line-height: 1.6;
        }

        .reset-right {
            padding: 3rem 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .reset-header {
            margin-bottom: 2rem;
        }

        .reset-header h3 {
            font-size: 1.75rem;
            color: #1f2937;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .reset-header p {
            color: #6b7280;
            font-size: 0.9rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
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

        .form-hint {
            font-size: 0.8rem;
            color: #9ca3af;
            margin-top: 0.35rem;
        }

        .btn-reset {
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

        .btn-reset:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(220, 38, 38, 0.35);
        }

        .btn-reset:active {
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

        .back-link {
            text-align: center;
            color: #6b7280;
            font-size: 0.9rem;
        }

        .back-link a {
            color: #dc2626;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .back-link a:hover {
            color: #b91c1c;
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

        .info-box {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 12px;
            padding: 1rem;
            margin-top: 1rem;
            display: flex;
            align-items: start;
            gap: 0.75rem;
        }

        .info-box i {
            color: #3b82f6;
            font-size: 1.1rem;
            margin-top: 0.1rem;
        }

        .info-box-content {
            font-size: 0.85rem;
            color: #1e40af;
            line-height: 1.5;
        }

        /* Mobile Responsive */
        @media (max-width: 968px) {
            .reset-container {
                grid-template-columns: 1fr;
                max-width: 480px;
            }

            .reset-left {
                padding: 2.5rem 2rem;
            }

            .reset-illustration {
                width: 100px;
                height: 100px;
                font-size: 3.5rem;
            }

            .reset-left h2 {
                font-size: 1.75rem;
            }

            .reset-right {
                padding: 2.5rem 2rem;
            }
        }

        @media (max-width: 480px) {
            .main-content {
                padding: 1rem 0.75rem;
                margin-top: 70px;
            }

            .reset-left,
            .reset-right {
                padding: 2rem 1.5rem;
            }

            .reset-header h3 {
                font-size: 1.5rem;
            }

            .reset-illustration {
                width: 90px;
                height: 90px;
                font-size: 3rem;
            }
        }
    </style>
</head>
<body>
    <!-- Include Header -->
    <?php include 'includes/header.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <div class="reset-container">
            <!-- Left Side -->
            <div class="reset-left">
                <div class="reset-left-content">
                    <div class="reset-illustration">
                        <i class="fas fa-key"></i>
                    </div>
                    <h2>Forgot Password?</h2>
                    <p>Don't worry! Enter your email address and we'll send you a link to reset your password.</p>
                </div>
            </div>

            <!-- Right Side -->
            <div class="reset-right">
                <div class="reset-header">
                    <h3>Reset Password</h3>
                    <p>Enter your email to receive reset instructions</p>
                </div>

                <?php if($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle alert-icon"></i>
                    <span><?php echo $error; ?></span>
                </div>
                <?php endif; ?>

                <?php if($message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle alert-icon"></i>
                    <span><?php echo $message; ?></span>
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
                        <div class="form-hint">Enter the email associated with your account</div>
                    </div>

                    <button type="submit" name="reset_password" class="btn-reset">
                        Send Reset Link
                    </button>
                </form>

                <div class="info-box">
                    <i class="fas fa-info-circle"></i>
                    <div class="info-box-content">
                        <strong>Note:</strong> The password reset link will expire in 1 hour. Make sure to check your spam folder if you don't receive the email.
                    </div>
                </div>

                <div class="divider">
                    <span>or</span>
                </div>

                <div class="back-link">
                    <a href="login.php">
                        <i class="fas fa-arrow-left"></i>
                        Back to Login
                    </a>
                </div>
            </div>
        </div>
    </main>

    <!-- Include Footer -->
    <?php include 'includes/footer.php'; ?>

    <script>
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