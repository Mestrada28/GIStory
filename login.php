<?php
// Include the necessary files and establish a database connection
require_once("config.php");
$conn = get_pdo_connection();

// Initialize the error message variable
$error_message = "";

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve the entered username and password
    $userName = $_POST["username"];
    $plaintextpassword = $_POST["password"];

    // Prepare and execute the SQL query to check if the user exists
    // Fetch only the necessary data, including the userID
    $stmt = $conn->prepare("SELECT userID, userName FROM Users WHERE userName = :username AND plaintextpassword = :password");
    $stmt->bindParam(":username", $userName);
    $stmt->bindParam(":password", $plaintextpassword);
    $stmt->execute();

    // Fetch the result as an associative array
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if a matching user is found
    if ($user) {
        // Authentication successful
        // You can start a session and store user information if needed
        session_start();
        //Safer to use the exact value from the database
        $_SESSION["username"] = $user["userName"];
        // Stores the userID in the session
        $_SESSION["userID"] = $user["userID"]; 

        // Redirect to the desired page after successful login
        header("Location: ./dashboard.php");
        exit();
    } else {
        // Redirect to unsuccessful login page if login unsuccessful
        header("Location: ./login_unsuccessful.html");
        exit();
    }
}
?>
