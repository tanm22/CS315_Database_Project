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
        if ($delete_id != $admin_id) { // Prevent self-deletion
            $delete_admins = $conn->prepare("DELETE FROM `admins` WHERE id = ?");
            if ($delete_admins->execute([$delete_id])) {
                $message[] = 'Admin account deleted successfully!';
            } else {
                $message[] = 'Failed to delete admin!';
            }
        } else {
            $message[] = 'Cannot delete your own account!';
        }
    } catch (PDOException $e) {
        $message[] = 'Database error: ' . $e->getMessage();
    }
    header('location:admin_accounts.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Accounts</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>
<?php include '../components/admin_header.php'; ?>

<section class="accounts">
    <h1 class="heading">Admin Accounts</h1>
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
        <div class="box">
            <p>Add New Admin</p>
            <a href="register_admin.php" class="option-btn">Register Admin</a>
        </div>
        <?php
            try {
                $select_accounts = $conn->prepare("SELECT * FROM `admins`");
                $select_accounts->execute();
                if ($select_accounts->rowCount() > 0) {
                    while ($fetch_accounts = $select_accounts->fetch(PDO::FETCH_ASSOC)) {
        ?>
        <div class="box">
            <p> Admin Id : <span><?= htmlspecialchars($fetch_accounts['id']); ?></span> </p>
            <p> Admin Name : <span><?= htmlspecialchars($fetch_accounts['name']); ?></span> </p>
            <div class="flex-btn">
                <a href="admin_accounts.php?delete=<?= $fetch_accounts['id']; ?>" onclick="return confirm('Delete this account?')" class="delete-btn">Delete</a>
                <?php
                    if ($fetch_accounts['id'] == $admin_id) {
                        echo '<a href="update_profile.php" class="option-btn">Update</a>';
                    }
                ?>
            </div>
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