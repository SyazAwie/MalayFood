<?php
// Database connection
$host = 'localhost';
$dbname = 'malay_traditional_food_heritage_system'; // Your database name
$username = 'root'; // Your MySQL username
$password = ''; // Your MySQL password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Get recipe ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Recipe ID not specified.");
}

$recipeId = intval($_GET['id']);

// Fetch recipe details
$sql = "SELECT * FROM recipes WHERE id = :id AND status = 'approved'";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id', $recipeId, PDO::PARAM_INT);
$stmt->execute();
$recipe = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$recipe) {
    die("Recipe not found or not approved.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($recipe['name']); ?></title>
    <link rel="stylesheet" href="recipe.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>
<body>
 <!-- Header Section -->
<?php
session_start(); // Start the session
$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in']; // Check login state
?>
<nav>
                <ul class='sidebar'>
                <li onclick=hideSidebar()><a href="#"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="m256-200-56-56 224-224-224-224 56-56 224 224 224-224 56 56-224 224 224 224-56 56-224-224-224 224Z"/></svg></a></li>
                    
                    <li><a href="home.php">Home</a></li>
                    <li><a href="all_recipes.php">All Recipes</a></li>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="recipe_report.php">About</a></li>
                    <li><a href="#">Contact</a></li>

                    <?php if ($isLoggedIn): ?>
                        <li><a href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                    <?php endif; ?>

                </ul>

                <ul>
                    <li><a href="home.php">Delicious Recipe</a></li>
                    <li class="hideOnMobile"><a href="all_recipes.php">All Recipes</a></li>
                    <li class="hideOnMobile"><a href="dashboard.php">Dashboard</a></li>
                    <li class="hideOnMobile"><a href="recipe_report.php">About</a></li>
                    <li class="hideOnMobile"><a href="#">Contact</a></li>

                    <?php if ($isLoggedIn): ?>
                        <li class="hideOnMobile"><a href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li class="hideOnMobile"><a href="login.php">Login</a></li>
                    <?php endif; ?>

                    <li class="menu-button" onclick=showSidebar()><a href="#"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M120-240v-80h720v80H120Zm0-200v-80h720v80H120Zm0-200v-80h720v80H120Z"/></svg></a></li>

                </ul>

            </nav>

<!-- JavaScript -->
<script>
function showSidebar(){
    const sidebar = document.querySelector('.sidebar')
        sidebar.style.display = 'flex'
}
function hideSidebar(){
    const sidebar = document.querySelector('.sidebar')
        sidebar.style.display = 'none'
        
}
</script>

<script>
    // Select elements
const nav = document.querySelector('nav');
const openBtn = document.querySelector('.fa-bars');
const closeBtn = document.querySelector('.fa-times');

// Open navigation
openBtn.addEventListener('click', () => {
    nav.classList.add('visible'); // Show the navigation
    openBtn.classList.add('hidden'); // Hide the .fa-bars
});

// Close navigation
closeBtn.addEventListener('click', () => {
    nav.classList.remove('visible'); // Hide the navigation
    openBtn.classList.remove('hidden'); // Show the .fa-bars
});

</script>

<div class="container">
    <div class="recipe-details">
        <h2 class="recipe-title"><?php echo htmlspecialchars($recipe['name']); ?></h2>
        <img src="<?php echo htmlspecialchars($recipe['picture']); ?>" alt="<?php echo htmlspecialchars($recipe['name']); ?>" class="recipe-image">
        <p class="recipe-origin">Origin: <?php echo htmlspecialchars($recipe['origin']); ?></p>
        
        <h3>Ingredients:</h3>
        <ul>
            <?php
            $ingredients = explode("\n", $recipe['ingredients']);
            foreach ($ingredients as $ingredient) {
                echo "<li>" . htmlspecialchars($ingredient) . "</li>";
            }
            ?>
        </ul>
        <h3>Preparation Steps:</h3>
        <ul>
            <?php
            $steps = explode("\n", $recipe['steps']);
            foreach ($steps as $step) {
                echo "<li>" . htmlspecialchars($step) . "</li>";
            }
            ?>
        </ul>
    </div>
</div>


<footer>
    <p>&copy; <?php echo date("Y"); ?> Malay Traditional Food Heritage System. All Rights Reserved.</p>
</footer>
</body>
</html>
