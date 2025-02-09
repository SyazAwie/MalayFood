<?php
session_start(); // Start the session

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

// Check if admin is logged in
$isAdmin = isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'moderator');


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
    <link rel="stylesheet" href="recipestyle.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 12px;
            margin: 5px;
            font-size: 16px;
            font-weight: bold;
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
        }

        .action-buttons a {
            text-decoration: none;
            font-size: 28px; /* Match share buttons */
            color: #555;
            transition: color 0.3s ease-in-out;
        }

        .action-buttons a:hover {
            color: #0073e6;
        }

        /* Specific colors for each button */
        #updateBtn {
            color:rgb(239, 246, 241); /* Green for Update */
        }

        #updateBtn:hover {
            color:rgb(15, 15, 15);
        }

        #deleteBtn {
            color:rgb(246, 244, 244); /* Red for Delete */
        }

        #deleteBtn:hover {
            color:rgb(14, 13, 13);
        }


        .share-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
        }

        .share-buttons a {
            text-decoration: none;
            font-size: 28px;
            color: #555;
            transition: color 0.3s ease-in-out;
        }

        .share-buttons a:hover {
            color: #0073e6;
        }


    </style>

</head>
<body>
 <!-- Header Section -->
<?php
$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in']; // Check login state
?>
            <nav>
                <ul class='sidebar'>
                <li onclick=hideSidebar()><a href="#"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="m256-200-56-56 224-224-224-224 56-56 224 224 224-224 56 56-224 224 224 224-56 56-224-224-224 224Z"/></svg></a></li>
                    
                    <li><a href="home.php">Home</a></li>
                    <li><a href="all_recipes.php">All Recipes</a></li>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="about.php">About</a></li>

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
                    <li class="hideOnMobile"><a href="about.php">About</a></li>

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
    <div id="printableRecipe">
        <h2 class="recipe-title"><?php echo htmlspecialchars($recipe['name']); ?></h2>
        <img src="<?php echo htmlspecialchars($recipe['picture']); ?>" alt="<?php echo htmlspecialchars($recipe['name']); ?>" class="recipe-image">
        <p><strong>Origin:</strong><?php echo htmlspecialchars($recipe['origin']); ?></p>
        <p><strong>By:</strong> <?php echo htmlspecialchars($recipe['submitted_by']); ?></p>
        <br/>
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
            <!-- Share, Copy Link & Print Buttons -->
            <div class="share-buttons">
                <a href="#" id="whatsappBtn" title="Share on WhatsApp"><i class="fab fa-whatsapp"></i></a>
                <a href="#" id="telegramBtn" title="Share on Telegram"><i class="fab fa-telegram"></i></a>
                <a href="#" id="facebookBtn" title="Share on Facebook"><i class="fab fa-facebook"></i></a>
                <a href="#" id="twitterBtn" title="Share on Twitter"><i class="fab fa-x-twitter"></i></a>
                <a href="#" id="emailBtn" title="Share via Email"><i class="fas fa-envelope"></i></a>
                <a href="#" id="copyLinkBtn" title="Copy Link"><i class="fas fa-link"></i></a>
                <a href="#" id="printBtn" title="Print Recipe"><i class="fas fa-print"></i></a>
            </div>


        <?php if ($isAdmin): ?>
            <!-- Admin Update and Delete Buttons -->
            <div class="action-buttons">
                <a href="#" id="updateBtn" title="Update Recipe" onclick="toggleUpdateForm()">
                    <i class="fas fa-edit"></i>
                </a>
                <a href="#" id="deleteBtn" title="Delete Recipe" onclick="confirmDelete(<?php echo $recipe['id']; ?>)">
                    <i class="fas fa-trash-alt"></i>
                </a>
            </div>
            <!-- Update Recipe Form (Initially hidden) -->
            <div id="updateForm" style="display:none;">
                <h3>Update Recipe</h3>
                <form action="update_recipe.php" method="POST">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($recipe['id']); ?>">
                    <label for="name">Recipe Name:</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($recipe['name']); ?>" required>
                    
                    <label for="submitted_by">Recipe By:</label>
                    <input type="text" id="submitted_by" name="submitted_by" value="<?php echo htmlspecialchars($recipe['submitted_by']); ?>" required>

                    <label for="ingredients">Ingredients:</label>
                    <textarea id="ingredients" name="ingredients" required><?php echo htmlspecialchars($recipe['ingredients']); ?></textarea>
                    
                    <label for="steps">Steps:</label>
                    <textarea id="steps" name="steps" required><?php echo htmlspecialchars($recipe['steps']); ?></textarea>
                    
                    <label for="origin">Origin:</label>
                    <input type="text" id="origin" name="origin" value="<?php echo htmlspecialchars($recipe['origin']); ?>" required>

                    <button type="submit" name="update_recipe">Update Recipe</button>
                </form>
            </div>
        <?php endif; ?>


    </div>
</div>

<footer>
    <p>&copy; <?php echo date("Y"); ?> Malay Traditional Food Heritage System. All Rights Reserved.</p>
</footer>

<script>
    function toggleUpdateForm() {
        var form = document.getElementById("updateForm");
        form.style.display = (form.style.display === "none") ? "block" : "none";
    }

    function confirmDelete(id) {
            // Use JavaScript confirmation box
            var isConfirmed = confirm("Are you sure you want to delete this recipe?");
            if (isConfirmed) {
                // If confirmed, redirect to delete the recipe
                window.location.href = "delete_recipe.php?id=" + id;
            }
        }
</script>
<script>
        // Show confirmation message after the recipe is updated
        window.onload = function() {
            var urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('message')) {
                var message = urlParams.get('message');
                if (message) {
                    // Display confirmation
                    var isConfirmed = confirm(message);
                    if (isConfirmed) {
                        // Do something after the admin clicks OK
                        // In this case, we just redirect to the recipe details page again
                        window.location.href = "recipe.php?id=<?php echo $recipe['id']; ?>";
                    }
                }
            }
        }
</script>

<script>
    // Get current page URL
    const recipeUrl = window.location.href;
    const recipeTitle = "<?php echo addslashes($recipe['name']); ?>";

    // WhatsApp Share
    document.getElementById("whatsappBtn").addEventListener("click", function() {
        window.open(`https://wa.me/?text=${encodeURIComponent(recipeTitle + " - " + recipeUrl)}`, "_blank");
    });

    // Telegram Share
    document.getElementById("telegramBtn").addEventListener("click", function() {
        window.open(`https://t.me/share/url?url=${encodeURIComponent(recipeUrl)}&text=${encodeURIComponent(recipeTitle)}`, "_blank");
    });

    // Facebook Share
    document.getElementById("facebookBtn").addEventListener("click", function() {
        window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(recipeUrl)}`, "_blank");
    });

    // Twitter Share
    document.getElementById("twitterBtn").addEventListener("click", function() {
        window.open(`https://twitter.com/intent/tweet?text=${encodeURIComponent(recipeTitle)}&url=${encodeURIComponent(recipeUrl)}`, "_blank");
    });

    // Email Share
    document.getElementById("emailBtn").addEventListener("click", function() {
        window.location.href = `mailto:?subject=${encodeURIComponent("Check out this recipe: " + recipeTitle)}&body=${encodeURIComponent(recipeUrl)}`;
    });

    // Copy Link to Clipboard
    document.getElementById("copyLinkBtn").addEventListener("click", function() {
        navigator.clipboard.writeText(recipeUrl).then(() => {
            alert("Recipe link copied to clipboard!");
        }).catch(err => {
            console.error("Error copying link: ", err);
        });
    });

    // Print Recipe
    document.getElementById("printBtn").addEventListener("click", function() {
        var printContents = document.getElementById("printableRecipe").innerHTML;
        var printWindow = window.open('', '', 'width=800,height=600');

        printWindow.document.write(`
            <html>
            <head>
                <title>Print Recipe</title>
                <style>
                    body { font-family: Arial, sans-serif; text-align: center; padding: 20px; }
                    .recipe-title { font-size: 24px; font-weight: bold; margin-bottom: 10px; }
                    
                    /* Set fixed image size for printing */
                    .recipe-image { 
                        width: 200px;  /* Adjust width */
                        height: 200px; /* Adjust height */
                        object-fit: cover; /* Ensure image scales properly */
                        margin: 10px 0; 
                        border-radius: 10px; 
                    }

                    ul { text-align: left; margin: 0 auto; max-width: 400px; }
                    strong { font-weight: bold; }
                </style>
            </head>
            <body>
                ${printContents}
                <script>
                    window.onload = function() { window.print(); window.close(); }
                <\/script>
            </body>
            </html>
        `);

        printWindow.document.close();
    });
</script>

</body>
</html>
