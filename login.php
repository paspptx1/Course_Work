<?php
session_start(); // Start the session at the beginning

// Include config.php file using the correct path
include 'config.php'; 

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect username and password from the POST request
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare SQL statement to fetch user data by username
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // Verify password if user exists
    if ($user && password_verify($password, $user['password'])) {
        // Store user ID in session for future access
        $_SESSION['user_id'] = $user['id'];

        // Redirect to dashboard
        header("Location: dashboard.php");
        exit(); // Ensure the script stops executing here
    } else {
        // Show error if credentials are invalid
        $error = "Invalid credentials!";
    }
}
?>

<!-- HTML Form for login -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="sty.css"> <!-- Link to your styles.css file -->
</head>
<body>
    <div class="container">
        <h2>Login</h2>

        <!-- Display error message if credentials are invalid -->
        <?php if (isset($error)) : ?>
            <p style="color: red;"><?php echo $error; ?></p>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</body>
</html>
