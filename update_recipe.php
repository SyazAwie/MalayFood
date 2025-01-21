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

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_recipe'])) {
    $recipeId = intval($_POST['id']);
    $name = $_POST['name'];
    $ingredients = $_POST['ingredients'];
    $steps = $_POST['steps'];
    $origin = $_POST['origin'];

    // Update the recipe in the database
    $sql = "UPDATE recipes SET name = :name, ingredients = :ingredients, steps = :steps, origin = :origin WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $recipeId, PDO::PARAM_INT);
    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
    $stmt->bindParam(':ingredients', $ingredients, PDO::PARAM_STR);
    $stmt->bindParam(':steps', $steps, PDO::PARAM_STR);
    $stmt->bindParam(':origin', $origin, PDO::PARAM_STR);

    if ($stmt->execute()) {
        // Redirect back to the recipe details page with a success message
        header("Location: recipe.php?id=$recipeId&message=Recipe updated successfully.");
        exit();
    } else {
        echo "Error updating the recipe.";
    }
} else {
    die("Invalid request.");
}
?>
