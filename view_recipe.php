<?php
$host = 'localhost';
$db = 'recipes';
$user = 'root'; // Replace with your database user
$pass = ''; // Replace with your database password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $id = $_GET['id'];

    $stmt = $pdo->prepare("SELECT name, picture, ingredients, steps, origin, submitted_by FROM recipes WHERE id = ?");
    $stmt->execute([$id]);
    $recipe = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$recipe) {
        die('<p>Recipe not found.</p>');
    }
} catch (PDOException $e) {
    die('<p>Database error: ' . $e->getMessage() . '</p>');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($recipe['name']); ?></title>
</head>
<body>
    <h1><?php echo htmlspecialchars($recipe['name']); ?></h1>
    <img src="<?php echo htmlspecialchars($recipe['picture']); ?>" alt="<?php echo htmlspecialchars($recipe['name']); ?>">
    <p><strong>Origin:</strong> <?php echo htmlspecialchars($recipe['origin']); ?></p>
    <p><strong>Submitted By:</strong> <?php echo htmlspecialchars($recipe['submitted_by']); ?></p>
    <h2>Ingredients</h2>
    <p><?php echo nl2br(htmlspecialchars($recipe['ingredients'])); ?></p>
    <h2>Steps</h2>
    <p><?php echo nl2br(htmlspecialchars($recipe['steps'])); ?></p>
</body>
</html>
