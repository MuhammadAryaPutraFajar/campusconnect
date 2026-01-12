<?php
include '../../includes/config.php';
include '../../includes/auth.php';

if($_SESSION['role'] != 'student') {
    header('Location: ../../index.php');
    exit();
}

// Check if student profile is complete
$profile_sql = "SELECT is_profile_complete FROM students WHERE user_id = ?";
$profile_stmt = $pdo->prepare($profile_sql);
$profile_stmt->execute([$_SESSION['user_id']]);
$student_profile = $profile_stmt->fetch();

// Pagination setup
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// Search and filter parameters
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'newest';

// Build query for UKM events only
$sql = "SELECT * FROM events WHERE end_date > NOW() AND type = 'UKM'";
$count_sql = "SELECT COUNT(*) as total FROM events WHERE end_date > NOW() AND type = 'UKM'";

$params = [];
$count_params = [];

// Add search filter
if($search) {
    $sql .= " AND (name LIKE ? OR description LIKE ?)";
    $count_sql .= " AND (name LIKE ? OR description LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $count_params[] = $search_term;
    $count_params[] = $search_term;
}

// Add sorting
switch($sort) {
    case 'newest':
        $sql .= " ORDER BY created_at DESC";
        break;
    case 'oldest':
        $sql .= " ORDER BY created_at ASC";
        break;
    case 'name_asc':
        $sql .= " ORDER BY name ASC";
        break;
    case 'name_desc':
        $sql .= " ORDER BY name DESC";
        break;
    case 'start_date':
        $sql .= " ORDER BY start_date ASC";
        break;
    default:
        $sql .= " ORDER BY created_at DESC";
}

// Add pagination
$sql .= " LIMIT $limit OFFSET $offset";

// Get events
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$events = $stmt->fetchAll();

// Get total count for pagination
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($count_params);
$total_events = $count_stmt->fetch()['total'];
$total_pages = ceil($total_events / $limit);

// Check if user has already applied to events
$application_sql = "SELECT event_id FROM applications WHERE student_id = ?";
$application_stmt = $pdo->prepare($application_sql);
$application_stmt->execute([$_SESSION['user_id']]);
$user_applications = $application_stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UKM Opportunities - CampusConnect</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            color: #1a1a1a;
            line-height: 1.6;
        }

        /* Header Section */
        .ukm-header {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            padding: 4rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .ukm-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" fill="%23ffffff" opacity="0.1"><polygon points="1000,100 1000,0 0,100"/></svg>');
            background-size: cover;
        }

        .ukm-header-content {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
        }

        .ukm-title {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
            letter-spacing: -1px;
        }

        .ukm-subtitle {
            font-size: 1.3rem;
            opacity: 0.9;
            margin-bottom: 2rem;
            font-weight: 300;
        }

        .ukm-stats {
            display: flex;
            justify-content: center;
            gap: 3rem;
            margin-top: 2rem;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            display: block;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.8;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Main Content */
        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 3rem 2rem;
        }

        /* Controls Section */
        .controls-section {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 3rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            border: 1px solid #f1f5f9;
        }

        .controls-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 1.5rem;
            align-items: end;
        }

        .search-box {
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 1rem 1rem 1rem 3rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        .search-input:focus {
            outline: none;
            border-color: #dc2626;
            background: white;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .filter-label {
            font-weight: 600;
            color: #475569;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .filter-select {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            background: #f8fafc;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .filter-select:focus {
            outline: none;
            border-color: #dc2626;
            background: white;
        }

        /* Events Grid */
        .events-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 2.2rem;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .section-title i {
            color: #dc2626;
        }

        .results-count {
            color: #64748b;
            font-size: 1rem;
        }

        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        /* Event Card */
        .event-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid #f1f5f9;
            position: relative;
        }

        .event-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 50px rgba(220, 38, 38, 0.15);
            border-color: #fecaca;
        }

        .event-image-container {
            position: relative;
            height: 220px;
            overflow: hidden;
        }

        .event-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .event-card:hover .event-image {
            transform: scale(1.05);
        }

        .event-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: white;
            padding: 0.5rem 1.2rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
        }

        .event-date-badge {
            position: absolute;
            bottom: 1rem;
            left: 1rem;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            padding: 0.6rem 1rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
            color: #dc2626;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .event-content {
            padding: 1.75rem;
        }

        .event-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.75rem;
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .event-description {
            color: #64748b;
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .event-meta {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            padding: 1.25rem;
            background: #f8fafc;
            border-radius: 12px;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.9rem;
            color: #475569;
        }

        .meta-item i {
            width: 16px;
            color: #dc2626;
        }

        .btn {
            display: block;
            width: 100%;
            padding: 1rem 1.5rem;
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            text-decoration: none;
            text-align: center;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(220, 38, 38, 0.4);
        }

        .btn:disabled {
            background: #94a3b8;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .btn:disabled:hover::before {
            left: -100%;
        }

        .btn-applied {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .btn-applied:hover {
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }

        .empty-state-icon {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 1.5rem;
        }

        .empty-state h3 {
            font-size: 1.8rem;
            color: #475569;
            margin-bottom: 1rem;
        }

        .empty-state p {
            color: #64748b;
            font-size: 1.1rem;
            max-width: 500px;
            margin: 0 auto 2rem;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 1rem;
            margin-top: 3rem;
        }

        .pagination a, .pagination span {
            padding: 0.75rem 1.25rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .pagination a {
            background: white;
            color: #475569;
            border: 2px solid #e2e8f0;
        }

        .pagination a:hover {
            background: #dc2626;
            color: white;
            border-color: #dc2626;
            transform: translateY(-2px);
        }

        .pagination .current {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            border: 2px solid #dc2626;
        }

        .pagination .disabled {
            background: #f1f5f9;
            color: #94a3b8;
            border-color: #e2e8f0;
            cursor: not-allowed;
        }

        /* Profile Warning */
        .profile-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            text-align: center;
            box-shadow: 0 8px 25px rgba(245, 158, 11, 0.3);
        }

        .profile-warning a {
            color: white;
            text-decoration: underline;
            font-weight: 600;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .events-grid {
                grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .ukm-title {
                font-size: 2.5rem;
            }
            
            .ukm-subtitle {
                font-size: 1.1rem;
            }
            
            .ukm-stats {
                flex-direction: column;
                gap: 1.5rem;
            }
            
            .controls-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .events-grid {
                grid-template-columns: 1fr;
            }
            
            .events-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .main-container {
                padding: 2rem 1rem;
            }
        }

        @media (max-width: 480px) {
            .ukm-header {
                padding: 3rem 1rem;
            }
            
            .ukm-title {
                font-size: 2rem;
            }
            
            .section-title {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <?php include '../../includes/header_student.php'; ?>
    
    <!-- UKM Header Section -->
    <section class="ukm-header">
        <div class="ukm-header-content">
            <h1 class="ukm-title">🎯 UKM Opportunities</h1>
            <p class="ukm-subtitle">Discover and join amazing Student Activity Units that match your interests and passions</p>
            <div class="ukm-stats">
                <div class="stat-item">
                    <span class="stat-number"><?= $total_events ?></span>
                    <span class="stat-label">Active UKM</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?= count($user_applications) ?></span>
                    <span class="stat-label">Your Applications</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?= date('Y') ?></span>
                    <span class="stat-label">Academic Year</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="main-container">
        <?php if(!$student_profile || !$student_profile['is_profile_complete']): ?>
        <div class="profile-warning">
            <strong>⚠️ Complete Your Profile First!</strong><br>
            You need to complete your student profile before you can apply to any UKM. 
            <a href="profile.php">Complete your profile here</a>.
        </div>
        <?php endif; ?>

        <!-- Controls Section -->
        <div class="controls-section">
            <form method="GET" class="controls-grid">
                <div class="search-box">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" name="search" class="search-input" placeholder="Search UKM by name or description..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="filter-group">
                    <label class="filter-label">Sort By</label>
                    <select name="sort" class="filter-select" onchange="this.form.submit()">
                        <option value="newest" <?= $sort == 'newest' ? 'selected' : '' ?>>Newest First</option>
                        <option value="oldest" <?= $sort == 'oldest' ? 'selected' : '' ?>>Oldest First</option>
                        <option value="name_asc" <?= $sort == 'name_asc' ? 'selected' : '' ?>>Name A-Z</option>
                        <option value="name_desc" <?= $sort == 'name_desc' ? 'selected' : '' ?>>Name Z-A</option>
                        <option value="start_date" <?= $sort == 'start_date' ? 'selected' : '' ?>>Start Date</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Results Per Page</label>
                    <select class="filter-select" disabled>
                        <option><?= $limit ?> items</option>
                    </select>
                </div>
            </form>
        </div>

        <!-- Events Section -->
        <div class="events-header">
            <h2 class="section-title">
                <i class="fas fa-flag"></i>
                Available UKM
            </h2>
            <div class="results-count">
                Showing <?= count($events) ?> of <?= $total_events ?> UKM opportunities
            </div>
        </div>

        <?php if(count($events) > 0): ?>
            <div class="events-grid">
                <?php foreach($events as $event): 
                    $has_applied = in_array($event['id'], $user_applications);
                    $is_expired = strtotime($event['end_date']) < time();
                ?>
                <div class="event-card">
                    <div class="event-image-container">
                        <?php if($event['poster']): ?>
                        <img src="../../<?= htmlspecialchars($event['poster']) ?>" alt="<?= htmlspecialchars($event['name']) ?>" class="event-image">
                        <?php else: ?>
                        <div style="background: linear-gradient(135deg, #fecaca 0%, #dc2626 100%); height: 100%; display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem;">
                            🎯
                        </div>
                        <?php endif; ?>
                        <span class="event-badge">UKM</span>
                        <div class="event-date-badge">
                            <i class="fas fa-clock"></i>
                            <?= date('d M Y', strtotime($event['start_date'])) ?>
                        </div>
                    </div>
                    
                    <div class="event-content">
                        <h3 class="event-title"><?= htmlspecialchars($event['name']) ?></h3>
                        <p class="event-description"><?= htmlspecialchars($event['description']) ?></p>
                        
                        <div class="event-meta">
                            <div class="meta-item">
                                <i class="fas fa-calendar-alt"></i>
                                <span><strong>Start:</strong> <?= date('d M Y, H:i', strtotime($event['start_date'])) ?></span>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-calendar-times"></i>
                                <span><strong>End:</strong> <?= date('d M Y, H:i', strtotime($event['end_date'])) ?></span>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-hourglass-half"></i>
                                <span>
                                    <strong>Status:</strong> 
                                    <?php if($is_expired): ?>
                                        <span style="color: #ef4444;">Closed</span>
                                    <?php else: ?>
                                        <span style="color: #10b981;">Open</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>

                        <?php if($has_applied): ?>
                            <button class="btn btn-applied" disabled>
                                <i class="fas fa-check-circle"></i> Already Applied
                            </button>
                        <?php elseif($is_expired): ?>
                            <button class="btn" disabled style="background: #94a3b8;">
                                <i class="fas fa-clock"></i> Registration Closed
                            </button>
                        <?php elseif(!$student_profile || !$student_profile['is_profile_complete']): ?>
                            <button class="btn" disabled>
                                <i class="fas fa-lock"></i> Complete Profile to Apply
                            </button>
                        <?php else: ?>
                            <a href="apply.php?event_id=<?= $event['id'] ?>" class="btn">
                                <i class="fas fa-paper-plane"></i> Apply Now
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if($total_pages > 1): ?>
            <div class="pagination">
                <?php if($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&sort=<?= $sort ?>">
                        <i class="fas fa-chevron-left"></i> Previous
                    </a>
                <?php else: ?>
                    <span class="disabled"><i class="fas fa-chevron-left"></i> Previous</span>
                <?php endif; ?>

                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if($i == $page): ?>
                        <span class="current"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&sort=<?= $sort ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if($page < $total_pages): ?>
                    <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&sort=<?= $sort ?>">
                        Next <i class="fas fa-chevron-right"></i>
                    </a>
                <?php else: ?>
                    <span class="disabled">Next <i class="fas fa-chevron-right"></i></span>
                <?php endif; ?>
            </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">🔍</div>
                <h3>No UKM Opportunities Found</h3>
                <p>
                    <?php if($search): ?>
                        We couldn't find any UKM matching "<?= htmlspecialchars($search) ?>". 
                        Try adjusting your search terms or browse all available UKM.
                    <?php else: ?>
                        There are currently no active UKM opportunities available. 
                        Check back later for new opportunities or contact campus administration.
                    <?php endif; ?>
                </p>
                <?php if($search): ?>
                    <a href="ukm_full.php" class="btn" style="width: auto; display: inline-block; padding: 0.75rem 1.5rem;">
                        <i class="fas fa-undo"></i> View All UKM
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Add smooth scrolling for anchor links
        document.addEventListener('DOMContentLoaded', function() {
            const links = document.querySelectorAll('a[href^="#"]');
            links.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });

            // Add loading state to buttons
            const buttons = document.querySelectorAll('.btn:not(:disabled)');
            buttons.forEach(button => {
                button.addEventListener('click', function() {
                    if (!this.disabled) {
                        const originalText = this.innerHTML;
                        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
                        this.disabled = true;
                        
                        // Revert after 2 seconds if still on same page
                        setTimeout(() => {
                            this.innerHTML = originalText;
                            this.disabled = false;
                        }, 2000);
                    }
                });
            });
        });

        // Auto-submit form when search input changes (with delay)
        let searchTimeout;
        const searchInput = document.querySelector('input[name="search"]');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.form.submit();
                }, 800);
            });
        }
    </script>
</body>
</html>