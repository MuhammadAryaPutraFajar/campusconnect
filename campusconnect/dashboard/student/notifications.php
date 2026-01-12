<?php
include '../../includes/config.php';
include '../../includes/auth.php';

if($_SESSION['role'] != 'student') {
    header('Location: ../../index.php');
    exit();
}

// Mark all notifications as read when page is loaded
$update_sql = "UPDATE notifications SET is_read = 1 WHERE user_id = ?";
$update_stmt = $pdo->prepare($update_sql);
$update_stmt->execute([$_SESSION['user_id']]);

// Get all notifications
$sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['user_id']]);
$notifications = $stmt->fetchAll();

// Calculate statistics
$total_notifications = count($notifications);
$today = date('Y-m-d');
$today_notifications = count(array_filter($notifications, function($n) use ($today) {
    return date('Y-m-d', strtotime($n['created_at'])) == $today;
}));
$this_week = date('Y-m-d', strtotime('-7 days'));
$week_notifications = count(array_filter($notifications, function($n) use ($this_week) {
    return strtotime($n['created_at']) >= strtotime($this_week);
}));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - CampusConnect</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8fafc;
            min-height: 100vh;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem 1.5rem;
        }

        /* Page Header */
        .page-header {
            margin-bottom: 2rem;
        }

        .page-header h1 {
            font-size: 2rem;
            color: #1f2937;
            margin-bottom: 0.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .page-header h1 i {
            color: #dc2626;
        }

        .page-header p {
            color: #6b7280;
            font-size: 0.95rem;
        }

        /* Statistics Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.25rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            gap: 1.25rem;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-icon.total {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #1e40af;
        }

        .stat-icon.today {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #92400e;
        }

        .stat-icon.week {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #065f46;
        }

        .stat-content h3 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.25rem;
        }

        .stat-content p {
            font-size: 0.875rem;
            color: #6b7280;
            font-weight: 600;
        }

        /* Notifications Header */
        .notifications-header {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notifications-header h2 {
            font-size: 1.5rem;
            color: #1f2937;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .mark-read-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: #d1fae5;
            color: #065f46;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        /* Notifications List */
        .notifications-list {
            display: grid;
            gap: 1rem;
        }

        .notification-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 1.25rem;
            align-items: start;
        }

        .notification-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .notification-card.read {
            opacity: 0.7;
        }

        .notification-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .notification-icon.info {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #1e40af;
        }

        .notification-icon.success {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #065f46;
        }

        .notification-icon.warning {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #92400e;
        }

        .notification-icon.danger {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #991b1b;
        }

        .notification-content {
            flex: 1;
        }

        .notification-message {
            font-size: 0.95rem;
            color: #1f2937;
            line-height: 1.6;
            margin-bottom: 0.5rem;
        }

        .notification-time {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.8rem;
            color: #9ca3af;
        }

        .notification-time i {
            font-size: 0.75rem;
        }

        .notification-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.4rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            flex-shrink: 0;
        }

        .notification-badge.new {
            background: #fee2e2;
            color: #991b1b;
        }

        .notification-badge.read {
            background: #f3f4f6;
            color: #6b7280;
        }

        /* Timeline Grouping */
        .timeline-group {
            margin-bottom: 2rem;
        }

        .timeline-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
            padding-left: 0.5rem;
        }

        .timeline-header h3 {
            font-size: 0.95rem;
            color: #6b7280;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .timeline-line {
            flex: 1;
            height: 2px;
            background: linear-gradient(90deg, #e5e7eb 0%, transparent 100%);
        }

        /* Empty State */
        .empty-state {
            background: white;
            border-radius: 16px;
            padding: 4rem 2rem;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
        }

        .empty-icon {
            width: 120px;
            height: 120px;
            margin: 0 auto 1.5rem;
            border-radius: 50%;
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3.5rem;
            color: #dc2626;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            color: #1f2937;
            margin-bottom: 0.75rem;
            font-weight: 700;
        }

        .empty-state p {
            color: #6b7280;
            font-size: 0.95rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 1.5rem 1rem;
            }

            .page-header h1 {
                font-size: 1.5rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .notifications-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .notification-card {
                grid-template-columns: auto 1fr;
                gap: 1rem;
            }

            .notification-badge {
                grid-column: 1 / -1;
                justify-self: start;
            }
        }

        @media (max-width: 480px) {
            .stat-card {
                flex-direction: column;
                text-align: center;
            }

            .notification-card {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .notification-icon {
                margin: 0 auto;
            }

            .notification-time {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <?php include '../../includes/header_student.php'; ?>
    
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1>
                <i class="fas fa-bell"></i>
                Notifications
            </h1>
            <p>Stay updated with your application status and important announcements</p>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon total">
                    <i class="fas fa-inbox"></i>
                </div>
                <div class="stat-content">
                    <h3><?= $total_notifications ?></h3>
                    <p>Total Notifications</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon today">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div class="stat-content">
                    <h3><?= $today_notifications ?></h3>
                    <p>Today</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon week">
                    <i class="fas fa-calendar-week"></i>
                </div>
                <div class="stat-content">
                    <h3><?= $week_notifications ?></h3>
                    <p>This Week</p>
                </div>
            </div>
        </div>

        <?php if(count($notifications) > 0): ?>
            <!-- Notifications Header -->
            <div class="notifications-header">
                <h2>
                    <i class="fas fa-list"></i>
                    All Notifications
                </h2>
                <div class="mark-read-badge">
                    <i class="fas fa-check-double"></i>
                    All marked as read
                </div>
            </div>

            <!-- Notifications List -->
            <div class="notifications-list">
                <?php 
                $current_date = '';
                foreach($notifications as $notif): 
                    $notif_date = date('Y-m-d', strtotime($notif['created_at']));
                    $display_date = '';
                    
                    if($notif_date == date('Y-m-d')) {
                        $display_date = 'Today';
                    } elseif($notif_date == date('Y-m-d', strtotime('-1 day'))) {
                        $display_date = 'Yesterday';
                    } else {
                        $display_date = date('l, d F Y', strtotime($notif['created_at']));
                    }
                    
                    if($current_date != $display_date):
                        if($current_date != '') echo '</div>';
                        $current_date = $display_date;
                ?>
                <div class="timeline-group">
                    <div class="timeline-header">
                        <h3><?= $display_date ?></h3>
                        <div class="timeline-line"></div>
                    </div>
                <?php endif; ?>
                
                <div class="notification-card <?= $notif['is_read'] ? 'read' : 'unread' ?>">
                    <div class="notification-icon <?= strpos(strtolower($notif['message']), 'accepted') !== false ? 'success' : (strpos(strtolower($notif['message']), 'rejected') !== false ? 'danger' : (strpos(strtolower($notif['message']), 'pending') !== false ? 'warning' : 'info')) ?>">
                        <?php if(strpos(strtolower($notif['message']), 'accepted') !== false): ?>
                            <i class="fas fa-check-circle"></i>
                        <?php elseif(strpos(strtolower($notif['message']), 'rejected') !== false): ?>
                            <i class="fas fa-times-circle"></i>
                        <?php elseif(strpos(strtolower($notif['message']), 'pending') !== false): ?>
                            <i class="fas fa-clock"></i>
                        <?php else: ?>
                            <i class="fas fa-info-circle"></i>
                        <?php endif; ?>
                    </div>
                    
                    <div class="notification-content">
                        <p class="notification-message"><?= htmlspecialchars($notif['message']) ?></p>
                        <div class="notification-time">
                            <i class="fas fa-clock"></i>
                            <span><?= date('H:i', strtotime($notif['created_at'])) ?></span>
                        </div>
                    </div>
                    
                    <div class="notification-badge <?= $notif['is_read'] ? 'read' : 'new' ?>">
                        <?php if($notif['is_read']): ?>
                            <i class="fas fa-eye"></i>
                            Read
                        <?php else: ?>
                            <i class="fas fa-circle"></i>
                            New
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <!-- Empty State -->
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-bell-slash"></i>
                </div>
                <h3>No Notifications Yet</h3>
                <p>You're all caught up! Check back later for updates.</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Animation on load
        document.addEventListener('DOMContentLoaded', () => {
            const notificationCards = document.querySelectorAll('.notification-card');
            
            notificationCards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.3s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 50);
            });
        });
    </script>
</body>
</html>