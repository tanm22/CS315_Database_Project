<?php
if (!isset($conn)) {
   die('Database connection not established.');
}

$message = isset($message) ? $message : [];

if (isset($_POST['add_to_wishlist'])) {
   try {
      $pid = filter_var($_POST['pid'], FILTER_SANITIZE_NUMBER_INT);
      $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
      $price = filter_var($_POST['price'], FILTER_SANITIZE_NUMBER_INT);
      $image = filter_var($_POST['image'], FILTER_SANITIZE_STRING);

      if (empty($user_id)) {
         $message[] = 'Please login to add to wishlist!';
         $_SESSION['message'] = $message;
         header('location:user_login.php');
         exit;
      } elseif ($pid <= 0 || empty($name) || $price <= 0 || empty($image)) {
         $message[] = 'Invalid product details!';
      } else {
         $check_product = $conn->prepare("SELECT id FROM `products` WHERE id = ?");
         $check_product->execute([$pid]);
         if ($check_product->rowCount() == 0) {
            $message[] = 'Product does not exist!';
         } else {
            $check_wishlist = $conn->prepare("SELECT * FROM `wishlist` WHERE user_id = ? AND pid = ?");
            $check_wishlist->execute([$user_id, $pid]);
            $check_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ? AND pid = ?");
            $check_cart->execute([$user_id, $pid]);

            if ($check_wishlist->rowCount() > 0) {
               $message[] = 'Already added to wishlist!';
            } elseif ($check_cart->rowCount() > 0) {
               $message[] = 'Already added to cart!';
            } else {
               $insert_wishlist = $conn->prepare("INSERT INTO `wishlist`(user_id, pid, name, price, image) VALUES(?,?,?,?,?)");
               $insert_wishlist->execute([$user_id, $pid, $name, $price, $image]);
               $message[] = 'Added to wishlist!';
            }
         }
      }
   } catch (PDOException $e) {
      $message[] = 'Database error: ' . htmlspecialchars($e->getMessage());
   }
}

if (isset($_POST['add_to_cart'])) {
   try {
      $pid = filter_var($_POST['pid'], FILTER_SANITIZE_NUMBER_INT);
      $qty = filter_var($_POST['qty'], FILTER_SANITIZE_NUMBER_INT);
      $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
      $price = filter_var($_POST['price'], FILTER_SANITIZE_NUMBER_INT);

      if (empty($user_id)) {
         $message[] = 'Please login to add to cart!';
         $_SESSION['message'] = $message;
         header('location:user_login.php');
         exit;
      } elseif ($pid <= 0 || $qty <= 0 || $qty > 99 || empty($name) || $price <= 0) {
         $message[] = 'Invalid product details or quantity!';
      } else {
         $check_product = $conn->prepare("SELECT id FROM `products` WHERE id = ?");
         $check_product->execute([$pid]);
         if ($check_product->rowCount() == 0) {
            $message[] = 'Product does not exist!';
         } else {
            $check_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ? AND pid = ?");
            $check_cart->execute([$user_id, $pid]);
            if ($check_cart->rowCount() > 0) {
               $message[] = 'Already added to cart!';
            } else {
               $check_wishlist = $conn->prepare("SELECT * FROM `wishlist` WHERE user_id = ? AND pid = ?");
               $check_wishlist->execute([$user_id, $pid]);
               if ($check_wishlist->rowCount() > 0) {
                  $delete_wishlist = $conn->prepare("DELETE FROM `wishlist` WHERE user_id = ? AND pid = ?");
                  $delete_wishlist->execute([$user_id, $pid]);
               }
               $insert_cart = $conn->prepare("INSERT INTO `cart`(user_id, pid, quantity, name, price) VALUES(?,?,?,?,?)");
               $insert_cart->execute([$user_id, $pid, $qty, $name, $price]);
               $message[] = 'Added to cart!';
            }
         }
      }
   } catch (PDOException $e) {
      $message[] = 'Database error: ' . htmlspecialchars($e->getMessage());
   }
}
?>