<?php
session_start();

// Database connection
$host = 'localhost';
$dbname = 'malay_traditional_food_heritage_system';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Check if admin is logged in
$isAdmin = isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'moderator');


if (!$isAdmin) {
    die("Access denied.");
}

if (isset($_GET['id'])) {
    $recipeId = intval($_GET['id']);

    // Delete the recipe from the database
    $sql = "DELETE FROM recipes WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $recipeId, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        // Successfully deleted the recipe, redirect with a success message
        header("Location: all_recipes.php?message=Recipe deleted successfully.");
        exit();
    } else {
        echo "Error deleting the recipe.";
    }
} else {
    echo "Invalid recipe ID.";
}
?>
