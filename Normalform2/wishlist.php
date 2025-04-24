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

include 'components/wishlist_cart.php';
$message = isset($message) ? $message : [];

if (isset($_POST['delete'])) {
   try {
      $pid = filter_var($_POST['pid'], FILTER_SANITIZE_NUMBER_INT);
      if ($pid <= 0) {
         $message[] = 'Invalid product ID!';
      } else {
         $delete_wishlist_item = $conn->prepare("DELETE FROM `wishlist` WHERE user_id = ? AND pid = ?");
         $delete_wishlist_item->execute([$user_id, $pid]);
         $message[] = 'Item deleted from wishlist!';
      }
   } catch (PDOException $e) {
      $message[] = 'Error deleting item: ' . htmlspecialchars($e->getMessage());
   }
}

if (isset($_GET['delete_all'])) {
   try {
      $delete_wishlist_item = $conn->prepare("DELETE FROM `wishlist` WHERE user_id = ?");
      $delete_wishlist_item->execute([$user_id]);
      $message[] = 'All items deleted from wishlist!';
      header('location:wishlist.php');
      exit;
   } catch (PDOException $e) {
      $message[] = 'Error deleting all items: ' . htmlspecialchars($e->getMessage());
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Wishlist</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'components/user_header.php'; ?>

<section class="products">
   <h3 class="heading">Your Wishlist</h3>
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
      $select_wishlist = $conn->prepare("SELECT w.user_id, w.pid, p.name, p.price, p.image_01 as image FROM `wishlist` w JOIN `products` p ON w.pid = p.id WHERE w.user_id = ?");
      $select_wishlist->execute([$user_id]);
      if ($select_wishlist->rowCount() > 0) {
         while ($fetch_wishlist = $select_wishlist->fetch(PDO::FETCH_ASSOC)) {
            $grand_total += $fetch_wishlist['price'];
   ?>
   <form action="" method="post" class="box">
      <input type="hidden" name="pid" value="<?= $fetch_wishlist['pid']; ?>">
      <input type="hidden" name="name" value="<?= htmlspecialchars($fetch_wishlist['name']); ?>">
      <input type="hidden" name="price" value="<?= $fetch_wishlist['price']; ?>">
      <input type="hidden" name="image" value="<?= htmlspecialchars($fetch_wishlist['image']); ?>">
      <a href="quick_view.php?pid=<?= $fetch_wishlist['pid']; ?>" class="fas fa-eye"></a>
      <img src="uploaded_img/<?= htmlspecialchars($fetch_wishlist['image']); ?>" alt="">
      <div class="name"><?= htmlspecialchars($fetch_wishlist['name']); ?></div>
      <div class="flex">
         <div class="price">INR<?= $fetch_wishlist['price']; ?>/-</div>
         <input type="number" name="qty" class="qty" min="1" max="99" onkeypress="if(this.value.length == 2) return false;" value="1">
      </div>
      <input type="submit" value="Add to Cart" class="btn" name="add_to_cart">
      <input type="submit" value="Delete Item" onclick="return confirm('Delete this from wishlist?');" class="delete-btn" name="delete">
   </form>
   <?php
         }
      } else {
         echo '<p class="empty">Your wishlist is empty</p>';
      }
   } catch (PDOException $e) {
      echo '<p class="empty">Error fetching wishlist: ' . htmlspecialchars($e->getMessage()) . '</p>';
   }
   ?>
   </div>
   <div class="wishlist-total">
      <p>Grand Total: <span>INR<?= $grand_total; ?>/-</span></p>
      <a href="shop.php" class="option-btn">Continue Shopping</a>
      <a href="wishlist.php?delete_all" class="delete-btn <?= ($grand_total > 0) ? '' : 'disabled'; ?>" onclick="return confirm('Delete all from wishlist?');">Delete All Items</a>
   </div>
</section>

<?php include 'components/footer.php'; ?>
<script src="js/script.js"></script>
</body>
</html>