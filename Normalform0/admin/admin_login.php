<?php
include '../components/connect.php';
session_start();

// Destroy any existing session to prevent fixation
session_unset();
session_destroy();
session_start();
$admin_id = null; // Initialize to avoid undefined variable
$message = []; // Initialize as empty array

if (isset($_POST['submit'])) {
    try {
        $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
        $pass = $_POST['pass'];

        // Debug: Log input
        error_log("Login attempt: username=$name, password=$pass");

        $select_admin = $conn->prepare("SELECT * FROM `admins` WHERE name = ?");
        $select_admin->execute([$name]);
        $row = $select_admin->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            // Debug: Log stored password
            error_log("Stored password: " . $row['password']);

            // Check if password is SHA-1 (legacy) or Bcrypt
            if (strlen($row['password']) === 40 && ctype_xdigit($row['password'])) {
                // Legacy SHA-1 hash
                if (sha1($pass) === $row['password']) {
                    $_SESSION['admin_id'] = $row['id'];
                    $message[] = 'Logged in with legacy password. Please update your password in Update Profile!';
                    error_log("Login successful: SHA-1 match for $name");
                    header('location:dashboard.php');
                    exit;
                } else {
                    $message[] = 'Incorrect username or password!';
                    error_log("Login failed: SHA-1 mismatch for $name");
                }
            } else {
                // Modern Bcrypt hash
                if (password_verify($pass, $row['password'])) {
                    $_SESSION['admin_id'] = $row['id'];
                    error_log("Login successful: Bcrypt match for $name");
                    header('location:dashboard.php');
                    exit;
                } else {
                    $message[] = 'Incorrect username or password!';
                    error_log("Login failed: Bcrypt mismatch for $name");
                }
            }
        } else {
            $message[] = 'Incorrect username or password!';
            error_log("Login failed: No user found for $name");
        }
    } catch (PDOException $e) {
        $message[] = 'Database error: ' . $e->getMessage();
        error_log("Login error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>
<?php
// Ensure $message is an array
if (!is_array($message)) {
    $message = is_string($message) ? [$message] : [];
}
if (!empty($message)) {
    foreach ($message as $msg) {
        echo '
        <div class="message">
            <span>' . htmlspecialchars($msg) . '</span>
            <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
        </div>
        ';
    }
}
?>

<section class="form-container">
    <form action="" method="post">
        <h3>Admin Login</h3>
        <input type="text" name="name" required placeholder="Enter your username" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
        <input type="password" name="pass" required placeholder="Enter your password" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
        <input type="submit" value="Login Now" class="btn" name="submit">
        <p>Forgot your credentials? Contact support.</p>
    </form>
</section>
</body>
</html>