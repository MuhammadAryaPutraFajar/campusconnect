<?php
include '../../includes/config.php';
include '../../includes/auth.php';

if($_SESSION['role'] != 'student') {
    header('Location: ../../index.php');
    exit();
}

// Get application history
$sql = "SELECT a.*, e.name as event_name, e.type, e.poster 
        FROM applications a 
        JOIN events e ON a.event_id = e.id 
        WHERE a.student_id = ? 
        ORDER BY a.applied_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['user_id']]);
$applications = $stmt->fetchAll();

// Calculate statistics
$total_applications = count($applications);
$pending = count(array_filter($applications, function($a) { return $a['status'] == 'pending'; }));
$accepted = count(array_filter($applications, function($a) { return $a['status'] == 'accepted'; }));
$rejected = count(array_filter($applications, function($a) { return $a['status'] == 'rejected'; }));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application History - CampusConnect</title>
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
            max-width: 1200px;
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
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
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

        .stat-icon.pending {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #92400e;
        }

        .stat-icon.accepted {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #065f46;
        }

        .stat-icon.rejected {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #991b1b;
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

        /* Applications List */
        .applications-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .applications-header h2 {
            font-size: 1.5rem;
            color: #1f2937;
            font-weight: 700;
        }

        .filter-buttons {
            display: flex;
            gap: 0.75rem;
        }

        .filter-btn {
            padding: 0.5rem 1rem;
            border: 2px solid #e5e7eb;
            background: white;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 600;
            color: #6b7280;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .filter-btn:hover,
        .filter-btn.active {
            border-color: #dc2626;
            background: #dc2626;
            color: white;
        }

        .applications-list {
            display: grid;
            gap: 1.25rem;
        }

        .application-card {
            background: white;
            border-radius: 16px;
            padding: 1.75rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 1.5rem;
            align-items: center;
        }

        .application-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        /* Event Poster */
        .event-poster {
            width: 100px;
            height: 100px;
            border-radius: 12px;
            object-fit: cover;
            border: 2px solid #e5e7eb;
        }

        .poster-placeholder {
            width: 100px;
            height: 100px;
            border-radius: 12px;
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: #dc2626;
            border: 2px solid #e5e7eb;
        }

        /* Application Info */
        .application-info {
            flex: 1;
        }

        .event-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .event-type {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            background: #dbeafe;
            color: #1e40af;
        }

        .application-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 0.75rem;
            margin-top: 0.75rem;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: #6b7280;
        }

        .detail-item i {
            color: #dc2626;
            width: 16px;
        }

        .detail-item strong {
            color: #374151;
            font-weight: 600;
        }

        /* Status Badge */
        .status-badge-wrapper {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 0.75rem;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-badge.status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-badge.status-accepted {
            background: #d1fae5;
            color: #065f46;
        }

        .status-badge.status-rejected {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-badge i {
            font-size: 1rem;
        }

        .application-date {
            font-size: 0.8rem;
            color: #9ca3af;
            font-weight: 500;
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
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
        }

        .btn-browse {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.875rem 1.5rem;
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(220, 38, 38, 0.25);
            text-decoration: none;
        }

        .btn-browse:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(220, 38, 38, 0.35);
        }

        /* Timeline Indicator */
        .timeline-indicator {
            padding-top: 0.75rem;
            border-top: 1px solid #e5e7eb;
            margin-top: 0.75rem;
        }

        .timeline-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.8rem;
            color: #9ca3af;
        }

        .timeline-item i {
            font-size: 0.7rem;
        }

        /* Responsive */
        @media (max-width: 968px) {
            .application-card {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .event-poster,
            .poster-placeholder {
                margin: 0 auto;
            }

            .status-badge-wrapper {
                align-items: center;
            }

            .application-details {
                grid-template-columns: 1fr;
            }

            .event-title {
                justify-content: center;
            }
        }

        @media (max-width: 640px) {
            .container {
                padding: 1.5rem 1rem;
            }

            .page-header h1 {
                font-size: 1.5rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .filter-buttons {
                flex-wrap: wrap;
            }

            .applications-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
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
                <i class="fas fa-history"></i>
                Application History
            </h1>
            <p>Track and manage all your event applications in one place</p>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon total">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="stat-content">
                    <h3><?= $total_applications ?></h3>
                    <p>Total Applications</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon pending">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <h3><?= $pending ?></h3>
                    <p>Pending Review</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon accepted">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <h3><?= $accepted ?></h3>
                    <p>Accepted</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon rejected">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-content">
                    <h3><?= $rejected ?></h3>
                    <p>Rejected</p>
                </div>
            </div>
        </div>

        <!-- Applications List -->
        <?php if(count($applications) > 0): ?>
            <div class="applications-header">
                <h2>Your Applications</h2>
                <div class="filter-buttons">
                    <button class="filter-btn active" data-filter="all">All</button>
                    <button class="filter-btn" data-filter="pending">Pending</button>
                    <button class="filter-btn" data-filter="accepted">Accepted</button>
                    <button class="filter-btn" data-filter="rejected">Rejected</button>
                </div>
            </div>

            <div class="applications-list">
                <?php foreach($applications as $app): ?>
                <div class="application-card" data-status="<?= $app['status'] ?>">
                    <!-- Event Poster -->
                    <?php if($app['poster']): ?>
                        <img src="../../<?= $app['poster'] ?>" alt="Event Poster" class="event-poster">
                    <?php else: ?>
                        <div class="poster-placeholder">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                    <?php endif; ?>

                    <!-- Application Info -->
                    <div class="application-info">
                        <div class="event-title">
                            <?= htmlspecialchars($app['event_name']) ?>
                            <span class="event-type">
                                <i class="fas fa-tag"></i>
                                <?= ucfirst($app['type']) ?>
                            </span>
                        </div>

                        <div class="application-details">
                            <div class="detail-item">
                                <i class="fas fa-paper-plane"></i>
                                <span><strong>Applied:</strong> <?= date('d M Y H:i', strtotime($app['applied_at'])) ?></span>
                            </div>
                            <?php if($app['status'] != 'pending'): ?>
                            <div class="detail-item">
                                <i class="fas fa-clock"></i>
                                <span><strong>Updated:</strong> <?= date('d M Y H:i', strtotime($app['updated_at'] ?? $app['applied_at'])) ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Status Badge -->
                    <div class="status-badge-wrapper">
                        <div class="status-badge status-<?= $app['status'] ?>">
                            <?php if($app['status'] == 'pending'): ?>
                                <i class="fas fa-clock"></i>
                                Pending
                            <?php elseif($app['status'] == 'accepted'): ?>
                                <i class="fas fa-check-circle"></i>
                                Accepted
                            <?php else: ?>
                                <i class="fas fa-times-circle"></i>
                                Rejected
                            <?php endif; ?>
                        </div>
                        <div class="application-date">
                            Application #<?= str_pad($app['id'], 4, '0', STR_PAD_LEFT) ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- Empty State -->
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-inbox"></i>
                </div>
                <h3>No Applications Yet</h3>
                <p>You haven't applied to any events yet.</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Filter functionality
        const filterButtons = document.querySelectorAll('.filter-btn');
        const applicationCards = document.querySelectorAll('.application-card');

        filterButtons.forEach(button => {
            button.addEventListener('click', () => {
                // Update active button
                filterButtons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');

                const filter = button.dataset.filter;

                // Filter cards
                applicationCards.forEach(card => {
                    if (filter === 'all' || card.dataset.status === filter) {
                        card.style.display = 'grid';
                        setTimeout(() => {
                            card.style.opacity = '1';
                            card.style.transform = 'translateY(0)';
                        }, 10);
                    } else {
                        card.style.opacity = '0';
                        card.style.transform = 'translateY(-10px)';
                        setTimeout(() => {
                            card.style.display = 'none';
                        }, 300);
                    }
                });
            });
        });

        // Animation on load
        document.addEventListener('DOMContentLoaded', () => {
            applicationCards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.3s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>