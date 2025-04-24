<?php
include '../components/connect.php';
session_start();

$admin_id = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : null;

if (!isset($admin_id)) {
    header('location:admin_login.php');
    exit;
}

$message = []; // Initialize as empty array

if (isset($_POST['submit'])) {
    try {
        $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
        $pass = $_POST['pass'];
        $cpass = $_POST['cpass'];

        // Validate password
        if (strlen($pass) < 8 || !preg_match('/[A-Za-z]/', $pass) || !preg_match('/[0-9]/', $pass)) {
            $message[] = 'Password must be at least 8 characters and contain letters and numbers!';
        } elseif ($pass !== $cpass) {
            $message[] = 'Confirm password not matched!';
        } else {
            // Check for existing admin
            $check_admin = $conn->prepare("SELECT * FROM `admins` WHERE name = ?");
            $check_admin->execute([$name]);
            if ($check_admin->rowCount() > 0) {
                $message[] = 'Username already exists!';
            } else {
                // Hash password and insert new admin
                $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);
                $insert_admin = $conn->prepare("INSERT INTO `admins`(name, password) VALUES(?,?)");
                if ($insert_admin->execute([$name, $hashed_pass])) {
                    $message[] = 'New admin registered successfully!';
                } else {
                    $message[] = 'Failed to register admin!';
                }
            }
        }
    } catch (PDOException $e) {
        $message[] = 'Database error: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>
<?php include '../components/admin_header.php'; ?>

<section class="form-container">
    <form action="" method="post">
        <h3>Register New Admin</h3>
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
        <input type="text" name="name" required placeholder="Enter username" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
        <input type="password" name="pass" required placeholder="Enter password" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
        <input type="password" name="cpass" required placeholder="Confirm password" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
        <input type="submit" value="Register Now" class="btn" name="submit">
    </form>
</section>
<script src="../js/admin_script.js"></script>
</body>
</html>