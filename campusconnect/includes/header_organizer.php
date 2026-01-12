<?php
// Get organizer info
$user_sql = "SELECT username FROM users WHERE id = ?";
$user_stmt = $pdo->prepare($user_sql);
$user_stmt->execute([$_SESSION['user_id']]);
$organizer = $user_stmt->fetch();

// Get unread notifications count
$notif_sql = "SELECT COUNT(*) as unread_count FROM notifications WHERE user_id = ? AND is_read = 0";
$notif_stmt = $pdo->prepare($notif_sql);
$notif_stmt->execute([$_SESSION['user_id']]);
$notif_count = $notif_stmt->fetch()['unread_count'];
?>

<style>
header {
    background: #ffffff;
    box-shadow: 0 2px 20px rgba(220, 38, 38, 0.1);
    position: sticky;
    top: 0;
    z-index: 1000;
    border-bottom: 2px solid rgba(220, 38, 38, 0.1);
}

header nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 2.5rem;
    max-width: 1400px;
    margin: 0 auto;
}

.nav-brand {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.brand-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #dc2626;
    letter-spacing: -0.5px;
    line-height: 1;
}

.brand-subtitle {
    font-size: 0.75rem;
    font-weight: 500;
    color: #ef4444;
    letter-spacing: 2px;
    text-transform: uppercase;
    margin-left: 2px;
    opacity: 0.8;
}

.nav-menu {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.nav-item {
    position: relative;
    display: flex;
    align-items: center;
}

.notification-bell {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 42px;
    height: 42px;
    background: rgba(220, 38, 38, 0.08);
    border-radius: 12px;
    font-size: 1.25rem;
    transition: all 0.3s ease;
    text-decoration: none;
}

.notification-bell:hover {
    background: rgba(220, 38, 38, 0.15);
    transform: translateY(-2px);
}

.notification-count {
    position: absolute;
    top: -6px;
    right: -6px;
    background: #dc2626;
    color: #ffffff;
    font-size: 0.7rem;
    font-weight: 700;
    padding: 2px 6px;
    border-radius: 10px;
    min-width: 20px;
    text-align: center;
    box-shadow: 0 2px 8px rgba(220, 38, 38, 0.3);
}

.user-profile {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px 16px 8px 8px;
    background: rgba(220, 38, 38, 0.05);
    border-radius: 50px;
    cursor: pointer;
    transition: all 0.3s ease;
    border: 1px solid rgba(220, 38, 38, 0.1);
}

.user-profile:hover {
    background: rgba(220, 38, 38, 0.1);
    border-color: rgba(220, 38, 38, 0.2);
}

.avatar {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #dc2626;
    transition: all 0.3s ease;
    flex-shrink: 0;
}

.user-profile:hover .avatar {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
}

.user-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
    min-width: 0;
}

.user-name {
    font-weight: 600;
    font-size: 0.9rem;
    color: #dc2626;
    line-height: 1.2;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 150px;
}

.user-role {
    font-size: 0.75rem;
    color: #ef4444;
    opacity: 0.7;
    line-height: 1;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.dropdown-arrow {
    color: #dc2626;
    font-size: 0.75rem;
    margin-left: 4px;
    transition: transform 0.3s ease;
}

.user-profile:hover .dropdown-arrow {
    transform: rotate(180deg);
}

.nav-item:hover .dropdown {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.dropdown {
    position: absolute;
    top: calc(100% + 15px);
    right: 0;
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    min-width: 240px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s ease;
    overflow: hidden;
    border: 1px solid rgba(220, 38, 38, 0.1);
}

.dropdown::before {
    content: '';
    position: absolute;
    top: -6px;
    right: 20px;
    width: 12px;
    height: 12px;
    background: #ffffff;
    transform: rotate(45deg);
    border-left: 1px solid rgba(220, 38, 38, 0.1);
    border-top: 1px solid rgba(220, 38, 38, 0.1);
}

.dropdown a {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.875rem 1.25rem;
    color: #374151;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    transition: all 0.2s ease;
    border-bottom: 1px solid #f3f4f6;
}

.dropdown a:last-child {
    border-bottom: none;
    color: #dc2626;
    font-weight: 600;
}

.dropdown a:hover {
    background: #fef2f2;
    color: #dc2626;
    padding-left: 1.5rem;
}

.dropdown a:last-child:hover {
    background: #dc2626;
    color: #ffffff;
}

.notif-badge {
    background: #dc2626;
    color: #ffffff;
    font-size: 0.7rem;
    font-weight: 700;
    padding: 2px 6px;
    border-radius: 10px;
    min-width: 20px;
    text-align: center;
}

/* Responsive Design */
@media (max-width: 768px) {
    header nav {
        padding: 1rem 1.5rem;
    }
    
    .brand-title {
        font-size: 1.5rem;
    }
    
    .brand-subtitle {
        font-size: 0.65rem;
    }
    
    .nav-menu {
        gap: 1rem;
    }
    
    .user-info {
        display: none;
    }
    
    .user-profile {
        padding: 8px;
        background: transparent;
        border: none;
    }
    
    .dropdown-arrow {
        display: none;
    }
    
    .dropdown {
        right: -10px;
    }
}

@media (max-width: 480px) {
    .user-name {
        max-width: 100px;
    }
}
</style>

<header>
    <nav>
        <div class="nav-brand">
            <div class="brand-title">CampusConnect</div>
            <div class="brand-subtitle">Organizer</div>
        </div>
        <div class="nav-menu">
            <div class="nav-item">
                <a href="notifications.php" class="notification-bell">
                    🔔
                    <?php if($notif_count > 0): ?>
                    <span class="notification-count"><?= $notif_count ?></span>
                    <?php endif; ?>
                </a>
            </div>
            <div class="nav-item">
                <div class="user-profile">
                    <img src="../../assets/images/admin-logo.png" class="avatar" alt="Organizer">
                    <div class="user-info">
                        <div class="user-name"><?= htmlspecialchars($organizer['username']) ?></div>
                        <div class="user-role">Organizer</div>
                    </div>
                    <span class="dropdown-arrow">▼</span>
                </div>
                <div class="dropdown">
                    <a href="index.php">Dashboard</a>
                    <a href="events.php">Manage Events</a>
                    <a href="applicants.php">Applicants</a>
                    <a href="notifications.php">
                        Notifications
                        <?php if($notif_count > 0): ?>
                        <span class="notif-badge"><?= $notif_count ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="../../logout.php">Logout</a>
                </div>
            </div>
        </div>
    </nav>
</header>