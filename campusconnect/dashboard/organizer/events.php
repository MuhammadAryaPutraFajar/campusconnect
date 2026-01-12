<?php
include '../../includes/config.php';
include '../../includes/auth.php';

if($_SESSION['role'] != 'organizer') {
    header('Location: ../../index.php');
    exit();
}

// Handle form submission for new event
if($_POST && isset($_POST['create_event'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $type = $_POST['type'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    
    // Handle file upload for poster
    $poster = '';
    if(isset($_FILES['poster']) && $_FILES['poster']['error'] == 0) {
        $upload_dir = '../../assets/uploads/';
        // Create uploads directory if it doesn't exist
        if(!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $poster_name = time() . '_' . $_FILES['poster']['name'];
        move_uploaded_file($_FILES['poster']['tmp_name'], $upload_dir . $poster_name);
        $poster = 'assets/uploads/' . $poster_name;
    }
    
    $sql = "INSERT INTO events (organizer_id, name, description, type, poster, start_date, end_date) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION['user_id'], $name, $description, $type, $poster, $start_date, $end_date]);
    
    // Create notification
    $message = "Event '{$name}' has been created successfully";
    $notif_sql = "INSERT INTO notifications (user_id, message) VALUES (?, ?)";
    $notif_stmt = $pdo->prepare($notif_sql);
    $notif_stmt->execute([$_SESSION['user_id'], $message]);
    
    header('Location: events.php');
    exit();
}

// Get all events by this organizer
$sql = "SELECT * FROM events WHERE organizer_id = ? ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['user_id']]);
$events = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management - CampusConnect</title>
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
        
        /* Create Event Section */
        .create-section {
            background: white;
            border-radius: 16px;
            padding: 2.5rem;
            margin-bottom: 3rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.05);
        }
        
        .section-title {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f3f4f6;
        }
        
        .section-title-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .section-title h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1a1a1a;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group.full-width {
            grid-column: span 2;
        }
        
        .form-group label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #4b5563;
            font-size: 0.875rem;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 0.9rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
            background: white;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }
        
        .file-input-wrapper {
            position: relative;
        }
        
        .file-input-wrapper input[type="file"] {
            display: none;
        }
        
        .file-input-label {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            padding: 1rem;
            background: #fafafa;
            border: 2px dashed #e5e7eb;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #6b7280;
            font-weight: 500;
        }
        
        .file-input-label:hover {
            border-color: #dc2626;
            background: #fff5f5;
            color: #dc2626;
        }
        
        .file-name {
            margin-top: 0.5rem;
            font-size: 0.875rem;
            color: #dc2626;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-create {
            background: linear-gradient(135deg, #dc2626, #991b1b);
            color: white;
            padding: 0.875rem 2rem;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.2);
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .btn-create:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(220, 38, 38, 0.3);
        }
        
        /* Events Section */
        .events-section {
            margin-top: 3rem;
        }
        
        .events-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f3f4f6;
        }
        
        .events-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1a1a1a;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .event-count {
            background: linear-gradient(135deg, #dc2626, #991b1b);
            color: white;
            padding: 0.375rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
        }
        
        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
        }
        
        .event-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .event-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 24px rgba(220, 38, 38, 0.15);
        }
        
        .event-image-container {
            position: relative;
            width: 100%;
            padding-top: 56.25%;
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
        
        .event-placeholder {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 4rem;
        }
        
        .event-type-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 700;
            color: #dc2626;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .event-content {
            padding: 1.5rem;
        }
        
        .event-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 0.75rem;
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 3.25em;
        }
        
        .event-description {
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 1.25rem;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            font-size: 0.9rem;
        }
        
        .event-dates {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1.25rem;
            padding: 1rem;
            background: #fafafa;
            border-radius: 12px;
        }
        
        .date-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .date-icon {
            width: 36px;
            height: 36px;
            background: white;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            color: #dc2626;
        }
        
        .date-info {
            display: flex;
            flex-direction: column;
        }
        
        .date-label {
            font-size: 0.7rem;
            color: #9ca3af;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .date-value {
            font-size: 0.875rem;
            color: #1a1a1a;
            font-weight: 600;
        }
        
        .event-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
        }
        
        .btn {
            padding: 0.75rem 1.25rem;
            border: none;
            border-radius: 10px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .btn-edit {
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            color: white;
            box-shadow: 0 2px 8px rgba(251, 191, 36, 0.2);
        }
        
        .btn-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(251, 191, 36, 0.3);
        }
        
        .btn-delete {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.2);
        }
        
        .btn-delete:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }
        
        /* Empty State */
        .empty-state {
            background: white;
            border-radius: 16px;
            padding: 4rem 2rem;
            text-align: center;
            border: 2px dashed #e5e7eb;
        }
        
        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        .empty-state h3 {
            color: #1a1a1a;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }
        
        .empty-state p {
            color: #6b7280;
            font-size: 1rem;
            line-height: 1.6;
        }
        
        /* Responsive */
        @media (max-width: 1024px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .form-group.full-width {
                grid-column: span 1;
            }
            
            .events-grid {
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
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
            
            .create-section {
                padding: 1.5rem;
            }
            
            .section-title {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            
            .events-grid {
                grid-template-columns: 1fr;
            }
            
            .event-actions {
                grid-template-columns: 1fr;
            }
            
            .event-dates {
                grid-template-columns: 1fr;
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
            <h1><span>📅</span> Event Management</h1>
            <p>Create and manage your campus events</p>
        </div>
    </div>
    
    <div class="container">
        <!-- Create Event Section -->
        <div class="create-section">
            <div class="section-title">
                <div class="section-title-icon">➕</div>
                <h2>Create New Event</h2>
            </div>
            
            <form method="POST" enctype="multipart/form-data" id="eventForm">
                <div class="form-grid">
                    <div class="form-group">
                        <label>
                            <span>🏷️</span>
                            <span>Event Name *</span>
                        </label>
                        <input type="text" name="name" placeholder="Enter event name" required>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <span>📋</span>
                            <span>Event Type *</span>
                        </label>
                        <select name="type" required>
                            <option value="">Select event type</option>
                            <option value="UKM">UKM</option>
                            <option value="Event">Event</option>
                            <option value="Volunteer">Volunteer</option>
                            <option value="Organization">Organization</option>
                        </select>
                    </div>
                    
                    <div class="form-group full-width">
                        <label>
                            <span>📝</span>
                            <span>Description *</span>
                        </label>
                        <textarea name="description" placeholder="Enter event description" required></textarea>
                    </div>
                    
                    <div class="form-group full-width">
                        <label>
                            <span>🖼️</span>
                            <span>Event Poster</span>
                        </label>
                        <div class="file-input-wrapper">
                            <input type="file" name="poster" id="posterInput" accept="image/*">
                            <label for="posterInput" class="file-input-label">
                                <span>☁️</span>
                                <span>Choose poster image (Max 2MB)</span>
                            </label>
                            <div class="file-name" id="fileName"></div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <span>📅</span>
                            <span>Start Date *</span>
                        </label>
                        <input type="datetime-local" name="start_date" required>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <span>📅</span>
                            <span>End Date *</span>
                        </label>
                        <input type="datetime-local" name="end_date" required>
                    </div>
                </div>
                
                <button type="submit" name="create_event" class="btn-create">
                    <span>🚀</span>
                    <span>Create Event</span>
                </button>
            </form>
        </div>

        <!-- Events List Section -->
        <div class="events-section">
            <div class="events-header">
                <h2 class="events-title">
                    <span>🎯</span>
                    <span>My Events</span>
                </h2>
                <span class="event-count"><?= count($events) ?> Events</span>
            </div>
            
            <?php if(count($events) > 0): ?>
                <div class="events-grid">
                    <?php foreach($events as $event): ?>
                    <div class="event-card">
                        <div class="event-image-container">
                            <?php if($event['poster']): ?>
                                <img src="../../<?= htmlspecialchars($event['poster']) ?>" alt="<?= htmlspecialchars($event['name']) ?>" class="event-image">
                            <?php else: ?>
                                <div class="event-placeholder">📅</div>
                            <?php endif; ?>
                            <span class="event-type-badge"><?= htmlspecialchars($event['type']) ?></span>
                        </div>
                        
                        <div class="event-content">
                            <h3 class="event-title"><?= htmlspecialchars($event['name']) ?></h3>
                            <p class="event-description"><?= htmlspecialchars($event['description']) ?></p>
                            
                            <div class="event-dates">
                                <div class="date-item">
                                    <div class="date-icon">▶️</div>
                                    <div class="date-info">
                                        <span class="date-label">Start</span>
                                        <span class="date-value"><?= date('d M Y', strtotime($event['start_date'])) ?></span>
                                    </div>
                                </div>
                                <div class="date-item">
                                    <div class="date-icon">⏹️</div>
                                    <div class="date-info">
                                        <span class="date-label">End</span>
                                        <span class="date-value"><?= date('d M Y', strtotime($event['end_date'])) ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="event-actions">
                                <a href="edit_event.php?id=<?= $event['id'] ?>" class="btn btn-edit">
                                    <span>✏️</span>
                                    <span>Edit</span>
                                </a>
                                <a href="delete_event.php?id=<?= $event['id'] ?>" class="btn btn-delete" 
                                   onclick="return confirm('Are you sure you want to delete this event?')">
                                    <span>🗑️</span>
                                    <span>Delete</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📭</div>
                    <h3>No Events Yet</h3>
                    <p>Start creating your first event using the form above to engage your campus community.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // File input display name
        document.getElementById('posterInput').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name;
            const fileNameDisplay = document.getElementById('fileName');
            
            if (fileName) {
                // File size validation
                const file = e.target.files[0];
                const maxSize = 2 * 1024 * 1024; // 2MB
                
                if (file.size > maxSize) {
                    alert('File size must be less than 2MB');
                    e.target.value = '';
                    fileNameDisplay.innerHTML = '';
                    return;
                }
                
                fileNameDisplay.innerHTML = '<span>✅</span><span>' + fileName + '</span>';
            }
        });

        // Form validation
        document.getElementById('eventForm').addEventListener('submit', function(e) {
            const startDate = new Date(document.querySelector('input[name="start_date"]').value);
            const endDate = new Date(document.querySelector('input[name="end_date"]').value);
            
            if (endDate <= startDate) {
                e.preventDefault();
                alert('End date must be after start date!');
                return false;
            }
        });
    </script>
</body>
</html>