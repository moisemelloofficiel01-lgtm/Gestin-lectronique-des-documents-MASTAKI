<?php
$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
header("Access-Control-Allow-Origin: " . $origin);
header("Vary: Origin");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin");

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// #region debug-point C:request-meta
@file_get_contents(
    'http://127.0.0.1:7777/event',
    false,
    stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\n",
            'content' => json_encode([
                'sessionId' => 'flutter-login-fetch',
                'runId' => 'pre-fix',
                'hypothesisId' => 'C',
                'location' => 'login.php:1',
                'msg' => '[DEBUG] API login request metadata',
                'data' => [
                    'method' => $_SERVER['REQUEST_METHOD'] ?? null,
                    'origin' => $_SERVER['HTTP_ORIGIN'] ?? null,
                    'contentType' => $_SERVER['CONTENT_TYPE'] ?? null,
                ],
                'ts' => round(microtime(true) * 1000),
            ]),
            'timeout' => 1,
        ],
    ])
);
// #endregion

include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$rawInput = file_get_contents("php://input");
$data = null;

if (!empty($rawInput)) {
    $decoded = json_decode($rawInput);
    if (json_last_error() === JSON_ERROR_NONE) {
        $data = $decoded;
    }
}

if (!$data && !empty($_POST)) {
    $data = (object) $_POST;
}

if (!$data && !empty($rawInput)) {
    parse_str($rawInput, $parsedBody);
    if (!empty($parsedBody)) {
        $data = (object) $parsedBody;
    }
}

if (!$data) {
    error_log("Login API Error: No data received or invalid payload. Raw input: " . $rawInput);
}

// #region debug-point D:payload-state
@file_get_contents(
    'http://127.0.0.1:7777/event',
    false,
    stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\n",
            'content' => json_encode([
                'sessionId' => 'flutter-login-fetch',
                'runId' => 'pre-fix',
                'hypothesisId' => 'D',
                'location' => 'login.php:42',
                'msg' => '[DEBUG] API login payload inspection',
                'data' => [
                    'rawInputLength' => strlen($rawInput),
                    'hasEmail' => isset($data->email),
                    'hasPassword' => isset($data->password),
                ],
                'ts' => round(microtime(true) * 1000),
            ]),
            'timeout' => 1,
        ],
    ])
);
// #endregion

if(isset($data->email) && isset($data->password)) {
    $email = $data->email;
    $password = $data->password;

    $query = "SELECT utilisateur_id, nom, prenom, mot_de_passe, roles, actif FROM utilisateurs WHERE email = :email LIMIT 0,1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":email", $email);
    $stmt->execute();

    if($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Check if account is active
        if (!$row['actif']) {
            echo json_encode(array("status" => "error", "message" => "Account is inactive."));
            exit;
        }

        if(password_verify($password, $row['mot_de_passe'])) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['user_id'] = $row['utilisateur_id'];
            $_SESSION['roles'] = $row['roles']; // JSON string
            $_SESSION['username'] = $row['prenom'] . ' ' . $row['nom'];

            echo json_encode(array(
                "status" => "success",
                "message" => "Login successful.",
                "user" => array(
                    "id" => $row['utilisateur_id'],
                    "username" => $row['prenom'] . ' ' . $row['nom'],
                    "roles" => json_decode($row['roles'])
                ),
                "redirect" => "index.php" 
            ));
        } else {
            echo json_encode(array("status" => "error", "message" => "Invalid password."));
        }
    } else {
        echo json_encode(array("status" => "error", "message" => "User not found."));
    }
} else {
    echo json_encode(array("status" => "error", "message" => "Incomplete data."));
}
?>
