<?php
include '../../includes/config.php';
include '../../includes/auth.php';

if($_SESSION['role'] != 'organizer') {
    header('Location: ../../index.php');
    exit();
}

// Check if event ID is provided
if(!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Event ID is required!";
    header('Location: events.php');
    exit();
}

$event_id = intval($_GET['id']);

// Get event details to verify ownership and get poster path
$sql = "SELECT * FROM events WHERE id = ? AND organizer_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$event_id, $_SESSION['user_id']]);
$event = $stmt->fetch();

if(!$event) {
    $_SESSION['error'] = "Event not found or you don't have permission to delete it!";
    header('Location: events.php');
    exit();
}

// Check if there are applications for this event
$apps_sql = "SELECT COUNT(*) as app_count FROM applications WHERE event_id = ?";
$apps_stmt = $pdo->prepare($apps_sql);
$apps_stmt->execute([$event_id]);
$app_count = $apps_stmt->fetch()['app_count'];

// Handle form submission for confirmation
if($_POST && isset($_POST['confirm_delete'])) {
    try {
        $pdo->beginTransaction();
        
        // Delete related applications first (due to foreign key constraints)
        $delete_apps_sql = "DELETE FROM applications WHERE event_id = ?";
        $delete_apps_stmt = $pdo->prepare($delete_apps_sql);
        $delete_apps_stmt->execute([$event_id]);
        
        // Delete the event
        $delete_sql = "DELETE FROM events WHERE id = ? AND organizer_id = ?";
        $delete_stmt = $pdo->prepare($delete_sql);
        $delete_stmt->execute([$event_id, $_SESSION['user_id']]);
        
        // Delete poster file if exists
        if($event['poster'] && file_exists('../../' . $event['poster'])) {
            $poster_path = '../../' . $event['poster'];
            // Check if file exists and is not a default image
            if(file_exists($poster_path) && !str_contains($poster_path, 'default')) {
                unlink($poster_path);
            }
        }
        
        $pdo->commit();
        
        $_SESSION['success'] = "Event '{$event['name']}' has been deleted successfully!";
        header('Location: events.php');
        exit();
        
    } catch(PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Error deleting event: " . $e->getMessage();
        header('Location: events.php');
        exit();
    }
}

// If cancellation requested
if(isset($_POST['cancel'])) {
    header('Location: events.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Delete Event - CampusConnect</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .delete-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
        }
        
        .delete-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            text-align: center;
            border-top: 5px solid #dc3545;
        }
        
        .warning-icon {
            font-size: 4rem;
            color: #dc3545;
            margin-bottom: 20px;
        }
        
        .event-details {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            text-align: left;
        }
        
        .event-detail {
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
        }
        
        .event-detail strong {
            color: #333;
        }
        
        .applications-warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            color: #856404;
        }
        
        .delete-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
        
        .poster-preview {
            max-width: 200px;
            margin: 0 auto 20px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .poster-preview img {
            width: 100%;
            height: auto;
            display: block;
        }
        
        @media (max-width: 768px) {
            .delete-container {
                margin: 20px auto;
                padding: 10px;
            }
            
            .delete-card {
                padding: 20px;
            }
            
            .delete-actions {
                flex-direction: column;
            }
            
            .btn-danger, .btn-secondary {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <?php include '../../includes/header_organizer.php'; ?>
    
    <div class="delete-container">
        <div class="delete-card">
            <div class="warning-icon">
                ⚠️
            </div>
            
            <h2>Delete Event</h2>
            <p>Are you sure you want to delete this event? This action cannot be undone.</p>
            
            <!-- Event Poster Preview -->
            <?php if($event['poster']): ?>
            <div class="poster-preview">
                <img src="../../<?= htmlspecialchars($event['poster']) ?>" alt="Event Poster">
            </div>
            <?php endif; ?>
            
            <!-- Event Details -->
            <div class="event-details">
                <div class="event-detail">
                    <strong>Event Name:</strong>
                    <span><?= htmlspecialchars($event['name']) ?></span>
                </div>
                <div class="event-detail">
                    <strong>Type:</strong>
                    <span><?= htmlspecialchars($event['type']) ?></span>
                </div>
                <div class="event-detail">
                    <strong>Start Date:</strong>
                    <span><?= date('d M Y H:i', strtotime($event['start_date'])) ?></span>
                </div>
                <div class="event-detail">
                    <strong>End Date:</strong>
                    <span><?= date('d M Y H:i', strtotime($event['end_date'])) ?></span>
                </div>
                <div class="event-detail">
                    <strong>Description:</strong>
                    <span><?= htmlspecialchars($event['description']) ?></span>
                </div>
            </div>
            
            <!-- Warning about applications -->
            <?php if($app_count > 0): ?>
            <div class="applications-warning">
                <strong>⚠️ Important:</strong> This event has <strong><?= $app_count ?></strong> application(s). 
                Deleting this event will also permanently delete all associated applications.
            </div>
            <?php endif; ?>
            
            <!-- Delete Confirmation Form -->
            <form method="POST">
                <div class="delete-actions">
                    <button type="submit" name="confirm_delete" class="btn-danger" 
                            onclick="return confirm('Are you absolutely sure? This cannot be undone!')">
                        Yes, Delete Event
                    </button>
                    <a href="events.php" class="btn-secondary">Cancel</a>
                </div>
            </form>
            
            <!-- Additional Safety Notice -->
            <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px; font-size: 0.9rem; color: #666;">
                <strong>Safety Notice:</strong> This action will permanently remove the event and all associated data from the system.
            </div>
        </div>
    </div>

    <script>
        // Additional confirmation for delete
        document.querySelector('form').addEventListener('submit', function(e) {
            if(!confirm('⚠️ FINAL WARNING: This will permanently delete the event and all applications. This action cannot be undone!')) {
                e.preventDefault();
            }
        });
        
        // Keyboard shortcut for cancel (Escape key)
        document.addEventListener('keydown', function(e) {
            if(e.key === 'Escape') {
                window.location.href = 'events.php';
            }
        });
    </script>
</body>
</html>