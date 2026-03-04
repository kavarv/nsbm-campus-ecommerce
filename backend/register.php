<?php
session_start();
include 'config/db.php';

$message = '';
$success = '';

// Redirect if already logged in
if(isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Handle register
if(isset($_POST['register'])) {

    $name = trim($_POST['name']);
    $email = strtolower(trim($_POST['email']));
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validation
    if(empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $message = "All fields are required!";
    }
    elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format!";
    }
    elseif(strlen($password) < 6) {
        $message = "Password must be at least 6 characters!";
    }
    elseif($password !== $confirm_password) {
        $message = "Passwords do not match!";
    }
    else {

        // Check if email exists
        $check = $conn->prepare("SELECT id FROM users WHERE email=?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if($check->num_rows > 0) {
            $message = "Email already registered!";
        }
        else {

            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert user (default role = customer)
            $stmt = $conn->prepare("
                INSERT INTO users (name, email, password, role) 
                VALUES (?, ?, ?, 'customer')
            ");

            $stmt->bind_param("sss", $name, $email, $hashed_password);

            if($stmt->execute()) {
                $success = "Registration successful! You can login now.";
            } else {
                $message = "Registration failed!";
            }

            $stmt->close();
        }

        $check->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - NSBM Store</title>
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>

<main class="login-page">

    <h2>Register</h2>

    <?php if($message){ ?>
        <p class="error"><?php echo htmlspecialchars($message); ?></p>
    <?php } ?>

    <?php if($success){ ?>
        <p class="success"><?php echo htmlspecialchars($success); ?></p>
    <?php } ?>

    <form method="POST" class="login-form">

        <input type="text" name="name" placeholder="Full Name" required>

        <input type="email" name="email" placeholder="Email" required>

        <input type="password" name="password" placeholder="Password" required>

        <input type="password" name="confirm_password" placeholder="Confirm Password" required>

        <button type="submit" name="register" class="btn">
            Register
        </button>

        <p>
            Already have an account? 
            <a href="login.php">Login Here</a>
        </p>

    </form>

</main>

</body>
</html>
