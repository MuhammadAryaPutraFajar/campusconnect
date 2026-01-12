<?php
include '../../includes/config.php';
include '../../includes/auth.php';

if($_SESSION['role'] != 'student') {
    header('Location: ../../index.php');
    exit();
}

// Check if student has completed profile
$sql = "SELECT is_profile_complete FROM students WHERE user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['user_id']]);
$student = $stmt->fetch();

if(!$student || !$student['is_profile_complete']) {
    header('Location: profile.php');
    exit();
}

$event_id = $_GET['event_id'] ?? 0;

// Get event details
$sql = "SELECT * FROM events WHERE id = ? AND end_date > NOW()";
$stmt = $pdo->prepare($sql);
$stmt->execute([$event_id]);
$event = $stmt->fetch();

if(!$event) {
    header('Location: index.php');
    exit();
}

// Check if already applied
$sql = "SELECT id FROM applications WHERE event_id = ? AND student_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$event_id, $_SESSION['user_id']]);
$existing_application = $stmt->fetch();

if($existing_application) {
    header('Location: history.php');
    exit();
}

// Handle application
if($_POST && isset($_POST['apply'])) {
    $sql = "INSERT INTO applications (event_id, student_id) VALUES (?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$event_id, $_SESSION['user_id']]);
    
    // Create notification for organizer
    $notif_sql = "INSERT INTO notifications (user_id, message) 
                  SELECT organizer_id, CONCAT('New application for ', name, ' from student') 
                  FROM events WHERE id = ?";
    $notif_stmt = $pdo->prepare($notif_sql);
    $notif_stmt->execute([$event_id]);
    
    header('Location: history.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply Event - CampusConnect</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #fafafa;
            color: #1a1a1a;
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Main Content Wrapper */
        .main-wrapper {
            flex: 1;
            margin-top: 100px;
            padding-bottom: 3rem;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #dc2626;
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 2rem;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }

        .back-link:hover {
            gap: 0.75rem;
        }

        .page-header {
            margin-bottom: 3rem;
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 0.5rem;
            letter-spacing: -1px;
        }

        .page-subtitle {
            font-size: 1.05rem;
            color: #666;
        }

        .event-details-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            margin-bottom: 2rem;
            border: 1px solid #f0f0f0;
        }

        .event-poster {
            width: 100%;
            height: 400px;
            object-fit: cover;
            background: linear-gradient(135deg, #fecaca 0%, #dc2626 100%);
        }

        .event-content {
            padding: 2.5rem;
        }

        .event-badge {
            display: inline-block;
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }

        .event-title {
            font-size: 2rem;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 2rem;
            line-height: 1.3;
        }

        .event-info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .info-item {
            background: #f9fafb;
            padding: 1.5rem;
            border-radius: 12px;
            border: 1px solid #f0f0f0;
        }

        .info-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.8rem;
            color: #666;
            font-weight: 600;
            margin-bottom: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-value {
            font-size: 1.05rem;
            color: #1a1a1a;
            font-weight: 600;
        }

        .event-description {
            background: #f9fafb;
            padding: 1.5rem;
            border-radius: 12px;
            border: 1px solid #f0f0f0;
        }

        .event-description h4 {
            font-size: 0.85rem;
            color: #666;
            font-weight: 600;
            margin-bottom: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .event-description p {
            color: #1a1a1a;
            line-height: 1.8;
            font-size: 0.98rem;
        }

        .confirmation-card {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            border-radius: 20px;
            padding: 3rem 2.5rem;
            text-align: center;
            border: 1px solid #fecaca;
        }

        .confirmation-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }

        .confirmation-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 0.75rem;
        }

        .confirmation-text {
            font-size: 1rem;
            color: #666;
            margin-bottom: 2.5rem;
            line-height: 1.6;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .btn {
            padding: 1rem 2.5rem;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(220, 38, 38, 0.3);
        }

        .btn-secondary {
            background: white;
            color: #666;
            border: 2px solid #e5e5e5;
        }

        .btn-secondary:hover {
            border-color: #dc2626;
            color: #dc2626;
        }

        /* Footer */
        .footer {
            background: white;
            border-top: 1px solid #e5e5e5;
            padding: 2rem;
            margin-top: auto;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 2rem;
            flex-wrap: wrap;
            text-align: center;
        }

        .footer-content p {
            color: #666;
            font-size: 0.9rem;
            margin: 0;
        }

        .footer-divider {
            color: #e5e5e5;
        }

        .footer-link {
            color: #dc2626;
            text-decoration: none;
            font-weight: 500;
            transition: opacity 0.3s;
        }

        .footer-link:hover {
            opacity: 0.7;
        }

        @media (max-width: 768px) {
            .main-wrapper {
                margin-top: 80px;
            }

            .page-title {
                font-size: 2rem;
            }

            .event-poster {
                height: 250px;
            }

            .event-content {
                padding: 1.5rem;
            }

            .event-title {
                font-size: 1.5rem;
            }

            .event-info-grid {
                grid-template-columns: 1fr;
            }

            .confirmation-card {
                padding: 2rem 1.5rem;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }

            .footer-content {
                flex-direction: column;
                gap: 0.5rem;
            }

            .footer-divider {
                display: none;
            }
        }
    </style>
</head>
<body>
    <?php include '../../includes/header_student.php'; ?>
    
    <div class="main-wrapper">
        <div class="container">
            <a href="index.php" class="back-link">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                Back to Dashboard
            </a>

            <div class="page-header">
                <h1 class="page-title">Apply for Event</h1>
                <p class="page-subtitle">Review the event details before submitting your application</p>
            </div>

            <!-- Event Details Card -->
            <div class="event-details-card">
                <?php if($event['poster']): ?>
                <img src="../../<?= htmlspecialchars($event['poster']) ?>" alt="<?= htmlspecialchars($event['name']) ?>" class="event-poster">
                <?php else: ?>
                <div class="event-poster"></div>
                <?php endif; ?>
                
                <div class="event-content">
                    <span class="event-badge"><?= htmlspecialchars($event['type']) ?></span>
                    
                    <h2 class="event-title"><?= htmlspecialchars($event['name']) ?></h2>
                    
                    <div class="event-info-grid">
                        <div class="info-item">
                            <div class="info-label">
                                📅 Start Date
                            </div>
                            <div class="info-value">
                                <?= date('d M Y, H:i', strtotime($event['start_date'])) ?>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">
                                📅 End Date
                            </div>
                            <div class="info-value">
                                <?= date('d M Y, H:i', strtotime($event['end_date'])) ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="event-description">
                        <h4>Description</h4>
                        <p><?= nl2br(htmlspecialchars($event['description'])) ?></p>
                    </div>
                </div>
            </div>

            <!-- Confirmation Card -->
            <form method="POST">
                <div class="confirmation-card">
                    <div class="confirmation-icon">✨</div>
                    <h3 class="confirmation-title">Ready to Apply?</h3>
                    <p class="confirmation-text">
                        By submitting this application, you confirm your interest in joining this event.<br>
                        The organizer will review your application and get back to you soon.
                    </p>
                    
                    <div class="action-buttons">
                        <button type="submit" name="apply" class="btn btn-primary">
                            Submit Application
                        </button>
                        <a href="index.php" class="btn btn-secondary">
                            Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <p>&copy; 2025 CampusConnect</p>
            <span class="footer-divider">•</span>
            <p>Ads Contact: <a href="mailto:ads@campusconnect.com" class="footer-link">ads@campusconnect.com</a></p>
            <span class="footer-divider">•</span>
            <p>+62 812-3456-7890</p>
        </div>
    </footer>
</body>
</html>