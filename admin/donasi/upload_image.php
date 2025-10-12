<?php
session_start();

if (!isset($_SESSION['admin'])) {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$targetDir = "../../uploads/";

if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
}

if (!empty($_FILES['file']['name'])) {
    $fileName = time() . '_' . basename($_FILES['file']['name']);
    $targetFilePath = $targetDir . $fileName;
    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

    $allowTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (in_array($fileType, $allowTypes)) {
        if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFilePath)) {
            // URL lengkap agar TinyMCE bisa menampilkan preview
            echo json_encode(['location' => '../../uploads/' . $fileName]);
            exit;
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Gagal mengupload file."]);
            exit;
        }
    } else {
        http_response_code(400);
        echo json_encode(["error" => "Jenis file tidak diizinkan."]);
        exit;
    }
} else {
    http_response_code(400);
    echo json_encode(["error" => "Tidak ada file yang diunggah."]);
    exit;
}
