<?php
// Include the necessary files and establish a database connection
require_once("config.php");
$conn = get_pdo_connection();

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve the form data
    $userName = $_POST["userName"];
    $email = $_POST["email"];
    $fName = $_POST["fName"];
    $lName = $_POST["lName"];
    $password = $_POST["password"];
    $confirmPassword = $_POST["confirmPassword"];

    // Validate the form data (you can add more validation if needed)
    if (empty($userName) || empty($email) || empty($fName) || empty($lName) || empty($password) || empty($confirmPassword)) {
        die("Please fill in all the required fields");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format");
    }

    if ($password !== $confirmPassword) {
        die("Passwords do not match");
    }

    // Check if the email already exists in the database
    $stmt = $conn->prepare("SELECT * FROM Users WHERE email = :email");
    $stmt->bindParam(":email", $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        die("Email already exists. Please use a different email.");
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Prepare and execute the SQL query to insert the user data
    $stmt = $conn->prepare("INSERT INTO Users (userName, email, fName, lName, passwrd, plaintextpassword) VALUES (:userName, :email, :fName, :lName, :hashedPassword, :password)");
    $stmt->bindParam(":userName", $userName);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":fName", $fName);
    $stmt->bindParam(":lName", $lName);
    $stmt->bindParam(":hashedPassword", $hashedPassword);
    $stmt->bindParam(":password", $password);
    $stmt->execute();

    // Redirect to a success page or display a success message
    header("Location: signup_success.html");
    exit();
}
?>
