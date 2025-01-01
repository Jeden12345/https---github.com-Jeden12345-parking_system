<?php
session_start();
require 'db.php'; // 資料庫連線

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// 查詢使用者資訊
$stmt = $pdo->prepare("SELECT name, has_paid FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    echo "無法找到使用者資訊，請重新登入。";
    exit;
}

$user_name = $user['name'];
$has_paid = $user['has_paid'];

// 查詢使用者是否已有預約
$stmt = $pdo->prepare("SELECT * FROM parking_slots WHERE reserved_by = ?");
$stmt->execute([$user_id]);
$user_reservation = $stmt->fetch();

// 查詢所有停車位資訊
$stmt = $pdo->query("SELECT * FROM parking_slots ORDER BY floor, slot_number");
$slots = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>停車位預約系統</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .slot {
            width: 80px;
            height: 80px;
            text-align: center;
            line-height: 80px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin: 5px;
        }
        .reserved { background-color: #f8d7da; color: #721c24; }
        .available { background-color: #d4edda; color: #155724; }

        /* 層的顯示格式 */
        .floor-container {
            display: grid;
            grid-template-columns: repeat(10, 1fr);  /* 每行顯示 10 個車位 */
            gap: 10px;  /* 設定車位之間的間距 */
            justify-items: center;  /* 居中對齊每個車位 */
            margin-bottom: 20px;  /* 每層樓底部距離，縮小行距 */
        }

        /* 樓層標題格式 */
        .floor-title {
            text-align: center;
            margin-top: 80px;  /* 加大樓層標題與車位區域的間距 */
            margin-bottom: 20px;  /* 增加樓層與車位之間的間距 */
            font-size: 1.5em;
        }

        /* 使用者資訊區塊 */
        .user-info {
            text-align: center;
            margin-bottom: 20px;
        }

        /* 預約按鈕 */
        .btn-reserve {
            width: 100%;
        }

        /* 響應式設計：小螢幕顯示 5 個車位 */
        @media (max-width: 768px) {
            .floor-container {
                grid-template-columns: repeat(5, 1fr);  /* 小螢幕下每行顯示 5 個 */
            }
        }
    </style>
</head>
<body class="container py-4">

    <div class="d-flex justify-content-center">
        <div class="col-md-8">

            <!-- 使用者資訊 -->
            <div class="user-info">
                <h1>停車位預約系統</h1>
                <p>歡迎，<?= htmlspecialchars($user_name) ?>！</p>
                <p>繳費狀態：<?= $has_paid ? '已繳費' : '未繳費' ?></p>
                <a href="logout.php" class="btn btn-danger">登出</a>
                <a href="reservation_history.php" class="btn btn-info">查詢紀錄</a> <!-- 查詢紀錄按鈕 -->
            </div>

            <!-- 預約資訊 -->
            <?php if ($user_reservation): ?>
                <div class="alert alert-success text-center">
                    您已預約車位：第 <?= $user_reservation['floor'] ?> 樓，車位 <?= $user_reservation['slot_number'] ?>
                    <form action="cancel_reservation.php" method="POST" class="mt-2">
                        <button type="submit" class="btn btn-warning">取消預約</button>
                    </form>
                </div>
            <?php endif; ?>

            <!-- 顯示每層樓的停車位 -->
            <?php foreach ([1, 2, 3] as $floor): ?>
                <div class="floor-title">第 <?= $floor ?> 樓</div>

                <div class="floor-container">
                    <?php foreach ($slots as $slot): ?>
                        <?php if ($slot['floor'] == $floor): ?>
                            <div class="slot <?= ($slot['reserved_by'] && $slot['reserved_by'] != $user_id) ? 'reserved' : 'available' ?>">
                                <p>車位 <?= $slot['slot_number'] ?></p>
                                <?php if (!$slot['reserved_by'] && !$user_reservation): ?>
                                    <form action="reserve.php" method="POST">
                                        <input type="hidden" name="slot_id" value="<?= $slot['id'] ?>">
                                        <button type="submit" class="btn btn-primary btn-sm" <?= !$has_paid ? 'disabled' : '' ?>>
                                            預約
                                        </button>
                                    </form>
                                <?php elseif ($slot['reserved_by'] && $slot['reserved_by'] == $user_id): ?>
                                    <p>已預約</p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>

        </div>
    </div>

</body>
</html>
