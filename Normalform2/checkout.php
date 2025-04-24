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
   header('location:user_login.php');
   exit;
}

$message = [];

if (isset($_POST['order'])) {
   try {
      $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
      $number = filter_var($_POST['number'], FILTER_SANITIZE_STRING);
      $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
      $method = filter_var($_POST['method'], FILTER_SANITIZE_STRING);
      $flat = filter_var($_POST['flat'], FILTER_SANITIZE_STRING);
      $street = filter_var($_POST['street'], FILTER_SANITIZE_STRING);
      $city = filter_var($_POST['city'], FILTER_SANITIZE_STRING);
      $state = filter_var($_POST['state'], FILTER_SANITIZE_STRING);
      $country = filter_var($_POST['country'], FILTER_SANITIZE_STRING);
      $pin_code = filter_var($_POST['pin_code'], FILTER_SANITIZE_STRING);
      $address = "flat no. $flat, $street, $city, $state, $country - $pin_code";

      if (empty($name) || strlen($name) > 20) {
         $message[] = 'Name must be 1-20 characters!';
      } elseif (strlen($number) != 10) {
         $message[] = 'Phone number must be 10 digits!';
      } elseif (!$email) {
         $message[] = 'Invalid email format!';
      } elseif (empty($method) || strlen($flat) > 50 || strlen($street) > 50 || strlen($city) > 50 || strlen($state) > 50 || strlen($country) > 50 || strlen($pin_code) != 6) {
         $message[] = 'Invalid input lengths!';
      } else {
          $check_cart = $conn->prepare("SELECT c.pid, c.quantity, p.name, p.price 
                                 FROM `cart` c 
                                 JOIN `products` p ON c.pid = p.id 
                                 WHERE c.user_id = ?");
         $check_cart->execute([$user_id]);

         if ($check_cart->rowCount() > 0) {
            $grand_total = 0;
            $cart_items = $check_cart->fetchAll(PDO::FETCH_ASSOC);
            foreach ($cart_items as $item) {
               $grand_total += $item['price'] * $item['quantity'];
            }

            $conn->beginTransaction();
            $insert_order = $conn->prepare("INSERT INTO `orders`(user_id, name, number, email, method, address, total_price) 
                                            VALUES(?,?,?,?,?,?,?)");
            $insert_order->execute([$user_id, $name, $number, $email, $method, $address, $grand_total]);
            $order_id = $conn-> lastInsertId();

            $insert_item = $conn->prepare("INSERT INTO `order_items`(order_id, product_id, quantity) 
                                           VALUES(?,?,?)");
            foreach ($cart_items as $item) {
               $insert_item->execute([$order_id, $item['pid'], $item['quantity']]);
            }

            $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
            $delete_cart->execute([$user_id]);

            $conn->commit();
            $message[] = 'Order placed successfully!';
         } else {
            $message[] = 'Your cart is empty!';
         }
      }
   } catch (PDOException $e) {
      $conn->rollBack();
      $message[] = 'Order placement failed: ' . htmlspecialchars($e->getMessage());
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Checkout</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'components/user_header.php'; ?>

<section class="checkout-orders">
   <form action="" method="POST">
      <h3>Your Orders</h3>
      <?php
      if (!empty($message)) {
         foreach ($message as $msg) {
            echo '<div class="message"><span>' . htmlspecialchars($msg) . '</span><i class="fas fa-times" onclick="this.parentElement.remove();"></i></div>';
         }
      }
      ?>
      <div class="display-orders">
      <?php
      $grand_total = 0;
      try {
         $select_cart = $conn->prepare("SELECT  c.quantity, p.name, p.price 
                                 FROM `cart` c 
                                 JOIN `products` p ON c.pid = p.id 
                                 WHERE c.user_id = ?");
         $select_cart->execute([$user_id]);
         if ($select_cart->rowCount() > 0) {
            while ($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)) {
               $grand_total += ($fetch_cart['price'] * $fetch_cart['quantity']);
      ?>
         <p><?= htmlspecialchars($fetch_cart['name']); ?> <span>(INR<?= $fetch_cart['price']; ?>/- x <?= $fetch_cart['quantity']; ?>)</span></p>
      <?php
            }
         } else {
            echo '<p class="empty">Your cart is empty!</p>';
         }
      } catch (PDOException $e) {
         $message[] = 'Error fetching cart: ' . htmlspecialchars($e->getMessage());
      }
      ?>
         <input type="hidden" name="total_price" value="<?= $grand_total; ?>">
         <div class="grand-total">Grand Total: <span>INR<?= $grand_total; ?>/-</span></div>
      </div>
      <h3>Place Your Orders</h3>
      <div class="flex">
         <div class="inputBox">
            <span>Your Full Name:</span>
            <input type="text" name="name" placeholder="Enter your name" class="box" maxlength="20" required>
         </div>
         <div class="inputBox">
            <span>Your Number:</span>
            <input type="number" name="number" placeholder="Enter your number" class="box" min="0" max="9999999999" onkeypress="if(this.value.length == 10) return false;" required>
         </div>
         <div class="inputBox">
            <span>Your Email:</span>
            <input type="email" name="email" placeholder="Enter your email" class="box" maxlength="50" required>
         </div>
         <div class="inputBox">
            <span>Payment Method:</span>
            <select name="method" class="box" required>
               <option value="cash on delivery">Cash On Delivery</option>
               <option value="credit card">Credit Card</option>
               <option value="paytm">NetBanking</option>
               <option value="paypal">UPI</option>
            </select>
         </div>
         <div class="inputBox">
            <span>Address line 01:</span>
            <input type="text" name="flat" placeholder="e.g. Flat number" class="box" maxlength="50" required>
         </div>
         <div class="inputBox">
            <span>Address line 02:</span>
            <input type="text" name="street" placeholder="Street name" class="box" maxlength="50" required>
         </div>
         <div class="inputBox">
            <span>City:</span>
            <input type="text" name="city" placeholder="Kanpur" class="box" maxlength="50" required>
         </div>
         <div class="inputBox">
            <span>Province:</span>
            <input type="text" name="state" placeholder="Kalyanpur" class="box" maxlength="50" required>
         </div>
         <div class="inputBox">
            <span>Country:</span>
            <input type="text" name="country" placeholder="India" class="box" maxlength="50" required>
         </div>
         <div class="inputBox">
            <span>ZIP CODE:</span>
            <input type="number" min="0" name="pin_code" placeholder="e.g. 56400" max="999999" onkeypress="if(this.value.length == 6) return false;" class="box" required>
         </div>
      </div>
      <input type="submit" name="order" class="btn <?= ($grand_total > 0) ? '' : 'disabled'; ?>" value="Place Order">
   </form>
</section>

<?php include 'components/footer.php'; ?>
<script src="js/script.js"></script>
</body>
</html>