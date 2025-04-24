<?php
include '../components/connect.php';
session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('location:admin_login.php');
    exit;
}

$message = []; // Initialize as empty array

if (isset($_POST['update'])) {
    try {
        $pid = filter_var($_POST['pid'], FILTER_SANITIZE_NUMBER_INT);
        $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
        $price = filter_var($_POST['price'], FILTER_SANITIZE_NUMBER_INT);
        $details = filter_var($_POST['details'], FILTER_SANITIZE_STRING);

        if ($price < 0 || $price > 9999999999) {
            $message[] = 'Price must be between 0 and 9999999999!';
        } else {
            // Update product details in products table
            $update_product = $conn->prepare("UPDATE `products` SET name = ?, price = ?, details = ? WHERE id = ?");
            $update_product->execute([$name, $price, $details, $pid]);
            $message[] = 'Product updated successfully!';

            // Update wishlist table with new name, price, and image_01
            $update_wishlist = $conn->prepare("UPDATE `wishlist` SET name = ?, price = ?, image = ? WHERE pid = ?");
            $update_wishlist->execute([$name, $price, $_POST['old_image_01'], $pid]); // Use old_image_01 initially

            // Update cart table with new name and price
            $update_cart = $conn->prepare("UPDATE `cart` SET name = ?, price = ? WHERE pid = ?");
            $update_cart->execute([$name, $price, $pid]);
        }

        $old_image_01 = filter_var($_POST['old_image_01'], FILTER_SANITIZE_STRING);
        $image_01 = filter_var($_FILES['image_01']['name'], FILTER_SANITIZE_STRING);
        $image_size_01 = $_FILES['image_01']['size'];
        $image_tmp_name_01 = $_FILES['image_01']['tmp_name'];
        $image_folder_01 = '../uploaded_img/' . $image_01;

        if (!empty($image_01)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_01 = finfo_file($finfo, $image_tmp_name_01);
            finfo_close($finfo);
            if (!in_array($mime_01, ['image/jpeg', 'image/png', 'image/webp'])) {
                $message[] = 'Invalid image type for image_01!';
            } elseif ($image_size_01 > 2000000) {
                $message[] = 'Image size is too large!';
            } else {
                $update_image_01 = $conn->prepare("UPDATE `products` SET image_01 = ? WHERE id = ?");
                $update_image_01->execute([$image_01, $pid]);
                move_uploaded_file($image_tmp_name_01, $image_folder_01);
                unlink('../uploaded_img/' . $old_image_01);
                $message[] = 'Image 01 updated successfully!';

                // Update wishlist image
                $update_wishlist_image = $conn->prepare("UPDATE `wishlist` SET image = ? WHERE pid = ?");
                $update_wishlist_image->execute([$image_01, $pid]);
            }
        }

        $old_image_02 = filter_var($_POST['old_image_02'], FILTER_SANITIZE_STRING);
        $image_02 = filter_var($_FILES['image_02']['name'], FILTER_SANITIZE_STRING);
        $image_size_02 = $_FILES['image_02']['size'];
        $image_tmp_name_02 = $_FILES['image_02']['tmp_name'];
        $image_folder_02 = '../uploaded_img/' . $image_02;

        if (!empty($image_02)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_02 = finfo_file($finfo, $image_tmp_name_02);
            finfo_close($finfo);
            if (!in_array($mime_02, ['image/jpeg', 'image/png', 'image/webp'])) {
                $message[] = 'Invalid image type for image_02!';
            } elseif ($image_size_02 > 2000000) {
                $message[] = 'Image size is too large!';
            } else {
                $update_image_02 = $conn->prepare("UPDATE `products` SET image_02 = ? WHERE id = ?");
                $update_image_02->execute([$image_02, $pid]);
                move_uploaded_file($image_tmp_name_02, $image_folder_02);
                unlink('../uploaded_img/' . $old_image_02);
                $message[] = 'Image 02 updated successfully!';
            }
        }

        $old_image_03 = filter_var($_POST['old_image_03'], FILTER_SANITIZE_STRING);
        $image_03 = filter_var($_FILES['image_03']['name'], FILTER_SANITIZE_STRING);
        $image_size_03 = $_FILES['image_03']['size'];
        $image_tmp_name_03 = $_FILES['image_03']['tmp_name'];
        $image_folder_03 = '../uploaded_img/' . $image_03;

        if (!empty($image_03)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_03 = finfo_file($finfo, $image_tmp_name_03);
            finfo_close($finfo);
            if (!in_array($mime_03, ['image/jpeg', 'image/png', 'image/webp'])) {
                $message[] = 'Invalid image type for image_03!';
            } elseif ($image_size_03 > 2000000) {
                $message[] = 'Image size is too large!';
            } else {
                $update_image_03 = $conn->prepare("UPDATE `products` SET image_03 = ? WHERE id = ?");
                $update_image_03->execute([$image_03, $pid]);
                move_uploaded_file($image_tmp_name_03, $image_folder_03);
                unlink('../uploaded_img/' . $old_image_03);
                $message[] = 'Image 03 updated successfully!';
            }
        }
    } catch (PDOException $e) {
        $message[] = 'Database error: ' . htmlspecialchars($e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Update Product</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>
<?php include '../components/admin_header.php'; ?>

<section class="update-product">
   <h1 class="heading">Update Product</h1>
   <?php
   if (!empty($message)) {
       foreach ($message as $msg) {
           echo '<div class="message"><span>' . htmlspecialchars($msg) . '</span><i class="fas fa-times" onclick="this.parentElement.remove();"></i></div>';
       }
   }
   ?>
   <?php
      $update_id = filter_var($_GET['update'], FILTER_SANITIZE_NUMBER_INT);
      try {
          $select_products = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
          $select_products->execute([$update_id]);
          if ($select_products->rowCount() > 0) {
              while ($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)) {
   ?>
   <form action="" method="post" enctype="multipart/form-data">
      <input type="hidden" name="pid" value="<?= $fetch_products['id']; ?>">
      <input type="hidden" name="old_image_01" value="<?= htmlspecialchars($fetch_products['image_01']); ?>">
      <input type="hidden" name="old_image_02" value="<?= htmlspecialchars($fetch_products['image_02']); ?>">
      <input type="hidden" name="old_image_03" value="<?= htmlspecialchars($fetch_products['image_03']); ?>">
      <div class="image-container">
         <div class="main-image">
            <img src="../uploaded_img/<?= htmlspecialchars($fetch_products['image_01']); ?>" alt="">
         </div>
         <div class="sub-image">
            <img src="../uploaded_img/<?= htmlspecialchars($fetch_products['image_01']); ?>" alt="">
            <img src="../uploaded_img/<?= htmlspecialchars($fetch_products['image_02']); ?>" alt="">
            <img src="../uploaded_img/<?= htmlspecialchars($fetch_products['image_03']); ?>" alt="">
         </div>
      </div>
      <span>Update Name</span>
      <input type="text" name="name" required class="box" maxlength="100" placeholder="enter product name" value="<?= htmlspecialchars($fetch_products['name']); ?>">
      <span>Update Price</span>
      <input type="number" name="price" required class="box" min="0" max="9999999999" placeholder="enter product price" value="<?= htmlspecialchars($fetch_products['price']); ?>">
      <span>Update Details</span>
      <textarea name="details" class="box" required cols="30" rows="10"><?= htmlspecialchars($fetch_products['details']); ?></textarea>
      <span>Update image 01</span>
      <input type="file" name="image_01" accept="image/jpg, image/jpeg, image/png, image/webp" class="box">
      <span>Update image 02</span>
      <input type="file" name="image_02" accept="image/jpg, image/jpeg, image/png, image/webp" class="box">
      <span>Update image 03</span>
      <input type="file" name="image_03" accept="image/jpg, image/jpeg, image/png, image/webp" class="box">
      <div class="flex-btn">
         <input type="submit" name="update" class="btn" value="update">
         <a href="products.php" class="option-btn">Go Back</a>
      </div>
   </form>
   <?php
              }
          } else {
              echo '<p class="empty">No product found!</p>';
          }
      } catch (PDOException $e) {
          echo '<p class="empty">Database error: ' . htmlspecialchars($e->getMessage()) . '</p>';
      }
   ?>
</section>
<script src="../js/admin_script.js"></script>
</body>
</html>