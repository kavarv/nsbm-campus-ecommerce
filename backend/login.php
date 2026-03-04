<?php
session_start();
include 'config/db.php';

$message = '';

// Redirect logged-in users away from login page
if(isset($_SESSION['user_id'])) {
    if($_SESSION['role'] === 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: index.php");
    }
    exit;
}

// Handle login
if(isset($_POST['login'])) {

    $email = strtolower(trim($_POST['email']));
    $password = trim($_POST['password']);

    if($email && $password) {

        // Prepare statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT id, name, role, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($id, $name, $role, $db_password);

        if($stmt->num_rows > 0) {
            $stmt->fetch();

            // For now: plain text password check
            if(password_verify($password, $db_password)) {

                // Secure session
                session_regenerate_id(true);

                $_SESSION['user_id'] = $id;
                $_SESSION['user_name'] = $name;
                $_SESSION['role'] = strtolower($role);

                // Redirect based on role
                if($_SESSION['role'] === 'admin'){
                    header("Location: admin/dashboard.php");
                } else {
                    header("Location: index.php");
                }
                exit;

            } else {
                $message = "Incorrect password!";
            }

        } else {
            $message = "Email not found!";
        }

        $stmt->close();

    } else {
        $message = "Please enter email and password!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - NSBM Store</title>
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>

<main class="login-page">
    <h2>Login</h2>

    <?php if($message){ ?>
        <p class="error"><?php echo htmlspecialchars($message); ?></p>
    <?php } ?>

    <form method="POST" class="login-form">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="login" class="btn">Login</button>
        <p>Don't have an account? <a href="register.php">Register Here</a></p>
    </form>
</main>

</body>
</html>