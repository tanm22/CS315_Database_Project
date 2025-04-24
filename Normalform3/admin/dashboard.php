<?php
include '../components/connect.php';
session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('location:admin_login.php');
    exit;
}

$message = []; // Initialize as empty array
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
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>
<?php include '../components/admin_header.php'; ?>

<section class="dashboard">
    <h1 class="heading">Dashboard</h1>
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
            <h3>Welcome!</h3>
            <p><?= htmlspecialchars($fetch_profile['name']); ?></p>
            <a href="update_profile.php" class="btn">Update Profile</a>
        </div>
        <div class="box">
            <?php
                $total_pendings = 0;
                try {
                    $select_pendings = $conn->prepare("SELECT * FROM `orders` WHERE payment_status = ?");
                    $select_pendings->execute(['pending']);
                    if ($select_pendings->rowCount() > 0) {
                        while ($fetch_pendings = $select_pendings->fetch(PDO::FETCH_ASSOC)) {
                            $total_pendings += $fetch_pendings['total_price'];
                        }
                    }
                } catch (PDOException $e) {
                    $message[] = 'Database error: ' . $e->getMessage();
                }
            ?>
            <h3><span>INR</span><?= htmlspecialchars($total_pendings); ?><span>/-</span></h3>
            <p>Total Pendings</p>
            <a href="placed_orders.php" class="btn">See Orders</a>
        </div>
        <div class="box">
            <?php
                $total_completes = 0;
                try {
                    $select_completes = $conn->prepare("SELECT * FROM `orders` WHERE payment_status = ?");
                    $select_completes->execute(['completed']);
                    if ($select_completes->rowCount() > 0) {
                        while ($fetch_completes = $select_completes->fetch(PDO::FETCH_ASSOC)) {
                            $total_completes += $fetch_completes['total_price'];
                        }
                    }
                } catch (PDOException $e) {
                    $message[] = 'Database error: ' . $e->getMessage();
                }
            ?>
            <h3><span>INR</span><?= htmlspecialchars($total_completes); ?><span>/-</span></h3>
            <p>Completed Orders</p>
            <a href="placed_orders.php" class="btn">See Orders</a>
        </div>
        <div class="box">
            <?php
                try {
                    $select_orders = $conn->prepare("SELECT * FROM `orders`");
                    $select_orders->execute();
                    $number_of_orders = $select_orders->rowCount();
                } catch (PDOException $e) {
                    $message[] = 'Database error: ' . $e->getMessage();
                }
            ?>
            <h3><?= htmlspecialchars($number_of_orders); ?></h3>
            <p>Orders Placed</p>
            <a href="placed_orders.php" class="btn">See Orders</a>
        </div>
        <div class="box">
            <?php
                try {
                    $select_products = $conn->prepare("SELECT * FROM `products`");
                    $select_products->execute();
                    $number_of_products = $select_products->rowCount();
                } catch (PDOException $e) {
                    $message[] = 'Database error: ' . $e->getMessage();
                }
            ?>
            <h3><?= htmlspecialchars($number_of_products); ?></h3>
            <p>Products Added</p>
            <a href="products.php" class="btn">See Products</a>
        </div>
        <div class="box">
            <?php
                try {
                    $select_users = $conn->prepare("SELECT * FROM `users`");
                    $select_users->execute();
                    $number_of_users = $select_users->rowCount();
                } catch (PDOException $e) {
                    $message[] = 'Database error: ' . $e->getMessage();
                }
            ?>
            <h3><?= htmlspecialchars($number_of_users); ?></h3>
            <p>Normal Users</p>
            <a href="users_accounts.php" class="btn">See Users</a>
        </div>
        <div class="box">
            <?php
                try {
                    $select_admins = $conn->prepare("SELECT * FROM `admins`");
                    $select_admins->execute();
                    $number_of_admins = $select_admins->rowCount();
                } catch (PDOException $e) {
                    $message[] = 'Database error: ' . $e->getMessage();
                }
            ?>
            <h3><?= htmlspecialchars($number_of_admins); ?></h3>
            <p>Admin Users</p>
            <a href="admin_accounts.php" class="btn">See Admins</a>
        </div>
        <div class="box">
            <?php
                try {
                    $select_messages = $conn->prepare("SELECT * FROM `messages`");
                    $select_messages->execute();
                    $number_of_messages = $select_messages->rowCount();
                } catch (PDOException $e) {
                    $message[] = 'Database error: ' . $e->getMessage();
                }
            ?>
            <h3><?= htmlspecialchars($number_of_messages); ?></h3>
            <p>New Messages</p>
            <a href="messages.php" class="btn">See Messages</a>
        </div>
    </div>
</section>
<script src="../js/admin_script.js"></script>
</body>
</html>