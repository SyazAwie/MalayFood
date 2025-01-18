<?php
session_start();
require 'db_connection.php'; // Include your database connection

// Check if user is logged in
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username']; // User's username
$role = $_SESSION['role']; // User's role

// Fetch Approved Recipes from the Past 7 Days for Admin
$recent_recipes = [];
if ($role === 'admin') {
    $query = "SELECT name, picture, ingredients, steps, submitted_by, DATE_FORMAT(updated_at, '%Y-%m-%d') as approved_date 
              FROM recipes 
              WHERE status = 'approved' AND updated_at >= NOW() - INTERVAL 7 DAY";
    $recent_recipes = $conn->query($query);

    if (!$recent_recipes) {
        die("Error fetching approved recipes: " . $conn->error);
    }
}

// Fetch Recipes Uploaded by the Logged-in User
$user_recipes = [];
if ($role === 'user') {
    $query = "SELECT id, name, picture, ingredients, steps, status, 
                     DATE_FORMAT(created_at, '%Y-%m-%d') as upload_date 
              FROM recipes 
              WHERE submitted_by = ?";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username); // Assuming $username is the logged-in user's username
    $stmt->execute();
    $user_recipes = $stmt->get_result();
    $stmt->close();
}


// Handle Recipe Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_recipe'])) {
    $name = htmlspecialchars($_POST['name']);
    $ingredients = htmlspecialchars($_POST['ingredients']);
    $steps = htmlspecialchars($_POST['steps']);
    $origin = htmlspecialchars($_POST['origin']);
    $submitted_by = $username;

    // Image Upload Handling
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $target_file = $target_dir . basename($_FILES["picture"]["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    if (getimagesize($_FILES["picture"]["tmp_name"])) {
        if (in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            if (move_uploaded_file($_FILES["picture"]["tmp_name"], $target_file)) {
                $status = ($role === 'admin') ? 'approved' : 'pending';
                $query = "INSERT INTO recipes (name, picture, ingredients, steps, origin, submitted_by, status) 
                          VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("sssssss", $name, $target_file, $ingredients, $steps, $origin, $submitted_by, $status);
                if ($stmt->execute()) {
                    $message = ($role === 'admin') ? "Recipe uploaded successfully." : "Recipe submitted successfully! Awaiting admin approval.";
                } else {
                    $error = "Error submitting recipe: " . $conn->error;
                }
                $stmt->close();
            } else {
                $error = "Error uploading the image file.";
            }
        } else {
            $error = "Only JPG, JPEG, PNG, and GIF files are allowed.";
        }
    } else {
        $error = "The file is not a valid image.";
    }
}

// Handle Recipe Approval or Rejection (Admin)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['recipe_id']) && $role === 'admin') {
    $recipe_id = (int)$_POST['recipe_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        $update_query = "UPDATE recipes SET status = 'approved', updated_at = NOW() WHERE id = ?";
    } elseif ($action === 'reject') {
        $update_query = "UPDATE recipes SET status = 'rejected', updated_at = NOW() WHERE id = ?";
    }

    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("i", $recipe_id);
    if ($stmt->execute()) {
        $admin_message = ($action === 'approve') ? "Recipe approved successfully." : "Recipe rejected successfully.";
    } else {
        $admin_error = "Error updating recipe status.";
    }
    $stmt->close();
    header("Location: dashboard.php");
    exit();
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="dashboardstyle.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
   
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

    <!-- Main Content -->
    <main>
        <h2>Dashboard Overview</h2>
        <p class="hello-text">Hello, <?php echo htmlspecialchars($username); ?> | Role: <strong><?php echo ucfirst($role); ?></strong></p>
            <!-- Upload Recipe Section (For Both Admin and Regular Users) -->
<section>
    <h3>Upload a New Recipe</h3>
    <?php if (isset($message)) echo "<p style='color:green;'>$message</p>"; ?>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

    <!-- Add Recipe Button -->
    <button id="showRecipeForm" class="toggle-btn">Add Recipe</button>

    <!-- Hidden Form -->
    <div id="recipeFormContainer" class="form-container" style="display: none;">
        <form action="dashboard.php" method="POST" enctype="multipart/form-data">
            <label for="name">Recipe Name:</label>
            <input type="text" id="name" name="name" required>
            <label for="ingredients">Ingredients:</label>
            <textarea id="ingredients" name="ingredients" rows="4" required></textarea>
            <label for="steps">Steps:</label>
            <textarea id="steps" name="steps" rows="4" required></textarea>

            <label for="origin">Origin (Region/State):</label>
            <select id="origin" name="origin" required>
                <option value="" disabled selected>Select a State</option>
                <option value="Johor">Johor</option>
                <option value="Kedah">Kedah</option>
                <option value="Kelantan">Kelantan</option>
                <option value="Melaka">Melaka</option>
                <option value="Negeri Sembilan">Negeri Sembilan</option>
                <option value="Pahang">Pahang</option>
                <option value="Perak">Perak</option>
                <option value="Perlis">Perlis</option>
                <option value="Pulau Pinang">Pulau Pinang</option>
                <option value="Sabah">Sabah</option>
                <option value="Sarawak">Sarawak</option>
                <option value="Selangor">Selangor</option>
                <option value="Terengganu">Terengganu</option>
                <option value="Kuala Lumpur">Kuala Lumpur</option>
                <option value="Labuan">Labuan</option>
                <option value="Putrajaya">Putrajaya</option>
            </select>

            <label for="picture">Upload Picture:</label>
            <input type="file" id="picture" name="picture" accept="image/*" required>

            <button type="submit" name="submit_recipe">Submit Recipe</button>
        </form>
    </div>
</section>

<?php
// Fetch Pending Recipes for Admin
$pending_recipes = [];
if ($role === 'admin') {
    $query = "SELECT id, name, picture, ingredients, steps, submitted_by, DATE_FORMAT(created_at, '%Y-%m-%d') as submit_date 
              FROM recipes 
              WHERE status = 'pending'";
    $pending_recipes = $conn->query($query);

    if (!$pending_recipes) {
        die("Error fetching pending recipes: " . $conn->error);
    }
}
?>

<!-- Admin Section to View Pending Recipes -->
<?php if ($role === 'admin'): ?>
    <section>
        <h3>Pending Recipes (To Approve or Reject)</h3>
        <?php if ($pending_recipes->num_rows > 0): ?>
            <table border="1" cellpadding="10">
                <thead>
                    <tr>
                        <th>Recipe Name</th>
                        <th>Picture</th>
                        <th>Ingredients</th>
                        <th>Steps</th>
                        <th>Submitted By</th>
                        <th>Submit Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($recipe = $pending_recipes->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($recipe['name']); ?></td>
                            <td><img src="<?php echo $recipe['picture']; ?>" alt="Recipe Picture" width="100"></td>
                            <td><?php echo htmlspecialchars($recipe['ingredients']); ?></td>
                            <td><?php echo htmlspecialchars($recipe['steps']); ?></td>
                            <td><?php echo htmlspecialchars($recipe['submitted_by']); ?></td>
                            <td><?php echo htmlspecialchars($recipe['submit_date']); ?></td>
                            <td>
                                <form action="dashboard.php" method="POST">
                                    <input type="hidden" name="recipe_id" value="<?php echo $recipe['id']; ?>">
                                    <button type="submit" name="action" value="approve" class="btn-approve">Approve</button>
                                    <button type="submit" name="action" value="reject" class="btn-reject">Reject</button>
                                </form>
                                <style>
                            .btn-approve {
                                background-color: green;
                                color: white;
                                border: none;
                                padding: 10px 20px;
                                cursor: pointer;
                            }
                            
                            .btn-approve:hover {
                                background-color: darkgreen;
                            }
                            
                            .btn-reject {
                                background-color: red;
                                color: white;
                                border: none;
                                padding: 10px 20px;
                                cursor: pointer;
                            }
                            
                            .btn-reject:hover {
                                background-color: darkred;
                            }
                        </style>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No pending recipes to approve/reject.</p>
        <?php endif; ?>
    </section>
<?php endif; ?>



        <!-- Admin: View Approved Recipes from the Past 7 Days -->
<?php if ($role === 'admin'): ?>
    <section>
        <h3>Approved Recipes in the Past 7 Days</h3>
        <?php if ($recent_recipes->num_rows > 0): ?>
            <table border="1" cellpadding="10">
                <thead>
                    <tr>
                        <th>Recipe Name</th>
                        <th>Picture</th>
                        <th>Ingredients</th>
                        <th>Steps</th>
                        <th>Submitted By</th>
                        <th>Approved Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($recipe = $recent_recipes->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($recipe['name']); ?></td>
                            <td><img src="<?php echo $recipe['picture']; ?>" alt="Recipe Picture" width="100"></td>
                            <td><?php echo htmlspecialchars($recipe['ingredients']); ?></td>
                            <td><?php echo htmlspecialchars($recipe['steps']); ?></td>
                            <td><?php echo htmlspecialchars($recipe['submitted_by']); ?></td>
                            <td><?php echo htmlspecialchars($recipe['approved_date']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No approved recipes in the past 7 days.</p>
        <?php endif; ?>
    </section>
<?php endif; ?>


<!-- User: View Uploaded Recipes -->
<?php if ($role === 'user'): ?>
    <section>
        <h3>My Uploaded Recipes</h3>
        <?php if ($user_recipes->num_rows > 0): ?>
            <table border="1" cellpadding="10">
                <thead>
                    <tr>
                        <th>Recipe Name</th>
                        <th>Picture</th>
                        <th>Ingredients</th>
                        <th>Steps</th>
                        <th>Status</th>
                        <th>Upload Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($recipe = $user_recipes->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($recipe['name']); ?></td>
                            <td><img src="<?php echo htmlspecialchars($recipe['picture']); ?>" alt="Recipe Picture" width="100"></td>
                            <td><?php echo htmlspecialchars($recipe['ingredients']); ?></td>
                            <td><?php echo htmlspecialchars($recipe['steps']); ?></td>
                            <td><?php echo htmlspecialchars(ucfirst($recipe['status'])); ?></td>
                            <td><?php echo htmlspecialchars($recipe['upload_date']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>You have not uploaded any recipes yet.</p>
        <?php endif; ?>
    </section>
<?php endif; ?>

        
    </main>

    <!-- Footer -->
    <footer>
        <p>&copy; <?php echo date("Y"); ?> Malay Traditional Food Heritage System. All Rights Reserved.</p>
    </footer>
    
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const navLinks = document.getElementById("navLinks");
        const showMenuBtn = document.querySelector(".fa-bars");
        const hideMenuBtn = document.querySelector(".fa-times");

    showMenuBtn.addEventListener("click", function () {
    navLinks.style.right = "0";
    });

    hideMenuBtn.addEventListener("click", function () {
    navLinks.style.right = "-250px";
    });
    });

</script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
    const toggleBtn = document.getElementById("showRecipeForm");
    const formContainer = document.getElementById("recipeFormContainer");

    toggleBtn.addEventListener("click", function () {
    // Toggle visibility
    if (formContainer.style.display === "none" || formContainer.style.display === "") {
        formContainer.style.display = "block";
    } else {
        formContainer.style.display = "none";
    }
    });
    });
</script>

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



