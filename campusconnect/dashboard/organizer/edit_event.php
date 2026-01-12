<?php
include '../../includes/config.php';
include '../../includes/auth.php';

if($_SESSION['role'] != 'organizer') {
    header('Location: ../../index.php');
    exit();
}

$event_id = $_GET['id'] ?? 0;

// Get event data
$sql = "SELECT * FROM events WHERE id = ? AND organizer_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$event_id, $_SESSION['user_id']]);
$event = $stmt->fetch();

if(!$event) {
    header('Location: events.php');
    exit();
}

if($_POST && isset($_POST['update_event'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $type = $_POST['type'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    
    $poster = $event['poster'];
    if(isset($_FILES['poster']) && $_FILES['poster']['error'] == 0) {
        $upload_dir = '../../assets/uploads/';
        $poster_name = time() . '_' . $_FILES['poster']['name'];
        move_uploaded_file($_FILES['poster']['tmp_name'], $upload_dir . $poster_name);
        $poster = 'assets/uploads/' . $poster_name;
    }
    
    $sql = "UPDATE events SET name = ?, description = ?, type = ?, poster = ?, start_date = ?, end_date = ? 
            WHERE id = ? AND organizer_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$name, $description, $type, $poster, $start_date, $end_date, $event_id, $_SESSION['user_id']]);
    
    header('Location: events.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event - CampusConnect</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #ffffff;
            min-height: 100vh;
            padding-bottom: 60px;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 40px;
            animation: fadeInDown 0.6s ease;
        }
        
        .page-header h1 {
            color: #2d3748;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }
        
        .page-header h1 i {
            color: #667eea;
        }
        
        .page-header p {
            color: #718096;
            font-size: 1.05rem;
            font-weight: 300;
        }
        
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 30px;
            color: #718096;
            font-size: 0.95rem;
        }
        
        .breadcrumb a {
            color: #667eea;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .breadcrumb a:hover {
            color: #764ba2;
        }
        
        .breadcrumb i {
            font-size: 0.8rem;
        }
        
        /* Edit Form Section */
        .edit-section {
            background: white;
            border-radius: 24px;
            padding: 45px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.08);
            animation: fadeInUp 0.6s ease;
            position: relative;
            overflow: hidden;
        }
        
        .edit-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }
        
        .section-title {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 35px;
            color: #2d3748;
        }
        
        .section-title i {
            font-size: 1.8rem;
            color: #667eea;
        }
        
        .section-title h2 {
            font-size: 1.8rem;
            font-weight: 600;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 25px;
        }
        
        .form-group {
            margin-bottom: 0;
        }
        
        .form-group.full-width {
            grid-column: span 2;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: #4a5568;
            font-size: 0.95rem;
        }
        
        .form-group label i {
            margin-right: 8px;
            color: #667eea;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 15px;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
            background: #f8fafc;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 4px rgba(102,126,234,0.1);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }
        
        /* Current Poster Display */
        .current-poster {
            margin-bottom: 15px;
            padding: 20px;
            background: #f8fafc;
            border-radius: 12px;
            border: 2px dashed #cbd5e0;
        }
        
        .current-poster-label {
            font-size: 0.85rem;
            color: #718096;
            font-weight: 600;
            margin-bottom: 12px;
            display: block;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .poster-preview {
            width: 100%;
            max-width: 300px;
            height: auto;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        /* File Input */
        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }
        
        .file-input-wrapper input[type="file"] {
            position: absolute;
            left: -9999px;
        }
        
        .file-input-label {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 14px 18px;
            background: #f8fafc;
            border: 2px dashed #cbd5e0;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #718096;
            font-weight: 500;
        }
        
        .file-input-label:hover {
            border-color: #667eea;
            background: #edf2f7;
            color: #667eea;
        }
        
        .file-input-label i {
            font-size: 1.2rem;
        }
        
        .file-name {
            margin-top: 8px;
            font-size: 0.85rem;
            color: #667eea;
            font-weight: 500;
        }
        
        /* Action Buttons */
        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 35px;
            padding-top: 30px;
            border-top: 2px solid #f1f5f9;
        }
        
        .btn {
            padding: 16px 35px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102,126,234,0.4);
            flex: 1;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(102,126,234,0.5);
        }
        
        .btn-secondary {
            background: #e2e8f0;
            color: #4a5568;
            flex: 0.5;
        }
        
        .btn-secondary:hover {
            background: #cbd5e0;
            transform: translateY(-2px);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        /* Alert Box */
        .alert-info {
            background: #e0e7ff;
            border-left: 4px solid #667eea;
            padding: 16px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            color: #4338ca;
        }
        
        .alert-info i {
            font-size: 1.3rem;
        }
        
        /* Animations */
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 20px 15px;
            }
            
            .page-header h1 {
                font-size: 1.8rem;
                flex-direction: column;
            }
            
            .edit-section {
                padding: 30px 20px;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .form-group.full-width {
                grid-column: span 1;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 5px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #5568d3 0%, #6a3f91 100%);
        }
    </style>
</head>
<body>
    <?php include '../../includes/header_organizer.php'; ?>
    
    <div class="container">
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="events.php"><i class="fas fa-home"></i> My Events</a>
            <i class="fas fa-chevron-right"></i>
            <span>Edit Event</span>
        </div>
        
        <!-- Page Header -->
        <div class="page-header">
            <h1>
                <i class="fas fa-edit"></i>
                Edit Event
            </h1>
            <p>Update your event information</p>
        </div>
        
        <!-- Edit Form Section -->
        <div class="edit-section">
            <div class="alert-info">
                <i class="fas fa-info-circle"></i>
                <span>You are editing: <strong><?= htmlspecialchars($event['name']) ?></strong></span>
            </div>
            
            <form method="POST" enctype="multipart/form-data" id="editForm">
                <div class="form-grid">
                    <div class="form-group">
                        <label><i class="fas fa-tag"></i> Event Name *</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($event['name']) ?>" placeholder="Enter event name" required>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-list"></i> Event Type *</label>
                        <select name="type" required>
                            <option value="UKM" <?= $event['type'] == 'UKM' ? 'selected' : '' ?>>UKM</option>
                            <option value="Event" <?= $event['type'] == 'Event' ? 'selected' : '' ?>>Event</option>
                            <option value="Volunteer" <?= $event['type'] == 'Volunteer' ? 'selected' : '' ?>>Volunteer</option>
                            <option value="Organization" <?= $event['type'] == 'Organization' ? 'selected' : '' ?>>Organization</option>
                        </select>
                    </div>
                    
                    <div class="form-group full-width">
                        <label><i class="fas fa-align-left"></i> Description *</label>
                        <textarea name="description" placeholder="Enter event description" required><?= htmlspecialchars($event['description']) ?></textarea>
                    </div>
                    
                    <div class="form-group full-width">
                        <label><i class="fas fa-image"></i> Event Poster</label>
                        
                        <?php if($event['poster']): ?>
                        <div class="current-poster">
                            <span class="current-poster-label">Current Poster</span>
                            <img src="../../<?= htmlspecialchars($event['poster']) ?>" alt="Current Poster" class="poster-preview">
                        </div>
                        <?php endif; ?>
                        
                        <div class="file-input-wrapper">
                            <input type="file" name="poster" id="posterInput" accept="image/*">
                            <label for="posterInput" class="file-input-label">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span>Change poster image (Max 2MB)</span>
                            </label>
                            <div class="file-name" id="fileName"></div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-calendar-check"></i> Start Date *</label>
                        <input type="datetime-local" name="start_date" value="<?= date('Y-m-d\TH:i', strtotime($event['start_date'])) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-calendar-times"></i> End Date *</label>
                        <input type="datetime-local" name="end_date" value="<?= date('Y-m-d\TH:i', strtotime($event['end_date'])) ?>" required>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="update_event" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Update Event
                    </button>
                    <a href="events.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                        Cancel
                    </a>
                </div>
            </form>
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
                    fileNameDisplay.textContent = '';
                    return;
                }
                
                fileNameDisplay.innerHTML = '<i class="fas fa-check-circle"></i> ' + fileName;
            }
        });

        // Form validation
        document.getElementById('editForm').addEventListener('submit', function(e) {
            const startDate = new Date(document.querySelector('input[name="start_date"]').value);
            const endDate = new Date(document.querySelector('input[name="end_date"]').value);
            
            if (endDate <= startDate) {
                e.preventDefault();
                alert('End date must be after start date!');
                return false;
            }
        });

        // Confirmation before leaving with unsaved changes
        let formChanged = false;
        const form = document.getElementById('editForm');
        const inputs = form.querySelectorAll('input, select, textarea');
        
        inputs.forEach(input => {
            input.addEventListener('change', () => {
                formChanged = true;
            });
        });
        
        window.addEventListener('beforeunload', (e) => {
            if (formChanged) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
        
        form.addEventListener('submit', () => {
            formChanged = false;
        });
    </script>
</body>
</html>