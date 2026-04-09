<?php
header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, proxy-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

session_start();

if (isset($_SESSION['user_id'])) {
    http_response_code(200);
    echo json_encode(['status' => 'active', 'user_id' => $_SESSION['user_id']]);
} else {
    http_response_code(401);
    echo json_encode(['status' => 'inactive']);
}
exit;
?>