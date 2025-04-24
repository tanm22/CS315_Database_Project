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
        $delete_message = $conn->prepare("DELETE FROM `messages` WHERE id = ?");
        if ($delete_message->execute([$delete_id])) {
            $message[] = 'Message deleted successfully!';
        } else {
            $message[] = 'Failed to delete message!';
        }
    } catch (PDOException $e) {
        $message[] = 'Database error: ' . $e->getMessage();
    }
    header('location:messages.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>
<?php include '../components/admin_header.php'; ?>

<section class="contacts">
    <h1 class="heading">Messages</h1>
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
            $select_messages = $conn->prepare("
                SELECT m.id, m.user_id, m.email AS message_email, m.message, u.name, uc.number
                FROM `messages` m
                JOIN `users` u ON m.user_id = u.id
                LEFT JOIN `users_contact` uc ON u.email = uc.email
            ");
            $select_messages->execute();
            if ($select_messages->rowCount() > 0) {
                while ($fetch_message = $select_messages->fetch(PDO::FETCH_ASSOC)) {
    ?>
    <div class="box">
        <p> User id : <span><?= htmlspecialchars($fetch_message['user_id']); ?></span></p>
        <p> Name : <span><?= htmlspecialchars($fetch_message['name']); ?></span></p>
        <p> Email : <span><?= htmlspecialchars($fetch_message['message_email']); ?></span></p>
        <p> Number : <span><?= htmlspecialchars($fetch_message['number'] ?? 'N/A'); ?></span></p>
        <p> Message : <span><?= htmlspecialchars($fetch_message['message']); ?></span></p>
        <a href="messages.php?delete=<?= $fetch_message['id']; ?>" onclick="return confirm('Delete this message?');" class="delete-btn">Delete</a>
    </div>
    <?php
                }
            } else {
                echo '<p class="empty">You have no messages</p>';
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