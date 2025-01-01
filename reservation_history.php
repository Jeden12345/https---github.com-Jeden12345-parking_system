<?php
session_start();
require 'db.php'; // 資料庫連線

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// 查詢使用者的預約紀錄，包括取消的紀錄
$stmt = $pdo->prepare("
    SELECT floor, slot_number, reserved_at, status
    FROM parking_slots
    WHERE reserved_by = :user_id OR (status = 'available' AND reserved_by IS NULL)
    ORDER BY reserved_at DESC
");
$stmt->execute(['user_id' => $user_id]);
$reservations = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>預約紀錄</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-4">

    <h1 class="text-center">預約紀錄</h1>

    <?php if (empty($reservations)): ?>
        <p class="text-center">您目前沒有預約紀錄。</p>
    <?php else: ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>樓層</th>
                    <th>車位號碼</th>
                    <th>預約時間</th>
                    <th>狀態</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reservations as $reservation): ?>
                    <tr>
                        <td><?= $reservation['floor'] ?></td>
                        <td><?= $reservation['slot_number'] ?></td>
                        <td>
                            <?= $reservation['reserved_at'] ? date('Y-m-d H:i:s', strtotime($reservation['reserved_at'])) : '無' ?>
                        </td>
                        <td>
                            <?= $reservation['status'] === 'reserved' ? '已預約' : '已取消' ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div class="text-center">
        <a href="index.php" class="btn btn-secondary">回到首頁</a>
    </div>

</body>
</html>
