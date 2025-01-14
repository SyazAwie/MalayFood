<?php
// Database connection
$host = 'localhost';
$db = 'recipes';
$user = 'root'; // Replace with your database user
$pass = ''; // Replace with your database password

header('Content-Type: application/json');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch only approved recipes
    $stmt = $pdo->prepare("SELECT id, name, picture, ingredients, steps FROM recipes WHERE status = 'approved'");
    $stmt->execute();
    $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return recipes as JSON
    echo json_encode($recipes);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
