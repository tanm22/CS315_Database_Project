<?php
include 'components/connect.php';
session_start();

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = '';
    header('location:user_login.php');
    exit;
}

$message = []; // Initialize $message

$select_profile = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
$select_profile->execute([$user_id]);
$fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);

if (isset($_POST['submit'])) {
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_STRING);

    $update_profile = $conn->prepare("UPDATE `users` SET name = ?, email = ? WHERE id = ?");
    $update_profile->execute([$name, $email, $user_id]);

    $empty_pass = 'da39a3ee5e6b4b0d3255bfef95601890afd80709';
    $prev_pass = $_POST['prev_pass'];
    $old_pass = sha1($_POST['old_pass']);
    $old_pass = filter_var($old_pass, FILTER_SANITIZE_STRING);
    $new_pass = sha1($_POST['new_pass']);
    $new_pass = filter_var($new_pass, FILTER_SANITIZE_STRING);
    $cpass = sha1($_POST['cpass']);
    $cpass = filter_var($cpass, FILTER_SANITIZE_STRING);

    if ($old_pass == $empty_pass) {
        $message[] = 'Please enter old password!';
    } elseif ($old_pass != $prev_pass) {
        $message[] = 'Old password not matched!';
    } elseif ($new_pass != $cpass) {
        $message[] = 'Confirm password not matched!';
    } else {
        if ($new_pass != $empty_pass) {
            $update_admin_pass = $conn->prepare("UPDATE `users` SET password = ? WHERE id = ?");
            $update_admin_pass->execute([$cpass, $user_id]);
            $message[] = 'Password updated successfully!';
        } else {
            $message[] = 'Please enter a new password!';
        }
    }
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
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'components/user_header.php'; ?>

<section class="form-container">
    <form action="" method="post">
        <h3>Update Now</h3>
        <?php
        if (isset($message)) {
            foreach ($message as $msg) {
                echo '<div class="message"><span>' . htmlspecialchars($msg) . '</span><i class="fas fa-times" onclick="this.parentElement.remove();"></i></div>';
            }
        }
        ?>
        <input type="hidden" name="prev_pass" value="<?= $fetch_profile['password']; ?>">
        <input type="text" name="name" required placeholder="Enter your username" maxlength="20" class="box" value="<?= $fetch_profile['name']; ?>">
        <input type="email" name="email" required placeholder="Enter your email" maxlength="50" class="box" oninput="this.value = this.value.replace(/\s/g, '')" value="<?= $fetch_profile['email']; ?>">
        <input type="password" name="old_pass" placeholder="Enter your old password" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
        <input type="password" name="new_pass" placeholder="Enter your new password" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
        <input type="password" name="cpass" placeholder="Confirm your new password" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
        <input type="submit" value="Update Now" class="btn" name="submit">
    </form>
</section>

<?php include 'components/footer.php'; ?>
<script src="js/script.js"></script>
</body>
</html>