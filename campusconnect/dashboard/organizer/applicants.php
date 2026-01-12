<?php
include '../../includes/config.php';
include '../../includes/auth.php';

if($_SESSION['role'] != 'organizer') {
    header('Location: ../../index.php');
    exit();
}

// Handle application status update
if(isset($_POST['update_status'])) {
    $application_id = $_POST['application_id'];
    $status = $_POST['status'];
    
    $sql = "UPDATE applications SET status = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$status, $application_id]);
    
    // Get application details for notification
    $app_sql = "SELECT a.student_id, e.name as event_name 
                FROM applications a 
                JOIN events e ON a.event_id = e.id 
                WHERE a.id = ?";
    $app_stmt = $pdo->prepare($app_sql);
    $app_stmt->execute([$application_id]);
    $application = $app_stmt->fetch();
    
    if($application) {
        // Create notification for student
        $message = "Your application for '{$application['event_name']}' has been {$status}";
        $notif_sql = "INSERT INTO notifications (user_id, message) VALUES (?, ?)";
        $notif_stmt = $pdo->prepare($notif_sql);
        $notif_stmt->execute([$application['student_id'], $message]);
    }
    
    header('Location: applicants.php');
    exit();
}

// Get all applicants for organizer's events
$sql = "SELECT a.*, e.name as event_name, s.full_name, u.email, s.photo, s.cv 
        FROM applications a 
        JOIN events e ON a.event_id = e.id 
        JOIN students s ON a.student_id = s.user_id 
        JOIN users u ON s.user_id = u.id 
        WHERE e.organizer_id = ? 
        ORDER BY a.applied_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['user_id']]);
$applicants = $stmt->fetchAll();

// Get application statistics
$stats_sql = "SELECT e.name, COUNT(a.id) as applicant_count 
              FROM events e 
              LEFT JOIN applications a ON e.id = a.event_id 
              WHERE e.organizer_id = ? 
              GROUP BY e.id";
$stats_stmt = $pdo->prepare($stats_sql);
$stats_stmt->execute([$_SESSION['user_id']]);
$stats = $stats_stmt->fetchAll();

// Count status
$total_applications = count($applicants);
$pending_count = count(array_filter($applicants, fn($a) => $a['status'] == 'pending'));
$accepted_count = count(array_filter($applicants, fn($a) => $a['status'] == 'accepted'));
$rejected_count = count(array_filter($applicants, fn($a) => $a['status'] == 'rejected'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applicants - CampusConnect</title>
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
            max-width: 1400px;
            margin: 0 auto;
            padding: 3rem 2rem;
        }

        /* Statistics Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            margin-bottom: 3rem;
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
            background: linear-gradient(90deg, #dc2626, #991b1b);
        }

        .stat-card.pending::before {
            background: linear-gradient(90deg, #f59e0b, #d97706);
        }

        .stat-card.accepted::before {
            background: linear-gradient(90deg, #10b981, #059669);
        }

        .stat-card.rejected::before {
            background: linear-gradient(90deg, #6b7280, #4b5563);
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
            background: linear-gradient(135deg, #fee2e2, #fecaca);
        }

        .stat-card.pending .stat-icon {
            background: linear-gradient(135deg, #fef3c7, #fde68a);
        }

        .stat-card.accepted .stat-icon {
            background: linear-gradient(135deg, #d1fae5, #a7f3d0);
        }

        .stat-card.rejected .stat-icon {
            background: linear-gradient(135deg, #f3f4f6, #e5e7eb);
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

        /* Events Statistics */
        .events-stats {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            margin-bottom: 3rem;
            border: 1px solid rgba(0,0,0,0.05);
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 1rem;
        }

        .event-stat-item {
            background: #f9fafb;
            padding: 1.5rem;
            border-radius: 12px;
            border: 2px solid #e5e7eb;
            transition: all 0.3s ease;
        }

        .event-stat-item:hover {
            border-color: #dc2626;
            background: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.1);
        }

        .event-stat-item h4 {
            font-size: 0.9rem;
            color: #1a1a1a;
            font-weight: 600;
            margin-bottom: 0.75rem;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 2.8em;
        }

        .event-stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #dc2626;
        }

        .event-stat-label {
            font-size: 0.8rem;
            color: #6b7280;
            font-weight: 500;
        }

        /* Applicants Section */
        .applicants-section {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.05);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid #f3f4f6;
        }

        .filter-controls {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            padding: 0.75rem 1rem 0.75rem 2.75rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 0.9rem;
            width: 280px;
            transition: all 0.3s ease;
            font-family: 'Inter', sans-serif;
        }

        .search-box input:focus {
            outline: none;
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        }

        .search-box::before {
            content: '🔍';
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.1rem;
        }

        .filter-select {
            padding: 0.75rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
            font-family: 'Inter', sans-serif;
        }

        .filter-select:focus {
            outline: none;
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        }

        .btn-export {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-export:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(16, 185, 129, 0.3);
        }

        /* Applicants Grid */
        .applicants-grid {
            display: grid;
            gap: 1.25rem;
        }

        .applicant-card {
            background: #fafafa;
            border: 2px solid #e5e7eb;
            border-radius: 16px;
            padding: 1.5rem;
            transition: all 0.3s ease;
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 1.5rem;
            align-items: start;
        }

        .applicant-card:hover {
            border-color: #dc2626;
            box-shadow: 0 8px 16px rgba(220, 38, 38, 0.1);
            background: white;
        }

        .applicant-photo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #dc2626;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.2);
        }

        .applicant-details {
            flex: 1;
            min-width: 0;
        }

        .applicant-name {
            font-size: 1.125rem;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 0.25rem;
        }

        .applicant-email {
            color: #6b7280;
            font-size: 0.875rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .applicant-meta {
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: #6b7280;
        }

        .meta-item-label {
            font-weight: 600;
            color: #4b5563;
        }

        .meta-item-value {
            color: #1a1a1a;
            font-weight: 500;
        }

        .applicant-actions {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            align-items: flex-end;
        }

        .status-badge {
            padding: 0.5rem 1.25rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }

        .status-badge.pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-badge.accepted {
            background: #d1fae5;
            color: #065f46;
        }

        .status-badge.rejected {
            background: #fee2e2;
            color: #991b1b;
        }

        .action-group {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            align-items: flex-end;
        }

        .btn-view-cv {
            background: #dc2626;
            color: white;
            padding: 0.625rem 1.25rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.875rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 2px 8px rgba(220, 38, 38, 0.2);
        }

        .btn-view-cv:hover {
            background: #b91c1c;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
        }

        .status-form {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .status-select {
            padding: 0.625rem 0.875rem;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Inter', sans-serif;
            background: white;
        }

        .status-select:focus {
            outline: none;
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        }

        .btn-update {
            background: #dc2626;
            color: white;
            padding: 0.625rem 1.25rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.875rem;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(220, 38, 38, 0.2);
        }

        .btn-update:hover {
            background: #b91c1c;
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
        }

        /* Empty State */
        .no-data {
            text-align: center;
            padding: 4rem 2rem;
            background: #fafafa;
            border-radius: 12px;
            border: 2px dashed #e5e7eb;
        }

        .no-data-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .no-data h3 {
            font-size: 1.5rem;
            color: #1a1a1a;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .no-data p {
            color: #6b7280;
            font-size: 1rem;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .events-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 992px) {
            .applicant-card {
                grid-template-columns: auto 1fr;
            }

            .applicant-actions {
                grid-column: 1 / -1;
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
                width: 100%;
            }

            .action-group {
                flex-direction: row;
                align-items: center;
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
                gap: 1rem;
            }

            .stat-card {
                padding: 1.5rem;
            }

            .stat-number {
                font-size: 2rem;
            }

            .events-grid {
                grid-template-columns: 1fr;
            }

            .section-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .filter-controls {
                width: 100%;
                flex-direction: column;
            }

            .search-box input,
            .filter-select,
            .btn-export {
                width: 100%;
            }

            .applicant-card {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .applicant-photo {
                margin: 0 auto;
            }

            .applicant-email,
            .applicant-meta {
                justify-content: center;
            }

            .applicant-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .action-group {
                flex-direction: column;
                align-items: stretch;
                width: 100%;
            }

            .btn-view-cv,
            .status-form,
            .status-select,
            .btn-update {
                width: 100%;
            }

            .status-form {
                flex-direction: column;
            }
        }

        @media (max-width: 480px) {
            .hero-content h1 {
                font-size: 1.5rem;
            }

            .section-title {
                font-size: 1.25rem;
            }

            .applicant-meta {
                flex-direction: column;
                gap: 0.75rem;
            }
        }
    </style>
</head>
<body>
    <?php include '../../includes/header_organizer.php'; ?>
    
    <!-- Hero Section -->
    <div class="hero-section">
        <div class="hero-content">
            <h1><span>📋</span> Applicant Management</h1>
            <p>Manage and review all applications for your events</p>
        </div>
    </div>

    <div class="container">
        <!-- Statistics Overview -->
        <div class="stats-grid">
            <div class="stat-card total">
                <div class="stat-header">
                    <div class="stat-icon">📊</div>
                </div>
                <div class="stat-label">Total Applications</div>
                <div class="stat-number"><?= $total_applications ?></div>
            </div>
            <div class="stat-card pending">
                <div class="stat-header">
                    <div class="stat-icon">⏳</div>
                </div>
                <div class="stat-label">Pending Review</div>
                <div class="stat-number"><?= $pending_count ?></div>
            </div>
            <div class="stat-card accepted">
                <div class="stat-header">
                    <div class="stat-icon">✅</div>
                </div>
                <div class="stat-label">Accepted</div>
                <div class="stat-number"><?= $accepted_count ?></div>
            </div>
            <div class="stat-card rejected">
                <div class="stat-header">
                    <div class="stat-icon">❌</div>
                </div>
                <div class="stat-label">Rejected</div>
                <div class="stat-number"><?= $rejected_count ?></div>
            </div>
        </div>

        <!-- Events Statistics -->
        <?php if(count($stats) > 0): ?>
        <div class="events-stats">
            <h2 class="section-title"><span>📅</span> Applications by Event</h2>
            <div class="events-grid">
                <?php foreach($stats as $stat): ?>
                <div class="event-stat-item">
                    <h4><?= htmlspecialchars($stat['name']) ?></h4>
                    <div>
                        <span class="event-stat-number"><?= $stat['applicant_count'] ?></span>
                        <span class="event-stat-label">applicants</span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Applicants List -->
        <div class="applicants-section">
            <div class="section-header">
                <h2 class="section-title"><span>👥</span> All Applicants</h2>
                <div class="filter-controls">
                    <div class="search-box">
                        <input type="text" id="searchInput" placeholder="Search applicants..." onkeyup="filterApplicants()">
                    </div>
                    <select class="filter-select" id="statusFilter" onchange="filterApplicants()">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="accepted">Accepted</option>
                        <option value="rejected">Rejected</option>
                    </select>
                    <?php if(count($applicants) > 0): ?>
                    <a href="export_applicants.php" class="btn-export">
                        <span>📥</span>
                        <span>Export Excel</span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <?php if(count($applicants) > 0): ?>
                <div class="applicants-grid" id="applicantsGrid">
                    <?php foreach($applicants as $app): ?>
                    <div class="applicant-card" data-status="<?= $app['status'] ?>" data-name="<?= htmlspecialchars($app['full_name']) ?>" data-email="<?= htmlspecialchars($app['email']) ?>" data-event="<?= htmlspecialchars($app['event_name']) ?>">
                        <img src="../../<?= $app['photo'] ?: 'assets/images/default-avatar.jpg' ?>" 
                             alt="<?= htmlspecialchars($app['full_name']) ?>" 
                             class="applicant-photo">
                        
                        <div class="applicant-details">
                            <div class="applicant-name"><?= htmlspecialchars($app['full_name']) ?></div>
                            <div class="applicant-email">
                                <span>📧</span>
                                <span><?= htmlspecialchars($app['email']) ?></span>
                            </div>
                            <div class="applicant-meta">
                                <div class="meta-item">
                                    <span>📅</span>
                                    <span class="meta-item-label">Event:</span>
                                    <span class="meta-item-value"><?= htmlspecialchars($app['event_name']) ?></span>
                                </div>
                                <div class="meta-item">
                                    <span>🕒</span>
                                    <span class="meta-item-label">Applied:</span>
                                    <span class="meta-item-value"><?= date('d M Y, H:i', strtotime($app['applied_at'])) ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="applicant-actions">
                            <span class="status-badge <?= $app['status'] ?>">
                                <?= ucfirst($app['status']) ?>
                            </span>
                            <div class="action-group">
                                <?php if($app['cv']): ?>
                                <a href="../../<?= $app['cv'] ?>" target="_blank" class="btn-view-cv">
                                    <span>📄</span>
                                    <span>View CV</span>
                                </a>
                                <?php endif; ?>
                                <form method="POST" class="status-form">
                                    <input type="hidden" name="application_id" value="<?= $app['id'] ?>">
                                    <select name="status" class="status-select">
                                        <option value="pending" <?= $app['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="accepted" <?= $app['status'] == 'accepted' ? 'selected' : '' ?>>Accepted</option>
                                        <option value="rejected" <?= $app['status'] == 'rejected' ? 'selected' : '' ?>>Rejected</option>
                                    </select>
                                    <button type="submit" name="update_status" class="btn-update">Update</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-data">
                    <div class="no-data-icon">📭</div>
                    <h3>No Applicants Yet</h3>
                    <p>When students apply to your events, they will appear here.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function filterApplicants() {
            const searchInput = document.getElementById('searchInput').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value.toLowerCase();
            const cards = document.querySelectorAll('.applicant-card');
            
            cards.forEach(card => {
                const name = card.dataset.name.toLowerCase();
                const email = card.dataset.email.toLowerCase();
                const event = card.dataset.event.toLowerCase();
                const status = card.dataset.status.toLowerCase();
                
                const matchesSearch = name.includes(searchInput) || 
                                     email.includes(searchInput) || 
                                     event.includes(searchInput);
                const matchesStatus = statusFilter === '' || status === statusFilter;
                
                if (matchesSearch && matchesStatus) {
                    card.style.display = 'grid';
                } else {
                    card.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>