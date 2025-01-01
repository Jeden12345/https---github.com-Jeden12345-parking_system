<?php
session_start();
require 'db.php'; // 資料庫連線檔案

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// 查詢使用者是否有預約
$stmt = $pdo->prepare("SELECT * FROM parking_slots WHERE reserved_by = ?");
$stmt->execute([$user_id]);
$user_reservation = $stmt->fetch();

if (!$user_reservation) {
    echo "您目前沒有任何預約。";
    exit;
}

// 取得預約的車位 ID
$slot_id = $user_reservation['id'];

// 更新該車位的狀態為可用，並移除預約者資訊
$stmt = $pdo->prepare("UPDATE parking_slots SET reserved_by = NULL, status = 'available', reserved_at = NULL WHERE id = ?");
$result = $stmt->execute([$slot_id]);

if ($result) {
    // 取消成功，重定向到首頁
    header("Location: index.php?message=cancel_success");
    exit;
} else {
    // 如果更新失敗，顯示錯誤訊息
    echo "取消失敗，請稍後再試。";
    exit;
}
