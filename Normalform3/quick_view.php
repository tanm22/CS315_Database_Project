<?php
include 'components/connect.php';
session_start();

$user_id = isset($_SESSION['user_id']) ? filter_var($_SESSION['user_id'], FILTER_SANITIZE_NUMBER_INT) : '';

include 'components/wishlist_cart.php';
$message = isset($message) ? $message : [];

if (isset($_POST['submit_review'])) {
    if ($user_id) {
        try {
            $product_id = filter_var($_POST['pid'], FILTER_SANITIZE_NUMBER_INT);
            $review = filter_var($_POST['review'], FILTER_SANITIZE_STRING);

            if (empty($review)) {
                $message[] = 'Review cannot be empty!';
            } elseif (strlen($review) > 100) {
                $message[] = 'Review must be 100 characters or less!';
            } else {
                // Check if user purchased the product with completed payment
                $check_purchase = $conn->prepare("SELECT * FROM `order_items` oi 
                                                 JOIN `orders` o ON oi.order_id = o.id 
                                                 WHERE oi.product_id = ? AND o.user_id = ? AND o.payment_status = 'completed'");
                $check_purchase->execute([$product_id, $user_id]);
                if ($check_purchase->rowCount() == 0) {
                    $message[] = 'You can only review purchased products with completed orders!';
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
                        } else {
                            $message[] = 'Failed to submit review!';
                        }
                    }
                }
            }
        } catch (PDOException $e) {
            $message[] = 'Database error: ' . htmlspecialchars($e->getMessage());
        }
    } else {
        $message[] = 'Please login to submit a review!';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quick View</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Quick View Section Styling */
        .quick-view {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .quick-view .heading {
            font-size: 2.4rem;
            color: #222;
            margin: 2rem 0;
            text-align: center;
            text-transform: capitalize;
        }

        /* Review Section Styling */
        .quick-view .box-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(25rem, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .quick-view .box-container .box {
            border: 1px solid #e0e0e0;
            border-radius: 0.5rem;
            padding: 2rem;
            background-color: #fff;
            box-shadow: 0 0.2rem 0.5rem rgba(0, 0, 0, 0.1);
            text-align: left;
            transition: transform 0.2s ease-in-out;
            max-width: 100%; /* Ensure box stays within container */
            overflow: hidden; /* Prevent content from spilling out */
        }

        .quick-view .box-container .box:hover {
            transform: translateY(-0.3rem);
        }

        .quick-view .box-container .box p {
            margin: 0.8rem 0;
            font-size: 1.6rem;
            color: #333;
            line-height: 1.5;
            overflow-wrap: break-word; /* Break long words */
            word-break: break-all; /* Fallback for very long strings */
            overflow: hidden; /* Hide overflow text */
            text-overflow: ellipsis; /* Add ellipsis for truncated text */
            display: -webkit-box;
            -webkit-line-clamp: 3; /* Limit to 3 lines */
            -webkit-box-orient: vertical;
        }

        .quick-view .box-container .box p span {
            font-weight: 600;
            color: #000;
        }

        .quick-view .box-container .empty {
            font-size: 1.8rem;
            color: #777;
            text-align: center;
            padding: 2rem;
            width: 100%;
            overflow-wrap: break-word; /* Ensure empty message text wraps */
        }

        .quick-view .box-container .empty a {
            color: #007bff;
            text-decoration: none;
        }

        .quick-view .box-container .empty a:hover {
            text-decoration: underline;
        }

        /* Review Submission Form */
        .quick-view form {
            max-width: 50rem;
            margin: 2rem auto;
            padding: 1.5rem;
            background-color: #f9f9f9;
            border: 1px solid #e0e0e0;
            border-radius: 0.5rem;
        }

        .quick-view form textarea.box {
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
            overflow-wrap: break-word; /* Ensure textarea text wraps */
        }

        .quick-view form textarea.box:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0.3rem rgba(0, 123, 255, 0.3);
        }

        .quick-view form input.btn {
            width: 100%;
            padding: 1rem;
            font-size: 1.6rem;
            background-color: #28a745; /* Green to match reviews.php */
            color: #fff;
            border: none;
            border-radius: 0.4rem;
            cursor: pointer;
            transition: background-color 0.2s ease-in-out;
        }

        .quick-view form input.btn:hover {
            background-color: #218838; /* Darker green on hover */
        }

        /* Message Styling */
        .quick-view .message {
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

        .quick-view .message span {
            font-size: 1.6rem;
            overflow-wrap: break-word; /* Ensure message text wraps */
        }

        .quick-view .message i {
            cursor: pointer;
            font-size: 1.8rem;
            color: #721c24;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .quick-view .box-container {
                grid-template-columns: 1fr;
            }

            .quick-view .box-container .box {
                padding: 1.5rem;
            }

            .quick-view form {
                max-width: 100%;
                margin: 1.5rem;
            }

            .quick-view .heading {
                font-size: 2rem;
            }
        }

        @media (max-width: 450px) {
            .quick-view .box-container .box p {
                font-size: 1.4rem;
            }

            .quick-view .box-container .empty {
                font-size: 1.6rem;
            }

            .quick-view form textarea.box {
                font-size: 1.4rem;
            }

            .quick-view form input.btn {
                font-size: 1.4rem;
            }

            .quick-view .message span {
                font-size: 1.4rem;
            }

            .quick-view .message i {
                font-size: 1.6rem;
            }
        }
    </style>
</head>
<body>
<?php include 'components/user_header.php'; ?>

<section class="quick-view">
    <h1 class="heading">Quick View</h1>

    <?php
    if (!empty($message)) {
        foreach ($message as $msg) {
            echo '<div class="message"><span>' . htmlspecialchars($msg) . '</span><i class="fas fa-times" onclick="this.parentElement.remove();"></i></div>';
        }
    }

    $pid = filter_var($_GET['pid'], FILTER_SANITIZE_NUMBER_INT);
    $select_products = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
    $select_products->execute([$pid]);
    if ($select_products->rowCount() > 0) {
        while ($fetch_product = $select_products->fetch(PDO::FETCH_ASSOC)) {
    ?>
    <form action="" method="post" class="box">
        <input type="hidden" name="pid" value="<?= $fetch_product['id']; ?>">
        <input type="hidden" name="name" value="<?= htmlspecialchars($fetch_product['name']); ?>">
        <input type="hidden" name="price" value="<?= $fetch_product['price']; ?>">
        <input type="hidden" name="image" value="<?= htmlspecialchars($fetch_product['image_01']); ?>">
        <div class="row">
            <div class="image-container">
                <div class="main-image">
                    <img src="uploaded_img/<?= htmlspecialchars($fetch_product['image_01']); ?>" alt="">
                </div>
                <div class="sub-image">
                    <img src="uploaded_img/<?= htmlspecialchars($fetch_product['image_01']); ?>" alt="">
                    <img src="uploaded_img/<?= htmlspecialchars($fetch_product['image_02']); ?>" alt="">
                    <img src="uploaded_img/<?= htmlspecialchars($fetch_product['image_03']); ?>" alt="">
                </div>
            </div>
            <div class="content">
                <div class="name"><?= htmlspecialchars($fetch_product['name']); ?></div>
                <div class="flex">
                    <div class="price"><span>INR</span><?= $fetch_product['price']; ?><span>/-</span></div>
                    <input type="number" name="qty" class="qty" min="1" max="99" onkeypress="if(this.value.length == 2) return false;" value="1">
                </div>
                <div class="details"><?= htmlspecialchars($fetch_product['details']); ?></div>
                <div class="flex-btn">
                    <input type="submit" value="Add to Cart" class="btn" name="add_to_cart">
                    <input class="option-btn" type="submit" name="add_to_wishlist" value="Add to Wishlist">
                </div>
            </div>
        </div>
    </form>
    <?php
        }
    } else {
        echo '<p class="empty">No products added yet!</p>';
    }
    ?>

    <h3 class="heading">Product Reviews</h3>
    <div class="box-container">
    <?php
    try {
        $select_reviews = $conn->prepare("SELECT r.user_id, r.pid, r.review, u.name AS user_name 
                                         FROM `reviews` r 
                                         JOIN `users` u ON r.user_id = u.id 
                                         WHERE r.pid = ?");
        $select_reviews->execute([$pid]);
        if ($select_reviews->rowCount() > 0) {
            while ($fetch_review = $select_reviews->fetch(PDO::FETCH_ASSOC)) {
    ?>
    <div class="box">
        <p>User: <span><?= htmlspecialchars($fetch_review['user_name']); ?></span></p>
        <p>Review: <span><?= htmlspecialchars($fetch_review['review']); ?></span></p>
    </div>
    <?php
            }
        } else {
            echo '<p class="empty">No reviews for this product yet!</p>';
        }
    } catch (PDOException $e) {
        echo '<p class="empty">Database error: ' . htmlspecialchars($e->getMessage()) . '</p>';
    }
    ?>

    <?php if ($user_id) {
        // Check if user purchased the product with completed payment and hasn't reviewed it
        $check_purchase = $conn->prepare("SELECT * FROM `order_items` oi 
                                         JOIN `orders` o ON oi.order_id = o.id 
                                         WHERE oi.product_id = ? AND o.user_id = ? AND o.payment_status = 'completed'");
        $check_purchase->execute([$pid, $user_id]);
        $check_review = $conn->prepare("SELECT user_id, pid FROM `reviews` WHERE user_id = ? AND pid = ?");
        $check_review->execute([$user_id, $pid]);
        if ($check_purchase->rowCount() > 0 && $check_review->rowCount() == 0) {
    ?>
    <h3>Submit Your Review</h3>
    <form action="" method="post">
        <input type="hidden" name="pid" value="<?= $pid; ?>">
        <textarea name="review" required class="box" cols="30" rows="5" maxlength="100" placeholder="Enter your review (max 100 characters)"></textarea>
        <input type="submit" value="Submit Review" class="btn" name="submit_review">
    </form>
    <?php
        } elseif ($check_purchase->rowCount() == 0) {
            echo '<p class="empty">Purchase this product with a completed order to submit a review!</p>';
        } else {
            echo '<p class="empty">You have already reviewed this product!</p>';
        }
    } else {
        echo '<p class="empty"><a href="user_login.php">Login</a> to submit a review!</p>';
    }
    ?>
    </div>
</section>

<?php include 'components/footer.php'; ?>
<script src="js/script.js"></script>
</body>
</html>