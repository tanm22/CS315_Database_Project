<?php
include '../components/connect.php';
session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('location:admin_login.php');
    exit;
}

if (!isset($conn)) {
    die('Database connection failed. Please check components/connect.php.');
}

$message = [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Product Reviews</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="../css/admin_style.css">
    <style>
        /* Reviews Section Styling */
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

        /* Review Cards Container */
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
            max-width: 100%; /* Ensure box stays within container */
            overflow: hidden; /* Prevent content from spilling out */
        }

        .reviews .box-container .box:hover {
            transform: translateY(-0.3rem);
        }

        .reviews .box-container .box img {
            max-width: 100%;
            height: auto;
            max-height: 150px; /* Limit image height for consistency */
            object-fit: cover; /* Ensure image fills space without distortion */
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }

        .reviews .box-container .box p {
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
            overflow-wrap: break-word; /* Ensure empty message text wraps */
        }

        /* Back to Products Button */
        .reviews a.btn {
            display: inline-block;
            padding: 1rem 2rem;
            font-size: 1.6rem;
            color: #fff;
            background-color: #28a745; /* Green to match other pages */
            border-radius: 0.5rem;
            text-align: center;
            text-decoration: none;
            margin: 2rem auto;
            transition: background-color 0.2s ease-in-out;
            display: block; /* Center button */
            max-width: 200px; /* Limit button width */
        }

        .reviews a.btn:hover {
            background-color: #218838; /* Darker green on hover */
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
            overflow-wrap: break-word; /* Ensure message text wraps */
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

            .reviews a.btn {
                padding: 0.8rem 1.5rem;
                font-size: 1.4rem;
            }
        }

        @media (max-width: 450px) {
            .reviews .box-container .box p {
                font-size: 1.4rem;
            }

            .reviews .box-container .empty {
                font-size: 1.6rem;
            }

            .reviews .box-container .box img {
                max-height: 120px; /* Smaller image on mobile */
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
<?php include '../components/admin_header.php'; ?>

<section class="reviews">
    <h1 class="heading">All Product Reviews</h1>
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
            $select_reviews = $conn->prepare("SELECT r.user_id, r.pid, r.review, u.name AS user_name, p.name AS product_name, p.image_01 
                                             FROM `reviews` r 
                                             JOIN `users` u ON r.user_id = u.id 
                                             JOIN `products` p ON r.pid = p.id 
                                             ORDER BY p.name, r.user_id");
            $select_reviews->execute();
            if ($select_reviews->rowCount() > 0) {
                while ($fetch_review = $select_reviews->fetch(PDO::FETCH_ASSOC)) {
    ?>
    <div class="box">
        <img src="../uploaded_img/<?= htmlspecialchars($fetch_review['image_01']); ?>" alt="">
        <p>Product: <span><?= htmlspecialchars($fetch_review['product_name']); ?></span></p>
        <p>User: <span><?= htmlspecialchars($fetch_review['user_name']); ?></span></p>
        <p>Review: <span><?= htmlspecialchars($fetch_review['review']); ?></span></p>
    </div>
    <?php
                }
            } else {
                echo '<p class="empty">No reviews found!</p>';
            }
        } catch (PDOException $e) {
            echo '<p class="empty">Database error: ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
    ?>
    </div>
    <a href="products.php" class="btn">Back to Products</a>
</section>
<script src="../js/admin_script.js"></script>
</body>
</html>