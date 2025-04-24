<?php
include 'components/connect.php';
session_start();
session_regenerate_id(true);

if (isset($_SESSION['user_id'])) {
    $user_id = filter_var($_SESSION['user_id'], FILTER_SANITIZE_NUMBER_INT);
    $check_user = $conn->prepare("SELECT id FROM `users` WHERE id = ?");
    $check_user->execute([$user_id]);
    if ($check_user->rowCount() == 0) {
        unset($_SESSION['user_id']);
        header('location:user_login.php');
        exit;
    }
} else {
    $user_id = '';
    header('location:user_login.php');
    exit;
}

$message = isset($_SESSION['message']) ? $_SESSION['message'] : [];
unset($_SESSION['message']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'components/user_header.php'; ?>

<section class="orders">
    <h1 class="heading">Placed Orders</h1>
    <?php
    if (!empty($message)) {
        foreach ($message as $msg) {
            echo '<div class="message"><span>' . htmlspecialchars($msg) . '</span><i class="fas fa-times" onclick="this.parentElement.remove();"></i></div>';
        }
    }
    ?>
    <div class="box-container">
    <?php
    try {
        $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE user_id = ?");
        $select_orders->execute([$user_id]);
        if ($select_orders->rowCount() > 0) {
            while ($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)) {
                $select_items = $conn->prepare("
                    SELECT oi.quantity, p.name 
                    FROM `order_items` oi 
                    JOIN `products` p ON oi.product_id = p.id 
                    WHERE oi.order_id = ?
                ");
                $select_items->execute([$fetch_orders['id']]);
                $order_items = [];
                while ($fetch_item = $select_items->fetch(PDO::FETCH_ASSOC)) {
                    $order_items[] = htmlspecialchars($fetch_item['name']) . ' (' . $fetch_item['quantity'] . ')';
                }
                $items_display = implode(', ', $order_items);
    ?>
    <div class="box">
        <p>Placed on: <span><?= htmlspecialchars($fetch_orders['placed_on']); ?></span></p>
        <p>Name: <span><?= htmlspecialchars($fetch_orders['name']); ?></span></p>
        <p>Email: <span><?= htmlspecialchars($fetch_orders['email']); ?></span></p>
        <p>Phone Number: <span><?= htmlspecialchars($fetch_orders['number']); ?></span></p>
        <p>Address: <span><?= htmlspecialchars($fetch_orders['address']); ?></span></p>
        <p>Payment Method: <span><?= htmlspecialchars($fetch_orders['method']); ?></span></p>
        <p>Your Orders: <span><?= $items_display ?: 'No items'; ?></span></p>
        <p>Total Price: <span>INR<?= $fetch_orders['total_price']; ?>/-</span></p>
        <p>Payment Status: <span style="color:<?php echo ($fetch_orders['payment_status'] == 'pending') ? 'var(--red)' : 'green'; ?>"><?= htmlspecialchars($fetch_orders['payment_status']); ?></span></p>
    </div>
    <?php
            }
        } else {
            echo '<p class="empty">No orders placed yet!</p>';
        }
    } catch (PDOException $e) {
        $message[] = 'Error fetching orders: ' . htmlspecialchars($e->getMessage());
        $_SESSION['message'] = $message;
    }
    ?>
    </div>
</section>

<?php include 'components/footer.php'; ?>
<script src="js/script.js"></script>
</body>
</html>