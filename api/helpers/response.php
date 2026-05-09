<?php
// Response helper for BMS API

class ApiResponse {
    public static function success($data = null, $message = '', $code = 200) {
        header('Content-Type: application/json');
        http_response_code($code);
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
        exit;
    }

    public static function error($message, $code = 400) {
        header('Content-Type: application/json');
        http_response_code($code);
        echo json_encode([
            'success' => false,
            'message' => $message
        ]);
        exit;
    }
}