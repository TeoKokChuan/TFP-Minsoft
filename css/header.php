<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once "database.php"; 

$cart_count = 0;

if (isset($_SESSION['User_ID'])) {
    $user_id = $_SESSION['User_ID'];
    
    $sql = "SELECT SUM(cartQuantity) FROM cart WHERE User_ID = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        $cart_count = $count ? $count : 0;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="header.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #ffffff; 
}


.top-bar {
    background-color: #1a1a1a; 
    color: #ffffff;
    height: 40px;
    display: flex;
    align-items: center;
    padding: 0 5%;
    position: relative;
    z-index: 1001;
}

.top-bar-content {
    width: 100%;
    display: flex;
    justify-content: flex-end; 
    align-items: center;
}


.promo-text {
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    font-size: 13px;
    white-space: nowrap;
    pointer-events: none; 
    color: #ffffff;
}

.top-bar-actions {
    display: flex;
    gap: 25px;
    align-items: center;
}

.icon-btn {
    color: #ffffff;
    text-decoration: none;
    font-size: 18px;
    position: relative;
    cursor: pointer;
    display: flex;
    align-items: center;
}


.cart-count {
    background-color: #82C139; 
    color: white;
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 50%;
    position: absolute;
    top: -10px;
    right: -12px;
    font-weight: bold;
}


.user-menu {
    position: relative;
}

.dropdown-content {
    display: none; 
    position: absolute;
    right: 0;
    top: 30px;
    background-color: #ffffff;
    min-width: 180px;
    box-shadow: 0 8px 16px rgba(0,0,0,0.15);
    z-index: 1002;
    border: 1px solid #eee;
    padding: 5px 0;
}


.user-menu.active .dropdown-content {
    display: block;
}

.dropdown-content a {
    color: #333 !important;
    padding: 12px 20px;
    text-decoration: none;
    display: block;
    font-size: 14px;
    text-align: left;
    transition: background 0.2s;
}

.dropdown-content a:hover {
    background-color: #f8f9fa;
}

.dropdown-content hr {
    border: 0;
    border-top: 1px solid #eee;
    margin: 5px 0;
}

.nav-container {
    background-color: #ffffff;
    height: 70px;
    display: flex;
    align-items: center;
    padding: 0 5%;
    border-bottom: 1px solid #eee;
}


.logo {
    display: flex;
    justify-content: flex-start;
    align-items: center;
}

.logo a {
    text-decoration: none;
   
    font-family: "Segoe UI", "Century Gothic", Roboto, "Helvetica Neue", sans-serif;
    
    font-size: 28px;
    font-weight: 800; 
    
    
    color: #1a202c !important; 
    
    display: block;
    letter-spacing: -1.5px; 
    text-transform: capitalize; 
    position: relative;
    padding-bottom: 2px; 
    
    text-shadow: 0.5px 0.5px 0.5px rgba(0,0,0,0.1);
    
    transition: all 0.3s ease;
}

.logo a:hover {
    color: #000000 !important; 
    transform: translateY(-0.5px);
}

.logo a::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%; 
    transform: translateX(-50%);
    width: 60px;
    height: 3px;
    
    background-color: #007bff; 
    
    border-radius: 4px;
    opacity: 0.8;
}


.nav-search-center {
    flex: 2; 
    display: flex;
    justify-content: center;
}


.search-bar {
    position: relative;
    width: 80%; 
    max-width: 600px;
    display: flex;
    align-items: center;
}


.search-bar input {
    width: 100%;
    padding: 10px 15px 10px 40px; 
    border: 1px solid #ddd;
    border-radius: 4px; 
    font-size: 14px;
    outline: none;
    transition: border-color 0.3s, box-shadow 0.3s;
}

.search-bar input:focus {
    border-color: #82C139;
    box-shadow: 0 0 5px rgba(130, 193, 57, 0.2);
}

.search-icon {
    position: absolute;
    left: 15px;
    color: #888;
    font-size: 14px;
    pointer-events: none; 
}


.nav-links-right {
    flex: 1;
    display: flex;
    justify-content: flex-end; 
    list-style: none;
    gap: 30px; 
}

.nav-links-right a {
    text-decoration: none;
    color: #333;
    font-size: 16px;
    font-weight: 500;
    transition: color 0.2s;
    white-space: nowrap;
}

.nav-links-right a:hover {
    color: #87CEFA;
}

@media (max-width: 900px) {
    .nav-links-right {
        gap: 15px;
        font-size: 14px;
    }
}

</style>

<body>

<header class="main-header">
    <div class="top-bar">
        <div class="top-bar-content">
            <div class="promo-text">Discover the Best Deals at Minsoft Store – Shop Now!</div>
            
            <div class="top-bar-actions">
                <div class="user-menu" id="userMenu">
                <a href="javascript:void(0)" class="icon-btn"><i class="fa-regular fa-user"></i></a>
                <div class="dropdown-content">
                <?php if (isset($_SESSION['User_ID'])): ?>
                <a href="user_dashboard.php">Dashboard</a>
            
                <a href="CheckHistory.php">Order History</a> 
            
                <hr>
                <a href="logout.php" style="color: #e11d48 !important;">Log Out</a>
                 <?php else: ?>
                <a href="login.php">Sign In</a>
                <a href="register.php">Sign Up</a>
                <hr>
                
                <a href="user_dashboard.php">Dashboard</a>
                <?php endif; ?>
                </div>
        </div>
                <a href="cart.php" class="icon-btn cart-link">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <span class="cart-count"><?php echo $cart_count; ?></span>
                </a>
            </div>
        </div>
    </div>

    <nav class="nav-container">
    <div class="logo">
        <a href="index.php">Minsoft Solution</a>
    </div>

    <div class="nav-search-center">
        <form action="search_result.php" method="GET" class="search-bar">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="text" name="query" placeholder="Search products..." required>
        </form>
    </div>

    <ul class="nav-links-right">
        <li><a href="index.php">Home</a></li>
        <li><a href="products.php">Products</a></li>
        <li><a href="pc_builder.php">Builder</a></li>
    </ul>
</nav>

     <script src="js/customer/header.js"></script>
</header>