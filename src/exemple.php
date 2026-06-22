<?php
$dbHost = getenv('MYSQL_HOST') ?: 'localhost';
$dbName = getenv('MYSQL_DATABASE') ?: 'my_database_ged';
$dbUser = getenv('MYSQL_USER') ?: 'root';
$dbPass = getenv('MYSQL_PASSWORD');
if ($dbPass === false) {
    $dbPass = '';
}

$dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $users = $pdo->query('SELECT id, username, email, created_at FROM users ORDER BY id')->fetchAll();
} catch (Throwable $e) {
    http_response_code(500);
    echo "<h1>Welcome</h1>";
    echo "<p>Cannot connect to MySQL: " . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        table { border-collapse: collapse; width: 100%; max-width: 600px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f4f4f4; }
    </style>
</head>
<body>
    <h1>Welcome</h1>
    <p>Connected to MySQL at <strong><?php echo htmlspecialchars($dbHost); ?></strong>.</p>

    <h2>Users</h2>
    <?php if (empty($users)): ?>
        <p>No users found.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <p>PhpMyAdmin: <a href="http://localhost/phpmyadmin" target="_blank" rel="noreferrer">http://localhost/phpmyadmin</a></p>
</body>
</html>


