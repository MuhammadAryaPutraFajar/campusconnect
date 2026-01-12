<?php
include '../../includes/config.php';
include '../../includes/auth.php';

if($_SESSION['role'] != 'organizer') {
    header('Location: ../../index.php');
    exit();
}

// Get organizer stats
$events_sql = "SELECT COUNT(*) as total_events FROM events WHERE organizer_id = ?";
$events_stmt = $pdo->prepare($events_sql);
$events_stmt->execute([$_SESSION['user_id']]);
$total_events = $events_stmt->fetch()['total_events'];

$apps_sql = "SELECT COUNT(*) as total_applications 
             FROM applications a 
             JOIN events e ON a.event_id = e.id 
             WHERE e.organizer_id = ?";
$apps_stmt = $pdo->prepare($apps_sql);
$apps_stmt->execute([$_SESSION['user_id']]);
$total_applications = $apps_stmt->fetch()['total_applications'];

$pending_sql = "SELECT COUNT(*) as pending_applications 
                FROM applications a 
                JOIN events e ON a.event_id = e.id 
                WHERE e.organizer_id = ? AND a.status = 'pending'";
$pending_stmt = $pdo->prepare($pending_sql);
$pending_stmt->execute([$_SESSION['user_id']]);
$pending_applications = $pending_stmt->fetch()['pending_applications'];

// Get recent events
$recent_events_sql = "SELECT * FROM events WHERE organizer_id = ? ORDER BY created_at DESC LIMIT 8";
$recent_events_stmt = $pdo->prepare($recent_events_sql);
$recent_events_stmt->execute([$_SESSION['user_id']]);
$recent_events = $recent_events_stmt->fetchAll();

// Get recent applications
$recent_apps_sql = "SELECT a.*, e.name as event_name, s.full_name 
                    FROM applications a 
                    JOIN events e ON a.event_id = e.id 
                    JOIN students s ON a.student_id = s.user_id 
                    WHERE e.organizer_id = ? 
                    ORDER BY a.applied_at DESC 
                    LIMIT 10";
$recent_apps_stmt = $pdo->prepare($recent_apps_sql);
$recent_apps_stmt->execute([$_SESSION['user_id']]);
$recent_apps = $recent_apps_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organizer Dashboard - CampusConnect</title>
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
            padding: 3rem 2rem 8rem;
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
            max-width: 1400px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .hero-content h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            letter-spacing: -0.5px;
        }

        .hero-content p {
            font-size: 1.1rem;
            opacity: 0.9;
            font-weight: 400;
        }

        /* Main Dashboard Container */
        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem 4rem;
        }

        /* Statistics Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin-top: -5rem;
            margin-bottom: 3rem;
            position: relative;
            z-index: 10;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05), 0 10px 20px rgba(0,0,0,0.05);
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
            background: linear-gradient(90deg, #dc2626, #991b1b);
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(220, 38, 38, 0.15);
        }

        .stat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-card h3 {
            font-size: 0.875rem;
            color: #6b7280;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1a1a1a;
            line-height: 1;
        }

        .stat-label {
            font-size: 0.875rem;
            color: #6b7280;
            margin-top: 0.5rem;
        }

        /* Action Buttons */
        .action-buttons {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 3rem;
        }

        .btn-action {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            padding: 1.25rem 2rem;
            background: white;
            color: #1a1a1a;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            border: 2px solid #e5e7eb;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .btn-action:hover {
            background: #dc2626;
            color: white;
            border-color: #dc2626;
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(220, 38, 38, 0.2);
        }

        .btn-action .icon {
            font-size: 1.25rem;
        }

        /* Content Grid Layout */
        .content-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
        }

        /* Section Container */
        .section {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.05);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f3f4f6;
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.5rem;
            font-weight: 700;
            color: #1a1a1a;
        }

        .section-title .icon {
            font-size: 1.75rem;
        }

        .view-all-link {
            color: #dc2626;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.875rem;
            transition: opacity 0.3s;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .view-all-link:hover {
            opacity: 0.7;
        }

        /* Events Grid */
        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .event-card {
            background: #fafafa;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
            border: 1px solid #e5e7eb;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .event-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            border-color: #dc2626;
        }

        .event-image-container {
            position: relative;
            width: 100%;
            padding-top: 60%;
            background: linear-gradient(135deg, #fecaca 0%, #dc2626 100%);
            overflow: hidden;
        }

        .event-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .event-badge {
            position: absolute;
            top: 0.75rem;
            right: 0.75rem;
            background: rgba(220, 38, 38, 0.95);
            color: white;
            padding: 0.375rem 0.875rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            backdrop-filter: blur(10px);
            text-transform: capitalize;
        }

        .event-content {
            padding: 1.25rem;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .event-card h4 {
            font-size: 1rem;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 0.5rem;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 2.8em;
        }

        .event-description {
            color: #6b7280;
            font-size: 0.875rem;
            line-height: 1.5;
            margin-bottom: 1rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            flex: 1;
        }

        .event-meta {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #6b7280;
            font-size: 0.8rem;
            padding-top: 0.75rem;
            border-top: 1px solid #e5e7eb;
            margin-top: auto;
        }

        .event-meta .icon {
            font-size: 1rem;
        }

        /* Applications List */
        .applications-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .application-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.25rem;
            background: #fafafa;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
        }

        .application-item:hover {
            background: #f3f4f6;
            border-color: #dc2626;
        }

        .app-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
            font-size: 0.9rem;
        }

        .app-student {
            color: #1a1a1a;
            font-weight: 600;
        }

        .app-text {
            color: #6b7280;
        }

        .app-event {
            color: #dc2626;
            font-weight: 600;
        }

        /* Status Badges */
        .status {
            padding: 0.375rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: capitalize;
            white-space: nowrap;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-approved {
            background: #d1fae5;
            color: #065f46;
        }

        .status-rejected {
            background: #fee2e2;
            color: #991b1b;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            background: #fafafa;
            border-radius: 12px;
            border: 2px dashed #e5e7eb;
        }

        .empty-state-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .empty-state h3 {
            font-size: 1.25rem;
            color: #1a1a1a;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .empty-state p {
            color: #6b7280;
            font-size: 0.9rem;
            margin-bottom: 1.25rem;
        }

        .empty-state a {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .empty-state a:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(220, 38, 38, 0.3);
        }

        /* Footer */
        .footer {
            background: white;
            border-top: 1px solid #e5e7eb;
            padding: 2rem;
            margin-top: 4rem;
        }

        .footer-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 1.5rem;
            flex-wrap: wrap;
            text-align: center;
            font-size: 0.875rem;
            color: #6b7280;
        }

        .footer-divider {
            color: #d1d5db;
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

        /* Responsive Design */
        @media (max-width: 1200px) {
            .events-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 992px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .events-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .hero-section {
                padding: 2rem 1.5rem 6rem;
            }

            .hero-content h1 {
                font-size: 1.75rem;
            }

            .hero-content p {
                font-size: 1rem;
            }

            .dashboard-container {
                padding: 0 1.5rem 3rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                margin-top: -3rem;
                gap: 1rem;
            }

            .stat-card {
                padding: 1.5rem;
            }

            .stat-number {
                font-size: 2rem;
            }

            .action-buttons {
                grid-template-columns: 1fr;
            }

            .events-grid {
                grid-template-columns: 1fr;
            }

            .section {
                padding: 1.5rem;
            }

            .section-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
            }

            .application-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
            }

            .app-info {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.25rem;
            }
        }

        @media (max-width: 480px) {
            .hero-content h1 {
                font-size: 1.5rem;
            }

            .section-title {
                font-size: 1.25rem;
            }
        }
    </style>
</head>
<body>
    <?php include '../../includes/header_organizer.php'; ?>
    
    <!-- Hero Section -->
    <div class="hero-section">
        <div class="hero-content">
            <h1>👋 Welcome Back, Organizer!</h1>
            <p>Manage your events and track applications efficiently</p>
        </div>
    </div>

    <!-- Main Dashboard -->
    <div class="dashboard-container">
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <h3>Total Events</h3>
                    <div class="stat-icon">📊</div>
                </div>
                <div class="stat-number"><?= $total_events ?></div>
                <div class="stat-label">Events created</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-header">
                    <h3>Applications</h3>
                    <div class="stat-icon">📝</div>
                </div>
                <div class="stat-number"><?= $total_applications ?></div>
                <div class="stat-label">Total received</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-header">
                    <h3>Pending</h3>
                    <div class="stat-icon">⏳</div>
                </div>
                <div class="stat-number"><?= $pending_applications ?></div>
                <div class="stat-label">Awaiting review</div>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="events.php" class="btn-action">
                <span class="icon">📋</span>
                <span>Manage Events</span>
            </a>
            <a href="applicants.php" class="btn-action">
                <span class="icon">👥</span>
                <span>View Applicants</span>
            </a>
        </div>

        <!-- Content Grid -->
        <div class="content-grid">
            <!-- Recent Events Section -->
            <div class="section">
                <div class="section-header">
                    <h2 class="section-title">
                        <span class="icon">🎯</span>
                        <span>Recent Events</span>
                    </h2>
                    <a href="events.php" class="view-all-link">
                        View All <span>→</span>
                    </a>
                </div>
                
                <?php if(count($recent_events) > 0): ?>
                    <div class="events-grid">
                        <?php foreach($recent_events as $event): ?>
                        <div class="event-card">
                            <div class="event-image-container">
                                <?php if($event['poster']): ?>
                                <img src="../../<?= htmlspecialchars($event['poster']) ?>" alt="<?= htmlspecialchars($event['name']) ?>" class="event-image">
                                <?php endif; ?>
                                <span class="event-badge"><?= htmlspecialchars($event['type']) ?></span>
                            </div>
                            <div class="event-content">
                                <h4><?= htmlspecialchars($event['name']) ?></h4>
                                <p class="event-description"><?= htmlspecialchars(substr($event['description'], 0, 100)) ?>...</p>
                                <div class="event-meta">
                                    <span class="icon">📅</span>
                                    <span><?= date('d M Y', strtotime($event['start_date'])) ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📭</div>
                        <h3>No Events Yet</h3>
                        <p>Start creating events to connect with students</p>
                        <a href="events.php">Create Your First Event</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Recent Applications Section -->
            <div class="section">
                <div class="section-header">
                    <h2 class="section-title">
                        <span class="icon">📬</span>
                        <span>Recent Applications</span>
                    </h2>
                    <a href="applicants.php" class="view-all-link">
                        View All <span>→</span>
                    </a>
                </div>
                
                <?php if(count($recent_apps) > 0): ?>
                    <div class="applications-list">
                        <?php foreach($recent_apps as $app): ?>
                        <div class="application-item">
                            <div class="app-info">
                                <span class="app-student"><?= htmlspecialchars($app['full_name']) ?></span>
                                <span class="app-text">applied for</span>
                                <span class="app-event"><?= htmlspecialchars($app['event_name']) ?></span>
                            </div>
                            <span class="status status-<?= $app['status'] ?>">
                                <?= ucfirst($app['status']) ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📋</div>
                        <h3>No Applications Yet</h3>
                        <p>Applications will appear here once students start applying</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <p>&copy; 2025 CampusConnect</p>
            <span class="footer-divider">•</span>
            <p>Ads: <a href="mailto:ads@campusconnect.com" class="footer-link">ads@campusconnect.com</a></p>
            <span class="footer-divider">•</span>
            <p>+62 812-3456-7890</p>
        </div>
    </footer>
</body>
</html>