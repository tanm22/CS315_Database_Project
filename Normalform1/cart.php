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

$message = [];

if (isset($_POST['delete'])) {
   try {
      $pid = filter_var($_POST['pid'], FILTER_SANITIZE_NUMBER_INT);
      if ($pid <= 0) {
         $message[] = 'Invalid product ID!';
      } else {
         $delete_cart_item = $conn->prepare("DELETE FROM `cart` WHERE user_id = ? AND pid = ?");
         $delete_cart_item->execute([$user_id, $pid]);
         $message[] = 'Item deleted from cart!';
      }
   } catch (PDOException $e) {
      $message[] = 'Error deleting item: ' . htmlspecialchars($e->getMessage());
   }
}

if (isset($_GET['delete_all'])) {
   try {
      $delete_cart_item = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
      $delete_cart_item->execute([$user_id]);
      $message[] = 'All items deleted from cart!';
      header('location:cart.php');
      exit;
   } catch (PDOException $e) {
      $message[] = 'Error deleting all items: ' . htmlspecialchars($e->getMessage());
   }
}

if (isset($_POST['update_qty'])) {
   try {
      $pid = filter_var($_POST['pid'], FILTER_SANITIZE_NUMBER_INT);
      $qty = filter_var($_POST['qty'], FILTER_SANITIZE_NUMBER_INT);
      if ($pid <= 0 || $qty <= 0 || $qty > 99) {
         $message[] = 'Invalid product ID or quantity!';
      } else {
         $update_qty = $conn->prepare("UPDATE `cart` SET quantity = ? WHERE user_id = ? AND pid = ?");
         $update_qty->execute([$qty, $user_id, $pid]);
         $message[] = 'Cart quantity updated!';
      }
   } catch (PDOException $e) {
      $message[] = 'Error updating quantity: ' . htmlspecialchars($e->getMessage());
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Shopping Cart</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'components/user_header.php'; ?>

<section class="products shopping-cart">
   <h3 class="heading">Shopping Cart</h3>
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
      $grand_total = 0;
      $select_cart = $conn->prepare("SELECT user_id, pid, quantity, name, price, (SELECT image_01 FROM `products` WHERE id = pid) AS image 
                                     FROM `cart` WHERE user_id = ?");
      $select_cart->execute([$user_id]);
      if ($select_cart->rowCount() > 0) {
         while ($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)) {
   ?>
   <form action="" method="post" class="box">
      <input type="hidden" name="pid" value="<?= $fetch_cart['pid']; ?>">
      <a href="quick_view.php?pid=<?= $fetch_cart['pid']; ?>" class="fas fa-eye"></a>
      <img src="uploaded_img/<?= htmlspecialchars($fetch_cart['image']); ?>" alt="">
      <div class="name"><?= htmlspecialchars($fetch_cart['name']); ?></div>
      <div class="flex">
         <div class="price">INR<?= $fetch_cart['price']; ?>/-</div>
         <input type="number" name="qty" class="qty" min="1" max="99" onkeypress="if(this.value.length == 2) return false;" value="<?= $fetch_cart['quantity']; ?>">
         <button type="submit" class="fas fa-edit" name="update_qty"></button>
      </div>
      <div class="sub-total">Sub Total: <span>INR<?= $sub_total = ($fetch_cart['price'] * $fetch_cart['quantity']); ?>/-</span></div>
      <input type="submit" value="Delete Item" onclick="return confirm('Delete this from cart?');" class="delete-btn" name="delete">
   </form>
   <?php
            $grand_total += $sub_total;
         }
      } else {
         echo '<p class="empty">Your cart is empty</p>';
      }
   } catch (PDOException $e) {
      echo '<p class="empty">Error fetching cart: ' . htmlspecialchars($e->getMessage()) . '</p>';
   }
   ?>
   </div>
   <div class="cart-total">
      <p>Grand Total: <span>INR<?= $grand_total; ?>/-</span></p>
      <a href="shop.php" class="option-btn">Continue Shopping</a>
      <a href="cart.php?delete_all" class="delete-btn <?= ($grand_total > 0) ? '' : 'disabled'; ?>" onclick="return confirm('Delete all from cart?');">Delete All Items</a>
      <a href="checkout.php" class="btn <?= ($grand_total > 0) ? '' : 'disabled'; ?>">Proceed to Checkout</a>
   </div>
</section>

<?php include 'components/footer.php'; ?>
<script src="js/script.js"></script>
</body>
</html>