<?php
include '../components/connect.php';
session_start();

$admin_id = $_SESSION['admin_id'];
if (!isset($admin_id)) {
    header('location:admin_login.php');
    exit;
}

$message = [];

// Add Product Logic
if (isset($_POST['add_product'])) {
    try {
        $id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);
        $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
        $price = filter_var($_POST['price'], FILTER_SANITIZE_NUMBER_INT);
        $details = filter_var($_POST['details'], FILTER_SANITIZE_STRING);
        $quantity = filter_var($_POST['quantity'], FILTER_SANITIZE_NUMBER_INT);

        // Validate manual product ID
        $check_id = $conn->prepare("SELECT id FROM `products` WHERE id = ?");
        $check_id->execute([$id]);
        if ($check_id->rowCount() > 0) {
            $message[] = 'Product ID already exists!';
        } else {
            // Image handling
            $image_01 = $_FILES['image_01']['name'];
            $image_01 = filter_var($image_01, FILTER_SANITIZE_STRING);
            $image_size_01 = $_FILES['image_01']['size'];
            $image_tmp_name_01 = $_FILES['image_01']['tmp_name'];
            $image_folder_01 = '../uploaded_img/' . $image_01;

            $image_02 = $_FILES['image_02']['name'];
            $image_02 = filter_var($image_02, FILTER_SANITIZE_STRING);
            $image_size_02 = $_FILES['image_02']['size'];
            $image_tmp_name_02 = $_FILES['image_02']['tmp_name'];
            $image_folder_02 = '../uploaded_img/' . $image_02;

            $image_03 = $_FILES['image_03']['name'];
            $image_03 = filter_var($image_03, FILTER_SANITIZE_STRING);
            $image_size_03 = $_FILES['image_03']['size'];
            $image_tmp_name_03 = $_FILES['image_03']['tmp_name'];
            $image_folder_03 = '../uploaded_img/' . $image_03;

            // Validate image types
            $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_01 = finfo_file($finfo, $image_tmp_name_01);
            $mime_02 = finfo_file($finfo, $image_tmp_name_02);
            $mime_03 = finfo_file($finfo, $image_tmp_name_03);
            finfo_close($finfo);

            if (!in_array($mime_01, $allowed_mimes) || !in_array($mime_02, $allowed_mimes) || !in_array($mime_03, $allowed_mimes)) {
                $message[] = 'Only JPG, PNG, and WEBP images are allowed!';
            } elseif ($image_size_01 > 2000000 || $image_size_02 > 2000000 || $image_size_03 > 2000000) {
                $message[] = 'Image size must be less than 2MB!';
            } else {
                $insert_product = $conn->prepare("INSERT INTO `products`(id, name, details, price, image_01, image_02, image_03, quantity) VALUES(?,?,?,?,?,?,?,?)");
                $insert_product->execute([$id, $name, $details, $price, $image_01, $image_02, $image_03, $quantity]);
                move_uploaded_file($image_tmp_name_01, $image_folder_01);
                move_uploaded_file($image_tmp_name_02, $image_folder_02);
                move_uploaded_file($image_tmp_name_03, $image_folder_03);
                $message[] = 'New product added successfully!';
            }
        }
    } catch (PDOException $e) {
        $message[] = 'Database error: ' . htmlspecialchars($e->getMessage());
    }
}

// Delete Product Logic
if (isset($_GET['delete'])) {
    try {
        $delete_id = filter_var($_GET['delete'], FILTER_SANITIZE_NUMBER_INT);
        $check_product = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
        $check_product->execute([$delete_id]);
        $fetch_product = $check_product->fetch(PDO::FETCH_ASSOC);

        if (!$fetch_product) {
            $message[] = 'Product not found!';
        } else {
            $conn->beginTransaction();

            // Delete images from server
            $images = [
                '../uploaded_img/' . $fetch_product['image_01'],
                '../uploaded_img/' . $fetch_product['image_02'],
                '../uploaded_img/' . $fetch_product['image_03']
            ];

            foreach ($images as $image) {
                if (file_exists($image)) {
                    unlink($image);
                }
            }

            // Delete product from database
            $delete_product = $conn->prepare("DELETE FROM `products` WHERE id = ?");
            $delete_product->execute([$delete_id]);
            
            $conn->commit();
            $message[] = 'Product deleted successfully!';
        }
    } catch (Exception $e) {
        $conn->rollBack();
        $message[] = 'Error deleting product: ' . htmlspecialchars($e->getMessage());
        error_log("Delete error: " . $e->getMessage());
    }
    header('location:products.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>
<?php include '../components/admin_header.php'; ?>

<section class="add-products">
    <h1 class="heading">Add Product</h1>
    <?php
    if (!empty($message)) {
        foreach ($message as $msg) {
            echo '<div class="message"><span>' . htmlspecialchars($msg) . '</span><i class="fas fa-times" onclick="this.parentElement.remove();"></i></div>';
        }
    }
    ?>
    <form action="" method="post" enctype="multipart/form-data">
        <div class="flex">
            <div class="inputBox">
                <span>Product ID (required)</span>
                <input type="number" name="id" class="box" required placeholder="Enter unique numeric ID">
            </div>
            <div class="inputBox">
                <span>Product Name (required)</span>
                <input type="text" name="name" class="box" required maxlength="100" placeholder="Enter product name">
            </div>
            <div class="inputBox">
                <span>Product Price (required)</span>
                <input type="number" name="price" class="box" required min="0" placeholder="Enter price">
            </div>
            <div class="inputBox">
                <span>Product Quantity (required)</span>
                <input type="number" name="quantity" class="box" required min="0" placeholder="Enter quantity">
            </div>
            <div class="inputBox">
                <span>Image 01 (required)</span>
                <input type="file" name="image_01" accept="image/jpg, image/jpeg, image/png, image/webp" class="box" required>
            </div>
            <div class="inputBox">
                <span>Image 02 (required)</span>
                <input type="file" name="image_02" accept="image/jpg, image/jpeg, image/png, image/webp" class="box" required>
            </div>
            <div class="inputBox">
                <span>Image 03 (required)</span>
                <input type="file" name="image_03" accept="image/jpg, image/jpeg, image/png, image/webp" class="box" required>
            </div>
            <div class="inputBox">
                <span>Product Details (required)</span>
                <textarea name="details" class="box" required maxlength="500" placeholder="Enter product description"></textarea>
            </div>
        </div>
        <input type="submit" value="Add Product" class="btn" name="add_product">
    </form>
</section>

<section class="show-products">
    <h1 class="heading">Products List</h1>
    <div class="box-container">
        <?php
        $select_products = $conn->prepare("SELECT * FROM `products`");
        $select_products->execute();
        if ($select_products->rowCount() > 0) {
            while ($fetch_product = $select_products->fetch(PDO::FETCH_ASSOC)) {
        ?>
        <div class="box">
            <img src="../uploaded_img/<?= htmlspecialchars($fetch_product['image_01']); ?>" alt="">
            <div class="name"><?= htmlspecialchars($fetch_product['name']); ?></div>
            <div class="price">₹<?= htmlspecialchars($fetch_product['price']); ?>/-</div>
            <div class="details"><?= htmlspecialchars($fetch_product['details']); ?></div>
            <div class="quantity">Stock: <?= htmlspecialchars($fetch_product['quantity']); ?></div>
            <div class="flex-btn">
                <a href="update_product.php?update=<?= $fetch_product['id']; ?>" class="option-btn">Update</a>
                <a href="products.php?delete=<?= $fetch_product['id']; ?>" class="delete-btn" onclick="return confirm('Delete this product?');">Delete</a>
            </div>
        </div>
        <?php
            }
        } else {
            echo '<p class="empty">No products added yet!</p>';
        }
        ?>
    </div>
</section>

<script src="../js/admin_script.js"></script>
</body>
</html>