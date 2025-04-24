<?php
include '../components/connect.php';
session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('location:admin_login.php');
    exit;
}

$message = []; // Initialize as empty array

if (isset($_POST['submit'])) {
    try {
        $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);

        $update_profile_name = $conn->prepare("UPDATE `admins` SET name = ? WHERE id = ?");
        $update_profile_name->execute([$name, $admin_id]);

        $old_pass = $_POST['old_pass'];
        $new_pass = $_POST['new_pass'];
        $confirm_pass = $_POST['confirm_pass'];

        if (!empty($old_pass)) {
            $select_admin = $conn->prepare("SELECT password FROM `admins` WHERE id = ?");
            $select_admin->execute([$admin_id]);
            $fetch_admin = $select_admin->fetch(PDO::FETCH_ASSOC);

            $password_matched = false;
            // Check if stored password is SHA-1 or bcrypt
            if (strlen($fetch_admin['password']) === 40 && ctype_xdigit($fetch_admin['password'])) {
                if (sha1($old_pass) === $fetch_admin['password']) {
                    $password_matched = true;
                }
            } elseif (password_verify($old_pass, $fetch_admin['password'])) {
                $password_matched = true;
            }

            if (!$password_matched) {
                $message[] = 'Old password not matched!';
            } elseif ($new_pass !== $confirm_pass) {
                $message[] = 'Confirm password not matched!';
            } elseif (empty($new_pass)) {
                $message[] = 'Please enter a new password!';
            } else {
                $hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);
                $update_admin_pass = $conn->prepare("UPDATE `admins` SET password = ? WHERE id = ?");
                $update_admin_pass->execute([$hashed_pass, $admin_id]);
                $message[] = 'Password updated successfully!';
            }
        }
    } catch (PDOException $e) {
        $message[] = 'Database error: ' . $e->getMessage();
    }
}

// Fetch current admin profile
try {
    $select_profile = $conn->prepare("SELECT * FROM `admins` WHERE id = ?");
    $select_profile->execute([$admin_id]);
    $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message[] = 'Database error: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>
<?php include '../components/admin_header.php'; ?>

<section class="form-container">
    <?php
    // Ensure $message is an array
    if (!is_array($message)) {
        $message = is_string($message) ? [$message] : [];
    }
    if (!empty($message)) {
        foreach ($message as $msg) {
            echo '<div class="message"><span>' . htmlspecialchars($msg) . '</span><i class="fas fa-times" onclick="this.parentElement.remove();"></i></div>';
        }
    }
    ?>
    <form action="" method="post">
        <h3>Update Profile</h3>
        <input type="text" name="name" value="<?= htmlspecialchars($fetch_profile['name']); ?>" required placeholder="Enter your username" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
        <input type="password" name="old_pass" placeholder="Enter old password" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
        <input type="password" name="new_pass" placeholder="Enter new password" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
        <input type="password" name="confirm_pass" placeholder="Confirm new password" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
        <input type="submit" value="Update Now" class="btn" name="submit">
    </form>
</section>
<script src="../js/admin_script.js"></script>
</body>
</html>