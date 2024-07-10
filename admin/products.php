<?php
include '../db_connect.php';

// Handle form submission for adding/editing products
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = $_POST['category_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $product_image = $_FILES['product_image']['name'];
    $target_dir = "../uploads/";
    $target_file = $target_dir . basename($product_image);

    if ($product_image && move_uploaded_file($_FILES['product_image']['tmp_name'], $target_file)) {
        $image_sql = ", product_image='$product_image'";
    } else {
        $image_sql = "";
    }

    if (isset($_POST['product_id']) && $_POST['product_id']) {
        // Update existing product
        $product_id = $_POST['product_id'];
        $query = "UPDATE product SET category_id='$category_id', name='$name', description='$description' $image_sql WHERE product_id='$product_id'";
    } else {
        // Insert new product
        $query = "INSERT INTO product (category_id, name, description, product_image) VALUES ('$category_id', '$name', '$description', '$product_image')";
    }

    if (mysqli_query($conn, $query)) {
        header("Location: products.php");
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

// Handle product deletion
if (isset($_GET['delete'])) {
    $product_id = $_GET['delete'];

    // Delete all items associated with the product
    $delete_items_query = "DELETE FROM product_item WHERE product_id='$product_id'";
    mysqli_query($conn, $delete_items_query);

    // Delete the product
    $delete_product_query = "DELETE FROM product WHERE product_id='$product_id'";
    if (mysqli_query($conn, $delete_product_query)) {
        header("Location: products.php");
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

// Fetch products for display
$product_query = "SELECT product.*, product_category.category_name AS category_name FROM product 
                  JOIN product_category ON product.category_id = product_category.category_id";
$product_result = mysqli_query($conn, $product_query);
if ($product_result) {
    $products = mysqli_fetch_all($product_result, MYSQLI_ASSOC);
} else {
    echo "Error fetching products: " . mysqli_error($conn);
    $products = [];
}

// Fetch categories for the dropdown
$category_query = "SELECT category_id, category_name FROM product_category";
$category_result = mysqli_query($conn, $category_query);
if ($category_result) {
    $categories = mysqli_fetch_all($category_result, MYSQLI_ASSOC);
} else {
    echo "Error fetching categories: " . mysqli_error($conn);
    $categories = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/styles.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        .modal-content {
        background-color: #343a40; /* Dark background color */
        color: white; /* White text color */
        }
    </style>
</head>
<body class="bg-dark text-white">
    <?php include 'navbar.php'; ?>
    <div class="container">
        <h1 class="mt-5">Products</h1>

        <!-- Button to open the Add Product modal -->
        <button type="button" class="btn mb-3" data-toggle="modal" data-target="#productModal" onclick="clearProductForm();">
            Add Product
        </button>

        <!-- Table to display existing products -->
        <table class="table table-dark table-striped">
            <thead>
                <tr>
                    <th>Product ID</th>
                    <th>Category</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Image</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo $product['product_id']; ?></td>
                        <td><?php echo $product['category_name']; ?></td>
                        <td><?php echo $product['name']; ?></td>
                        <td><?php echo $product['description']; ?></td>
                        <td><img src="../uploads/<?php echo $product['product_image']; ?>" alt="Product Image" style="width: 50px; height: auto;"></td>
                        <td>
                            <button class="btn btn-sm" onclick="editProduct(<?php echo htmlspecialchars(json_encode($product)); ?>);">Edit</button>
                            <a href="products.php?delete=<?php echo $product['product_id']; ?>" class="btn btn-sm" onclick="return confirm('Are you sure you want to delete this product and all its items?');">Delete</a>
                            <a href="items.php?product_id=<?php echo $product['product_id']; ?>" class="btn btn-sm">Manage Items</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal for adding/editing products -->
    <div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content bg-dark">
                <div class="modal-header">
                    <h5 class="modal-title" id="productModalLabel">Add/Edit Product</h5>
                    <button type="button" class="close light" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="productForm" action="products.php" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="product_id" id="product_id">
                        <div class="form-group">
                            <label for="category_id">Category</label>
                            <select class="form-control" id="category_id" name="category_id" required>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['category_id']; ?>"><?php echo $category['category_name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="name">Product Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="product_image">Product Image</label>
                            <input type="file" class="form-control-file" id="product_image" name="product_image">
                        </div>
                        <button type="submit" class="btn">Save Product</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function clearProductForm() {
            document.getElementById('product_id').value = '';
            document.getElementById('category_id').value = '';
            document.getElementById('name').value = '';
            document.getElementById('description').value = '';
        }

        function editProduct(product) {
            document.getElementById('product_id').value = product.product_id;
            document.getElementById('category_id').value = product.category_id;
            document.getElementById('name').value = product.name;
            document.getElementById('description').value = product.description;
            $('#productModal').modal('show');
        }
    </script>
    <?php include 'footer.php'; ?>
</body>
</html>
