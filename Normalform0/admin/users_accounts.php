<?php
include '../components/connect.php';
session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('location:admin_login.php');
    exit;
}

$message = []; // Initialize as empty array

if (isset($_GET['delete'])) {
    try {
        $delete_id = $_GET['delete'];
        $delete_order_items = $conn->prepare("UPDATE `orders` SET total_products = '' WHERE user_id = ?");
        $delete_order_items->execute([$delete_id]);
        $delete_orders = $conn->prepare("DELETE FROM `orders` WHERE user_id = ?");
        $delete_orders->execute([$delete_id]);
        $delete_messages = $conn->prepare("DELETE FROM `messages` WHERE user_id = ?");
        $delete_messages->execute([$delete_id]);
        $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
        $delete_cart->execute([$delete_id]);
        $delete_wishlist = $conn->prepare("DELETE FROM `wishlist` WHERE user_id = ?");
        $delete_wishlist->execute([$delete_id]);
        $delete_user = $conn->prepare("DELETE FROM `users` WHERE id = ?");
        if ($delete_user->execute([$delete_id])) {
            $message[] = 'User account and related data deleted successfully!';
        } else {
            $message[] = 'Failed to delete user!';
        }
    } catch (PDOException $e) {
        $message[] = 'Database error: ' . $e->getMessage();
    }
    header('location:users_accounts.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Accounts</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>
<?php include '../components/admin_header.php'; ?>

<section class="accounts">
    <h1 class="heading">User Accounts</h1>
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
    <div class="box-container">
    <?php
        try {
            $select_accounts = $conn->prepare("SELECT * FROM `users`");
            $select_accounts->execute();
            if ($select_accounts->rowCount() > 0) {
                while ($fetch_accounts = $select_accounts->fetch(PDO::FETCH_ASSOC)) {
    ?>
    <div class="box">
        <p> User id : <span><?= htmlspecialchars($fetch_accounts['id']); ?></span> </p>
        <p> Username : <span><?= htmlspecialchars($fetch_accounts['name']); ?></span> </p>
        <p> Email : <span><?= htmlspecialchars($fetch_accounts['email']); ?></span> </p>
        <a href="users_accounts.php?delete=<?= htmlspecialchars($fetch_accounts['id']); ?>" onclick="return confirm('Delete this account? All related user information will also be deleted!')" class="delete-btn">Delete</a>
    </div>
    <?php
                }
            } else {
                echo '<p class="empty">No accounts available!</p>';
            }
        } catch (PDOException $e) {
            echo '<p class="empty">Database error: ' . $e->getMessage() . '</p>';
        }
    ?>
    </div>
</section>
<script src="../js/admin_script.js"></script>
</body>
</html>