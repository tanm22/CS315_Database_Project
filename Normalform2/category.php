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
      $user_id = '';
   }
} else {
   $user_id = '';
}

include 'components/wishlist_cart.php';
$message = isset($message) ? $message : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Category</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'components/user_header.php'; ?>

<section class="products">
   <h1 class="heading">Category</h1>
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
      $category = filter_var($_GET['category'] ?? '', FILTER_SANITIZE_STRING);
      if (empty($category)) {
         echo '<p class="empty">No category specified!</p>';
      } else {
         $select_products = $conn->prepare("SELECT * FROM `products` WHERE name LIKE ?");
         $select_products->execute(["%$category%"]);
         if ($select_products->rowCount() > 0) {
            while ($fetch_product = $select_products->fetch(PDO::FETCH_ASSOC)) {
   ?>
   <form action="" method="post" class="box">
      <input type="hidden" name="pid" value="<?= $fetch_product['id']; ?>">
      <input type="hidden" name="name" value="<?= htmlspecialchars($fetch_product['name']); ?>">
      <input type="hidden" name="price" value="<?= $fetch_product['price']; ?>">
      <input type="hidden" name="image" value="<?= htmlspecialchars($fetch_product['image_01']); ?>">
      <button class="fas fa-heart" type="submit" name="add_to_wishlist"></button>
      <a href="quick_view.php?pid=<?= $fetch_product['id']; ?>" class="fas fa-eye"></a>
      <img src="uploaded_img/<?= htmlspecialchars($fetch_product['image_01']); ?>" alt="">
      <div class="name"><?= htmlspecialchars($fetch_product['name']); ?></div>
      <div class="flex">
         <div class="price"><span>INR</span><?= $fetch_product['price']; ?><span>/-</span></div>
         <input type="number" name="qty" class="qty" min="1" max="99" onkeypress="if(this.value.length == 2) return false;" value="1">
      </div>
      <input type="submit" value="Add to Cart" class="btn" name="add_to_cart">
   </form>
   <?php
            }
         } else {
            echo '<p class="empty">No products found!</p>';
         }
      }
   } catch (PDOException $e) {
      $message[] = 'Error fetching products: ' . htmlspecialchars($e->getMessage());
   }
   ?>
   </div>
</section>

<?php include 'components/footer.php'; ?>
<script src="js/script.js"></script>
</body>
</html>