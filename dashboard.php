<?php
session_start();
error_reporting(E_ALL);
ini_set("log_errors", 1);
ini_set("display_errors", 0);

require_once("config.php");

if (!isset($_SESSION["username"])) {
    header("Location: login.html");
    exit;
}

session_regenerate_id();

$conn = get_pdo_connection();
$userID = $_SESSION["userID"];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["create"])) {
    $workName = trim($_POST["workName"]);
    if (!empty($workName)) {
        $insertStmt = $conn->prepare("INSERT INTO Workspaces (userID, workName) VALUES (:userID, :workName)");
        $insertStmt->bindParam(":userID", $userID, PDO::PARAM_INT);
        $insertStmt->bindParam(":workName", $workName, PDO::PARAM_STR);
        if ($insertStmt->execute()) {
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $error_message = "Error creating workspace. Please try again.";
        }
    }
}

$stmt = $conn->prepare("SELECT * FROM Workspaces WHERE userID = :userID");
$stmt->bindParam(":userID", $userID, PDO::PARAM_INT);
$stmt->execute();
$workspaces = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GIStory - Workspace Management</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <style>
    @keyframes gradientBG {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    :root {
        --primary-color: #e2b714;
        --background-color: #323437;
        --text-color: #d1d0c5;
        --card-bg-color: #2c2e31;
        --edit-hover-color: #007bff;
        --delete-hover-color: #dc3545;
    }

    .workspace-image-container {
        position: relative;
        width: 100%;
        margin: 10px 0 20px 0;
        overflow: hidden;
        border-radius: 8px;
        background-color: rgba(226, 183, 20, 0.1);
        aspect-ratio: 16/9;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .workspace-preview {
        width: 100%;
        height: 100%;
        border-radius: 8px;
        margin: 0;
        object-fit: cover;
        transition: transform 0.5s ease;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .workspace-preview:hover {
        transform: scale(1.05);
    }

    .no-image-placeholder {
        color: var(--primary-color);
        font-size: 1.2rem;
        text-align: center;
        padding: 20px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
    }

    .no-image-placeholder::before {
        content: "🖼️";
        font-size: 2rem;
        opacity: 0.7;
    }

    body {
        font-family: 'Roboto Mono', monospace;
        color: var(--text-color);
        background: linear-gradient(45deg, #000000, #323437, #000000);
        background-size: 400% 400%;
        animation: gradientBG 15s ease infinite;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }

    .navbar {
        background-color: rgba(226, 183, 20, 0.9) !important;
        backdrop-filter: blur(10px);
        padding: 0.5rem 0;
        position: sticky;
        top: 0;
        z-index: 1000;
        transition: all 0.3s ease;
    }

    .navbar-brand, .nav-link {
        color: var(--background-color) !important;
        font-weight: bold;
    }

    .nav-link:hover, .nav-link:focus {
        color: var(--card-bg-color) !important;
    }

    .content-box {
        background-color: rgba(44, 46, 49, 0.8);
        border-radius: 15px;
        padding: 30px;
        margin-top: 40px;
        margin-bottom: 40px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        backdrop-filter: blur(5px);
        animation: fadeInUp 0.8s ease-out;
    }

    .btn-primary {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        color: var(--background-color);
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background-color: #c19b12;
        border-color: #c19b12;
        color: var(--background-color);
        transform: translateY(-3px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }

    .welcome-text {
        color: var(--primary-color);
        margin-bottom: 30px;
        font-size: 2.5rem;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        animation: fadeInUp 1s ease-out;
    }

    h2 {
        color: var(--primary-color);
        margin-top: 40px;
        font-size: 2rem;
        text-shadow: 1px 1px 3px rgba(0,0,0,0.3);
    }

    input[type="text"] {
        background-color: rgba(50, 52, 55, 0.8);
        border: 2px solid var(--primary-color);
        color: #e2b714;
        border-radius: 8px;
        padding: 12px;
        transition: all 0.3s ease;
        caret-color: #e2b714;
    }

    input[type="text"]::placeholder {
        color: rgba(226, 183, 20, 0.5);
    }

    input[type="text"]:focus {
        background-color: rgba(60, 62, 65, 0.9);
        box-shadow: 0 0 10px var(--primary-color);
        outline: none;
        color: #e2b714;
    }

    input[type="text"]:-webkit-autofill,
    input[type="text"]:-webkit-autofill:hover,
    input[type="text"]:-webkit-autofill:focus {
        -webkit-text-fill-color: #e2b714;
        -webkit-box-shadow: 0 0 0px 1000px rgba(50, 52, 55, 0.8) inset;
        transition: background-color 5000s ease-in-out 0s;
    }

    .error-message {
        color: #ff6b6b;
        margin-bottom: 15px;
        animation: fadeInUp 0.5s ease-out;
    }

    footer {
        background-color: rgba(30, 31, 33, 0.9);
        color: var(--text-color);
        padding: 20px 0;
        margin-top: auto;
        backdrop-filter: blur(5px);
    }

    .workspace-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 30px;
        margin-top: 30px;
    }

    .workspace-box {
        background-color: rgba(50, 52, 55, 0.7);
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 6px 10px rgba(0,0,0,0.15);
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        min-height: 200px;
        backdrop-filter: blur(3px);
        animation: fadeInUp 0.5s ease-out;
    }

    .workspace-box:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 15px 30px rgba(0,0,0,0.3);
    }

    .workspace-title {
        color: var(--primary-color);
        margin-bottom: 20px;
        font-size: 1.6rem;
        display: flex;
        align-items: center;
    }

    .workspace-icon {
        font-size: 2rem;
        margin-right: 1rem;
        animation: bounce 2s infinite;
    }

    @keyframes bounce {
        0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
        40% { transform: translateY(-10px); }
        60% { transform: translateY(-5px); }
    }

    .workspace-actions {
        margin-top: auto;
        display: flex;
        gap: 15px;
    }

    .workspace-actions .btn {
        flex: 1;
        padding: 12px 15px;
        font-size: 1rem;
        transition: all 0.3s ease;
        font-weight: bold;
        border-radius: 8px;
    }

    .workspace-actions .btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 15px rgba(0,0,0,0.3);
    }

    .workspace-actions .btn-primary {
        background-color: #ffc107;
        border-color: #ffc107;
        color: #000;
    }

    .workspace-actions .btn-primary:hover {
        background-color: #e0a800;
        border-color: #e0a800;
    }

    .workspace-actions .btn-outline-primary {
        color: #007bff;
        border-color: #007bff;
    }

    .workspace-actions .btn-outline-primary:hover {
        background-color: var(--edit-hover-color);
        border-color: var(--edit-hover-color);
        color: #ffffff;
    }

    .workspace-actions .btn-outline-danger {
        color: #dc3545;
        border-color: #dc3545;
    }

    .workspace-actions .btn-outline-danger:hover {
        background-color: var(--delete-hover-color);
        border-color: var(--delete-hover-color);
        color: #ffffff;
    }

    .btn-danger {
        transition: all 0.3s ease;
    }

    .btn-danger:hover {
        background-color: var(--delete-hover-color);
        border-color: var(--delete-hover-color);
        transform: translateY(-3px);
        box-shadow: 0 8px 15px rgba(0,0,0,0.3);
    }

    .nav-button {
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: bold;
        transition: all 0.3s ease;
    }

    .nav-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }

    .nav-button-logout {
        background-color: #007bff;
        color: white !important;
        border: none;
    }

    .nav-button-logout:hover {
        background-color: #0056b3;
    }

    html {
        scroll-behavior: smooth;
    }

    .create-workspace-form {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.5s ease-out;
    }

    .create-workspace-form.active {
        max-height: 300px;
    }

    .toggle-form-btn {
        background-color: var(--primary-color);
        color: var(--background-color);
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-bottom: 20px;
    }

    .toggle-form-btn:hover {
        background-color: #c19b12;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }

    .workspace-box {
        opacity: 0;
        transform: translateY(20px);
        transition: opacity 0.5s ease, transform 0.5s ease;
    }

    .workspace-box.visible {
        opacity: 1;
        transform: translateY(0);
    }
</style>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="#">GIStory</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link nav-button nav-button-logout" href="logOut.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container">
        <div class="content-box">
            <h1 class="welcome-text">Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h1>
            
            <?php if (isset($error_message)): ?>
                <p class="error-message"><?php echo $error_message; ?></p>
            <?php endif; ?>

            <button class="toggle-form-btn" id="toggleFormBtn">Create New Workspace</button>

            <div class="create-workspace-form" id="createWorkspaceForm">
                <h2>Create New Workspace</h2>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="mb-4">
                    <div class="mb-3">
                        <input type="text" class="form-control" id="workName" name="workName" placeholder="Enter new workspace name" required>
                    </div>
                    <button type="submit" name="create" class="btn btn-primary">Create Workspace</button>
                </form>
            </div>

            <h2>Your Workspaces</h2>
            <?php if (!empty($workspaces)): ?>
                <div class="workspace-grid">
                <?php foreach ($workspaces as $workspace): ?>
                    <div class="workspace-box">
                        <h5 class="workspace-title">
                            <span class="workspace-icon">🗺️</span>
                            <?php echo htmlspecialchars($workspace['workName']); ?>
                        </h5>
                        
                        <div class="workspace-image-container">
                            <?php 
                            // Get a random image from this workspace's points
                            $stmt = $conn->prepare("
                                SELECT p.path 
                                FROM Pictures p 
                                JOIN Points pt ON p.pointID = pt.pointID 
                                WHERE pt.WorkspaceID = :workspaceID 
                                    AND p.Path IS NOT NULL 
                                    AND p.Path != ''
                                ORDER BY RAND() 
                                LIMIT 1
                            ");
                            $stmt->bindParam(':workspaceID', $workspace['WorkspaceID']);
                            $stmt->execute();
                            $previewImage = $stmt->fetch(PDO::FETCH_COLUMN);
                            
                            if ($previewImage): ?>
                                <img src="<?php echo htmlspecialchars($previewImage); ?>" 
                                     alt="Workspace Preview" 
                                     class="workspace-preview">
                            <?php else: ?>
                                <div class="no-image-placeholder">
                                    No images available in this workspace
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="workspace-actions">
                            <a href="view_workspace.php?id=<?php echo htmlspecialchars($workspace['WorkspaceID']); ?>" class="btn btn-primary">View</a>
                            <a href="edit_workspace2.php?id=<?php echo htmlspecialchars($workspace['WorkspaceID']); ?>" class="btn btn-outline-primary">Edit</a>
                            <a href="delete_workspace.html?id=<?php echo htmlspecialchars($workspace['WorkspaceID']); ?>" class="btn btn-outline-danger">Delete</a>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>You have no workspaces yet. Create one to get started!</p>
            <?php endif; ?>
            
            <div class="mt-4">
                <a href="delete_user.html" class="btn btn-danger">Delete Account</a>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <h5>GIStory</h5>
            <p>Explore and create historical timelines and maps</p>
            <p>&copy; <?php echo date("Y"); ?> GIStory. All rights reserved.</p>
        </div>
    </footer>
    <script>
        // Toggle create workspace form
        document.getElementById('toggleFormBtn').addEventListener('click', function() {
            var form = document.getElementById('createWorkspaceForm');
            form.classList.toggle('active');
            this.textContent = form.classList.contains('active') ? 'Hide Form' : 'Create New Workspace';
        });

        // Function to animate workspace boxes on scroll
        function isElementInViewport(el) {
            var rect = el.getBoundingClientRect();
            return (
                rect.top >= 0 &&
                rect.left >= 0 &&
                rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
                rect.right <= (window.innerWidth || document.documentElement.clientWidth)
            );
        }

        function animateWorkspaceBoxes() {
            var boxes = document.querySelectorAll('.workspace-box');
            boxes.forEach(function(box) {
                if (isElementInViewport(box)) {
                    box.classList.add('visible');
                }
            });
        }

        // Initial call to animate workspace boxes
        animateWorkspaceBoxes();

        // Animate workspace boxes on scroll
        window.addEventListener('scroll', animateWorkspaceBoxes);

        // Add parallax effect to background
        window.addEventListener('scroll', function() {
            var scrolled = window.pageYOffset;
            document.body.style.backgroundPositionY = -(scrolled * 0.1) + 'px';
        });

        // Add smooth scrolling to internal links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>
