<?php
include 'components/connect.php';
session_start();

$user_id = isset($_SESSION['user_id']) ? filter_var($_SESSION['user_id'], FILTER_SANITIZE_NUMBER_INT) : null;

if (!$user_id) {
    header('location:user_login.php');
    exit;
}

if (!isset($conn)) {
    die('Database connection failed. Please check components/connect.php.');
}

$message = [];

// Handle specific product review from orders.php
$specific_product_id = isset($_GET['product_id']) ? filter_var($_GET['product_id'], FILTER_SANITIZE_NUMBER_INT) : null;

if (isset($_POST['submit_review'])) {
    try {
        $product_id = filter_var($_POST['product_id'], FILTER_SANITIZE_NUMBER_INT);
        $review = filter_var($_POST['review'], FILTER_SANITIZE_STRING);

        // Validate inputs
        if (empty($review)) {
            $message[] = 'Review cannot be empty!';
        } elseif (strlen($review) > 100) {
            $message[] = 'Review must be 100 characters or less!';
        } else {
            // Check if user purchased the product
            $check_purchase = $conn->prepare("SELECT * FROM `order_items` WHERE product_id = ? AND order_id IN (SELECT id FROM `orders` WHERE user_id = ?)");
            $check_purchase->execute([$product_id, $user_id]);
            if ($check_purchase->rowCount() == 0) {
                $message[] = 'You can only review purchased products!';
            } else {
                // Check if review already exists
                $check_review = $conn->prepare("SELECT user_id, pid FROM `reviews` WHERE user_id = ? AND pid = ?");
                $check_review->execute([$user_id, $product_id]);
                if ($check_review->rowCount() > 0) {
                    $message[] = 'You have already reviewed this product!';
                } else {
                    // Insert review
                    $insert_review = $conn->prepare("INSERT INTO `reviews`(user_id, pid, review) VALUES(?,?,?)");
                    if ($insert_review->execute([$user_id, $product_id, $review])) {
                        $message[] = 'Review submitted successfully!';
                        // Redirect back to quick_view.php
                        header("location:quick_view.php?pid=$product_id");
                        exit;
                    } else {
                        $message[] = 'Failed to submit review!';
                    }
                }
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
    <title>Product Reviews</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Review Section Styling */
        .reviews {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .reviews .heading {
            font-size: 2.4rem;
            color: #222;
            margin: 2rem 0;
            text-align: center;
            text-transform: capitalize;
        }

        .reviews h2 {
            font-size: 2rem;
            color: #333;
            margin: 1.5rem 0;
            text-align: center;
        }

        .reviews .box-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(25rem, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .reviews .box-container .box {
            border: 1px solid #e0e0e0;
            border-radius: 0.5rem;
            padding: 2rem;
            background-color: #fff;
            box-shadow: 0 0.2rem 0.5rem rgba(0, 0, 0, 0.1);
            text-align: left;
            transition: transform 0.2s ease-in-out;
        }

        .reviews .box-container .box:hover {
            transform: translateY(-0.3rem);
        }

        .reviews .box-container .box img {
            max-width: 100%;
            height: auto;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }

        .reviews .box-container .box p {
            margin: 0.8rem 0;
            font-size: 1.6rem;
            color: #333;
            line-height: 1.5;
        }

        .reviews .box-container .box p span {
            font-weight: 600;
            color: #000;
        }

        .reviews .box-container .empty {
            font-size: 1.8rem;
            color: #777;
            text-align: center;
            padding: 2rem;
            width: 100%;
        }

        /* Review Submission Form */
        .reviews .box-container .box form {
            margin-top: 1rem;
        }

        .reviews .box-container .box form textarea.box {
            width: 100%;
            padding: 1rem;
            font-size: 1.6rem;
            color: #333;
            background-color: #fff;
            border: 1px solid #ccc;
            border-radius: 0.4rem;
            resize: vertical;
            min-height: 10rem;
            margin-bottom: 1rem;
        }

        .reviews .box-container .box form textarea.box:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0.3rem rgba(0, 123, 255, 0.3);
        }

        .reviews .box-container .box form input.btn {
            width: 100%;
            padding: 1rem;
            font-size: 1.6rem;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 0.4rem;
            cursor: pointer;
            transition: background-color 0.2s ease-in-out;
        }

        .reviews .box-container .box form input.btn:hover {
            background-color: #0056b3;
        }

        /* Message Styling */
        .reviews .message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 1rem;
            margin: 1rem 0;
            border: 1px solid #f5c6cb;
            border-radius: 0.4rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .reviews .message span {
            font-size: 1.6rem;
        }

        .reviews .message i {
            cursor: pointer;
            font-size: 1.8rem;
            color: #721c24;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .reviews .box-container {
                grid-template-columns: 1fr;
            }

            .reviews .box-container .box {
                padding: 1.5rem;
            }

            .reviews .heading {
                font-size: 2rem;
            }

            .reviews h2 {
                font-size: 1.8rem;
            }
        }

        @media (max-width: 450px) {
            .reviews .box-container .box p {
                font-size: 1.4rem;
            }

            .reviews .box-container .empty {
                font-size: 1.6rem;
            }

            .reviews .box-container .box form textarea.box {
                font-size: 1.4rem;
            }

            .reviews .box-container .box form input.btn {
                font-size: 1.4rem;
            }

            .reviews .message span {
                font-size: 1.4rem;
            }

            .reviews .message i {
                font-size: 1.6rem;
            }
        }
    </style>
</head>
<body>
<?php include 'components/user_header.php'; ?>

<section class="reviews">
    <h1 class="heading">Your Product Reviews</h1>
    <?php
    if (!empty($message)) {
        foreach ($message as $msg) {
            echo '<div class="message"><span>' . htmlspecialchars($msg) . '</span><i class="fas fa-times" onclick="this.parentElement.remove();"></i></div>';
        }
    }
    ?>

    <!-- Specific Product Review (if product_id is provided) -->
    <?php if ($specific_product_id) { ?>
    <h2>Review Product</h2>
    <div class="box-container">
    <?php
        try {
            $select_product = $conn->prepare("SELECT p.id, p.name, p.image_01 
                                             FROM `products` p 
                                             WHERE p.id = ? 
                                             AND EXISTS (
                                                 SELECT 1 FROM `order_items` oi 
                                                 JOIN `orders` o ON oi.order_id = o.id 
                                                 WHERE oi.pid = p.id 
                                                 AND o.user_id = ?
                                             )");
            $select_product->execute([$specific_product_id, $user_id]);
            if ($select_product->rowCount() > 0) {
                $fetch_product = $select_product->fetch(PDO::FETCH_ASSOC);
                // Check if already reviewed
                $check_review = $conn->prepare("SELECT user_id, pid FROM `reviews` WHERE user_id = ? AND pid = ?");
                $check_review->execute([$user_id, $fetch_product['id']]);
                if ($check_review->rowCount() == 0) {
    ?>
    <div class="box">
        <img src="uploaded_img/<?= htmlspecialchars($fetch_product['image_01']); ?>" alt="">
        <p>Name: <span><?= htmlspecialchars($fetch_product['name']); ?></span></p>
        <form action="" method="post">
            <input type="hidden" name="product_id" value="<?= $fetch_product['id']; ?>">
            <p>Review: <textarea name="review" required class="box" cols="30" rows="5" maxlength="100" placeholder="Enter your review (max 100 characters)"></textarea></p>
            <input type="submit" value="Submit Review" class="btn" name="submit_review">
        </form>
    </div>
    <?php
                } else {
                    echo '<p class="empty">You have already reviewed this product!</p>';
                }
            } else {
                echo '<p class="empty">Product not found or not purchased!</p>';
            }
        } catch (PDOException $e) {
            echo '<p class="empty">Database error: ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
    ?>
    </div>
    <?php } ?>

    <!-- All Purchased Products for Review -->
    <h2>Review Purchased Products</h2>
    <div class="box-container">
    <?php
        try {
            $select_purchases = $conn->prepare("SELECT DISTINCT p.id, p.name, p.image_01 
                                               FROM `order_items` oi 
                                               JOIN `products` p ON oi.product_id = p.id 
                                               WHERE oi.order_id IN (SELECT id FROM `orders` WHERE user_id = ?)");
            $select_purchases->execute([$user_id]);
            if ($select_purchases->rowCount() > 0) {
                while ($fetch_product = $select_purchases->fetch(PDO::FETCH_ASSOC)) {
                    // Skip if this is the specific product already displayed
                    if ($specific_product_id && $fetch_product['id'] == $specific_product_id) {
                        continue;
                    }
                    // Check if already reviewed
                    $check_review = $conn->prepare("SELECT user_id, pid FROM `reviews` WHERE user_id = ? AND pid = ?");
                    $check_review->execute([$user_id, $fetch_product['id']]);
                    if ($check_review->rowCount() == 0) {
    ?>
    <div class="box">
        <img src="uploaded_img/<?= htmlspecialchars($fetch_product['image_01']); ?>" alt="">
        <p>Name: <span><?= htmlspecialchars($fetch_product['name']); ?></span></p>
        <form action="" method="post">
            <input type="hidden" name="product_id" value="<?= $fetch_product['id']; ?>">
            <p>Review: <textarea name="review" required class="box" cols="30" rows="5" maxlength="100" placeholder="Enter your review (max 100 characters)"></textarea></p>
            <input type="submit" value="Submit Review" class="btn" name="submit_review">
        </form>
    </div>
    <?php
                    }
                }
            } else {
                echo '<p class="empty">No purchased products available to review!</p>';
            }
        } catch (PDOException $e) {
            echo '<p class="empty">Database error: ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
    ?>
    </div>

    <!-- Existing Reviews -->
    <h2>Your Submitted Reviews</h2>
    <div class="box-container">
    <?php
        try {
            $select_reviews = $conn->prepare("SELECT r.user_id, r.pid, r.review, p.name, p.image_01 
                                             FROM `reviews` r 
                                             JOIN `products` p ON r.pid = p.id 
                                             WHERE r.user_id = ?");
            $select_reviews->execute([$user_id]);
            if ($select_reviews->rowCount() > 0) {
                while ($fetch_review = $select_reviews->fetch(PDO::FETCH_ASSOC)) {
    ?>
    <div class="box">
        <img src="uploaded_img/<?= htmlspecialchars($fetch_review['image_01']); ?>" alt="">
        <p>Product: <span><?= htmlspecialchars($fetch_review['name']); ?></span></p>
        <p>Review: <span><?= htmlspecialchars($fetch_review['review']); ?></span></p>
    </div>
    <?php
                }
            } else {
                echo '<p class="empty">You have not submitted any reviews!</p>';
            }
        } catch (PDOException $e) {
            echo '<p class="empty">Database error: ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
    ?>
    </div>
</section>

<?php include 'components/footer.php'; ?>
<script src="js/script.js"></script>
</body>
</html>