<?php
require_once 'connection.php';

if (isLoggedIn()) {
    header("Location: index.php");
    exit();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = mysqli_prepare($con, "SELECT * FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['fname'] = $user['fname'];
        $_SESSION['role'] = $user['role'];
        header("Location: index.php");
        exit();
    } else {
        $error = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login &ndash; OtakuZone</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700;900&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="auth.css">
</head>
<body>
    <div class="auth-bg">
        <div class="auth-card">
            <a href="index.php" class="brand">&#x26E9; OtakuZone</a>
            <h2>Welcome Back</h2>
            <p class="sub">Continue your journey</p>
            <?php if ($error): ?>
                <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="post">
                <div class="field">
                    <label>Email</label>
                    <input type="email" name="email" required placeholder="you@example.com">
                </div>
                <div class="field">
                    <label>Password</label>
                    <input type="password" name="password" required placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;">
                </div>
                <button type="submit" class="btn-primary">Log In</button>
            </form>
            <p class="switch">New here? <a href="signup.php">Create account</a></p>
        </div>
    </div>
</body>
</html>
