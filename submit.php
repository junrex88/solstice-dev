<?php
// 1. DATABASE CONFIGURATION
$db_host = 'localhost';
$db_name = 'solstice_db';
$db_user = 'root';
$db_pass = ''; // Leave blank for local environment or update with your server credentials

try {
    // Establish secure PDO connection
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    // If database connection fails, redirect with a clean connection error message
    header("Location: index.php?status=error&msg=" . urlencode("Database connection failed. Please ensure database exists."));
    exit;
}

// 2. CHECK FOR POST SUBMISSION
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Retrieve and sanitize input data
    $name    = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email   = isset($_POST['email']) ? trim($_POST['email']) : '';
    $enquiry = isset($_POST['enquiry']) ? trim($_POST['enquiry']) : '';
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';

    // 3. SERVER-SIDE VALIDATION MATRIX
    if (empty($name)) {
        header("Location: index.php?status=error&msg=" . urlencode("Full Name is a required field."));
        exit;
    }

    if (empty($email)) {
        header("Location: index.php?status=error&msg=" . urlencode("Email Address is a required field."));
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: index.php?status=error&msg=" . urlencode("Please provide a valid email format."));
        exit;
    }

    if (empty($enquiry)) {
        header("Location: index.php?status=error&msg=" . urlencode("Please select a valid Type of Enquiry."));
        exit;
    }

    if (empty($message)) {
        header("Location: index.php?status=error&msg=" . urlencode("The message field cannot be left blank."));
        exit;
    }

    // 4. SECURE DATA STORAGE (BONUS POINT CRITERIA)
    try {
        $sql = "INSERT INTO submissions (name, email, enquiry, message, created_at) 
                VALUES (:name, :email, :enquiry, :message, NOW())";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':name'    => $name,
            ':email'   => $email,
            ':enquiry' => $enquiry,
            ':message' => $message
        ]);

        // Success redirection
        header("Location: index.php?status=success");
        exit;

    } catch (PDOException $e) {
        // Redirection on SQL execution failure
        header("Location: index.php?status=error&msg=" . urlencode("Failed to write submission to database."));
        exit;
    }

} else {
    // Redirect if someone tries to access submit.php directly without POSTing data
    header("Location: index.php");
    exit;
}