<?php
include '../../includes/config.php';
include '../../includes/auth.php';

if($_SESSION['role'] != 'student') {
    header('Location: ../../index.php');
    exit();
}

// Get student data
$sql = "SELECT * FROM students WHERE user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['user_id']]);
$student = $stmt->fetch();

$success_message = '';
$error_message = '';

// Handle form submission
if($_POST && isset($_POST['update_profile'])) {
    $full_name = $_POST['full_name'];
    $portfolio = $_POST['portfolio'];
    
    // Handle photo upload
    $photo = $student['photo'] ?? '';
    if(isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        if(in_array($_FILES['photo']['type'], $allowed_types)) {
            $upload_dir = '../../assets/uploads/';
            if(!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $photo_name = time() . '_' . $_FILES['photo']['name'];
            move_uploaded_file($_FILES['photo']['tmp_name'], $upload_dir . $photo_name);
            $photo = 'assets/uploads/' . $photo_name;
        }
    }
    
    // Handle CV upload
    $cv = $student['cv'] ?? '';
    if(isset($_FILES['cv']) && $_FILES['cv']['error'] == 0) {
        $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        if(in_array($_FILES['cv']['type'], $allowed_types)) {
            $upload_dir = '../../assets/uploads/';
            if(!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $cv_name = time() . '_cv_' . $_FILES['cv']['name'];
            move_uploaded_file($_FILES['cv']['tmp_name'], $upload_dir . $cv_name);
            $cv = 'assets/uploads/' . $cv_name;
        }
    }
    
    // Check if profile is complete
    $is_profile_complete = ($full_name && $photo && $cv && $portfolio) ? 1 : 0;
    
    if($student) {
        // Update existing student
        $sql = "UPDATE students SET full_name = ?, photo = ?, cv = ?, portfolio = ?, is_profile_complete = ? WHERE user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$full_name, $photo, $cv, $portfolio, $is_profile_complete, $_SESSION['user_id']]);
    } else {
        // Insert new student
        $sql = "INSERT INTO students (user_id, full_name, photo, cv, portfolio, is_profile_complete) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_SESSION['user_id'], $full_name, $photo, $cv, $portfolio, $is_profile_complete]);
    }
    
    $success_message = "Profile updated successfully!";
    
    // Refresh student data
    $sql = "SELECT * FROM students WHERE user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION['user_id']]);
    $student = $stmt->fetch();
}

// Calculate profile completion
$completion_fields = [
    'full_name' => isset($student['full_name']) && $student['full_name'],
    'photo' => isset($student['photo']) && $student['photo'],
    'cv' => isset($student['cv']) && $student['cv'],
    'portfolio' => isset($student['portfolio']) && $student['portfolio']
];
$completed = count(array_filter($completion_fields));
$total = count($completion_fields);
$completion_percentage = ($completed / $total) * 100;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - CampusConnect</title>
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

        .page-header {
            margin-bottom: 2rem;
        }

        .page-header h1 {
            font-size: 2rem;
            color: #1f2937;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .page-header p {
            color: #6b7280;
            font-size: 0.95rem;
        }

        .profile-grid {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        /* Profile Card */
        .profile-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
            text-align: center;
            height: fit-content;
        }

        .profile-photo-wrapper {
            position: relative;
            width: 150px;
            height: 150px;
            margin: 0 auto 1.5rem;
        }

        .profile-photo {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #dc2626;
            box-shadow: 0 4px 15px rgba(220, 38, 38, 0.2);
        }

        .profile-photo-placeholder {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            color: #dc2626;
            border: 4px solid #dc2626;
        }

        .profile-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .profile-status {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }

        .status-complete {
            background: #d1fae5;
            color: #065f46;
        }

        .status-incomplete {
            background: #fef3c7;
            color: #92400e;
        }

        /* Progress Section */
        .progress-section {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e5e7eb;
        }

        .progress-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
        }

        .progress-header span {
            font-size: 0.9rem;
            color: #6b7280;
            font-weight: 600;
        }

        .progress-percentage {
            font-size: 1.1rem;
            font-weight: 700;
            color: #dc2626;
        }

        .progress-bar {
            width: 100%;
            height: 8px;
            background: #e5e7eb;
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #dc2626 0%, #ef4444 100%);
            border-radius: 10px;
            transition: width 0.5s ease;
        }

        .progress-items {
            margin-top: 1rem;
            text-align: left;
        }

        .progress-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 0;
            font-size: 0.875rem;
            color: #6b7280;
        }

        .progress-item i {
            width: 20px;
            font-size: 0.9rem;
        }

        .progress-item.completed {
            color: #059669;
        }

        .progress-item.completed i {
            color: #059669;
        }

        /* Form Card */
        .form-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
        }

        .form-card h2 {
            font-size: 1.5rem;
            color: #1f2937;
            margin-bottom: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .form-card h2 i {
            color: #dc2626;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
            color: #374151;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .label-icon {
            color: #dc2626;
            font-size: 0.9rem;
        }

        .form-control {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            background: #f9fafb;
            color: #1f2937;
        }

        .form-control:focus {
            outline: none;
            border-color: #dc2626;
            background: white;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.08);
        }

        .form-control::placeholder {
            color: #9ca3af;
        }

        /* File Upload */
        .file-upload-wrapper {
            position: relative;
        }

        .file-upload-input {
            display: none;
        }

        .file-upload-label {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            padding: 1rem;
            border: 2px dashed #d1d5db;
            border-radius: 12px;
            background: #f9fafb;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            color: #6b7280;
        }

        .file-upload-label:hover {
            border-color: #dc2626;
            background: #fef2f2;
            color: #dc2626;
        }

        .file-upload-label i {
            font-size: 1.5rem;
        }

        .current-file {
            margin-bottom: 0.75rem;
            padding: 0.75rem;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .current-file i {
            color: #3b82f6;
            font-size: 1.2rem;
        }

        .current-file-info {
            flex: 1;
        }

        .current-file-name {
            font-size: 0.875rem;
            color: #1e40af;
            font-weight: 600;
        }

        .current-file-actions {
            display: flex;
            gap: 0.5rem;
        }

        .file-action-btn {
            padding: 0.4rem 0.75rem;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-view {
            background: #dbeafe;
            color: #1e40af;
        }

        .btn-view:hover {
            background: #3b82f6;
            color: white;
        }

        .current-photo {
            margin-bottom: 0.75rem;
            text-align: center;
        }

        .current-photo img {
            width: 120px;
            height: 120px;
            border-radius: 12px;
            object-fit: cover;
            border: 2px solid #dc2626;
        }

        .form-hint {
            font-size: 0.8rem;
            color: #9ca3af;
            margin-top: 0.35rem;
        }

        /* Buttons */
        .btn-primary {
            width: 100%;
            padding: 0.95rem;
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(220, 38, 38, 0.25);
            letter-spacing: 0.3px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(220, 38, 38, 0.35);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        /* Alerts */
        .alert {
            padding: 1rem 1.25rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            animation: slideDown 0.3s ease;
            font-size: 0.9rem;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background: #d1fae5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }

        .alert-warning {
            background: #fef3c7;
            border: 1px solid #fde68a;
            color: #92400e;
        }

        .alert i {
            font-size: 1.2rem;
        }

        /* Responsive */
        @media (max-width: 968px) {
            .profile-grid {
                grid-template-columns: 1fr;
            }

            .profile-card {
                margin-bottom: 1rem;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 1.5rem 1rem;
            }

            .page-header h1 {
                font-size: 1.5rem;
            }

            .profile-photo,
            .profile-photo-placeholder {
                width: 120px;
                height: 120px;
            }

            .profile-name {
                font-size: 1.25rem;
            }

            .form-card {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <?php include '../../includes/header_student.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>My Profile</h1>
            <p>Manage your personal information and complete your profile to apply for events</p>
        </div>

        <?php if($success_message): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <span><?php echo $success_message; ?></span>
        </div>
        <?php endif; ?>

        <div class="profile-grid">
            <!-- Profile Card -->
            <div class="profile-card">
                <div class="profile-photo-wrapper">
                    <?php if(isset($student['photo']) && $student['photo']): ?>
                        <img src="../../<?= $student['photo'] ?>" alt="Profile Photo" class="profile-photo">
                    <?php else: ?>
                        <div class="profile-photo-placeholder">
                            <i class="fas fa-user"></i>
                        </div>
                    <?php endif; ?>
                </div>

                <h3 class="profile-name">
                    <?= htmlspecialchars($student['full_name'] ?? 'Complete Your Profile') ?>
                </h3>

                <?php if($student && $student['is_profile_complete']): ?>
                    <div class="profile-status status-complete">
                        <i class="fas fa-check-circle"></i>
                        Profile Complete
                    </div>
                <?php else: ?>
                    <div class="profile-status status-incomplete">
                        <i class="fas fa-exclamation-circle"></i>
                        Profile Incomplete
                    </div>
                <?php endif; ?>

                <!-- Progress Section -->
                <div class="progress-section">
                    <div class="progress-header">
                        <span>Profile Completion</span>
                        <span class="progress-percentage"><?= round($completion_percentage) ?>%</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?= $completion_percentage ?>%"></div>
                    </div>
                    
                    <div class="progress-items">
                        <div class="progress-item <?= $completion_fields['full_name'] ? 'completed' : '' ?>">
                            <i class="<?= $completion_fields['full_name'] ? 'fas fa-check-circle' : 'far fa-circle' ?>"></i>
                            <span>Full Name</span>
                        </div>
                        <div class="progress-item <?= $completion_fields['photo'] ? 'completed' : '' ?>">
                            <i class="<?= $completion_fields['photo'] ? 'fas fa-check-circle' : 'far fa-circle' ?>"></i>
                            <span>Profile Photo</span>
                        </div>
                        <div class="progress-item <?= $completion_fields['cv'] ? 'completed' : '' ?>">
                            <i class="<?= $completion_fields['cv'] ? 'fas fa-check-circle' : 'far fa-circle' ?>"></i>
                            <span>CV/Resume</span>
                        </div>
                        <div class="progress-item <?= $completion_fields['portfolio'] ? 'completed' : '' ?>">
                            <i class="<?= $completion_fields['portfolio'] ? 'fas fa-check-circle' : 'far fa-circle' ?>"></i>
                            <span>Portfolio Link</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Card -->
            <div class="form-card">
                <h2>
                    <i class="fas fa-user-edit"></i>
                    Edit Profile Information
                </h2>

                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="full_name">
                            <i class="fas fa-user label-icon"></i>
                            Full Name
                        </label>
                        <input 
                            type="text" 
                            id="full_name"
                            name="full_name" 
                            class="form-control" 
                            value="<?= htmlspecialchars($student['full_name'] ?? '') ?>" 
                            placeholder="Enter your full name"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label>
                            <i class="fas fa-camera label-icon"></i>
                            Profile Photo
                        </label>
                        <?php if(isset($student['photo']) && $student['photo']): ?>
                        <div class="current-photo">
                            <img src="../../<?= $student['photo'] ?>" alt="Current Photo">
                        </div>
                        <?php endif; ?>
                        <div class="file-upload-wrapper">
                            <input 
                                type="file" 
                                id="photo"
                                name="photo" 
                                class="file-upload-input"
                                accept="image/*"
                            >
                            <label for="photo" class="file-upload-label">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span>Click to upload photo (JPG, PNG)</span>
                            </label>
                        </div>
                        <div class="form-hint">Maximum file size: 2MB</div>
                    </div>

                    <div class="form-group">
                        <label>
                            <i class="fas fa-file-alt label-icon"></i>
                            CV/Resume
                        </label>
                        <?php if(isset($student['cv']) && $student['cv']): ?>
                        <div class="current-file">
                            <i class="fas fa-file-pdf"></i>
                            <div class="current-file-info">
                                <div class="current-file-name">Current CV</div>
                            </div>
                            <div class="current-file-actions">
                                <a href="../../<?= $student['cv'] ?>" target="_blank" class="file-action-btn btn-view">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>
                        <div class="file-upload-wrapper">
                            <input 
                                type="file" 
                                id="cv"
                                name="cv" 
                                class="file-upload-input"
                                accept=".pdf,.doc,.docx"
                            >
                            <label for="cv" class="file-upload-label">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span>Click to upload CV (PDF, DOC, DOCX)</span>
                            </label>
                        </div>
                        <div class="form-hint">Maximum file size: 5MB</div>
                    </div>

                    <div class="form-group">
                        <label for="portfolio">
                            <i class="fas fa-link label-icon"></i>
                            Portfolio/LinkedIn Profile
                        </label>
                        <input 
                            type="url" 
                            id="portfolio"
                            name="portfolio" 
                            class="form-control" 
                            value="<?= htmlspecialchars($student['portfolio'] ?? '') ?>" 
                            placeholder="https://linkedin.com/in/yourprofile"
                        >
                        <div class="form-hint">Add your LinkedIn profile or portfolio website</div>
                    </div>

                    <button type="submit" name="update_profile" class="btn-primary">
                        <i class="fas fa-save"></i>
                        Update Profile
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // File upload preview
        document.getElementById('photo').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name;
            if(fileName) {
                const label = this.nextElementSibling;
                label.querySelector('span').textContent = fileName;
            }
        });

        document.getElementById('cv').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name;
            if(fileName) {
                const label = this.nextElementSibling;
                label.querySelector('span').textContent = fileName;
            }
        });

        // Auto-hide success message
        const alertSuccess = document.querySelector('.alert-success');
        if(alertSuccess) {
            setTimeout(() => {
                alertSuccess.style.opacity = '0';
                alertSuccess.style.transform = 'translateY(-10px)';
                setTimeout(() => {
                    alertSuccess.remove();
                }, 300);
            }, 3000);
        }
    </script>
</body>
</html>