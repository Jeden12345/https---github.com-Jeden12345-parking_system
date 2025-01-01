<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $student_id = $_POST['student_id'];
    $has_paid = isset($_POST['has_paid']) ? 1 : 0;

    // 檢查學號是否已經註冊
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE student_id = ?");
    $stmt->execute([$student_id]);
    if ($stmt->fetchColumn() > 0) {
        $error = "該學號已被註冊，請使用其他學號。";
    } else {
        // 新增使用者到資料庫
        $stmt = $pdo->prepare("INSERT INTO users (name, student_id, has_paid) VALUES (?, ?, ?)");
        $stmt->execute([$name, $student_id, $has_paid]);
        header("Location: login.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>註冊 - 停車位預約系統</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-4">
    <h1 class="text-center">註冊</h1>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    <form method="POST" class="mt-4">
        <div class="mb-3">
            <label for="name" class="form-label">姓名</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="mb-3">
            <label for="student_id" class="form-label">學號</label>
            <input type="text" class="form-control" id="student_id" name="student_id" required>
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="has_paid" name="has_paid">
            <label for="has_paid" class="form-check-label">已繳費</label>
        </div>
        <button type="submit" class="btn btn-primary">註冊</button>
    </form>
    <div class="mt-3">
        已有帳號？<a href="login.php">點此登入</a>
    </div>
</body>
</html>
