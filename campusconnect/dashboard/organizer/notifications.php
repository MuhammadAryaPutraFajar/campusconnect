<?php
include '../../includes/config.php';
include '../../includes/auth.php';

if($_SESSION['role'] != 'organizer') {
    header('Location: ../../index.php');
    exit();
}

// Mark all notifications as read when page is loaded
if(isset($_GET['mark_all_read'])) {
    $update_sql = "UPDATE notifications SET is_read = 1 WHERE user_id = ?";
    $update_stmt = $pdo->prepare($update_sql);
    $update_stmt->execute([$_SESSION['user_id']]);
    
    header('Location: notifications.php');
    exit();
}

// Mark single notification as read
if(isset($_GET['mark_read'])) {
    $notification_id = $_GET['mark_read'];
    $update_sql = "UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?";
    $update_stmt = $pdo->prepare($update_sql);
    $update_stmt->execute([$notification_id, $_SESSION['user_id']]);
    
    header('Location: notifications.php');
    exit();
}

// Delete notification
if(isset($_GET['delete'])) {
    $notification_id = $_GET['delete'];
    $delete_sql = "DELETE FROM notifications WHERE id = ? AND user_id = ?";
    $delete_stmt = $pdo->prepare($delete_sql);
    $delete_stmt->execute([$notification_id, $_SESSION['user_id']]);
    
    header('Location: notifications.php');
    exit();
}

// Clear all read notifications
if(isset($_GET['clear_read'])) {
    $clear_sql = "DELETE FROM notifications WHERE user_id = ? AND is_read = 1";
    $clear_stmt = $pdo->prepare($clear_sql);
    $clear_stmt->execute([$_SESSION['user_id']]);
    
    header('Location: notifications.php');
    exit();
}

// Get all notifications for organizer
$sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['user_id']]);
$notifications = $stmt->fetchAll();

// Count unread notifications
$unread_sql = "SELECT COUNT(*) as unread_count FROM notifications WHERE user_id = ? AND is_read = 0";
$unread_stmt = $pdo->prepare($unread_sql);
$unread_stmt->execute([$_SESSION['user_id']]);
$unread_count = $unread_stmt->fetch()['unread_count'];

// Count read notifications
$read_sql = "SELECT COUNT(*) as read_count FROM notifications WHERE user_id = ? AND is_read = 1";
$read_stmt = $pdo->prepare($read_sql);
$read_stmt->execute([$_SESSION['user_id']]);
$read_count = $read_stmt->fetch()['read_count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - CampusConnect</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f8f9fa;
            color: #1a1a1a;
            line-height: 1.6;
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 50%, #991b1b 100%);
            color: white;
            padding: 3rem 2rem;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 50%, rgba(255,255,255,0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(255,255,255,0.1) 0%, transparent 50%);
            z-index: 0;
        }

        .hero-content {
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .hero-content h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .hero-content p {
            font-size: 1.1rem;
            opacity: 0.9;
            font-weight: 400;
        }

        /* Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 3rem 2rem;
        }

        /* Statistics Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            border: 1px solid rgba(0,0,0,0.05);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
        }

        .stat-card.total::before {
            background: linear-gradient(90deg, #3b82f6, #2563eb);
        }

        .stat-card.unread::before {
            background: linear-gradient(90deg, #dc2626, #991b1b);
        }

        .stat-card.read::before {
            background: linear-gradient(90deg, #10b981, #059669);
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.1);
        }

        .stat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-card.total .stat-icon {
            background: linear-gradient(135deg, #dbeafe, #bfdbfe);
        }

        .stat-card.unread .stat-icon {
            background: linear-gradient(135deg, #fee2e2, #fecaca);
        }

        .stat-card.read .stat-icon {
            background: linear-gradient(135deg, #d1fae5, #a7f3d0);
        }

        .stat-label {
            font-size: 0.875rem;
            color: #6b7280;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1a1a1a;
            line-height: 1;
        }

        /* Action Bar */
        .action-bar {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
            border: 1px solid rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .filter-tabs {
            display: flex;
            gap: 0.5rem;
            background: #f3f4f6;
            padding: 0.375rem;
            border-radius: 12px;
        }

        .filter-tab {
            padding: 0.625rem 1.25rem;
            background: transparent;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.875rem;
            transition: all 0.3s ease;
            color: #6b7280;
            font-family: 'Inter', sans-serif;
        }

        .filter-tab:hover {
            color: #1a1a1a;
        }

        .filter-tab.active {
            background: white;
            color: #dc2626;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .action-buttons {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .btn {
            padding: 0.625rem 1.25rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.875rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(59, 130, 246, 0.3);
        }

        .btn-danger {
            background: linear-gradient(135deg, #dc2626, #991b1b);
            color: white;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.2);
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(220, 38, 38, 0.3);
        }

        /* Notifications List */
        .notifications-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .notification-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            border: 2px solid #e5e7eb;
            transition: all 0.3s ease;
            position: relative;
        }

        .notification-card::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 5px;
            border-radius: 16px 0 0 16px;
        }

        .notification-card.unread {
            background: #fff5f5;
            border-color: #fecaca;
        }

        .notification-card.unread::before {
            background: linear-gradient(180deg, #dc2626, #991b1b);
        }

        .notification-card.read::before {
            background: linear-gradient(180deg, #6b7280, #4b5563);
        }

        .notification-card:hover {
            border-color: #dc2626;
            box-shadow: 0 8px 16px rgba(220, 38, 38, 0.1);
            transform: translateX(4px);
        }

        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .notification-meta {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .notification-type {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.375rem 0.875rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .type-application {
            background: linear-gradient(135deg, #dbeafe, #bfdbfe);
            color: #1e40af;
        }

        .type-system {
            background: linear-gradient(135deg, #f3e8ff, #e9d5ff);
            color: #6b21a8;
        }

        .type-event {
            background: linear-gradient(135deg, #d1fae5, #a7f3d0);
            color: #065f46;
        }

        .notification-status {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.375rem 0.875rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .notification-status.unread {
            background: #fee2e2;
            color: #991b1b;
        }

        .notification-status.read {
            background: #f3f4f6;
            color: #4b5563;
        }

        .notification-time {
            color: #6b7280;
            font-size: 0.875rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }

        .notification-message {
            font-size: 1rem;
            line-height: 1.6;
            color: #1a1a1a;
            margin-bottom: 1rem;
        }

        .notification-actions {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .btn-small {
            padding: 0.5rem 1rem;
            font-size: 0.8rem;
            border-radius: 8px;
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.2);
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 16px;
            border: 2px dashed #e5e7eb;
        }

        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            color: #1a1a1a;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .empty-state p {
            color: #6b7280;
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 1.75rem;
            }

            .hero-content p {
                font-size: 1rem;
            }

            .container {
                padding: 2rem 1.5rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .stat-card {
                padding: 1.5rem;
            }

            .stat-number {
                font-size: 2rem;
            }

            .action-bar {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-tabs {
                width: 100%;
                flex-wrap: wrap;
            }

            .filter-tab {
                flex: 1;
                min-width: fit-content;
            }

            .action-buttons {
                width: 100%;
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }

            .notification-header {
                flex-direction: column;
            }

            .notification-meta {
                width: 100%;
            }

            .notification-actions {
                flex-direction: column;
            }

            .btn-small {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .hero-content h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <?php include '../../includes/header_organizer.php'; ?>
    
    <!-- Hero Section -->
    <div class="hero-section">
        <div class="hero-content">
            <h1><span>🔔</span> Notifications</h1>
            <p>Stay updated with your latest activities and updates</p>
        </div>
    </div>

    <div class="container">
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card total">
                <div class="stat-header">
                    <div class="stat-icon">📊</div>
                </div>
                <div class="stat-label">Total Notifications</div>
                <div class="stat-number"><?= count($notifications) ?></div>
            </div>
            <div class="stat-card unread">
                <div class="stat-header">
                    <div class="stat-icon">🔴</div>
                </div>
                <div class="stat-label">Unread</div>
                <div class="stat-number"><?= $unread_count ?></div>
            </div>
            <div class="stat-card read">
                <div class="stat-header">
                    <div class="stat-icon">✅</div>
                </div>
                <div class="stat-label">Read</div>
                <div class="stat-number"><?= $read_count ?></div>
            </div>
        </div>

        <!-- Action Bar -->
        <div class="action-bar">
            <div class="filter-tabs">
                <button class="filter-tab active" onclick="filterNotifications('all', this)">
                    All Notifications
                </button>
                <button class="filter-tab" onclick="filterNotifications('unread', this)">
                    Unread Only
                </button>
                <button class="filter-tab" onclick="filterNotifications('read', this)">
                    Read Only
                </button>
            </div>
            <div class="action-buttons">
                <?php if($unread_count > 0): ?>
                <a href="?mark_all_read" class="btn btn-primary">
                    <span>✓</span>
                    <span>Mark All as Read</span>
                </a>
                <?php endif; ?>
                <?php if($read_count > 0): ?>
                <a href="?clear_read" class="btn btn-danger" onclick="return confirm('Are you sure you want to clear all read notifications?')">
                    <span>🗑️</span>
                    <span>Clear Read</span>
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Notifications List -->
        <?php if(count($notifications) > 0): ?>
            <div class="notifications-list" id="notificationsList">
                <?php foreach($notifications as $notif): 
                    // Determine notification type based on message content
                    $type = 'system';
                    if(strpos(strtolower($notif['message']), 'application') !== false || 
                       strpos(strtolower($notif['message']), 'applied') !== false) {
                        $type = 'application';
                    } elseif(strpos(strtolower($notif['message']), 'event') !== false) {
                        $type = 'event';
                    }
                    
                    $type_icon = [
                        'application' => '📝',
                        'event' => '🎯',
                        'system' => '⚙️'
                    ];
                ?>
                <div class="notification-card <?= $notif['is_read'] ? 'read' : 'unread' ?>" data-status="<?= $notif['is_read'] ? 'read' : 'unread' ?>">
                    <div class="notification-header">
                        <div class="notification-meta">
                            <span class="notification-type type-<?= $type ?>">
                                <span><?= $type_icon[$type] ?></span>
                                <span><?= ucfirst($type) ?></span>
                            </span>
                            <span class="notification-status <?= $notif['is_read'] ? 'read' : 'unread' ?>">
                                <span><?= $notif['is_read'] ? '✓' : '●' ?></span>
                                <span><?= $notif['is_read'] ? 'Read' : 'Unread' ?></span>
                            </span>
                        </div>
                        <div class="notification-time">
                            <span>🕒</span>
                            <span><?= date('M j, Y g:i A', strtotime($notif['created_at'])) ?></span>
                        </div>
                    </div>
                    
                    <div class="notification-message">
                        <?= htmlspecialchars($notif['message']) ?>
                    </div>
                    
                    <div class="notification-actions">
                        <?php if(!$notif['is_read']): ?>
                        <a href="?mark_read=<?= $notif['id'] ?>" class="btn btn-small btn-success">
                            <span>✓</span>
                            <span>Mark as Read</span>
                        </a>
                        <?php endif; ?>
                        <a href="?delete=<?= $notif['id'] ?>" class="btn btn-small btn-danger" 
                           onclick="return confirm('Are you sure you want to delete this notification?')">
                            <span>🗑️</span>
                            <span>Delete</span>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">🔔</div>
                <h3>No Notifications</h3>
                <p>You don't have any notifications at the moment.</p>
                <p>Notifications will appear here when you receive new applications or important updates.</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function filterNotifications(type, element) {
            const notifications = document.querySelectorAll('.notification-card');
            const tabs = document.querySelectorAll('.filter-tab');
            
            // Update active tab
            tabs.forEach(tab => tab.classList.remove('active'));
            element.classList.add('active');
            
            // Filter notifications
            notifications.forEach(notification => {
                const status = notification.getAttribute('data-status');
                
                if (type === 'all') {
                    notification.style.display = 'block';
                } else if (type === 'unread') {
                    notification.style.display = status === 'unread' ? 'block' : 'none';
                } else if (type === 'read') {
                    notification.style.display = status === 'read' ? 'block' : 'none';
                }
            });
        }
        
        // Mark notification as read when clicked on message area (optional)
        document.addEventListener('DOMContentLoaded', function() {
            const notifications = document.querySelectorAll('.notification-card.unread');
            notifications.forEach(notification => {
                const messageArea = notification.querySelector('.notification-message');
                messageArea.style.cursor = 'pointer';
                
                messageArea.addEventListener('click', function(e) {
                    if (!e.target.closest('.notification-actions')) {
                        const markReadLink = notification.querySelector('a[href*="mark_read"]');
                        if (markReadLink && confirm('Mark this notification as read?')) {
                            window.location.href = markReadLink.href;
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>