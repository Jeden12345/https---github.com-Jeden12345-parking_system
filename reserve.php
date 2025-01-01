<?php
session_start();
require 'db.php'; // 資料庫連線檔案

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// 查詢使用者是否已經有預約
$stmt = $pdo->prepare("SELECT * FROM parking_slots WHERE reserved_by = ?");
$stmt->execute([$user_id]);
$user_reservation = $stmt->fetch();

if ($user_reservation) {
    echo "您已經預約了一個車位，無法再進行預約。";
    exit;
}

// 確保有提供 slot_id 且值不為空
if (!isset($_POST['slot_id']) || empty($_POST['slot_id'])) {
    echo "無效的請求。未指定車位。";
    exit;
}

$slot_id = $_POST['slot_id'];

// 查詢該車位是否已經被預約
$stmt = $pdo->prepare("SELECT * FROM parking_slots WHERE id = ? AND status = 'available'");
$stmt->execute([$slot_id]);
$slot = $stmt->fetch();

if (!$slot) {
    echo "該車位已被預約或不存在。";
    exit;
}

// 更新該車位的狀態為已預約並指定預約使用者
$stmt = $pdo->prepare("UPDATE parking_slots SET reserved_by = ?, status = 'reserved', reserved_at = NOW() WHERE id = ?");
$result = $stmt->execute([$user_id, $slot_id]);

if ($result) {
    // 更新成功，重定向回首頁
    header("Location: index.php?message=reserve_success");
    exit;
} else {
    // 如果更新失敗，顯示錯誤訊息
    echo "預約失敗，請稍後再試。";
    exit;
}
var_dump($_POST);
exit;

?>
