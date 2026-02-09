<?php
function successResponse($data,$message, $statusCode = 201) {
    http_response_code($statusCode);
    echo json_encode([
        'status' => 'success',
        'message' => $message,
        'data' => $data
    ]);

}

function errorResponse($message, $statusCode = 400, $data = null) {
    http_response_code($statusCode);
    echo json_encode([
        'status' => 'error',
        'message' => $message,
        'data' => $data
    ]);
}
?>