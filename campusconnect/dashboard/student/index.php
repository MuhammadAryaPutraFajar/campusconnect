<?php
include '../../includes/config.php';
include '../../includes/auth.php';

if($_SESSION['role'] != 'student') {
    header('Location: ../../index.php');
    exit();
}

// Get category from URL
$category = $_GET['category'] ?? '';
$page = $_GET['page'] ?? 1;
$limit = 8;
$offset = ($page - 1) * $limit;

// Build query based on category
if($category) {
    $sql = "SELECT * FROM events WHERE end_date > NOW() AND type = ? ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
    $count_sql = "SELECT COUNT(*) as total FROM events WHERE end_date > NOW() AND type = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$category]);
    $events = $stmt->fetchAll();
    
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute([$category]);
    $total_events = $count_stmt->fetch()['total'];
    $total_pages = ceil($total_events / $limit);
} else {
    // Get events by category for homepage
    $ukm_sql = "SELECT * FROM events WHERE end_date > NOW() AND type = 'UKM' ORDER BY created_at DESC LIMIT 4";
    $event_sql = "SELECT * FROM events WHERE end_date > NOW() AND type = 'Event' ORDER BY created_at DESC LIMIT 4";
    $volunteer_sql = "SELECT * FROM events WHERE end_date > NOW() AND type = 'Volunteer' ORDER BY created_at DESC LIMIT 4";
    $organization_sql = "SELECT * FROM events WHERE end_date > NOW() AND type = 'Organization' ORDER BY created_at DESC LIMIT 4";
    
    $ukm_events = $pdo->query($ukm_sql)->fetchAll();
    $event_events = $pdo->query($event_sql)->fetchAll();
    $volunteer_events = $pdo->query($volunteer_sql)->fetchAll();
    $organization_events = $pdo->query($organization_sql)->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusConnect - Student Dashboard</title>
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
        }

        /* Hero Carousel */
        .carousel-container {
            height: 100vh;
            position: relative;
            overflow: hidden;
        }

        .carousel-slide {
            position: absolute;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 1.2s ease-in-out;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            background-size: cover;
            background-position: center;
        }

        .carousel-slide::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1;
        }

        .carousel-slide.active {
            opacity: 1;
        }

        .carousel-slide:nth-child(1) {
            background-image: linear-gradient(135deg, rgba(220, 38, 38, 0.8) 0%, rgba(127, 29, 29, 0.8) 100%), 
                              url('https://images.unsplash.com/photo-1523050854058-8df90110c9f1?w=1920');
        }

        .carousel-slide:nth-child(2) {
            background-image: linear-gradient(135deg, rgba(185, 28, 28, 0.8) 0%, rgba(69, 10, 10, 0.8) 100%), 
                              url('https://images.unsplash.com/photo-1541339907198-e08756dedf3f?w=1920');
        }

        .carousel-slide:nth-child(3) {
            background-image: linear-gradient(135deg, rgba(239, 68, 68, 0.8) 0%, rgba(153, 27, 27, 0.8) 100%), 
                              url('https://images.unsplash.com/photo-1517486808906-6ca8b3f04846?w=1920');
        }

        .carousel-content {
            max-width: 900px;
            padding: 3rem;
            text-align: center;
            animation: fadeInUp 1s ease-out;
            position: relative;
            z-index: 2;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .carousel-content h1 {
            font-size: 4rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            letter-spacing: -1px;
        }

        .carousel-content p {
            font-size: 1.5rem;
            font-weight: 300;
            line-height: 1.8;
            opacity: 0.95;
        }

        .carousel-nav {
            position: absolute;
            bottom: 3rem;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 1rem;
            z-index: 10;
        }

        .carousel-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: rgba(255,255,255,0.4);
            cursor: pointer;
            transition: all 0.4s ease;
        }

        .carousel-dot.active {
            background: white;
            width: 35px;
            border-radius: 5px;
        }

        /* Main Content */
        .main-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 5rem 2rem;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 1rem;
            letter-spacing: -1px;
        }

        .section-subtitle {
            text-align: center;
            font-size: 1.1rem;
            color: #666;
            font-weight: 300;
            margin-bottom: 3rem;
        }

        /* Category Cards - 4 in a row */
        .category-cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
            margin-bottom: 5rem;
        }

        .category-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.4s ease;
            border: 2px solid #f0f0f0;
            text-decoration: none;
            color: inherit;
        }

        .category-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(220, 38, 38, 0.15);
            border-color: #dc2626;
        }

        .category-icon {
            font-size: 3.5rem;
            margin-bottom: 1.25rem;
        }

        .category-card h3 {
            font-size: 1.4rem;
            font-weight: 600;
            color: #dc2626;
            margin-bottom: 0.75rem;
        }

        .category-card p {
            color: #666;
            font-size: 0.95rem;
            line-height: 1.6;
        }

        /* Events Section */
        .events-section {
            margin-bottom: 4rem;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f0f0f0;
        }

        .section-header h2 {
            font-size: 2rem;
            font-weight: 700;
            color: #1a1a1a;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .view-all {
            color: #dc2626;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .view-all:hover {
            opacity: 0.7;
        }

        /* Events Grid - 4 cards in a row */
        .events-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
        }

        .event-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.4s ease;
            border: 1px solid #f0f0f0;
            position: relative;
        }

        .event-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.08);
        }

        .event-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: linear-gradient(135deg, #fecaca 0%, #dc2626 100%);
        }

        .event-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(220, 38, 38, 0.95);
            color: white;
            padding: 0.4rem 1rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
            backdrop-filter: blur(10px);
        }

        .event-content {
            padding: 1.75rem;
        }

        .event-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 0.75rem;
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .event-description {
            color: #666;
            font-size: 0.9rem;
            line-height: 1.6;
            margin-bottom: 1.25rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .event-meta {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: #f9fafb;
            border-radius: 12px;
        }

        .event-date {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            color: #666;
        }

        .btn {
            display: block;
            width: 100%;
            padding: 0.875rem;
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            color: white;
            text-decoration: none;
            text-align: center;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: scale(1.02);
            box-shadow: 0 8px 20px rgba(220, 38, 38, 0.3);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            color: #1a1a1a;
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: #666;
        }

        /* Footer */
        .footer {
            background: white;
            border-top: 1px solid #e5e5e5;
            padding: 2rem;
            margin-top: 5rem;
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

        @media (max-width: 1200px) {
            .category-cards,
            .events-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .carousel-content h1 {
                font-size: 2.5rem;
            }
            
            .carousel-content p {
                font-size: 1.2rem;
            }
            
            .section-title {
                font-size: 2rem;
            }
            
            .category-cards,
            .events-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include '../../includes/header_student.php'; ?>
    
    <!-- Hero Carousel -->
    <div class="carousel-container">
        <div class="carousel-slide active">
            <div class="carousel-content">
                <h1>Welcome to CampusConnect</h1>
                <p>Bridging the gap between organizers and students.<br>Your gateway to endless campus opportunities.</p>
            </div>
        </div>
        <div class="carousel-slide">
            <div class="carousel-content">
                <h1>Discover & Explore</h1>
                <p>Find student organizations, events, volunteer programs,<br>and activities tailored just for you.</p>
            </div>
        </div>
        <div class="carousel-slide">
            <div class="carousel-content">
                <h1>Connect & Grow</h1>
                <p>Join communities, gain valuable experience,<br>and create lasting connections throughout your journey.</p>
            </div>
        </div>
        <div class="carousel-nav">
            <span class="carousel-dot active" onclick="changeSlide(0)"></span>
            <span class="carousel-dot" onclick="changeSlide(1)"></span>
            <span class="carousel-dot" onclick="changeSlide(2)"></span>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h2 class="section-title">Explore Categories</h2>
        <p class="section-subtitle">Find the perfect opportunity that matches your interests</p>

        <!-- Category Cards - 4 in a row -->
        <div class="category-cards">
            <a href="ukm_full.php" class="category-card">
                <div class="category-icon">🎯</div>
                <h3>UKM</h3>
                <p>Join clubs and organizations that match your interests</p>
            </a>
            <a href="event_full.php" class="category-card">
                <div class="category-icon">🎉</div>
                <h3>Events</h3>
                <p>Participate in exciting campus events and activities</p>
            </a>
            <a href="volunteer_full.php" class="category-card">
                <div class="category-icon">🤝</div>
                <h3>Volunteer</h3>
                <p>Make a difference through volunteer opportunities</p>
            </a>
            <a href="organization_full.php" class="category-card">
                <div class="category-icon">🏢</div>
                <h3>Organizations</h3>
                <p>Connect with campus and external organizations</p>
            </a>
        </div>

        <!-- UKM Section -->
        <?php if(count($ukm_events) > 0): ?>
        <div class="events-section">
            <div class="section-header">
                <h2><span>🎯</span> UKM</h2>
                <a href="ukm_full.php" class="view-all">See More →</a>
            </div>
            <div class="events-grid">
                <?php foreach($ukm_events as $event): ?>
                <div class="event-card">
                    <?php if($event['poster']): ?>
                    <img src="../../<?= htmlspecialchars($event['poster']) ?>" alt="<?= htmlspecialchars($event['name']) ?>" class="event-image">
                    <?php else: ?>
                    <div class="event-image"></div>
                    <?php endif; ?>
                    <span class="event-badge"><?= htmlspecialchars($event['type']) ?></span>
                    <div class="event-content">
                        <h3 class="event-title"><?= htmlspecialchars($event['name']) ?></h3>
                        <p class="event-description"><?= htmlspecialchars($event['description']) ?></p>
                        <div class="event-meta">
                            <div class="event-date">
                                📅 Start: <?= date('d M Y, H:i', strtotime($event['start_date'])) ?>
                            </div>
                            <div class="event-date">
                                📅 End: <?= date('d M Y, H:i', strtotime($event['end_date'])) ?>
                            </div>
                        </div>
                        <a href="apply.php?event_id=<?= $event['id'] ?>" class="btn">Apply Now</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Events Section -->
        <?php if(count($event_events) > 0): ?>
        <div class="events-section">
            <div class="section-header">
                <h2><span>🎉</span>Events</h2>
                <a href="event_full.php" class="view-all">See More →</a>
            </div>
            <div class="events-grid">
                <?php foreach($event_events as $event): ?>
                <div class="event-card">
                    <?php if($event['poster']): ?>
                    <img src="../../<?= htmlspecialchars($event['poster']) ?>" alt="<?= htmlspecialchars($event['name']) ?>" class="event-image">
                    <?php else: ?>
                    <div class="event-image"></div>
                    <?php endif; ?>
                    <span class="event-badge"><?= htmlspecialchars($event['type']) ?></span>
                    <div class="event-content">
                        <h3 class="event-title"><?= htmlspecialchars($event['name']) ?></h3>
                        <p class="event-description"><?= htmlspecialchars($event['description']) ?></p>
                        <div class="event-meta">
                            <div class="event-date">
                                📅 Start: <?= date('d M Y, H:i', strtotime($event['start_date'])) ?>
                            </div>
                            <div class="event-date">
                                📅 End: <?= date('d M Y, H:i', strtotime($event['end_date'])) ?>
                            </div>
                        </div>
                        <a href="apply.php?event_id=<?= $event['id'] ?>" class="btn">Apply Now</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Volunteer Section -->
        <?php if(count($volunteer_events) > 0): ?>
        <div class="events-section">
            <div class="section-header">
                <h2><span>🤝</span> Volunteer</h2>
                <a href="volunteer_full.php" class="view-all">See More →</a>
            </div>
            <div class="events-grid">
                <?php foreach($volunteer_events as $event): ?>
                <div class="event-card">
                    <?php if($event['poster']): ?>
                    <img src="../../<?= htmlspecialchars($event['poster']) ?>" alt="<?= htmlspecialchars($event['name']) ?>" class="event-image">
                    <?php else: ?>
                    <div class="event-image"></div>
                    <?php endif; ?>
                    <span class="event-badge"><?= htmlspecialchars($event['type']) ?></span>
                    <div class="event-content">
                        <h3 class="event-title"><?= htmlspecialchars($event['name']) ?></h3>
                        <p class="event-description"><?= htmlspecialchars($event['description']) ?></p>
                        <div class="event-meta">
                            <div class="event-date">
                                📅 Start: <?= date('d M Y, H:i', strtotime($event['start_date'])) ?>
                            </div>
                            <div class="event-date">
                                📅 End: <?= date('d M Y, H:i', strtotime($event['end_date'])) ?>
                            </div>
                        </div>
                        <a href="apply.php?event_id=<?= $event['id'] ?>" class="btn">Apply Now</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Organization Section -->
        <?php if(count($organization_events) > 0): ?>
        <div class="events-section">
            <div class="section-header">
                <h2><span>🏢</span> Organizations</h2>
                <a href="organization_full.php" class="view-all">See More →</a>
            </div>
            <div class="events-grid">
                <?php foreach($organization_events as $event): ?>
                <div class="event-card">
                    <?php if($event['poster']): ?>
                    <img src="../../<?= htmlspecialchars($event['poster']) ?>" alt="<?= htmlspecialchars($event['name']) ?>" class="event-image">
                    <?php else: ?>
                    <div class="event-image"></div>
                    <?php endif; ?>
                    <span class="event-badge"><?= htmlspecialchars($event['type']) ?></span>
                    <div class="event-content">
                        <h3 class="event-title"><?= htmlspecialchars($event['name']) ?></h3>
                        <p class="event-description"><?= htmlspecialchars($event['description']) ?></p>
                        <div class="event-meta">
                            <div class="event-date">
                                📅 Start: <?= date('d M Y, H:i', strtotime($event['start_date'])) ?>
                            </div>
                            <div class="event-date">
                                📅 End: <?= date('d M Y, H:i', strtotime($event['end_date'])) ?>
                            </div>
                        </div>
                        <a href="apply.php?event_id=<?= $event['id'] ?>" class="btn">Apply Now</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
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

    <script>
        let currentSlide = 0;
        const slides = document.querySelectorAll('.carousel-slide');
        const dots = document.querySelectorAll('.carousel-dot');

        function showSlide(n) {
            slides[currentSlide].classList.remove('active');
            dots[currentSlide].classList.remove('active');
            
            currentSlide = (n + slides.length) % slides.length;
            
            slides[currentSlide].classList.add('active');
            dots[currentSlide].classList.add('active');
        }

        function changeSlide(n) {
            showSlide(n);
        }

        setInterval(() => {
            showSlide(currentSlide + 1);
        }, 5000);
    </script>
</body>
</html>