<?php
include '../components/connect.php';
session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('location:admin_login.php');
    exit;
}

$message = []; // Initialize as empty array

if (isset($_POST['update_payment'])) {
    try {
        $order_id = filter_var($_POST['order_id'], FILTER_SANITIZE_NUMBER_INT);
        $payment_status = filter_var($_POST['payment_status'], FILTER_SANITIZE_STRING);
        if ($order_id <= 0) {
            $message[] = 'Invalid order ID!';
        } else {
            $update_payment = $conn->prepare("UPDATE `orders` SET payment_status = ? WHERE id = ?");
            if ($update_payment->execute([$payment_status, $order_id])) {
                $message[] = 'Payment status updated!';
            } else {
                $message[] = 'Failed to update payment status!';
                error_log("Failed to update payment status for order ID: $order_id");
            }
        }
    } catch (PDOException $e) {
        $message[] = 'Database error: Contact support.';
        error_log("Update payment error for order ID $order_id: " . $e->getMessage());
    }
}

if (isset($_GET['delete'])) {
    try {
        $delete_id = filter_var($_GET['delete'], FILTER_SANITIZE_NUMBER_INT);
        if ($delete_id <= 0) {
            $message[] = 'Invalid order ID!';
        } else {
            // Check if order exists
            $check_order = $conn->prepare("SELECT id FROM `orders` WHERE id = ?");
            $check_order->execute([$delete_id]);
            if ($check_order->rowCount() === 0) {
                $message[] = 'Order not found!';
            } else {
                $total_products = $fetch_orders['total_products']; // Fetch the total_products column for reference

                // Delete order
                $delete_order = $conn->prepare("DELETE FROM `orders` WHERE id = ?");
                if ($delete_order->execute([$delete_id])) {
                    $message[] = 'Order and related items deleted successfully!';
                } else {
                    $message[] = 'Failed to delete order! Contact support.';
                    error_log("Failed to delete order ID: $delete_id");
                }
            }
        }
    } catch (PDOException $e) {
        $message[] = 'Database error: Contact support.';
        error_log("Delete order error for ID $delete_id: " . $e->getMessage());
    }
    header('location:placed_orders.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Placed Orders</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>
<?php include '../components/admin_header.php'; ?>

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
          $select_orders = $conn->prepare("SELECT * FROM `orders`");
          $select_orders->execute();
          if ($select_orders->rowCount() > 0) {
              while ($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)) {
                  $total_products = $fetch_orders['total_products'];
   ?>
   <div class="box">
      <p> Placed On : <span><?= htmlspecialchars($fetch_orders['placed_on']); ?></span> </p>
      <p> Name : <span><?= htmlspecialchars($fetch_orders['name']); ?></span> </p>
      <p> Number : <span><?= htmlspecialchars($fetch_orders['number']); ?></span> </p>
      <p> Address : <span><?= htmlspecialchars($fetch_orders['address']); ?></span> </p>
      <p> Total products : <span><?= htmlspecialchars($total_products); ?></span> </p>
      <p> Total price : <span>Nrs.<?= htmlspecialchars($fetch_orders['total_price']); ?>/-</span> </p>
      <p> Payment method : <span><?= htmlspecialchars($fetch_orders['method']); ?></span> </p>
      <form action="" method="post">
         <input type="hidden" name="order_id" value="<?= $fetch_orders['id']; ?>">
         <select name="payment_status" class="select">
            <option selected disabled><?= htmlspecialchars($fetch_orders['payment_status']); ?></option>
            <option value="pending">Pending</option>
            <option value="completed">Completed</option>
         </select>
         <div class="flex-btn">
            <input type="submit" value="update" class="option-btn" name="update_payment">
            <a href="placed_orders.php?delete=<?= $fetch_orders['id']; ?>" class="delete-btn" onclick="return confirm('delete this order?');">delete</a>
         </div>
      </form>
   </div>
   <?php
              }
          } else {
              echo '<p class="empty">No orders placed yet!</p>';
          }
      } catch (PDOException $e) {
          echo '<p class="empty">Database error: Contact support.</p>';
          error_log("Select orders error: " . $e->getMessage());
      }
   ?>
   </div>
</section>
<script src="../js/admin_script.js"></script>
</body>
</html>