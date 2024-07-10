<?php
include '../db_connect.php';
include 'navbar.php';

// Constants
$rowPerPage = 12;

// Function to fetch categories
function getCategories($conn) {
    $query = $conn->query("SELECT * FROM product_category");
    if (!$query) {
        die("Error retrieving categories: " . $conn->error);
    }
    return $query->fetch_all(MYSQLI_ASSOC);
}

// Function to fetch products
function getProducts($conn, $filterCategory, $startRow, $rowPerPage) {
    $filterCondition = $filterCategory ? "WHERE category_id = $filterCategory" : '';
    $query = $conn->query("SELECT * FROM product $filterCondition LIMIT $startRow, $rowPerPage");
    if (!$query) {
        die("Error retrieving products: " . $conn->error);
    }
    return $query->fetch_all(MYSQLI_ASSOC);
}

// Function to count total products
function getTotalProducts($conn, $filterCategory) {
    $filterCondition = $filterCategory ? "WHERE category_id = $filterCategory" : '';
    $query = $conn->query("SELECT COUNT(*) AS total FROM product $filterCondition");
    if (!$query) {
        die("Error retrieving total number of products: " . $conn->error);
    }
    return $query->fetch_assoc()['total'];
}

$categories = getCategories($conn);

// Determine current page
$currentPage = isset($_GET['pageNum']) ? intval($_GET['pageNum']) : 1;
$startRow = ($currentPage - 1) * $rowPerPage;

// Filter products by category if a category is selected
$filterCategory = isset($_GET['category']) ? $_GET['category'] : '';
$products = getProducts($conn, $filterCategory, $startRow, $rowPerPage);

// Count total number of products
$totalProducts = getTotalProducts($conn, $filterCategory);
$totalPages = ceil($totalProducts / $rowPerPage);

// Function to get category name
function getCategoryName($categories, $categoryId) {
    foreach ($categories as $category) {
        if ($category['category_id'] == $categoryId) {
            return $category['category_name'];
        }
    }
    return 'All Categories';
}

// Determine selected category name
$selectedCategoryName = getCategoryName($categories, $filterCategory);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../assets/logo.png">
    <title>Motoracer Shop</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom styles -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #1a1a1a;
            font-family: 'Titillium Web', sans-serif;
            color: #ffffff;
        }
        .card {
            background-color: #2c2c2c;
            border: 1px solid #3d3d3d;
            border-radius: 8px;
            transition: transform 0.2s ease-in-out;
            height: 100%;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0px 0px 15px rgba(0,0,0,0.1);
        }
        .card-img-top {
            height: 200px;
            object-fit: cover;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }
        .card-title {
            color: #ffffff;
            font-weight: bold;
            font-size: 18px;
        }
        .card-text {
            color: #e0e0e0;
            font-size: 14px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .pagination {
            justify-content: center;
            margin-top: 20px;
        }
        .btn {
            background-color: #2c2c2c;
            border-color: #3d3d3d;
            color: #ffffff;
        }
        .btn:hover {
            background-color: #444;
        }
        .category-card {
            max-height: 500px;
            overflow-y: auto;
            background-color: #2c2c2c;
            border: 1px solid #3d3d3d;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            width: 100%;
            margin-left: -100px; /* Adjust left margin */
            margin-top: 110px;  /* Adjust top margin */
        }
        .category-card h5 {
            color: #ffffff;
            font-weight: bold;
        }
        .category-card .nav-link {
            color: #e0e0e0;
            font-size: 16px;
        }
        .category-card .nav-link:hover {
            color: #ffffff;
        }
        .category-card::-webkit-scrollbar {
            width: 8px;
        }
        .category-card::-webkit-scrollbar-thumb {
            background-color: #3d3d3d;
            border-radius: 4px;
        }
        .category-card::-webkit-scrollbar-track {
            background-color: #2c2c2c;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row justify-content-center">
            <!-- Categories Card -->
            <div class="col-md-4 col-lg-3">
                <div class="category-card">
                    <h5 class="mt-4 mb-3">Filter by Category:</h5>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="shop.php">All Categories</a>
                        </li>
                        <?php foreach ($categories as $category): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="shop.php?category=<?php echo $category['category_id']; ?>"><?php echo $category['category_name']; ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <main role="main" class="col-md-8 col-lg-9 ml-md-auto px-4">
                <div class="container mt-5">
                    <h2 class="mb-4">Products <?php echo ($selectedCategoryName != 'All Categories') ? 'in ' . $selectedCategoryName : ''; ?></h2>
                    <div class="row">
                        <?php foreach ($products as $product): ?>
                            <div class="col-lg-4 col-md-6 mb-2">
                                <div class="card">
                                    <img src="../uploads/<?php echo $product['product_image']; ?>" class="card-img-top" alt="<?php echo $product['name']; ?>">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo $product['name']; ?></h5>
                                        <p class="card-text"><?php echo (strlen($product['description']) > 100) ? substr($product['description'], 0, 100) . '...' : $product['description']; ?></p>
                                        <a href="#" class="btn">View Details</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <!-- Pagination -->
                    <nav aria-label="Page navigation example">
                        <ul class="pagination">
                            <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?pageNum=1<?php echo ($filterCategory) ? '&category=' . $filterCategory : ''; ?>">First</a>
                            </li>
                            <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?pageNum=<?php echo $currentPage - 1; ?><?php echo ($filterCategory) ? '&category=' . $filterCategory : ''; ?>">Previous</a>
                            </li>
                            <li class="page-item disabled">
                                <a class="page-link" href="#"><?php echo $currentPage; ?></a>
                            </li>
                            <li class="page-item <?php echo ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?pageNum=<?php echo $currentPage + 1; ?><?php echo ($filterCategory) ? '&category=' . $filterCategory : ''; ?>">Next</a>
                            </li>
                            <li class="page-item <?php echo ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?pageNum=<?php echo $totalPages; ?><?php echo ($filterCategory) ? '&category=' . $filterCategory : ''; ?>">Last</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
