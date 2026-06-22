<?php
function ensureNotificationsTableSchema($conn) {
    // Check if table exists
    $checkTable = mysqli_query($conn, "SHOW TABLES LIKE 'notifications'");
    if (mysqli_num_rows($checkTable) == 0) {
        $sql = "CREATE TABLE notifications (
            notification_id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            title VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            type VARCHAR(50) DEFAULT 'info', -- info, warning, success, error
            is_read BOOLEAN DEFAULT FALSE,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            link VARCHAR(255),
            FOREIGN KEY (user_id) REFERENCES utilisateurs(utilisateur_id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        if (!mysqli_query($conn, $sql)) {
            error_log("Error creating table notifications: " . mysqli_error($conn));
            return false;
        }
    }
    return true;
}
?>