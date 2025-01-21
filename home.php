<?php
// Database connection
$host = 'localhost';
$dbname = 'malay_traditional_food_heritage_system'; // Update with your actual database name
$username = 'root'; // Update with your MySQL username
$password = ''; // Update with your MySQL password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Fetch latest six approved recipes
$sql = "SELECT * FROM recipes WHERE status = 'approved' ORDER BY created_at DESC LIMIT 6";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle search query
$searchResults = [];
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchTerm = '%' . $_GET['search'] . '%';
    $searchSql = "SELECT * FROM recipes WHERE status = 'approved' AND (name LIKE :searchTerm OR origin LIKE :searchTerm)";
    $searchStmt = $pdo->prepare($searchSql);
    $searchStmt->bindParam(':searchTerm', $searchTerm, PDO::PARAM_STR);
    $searchStmt->execute();
    $searchResults = $searchStmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Malaysian Traditional Recipes</title>
    <link rel="stylesheet" href=homestyle.css>
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
                    <li><a href="#">Contact</a></li>

                    <?php if ($isLoggedIn): ?>
                        <li><a href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                    <?php endif; ?>

                </ul>

                <ul>
                    <li><a href="home.php">Delicious Recipe</a></li>
                    <li class="hideOnMobile"><a href="home.php">Home</a></li>
                    <li class="hideOnMobile"><a href="all_recipes.php">All Recipes</a></li>
                    <li class="hideOnMobile"><a href="dashboard.php">Dashboard</a></li>
                    <li class="hideOnMobile"><a href="#">Contact</a></li>

                    <?php if ($isLoggedIn): ?>
                        <li class="hideOnMobile"><a href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li class="hideOnMobile"><a href="login.php">Login</a></li>
                    <?php endif; ?>

                    <li class="menu-button" onclick=showSidebar()><a href="#"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M120-240v-80h720v80H120Zm0-200v-80h720v80H120Zm0-200v-80h720v80H120Z"/></svg></a></li>

                </ul>

            </nav>


    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h2>Welcome to Malay Traditional Recipes!</h2>
            <p>Discover and share mouth-watering traditional recipes from Malaysia.</p>
        </div>
        <!-- Search Section -->
    <section class="search-bar">
        <form method="GET" action="">
            <div class="search-box">
                <input type="text" name="search" placeholder="Search for a recipe or origin..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button type="submit">Search</button>
            </div>
        </form>
    </section>
    </section>

    <!-- Search Results -->
    <?php if (isset($_GET['search']) && !empty($_GET['search'])): ?>
    <?php if (!empty($searchResults)): ?>
        <section class="search-results">
            <h1>Search Results</h1>
            <br>
            <div class="recipe-section">
                <?php foreach ($searchResults as $recipe): ?>
                    <div class="recipe-card">
                        <img src="<?php echo htmlspecialchars($recipe['picture']); ?>" alt="<?php echo htmlspecialchars($recipe['name']); ?>">
                        <h2><?php echo htmlspecialchars($recipe['name']); ?></h2>
                        <p><strong>Origin:</strong> <?php echo htmlspecialchars($recipe['origin']); ?></p>
                        <p><strong>By:</strong> <?php echo htmlspecialchars($recipe['submitted_by']); ?></p>
                        <a href="recipe.php?id=<?php echo $recipe['id']; ?>">View Recipe</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
<?php else: ?>
    <section class="search-results">
        <h1>No Results Found</h1>
        <p>Sorry, no recipes were found matching your search. Please try again with different keywords.</p>
    </section>
<?php endif; ?>
<?php endif; ?>

    <!-- Latest Recipes Section -->
    <section class="recipes">
        <h1>Latest Recipes</h1>
        <div class="recipe-section">
            <?php if (!empty($recipes)): ?>
                <?php foreach ($recipes as $recipe): ?>
                    <div class="recipe-card">
                        <img src="<?php echo htmlspecialchars($recipe['picture']); ?>" alt="<?php echo htmlspecialchars($recipe['name']); ?>">
                        <h2><?php echo htmlspecialchars($recipe['name']); ?></h2>
                        <p><strong>Origin:</strong> <?php echo htmlspecialchars($recipe['origin']); ?></p>
                        <p><strong>By:</strong> <?php echo htmlspecialchars($recipe['submitted_by']); ?></p>
                        <a href="recipe.php?id=<?php echo $recipe['id']; ?>">View Recipe</a>
                    </div>
                <?php endforeach; ?>

            <?php else: ?>
                <p>No recipes found.</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer Section -->
    <footer>
        <p>&copy; <?php echo date("Y"); ?> Malay Traditional Food Heritage System. All Rights Reserved.</p>
    </footer>

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
</body>
</html>
