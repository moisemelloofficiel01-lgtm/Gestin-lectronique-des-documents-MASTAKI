<?php
header("Content-Type: application/json; charset=UTF-8");

echo json_encode([
    "status" => "success",
    "message" => "Point d'entree des APIs mobile.",
    "base_path" => "/team37/src/api/mobile/",
]);
?>
