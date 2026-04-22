<?php
require_once 'connection.php';

if (isLoggedIn()) {
    header("Location: index.php");
    exit();
}

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fname = trim($_POST['fname']);
    $lname = trim($_POST['lname']);
    $email = trim($_POST['email']);
    $gender = isset($_POST['gender']) ? $_POST['gender'] : 'Not Specified';
    $password = $_POST['password'];
    $cpassword = $_POST['cpassword'];

    if (!preg_match('/^[a-zA-Z ]+$/', $fname) || !preg_match('/^[a-zA-Z ]+$/', $lname)) {
        $error = "Names must contain only letters.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } elseif (strlen($password) < 5) {
        $error = "Password must be at least 5 characters.";
    } elseif ($password !== $cpassword) {
        $error = "Passwords do not match.";
    } else {
        $check = mysqli_prepare($con, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($check, "s", $email);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {
            $error = "Email already registered.";
        } else {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $stmt = mysqli_prepare($con, "INSERT INTO users (fname, lname, email, password, role) VALUES (?, ?, ?, ?, 'user')");
            mysqli_stmt_bind_param($stmt, "ssss", $fname, $lname, $email, $hashed);
            if (mysqli_stmt_execute($stmt)) {
                $success = "Account created! <a href='login.php'>Log in now</a>";
            } else {
                $error = "Registration failed. Try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up – OtakuZone</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700;900&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="auth.css">
</head>
<body>
    <div class="auth-bg">
        <div class="auth-card wide">
            <a href="index.php" class="brand">⛩ OtakuZone</a>
            <h2>Join the Zone</h2>
            <p class="sub">Your anime journey starts here</p>
            <?php if ($error): ?>
                <div class="error-msg"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="success-msg"><?= $success ?></div>
            <?php endif; ?>
            <form method="post" id="signupForm">
                <div class="row2">
                    <div class="field">
                        <label>First Name</label>
                        <input type="text" name="fname" id="fname" required placeholder="Naruto">
                        <span class="err" id="efname"></span>
                    </div>
                    <div class="field">
                        <label>Last Name</label>
                        <input type="text" name="lname" id="lname" required placeholder="Uzumaki">
                        <span class="err" id="elname"></span>
                    </div>
                </div>
                <div class="field">
                    <label>Email</label>
                    <input type="email" name="email" id="email" required placeholder="you@example.com">
                    <span class="err" id="eemail"></span>
                </div>
                <div class="field">
                    <label>Gender</label>
                    <div class="radio-group">
                        <label><input type="radio" name="gender" value="Male"> Male</label>
                        <label><input type="radio" name="gender" value="Female"> Female</label>
                        <label><input type="radio" name="gender" value="Other"> Other</label>
                    </div>
                    <span class="err" id="egender"></span>
                </div>
                <div class="field">
                    <label>Password</label>
                    <input type="password" name="password" id="pass" required placeholder="••••••••">
                    <div class="pass-hints" id="passHints"></div>
                </div>
                <div class="field">
                    <label>Confirm Password</label>
                    <input type="password" name="cpassword" id="cpass" required placeholder="••••••••">
                    <span class="err" id="ecpass"></span>
                </div>
                <button type="submit" id="sub" class="btn-primary">Create Account</button>
            </form>
            <p class="switch">Already have an account? <a href="login.php">Log in</a></p>
        </div>
    </div>
    <script src="auth.js"></script>
</body>
</html>
