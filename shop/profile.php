<?php
include 'navbar.php';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    $sql = "SELECT firstname, lastname FROM user WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if ($user) {
        $_SESSION['firstname'] = $user['firstname'];
        $_SESSION['lastname'] = $user['lastname'];
    } 
}
?>
<div class="container mt-5">
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['firstname']) . ' ' . htmlspecialchars($_SESSION['lastname']); ?>!</h2>
    <div class="row mt-4">
        <div class="col-md-4">
            <h4>Profile Information</h4>
            <p>First Name: <?php echo htmlspecialchars($_SESSION['firstname']); ?></p>
            <p>Last Name: <?php echo htmlspecialchars($_SESSION['lastname']); ?></p>
            <!-- Add more profile information as needed -->
        </div>
        <div class="col-md-8">
            <h4>Order History</h4>
            <!-- Display user's order history here -->
        </div>
    </div>
</div>
<?php
include 'footer.php';
?>
