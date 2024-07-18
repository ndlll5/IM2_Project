<?php 
include '../db_connect.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$statuses = ['Delivered', 'Cancelled', 'Resolved', 'Returned', 'In Transit', 'Payment Due', 'Processing', 'Ready for Pickup', 'Problem'];
$counts = [];

foreach ($statuses as $status) {
    $sql = "SELECT COUNT(*) as count FROM shop_order WHERE order_status = '$status'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $counts[$status] = $row['count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/styles.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #343a40 !important;
            color: #e0e0e0 !important;
        }

        .btn-secondary{
            background-color: #343a40 !important;
            color: white !important;
            border: none !important;
        }

        .card-custom {
            background-color: #ffc107 !important;
            border-radius: 1rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s;
        }
        .card-custom:hover {
            transform: translateY(-5px);
        }
        .card-title {
            font-size: 1.1rem;
            color: #343a40 !important;
        }
        .card-text {
            font-size: 2rem;
            color: #343a40 !important;
        }
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px 0;
        }
        .border-bottom-custom {
            border-bottom: 2px solid white;
        }
        .hidden-card {
            display: none;
        }
        .order-card-container {
            background-color: #3c4045;
            border-radius: 1rem;
            padding: 20px;
            margin-bottom: 20px;
        }
        #toggleCards {
            background-color: #ffca2c;
            border-color: #ffca2c;
            color: #343a40;
            font-size: 0.875rem;
            padding: 5px 10px;
        }
        #toggleCards:hover {
            background-color: #ffc107;
            border-color: #ffc107;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom-custom">
            <h1 class="mt-2">Dashboard</h1>
        </div>
        <div class="order-card-container" id="orderCardContainer">
            <h2 class="mb-3">Order Status Overview</h2>
            <div class="dashboard-grid" id="orderCards">
                <?php 
                $visibleCount = 4; // Number of cards to show initially
                $i = 0;
                foreach ($counts as $status => $count): 
                    $hiddenClass = $i >= $visibleCount ? 'hidden-card' : '';
                ?>
                    <div class="card card-custom border-0 <?php echo $hiddenClass; ?>">
                        <div class="card-body text-center">
                            <h5 class="card-title"><?php echo $status; ?> Orders</h5>
                            <h3 class="card-text"><?php echo $count; ?></h3>
                        </div>
                    </div>
                <?php 
                    $i++;
                endforeach; 
                ?>
            </div>
            <button class="btn btn-secondary mt-3" id="toggleCards" type="button">
                Show More
            </button>
        </div>
    </div>
    <?php include 'footer.php'; ?>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        document.getElementById('toggleCards').addEventListener('click', function() {
            var hiddenCards = document.querySelectorAll('.hidden-card');
            hiddenCards.forEach(function(card) {
                card.style.display = card.style.display === 'none' ? 'block' : 'none';
            });
            this.textContent = this.textContent === 'Show More' ? 'Show Less' : 'Show More';
        });
    </script>
</body>
</html>
