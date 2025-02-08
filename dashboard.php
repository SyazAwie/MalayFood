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

// Total Recipes Uploaded
$query_total = "SELECT COUNT(*) as total_recipes FROM recipes";
$result_total = $conn->query($query_total);
$total_recipes = ($result_total->num_rows > 0) ? $result_total->fetch_assoc()['total_recipes'] : 0;

// Pending Approval Recipes
$query_pending = "SELECT COUNT(*) as pending_recipes FROM recipes WHERE status = 'pending'";
$result_pending = $conn->query($query_pending);
$pending_recipes = ($result_pending->num_rows > 0) ? $result_pending->fetch_assoc()['pending_recipes'] : 0;

// Approved Recipes
$query_approved = "SELECT COUNT(*) as approved_recipes FROM recipes WHERE status = 'approved'";
$result_approved = $conn->query($query_approved);
$approved_recipes = ($result_approved->num_rows > 0) ? $result_approved->fetch_assoc()['approved_recipes'] : 0;

// Fetch Recipe Stats for Logged-in User
if ($role === 'user') {
    $query = "SELECT 
                COUNT(*) AS user_total_recipes,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS user_pending_recipes,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) AS user_approved_recipes
              FROM recipes
              WHERE submitted_by = ?";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($user_total_recipes, $user_pending_recipes, $user_approved_recipes);
    $stmt->fetch();
    $stmt->close();
}

// Fetch Approved Recipes from the Past 7 Days for Admin
$recent_recipes = [];
if ($role === 'admin' || $role === 'moderator') {
    $query = "SELECT id, name, picture, ingredients, steps, submitted_by, DATE_FORMAT(updated_at, '%Y-%m-%d') as approved_date 
              FROM recipes 
              WHERE status = 'approved' AND updated_at >= NOW() - INTERVAL 7 DAY
              ORDER BY created_at DESC";
    $recent_recipes = $conn->query($query);

    if (!$recent_recipes) {
        die("Error fetching approved recipes: " . $conn->error);
    }
}

// Fetch Recipes Uploaded by the Logged-in User
$user_recipes = [];
if ($role === 'user') {
    $query = "SELECT id, name, picture, ingredients, steps, status, rejection_comment, 
                     DATE_FORMAT(created_at, '%Y-%m-%d') as upload_date 
              FROM recipes 
              WHERE submitted_by = ?
              ORDER BY created_at DESC";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username); // Assuming $username is the logged-in user's username
    $stmt->execute();
    $user_recipes = $stmt->get_result();
    $stmt->close();
}


// Handle Recipe Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit_recipe"])) {

    $name = $_POST["name"];
    $ingredients = $_POST["ingredients"];
    $steps = $_POST["steps"];
    $origin = $_POST["origin"];
    
    // Check user role from session (Assuming role is stored in session)
    $role = $_SESSION['role'] ?? 'user';
    $user_id = $_SESSION['user_id'] ?? null;

    // Determine the submitted_by field
    if ($role === 'admin' || $role === 'moderator') {
        $submitted_by = $_POST["submitted_by"]; // Admins & Moderators input manually
    } else {
        $submitted_by = $user_id; // Regular users use their ID
    }

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
                $status = ($role === 'admin' || $role === 'moderator') ? 'approved' : 'pending';

                $query = "INSERT INTO recipes (name, picture, ingredients, steps, origin, submitted_by, status) 
                          VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("sssssss", $name, $target_file, $ingredients, $steps, $origin, $submitted_by, $status);

                if ($stmt->execute()) {
                    $message = ($role === 'admin') ? "Recipe uploaded successfully." : "Recipe submitted successfully! Awaiting admin approval.";
                    header("Location: dashboard.php");
                    exit();
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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['recipe_id']) && ($role === 'admin' || $role === 'moderator')) {
    $recipe_id = (int)$_POST['recipe_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        // Approve recipe query
        $update_query = "UPDATE recipes SET status = 'approved', updated_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("i", $recipe_id);
    } elseif ($action === 'reject' && isset($_POST['rejection_comment'])) {
        // Reject recipe query with rejection comment
        $rejection_comment = $_POST['rejection_comment'];
        $update_query = "UPDATE recipes SET status = 'rejected', rejection_comment = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("si", $rejection_comment, $recipe_id);
    }

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
    <style>
        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 16px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        table thead {
            background-color: #f4f4f4;
        }

        table th, table td {
            border: 1px solid #ddd;
            padding: 10px;
        }

        table th {
            font-weight: bold;
            background-color: #f9f9f9;
        }

        table td img {
            display: block;
            margin: 0 auto;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            color:rgb(0, 4, 255);
        }

        h3 {
            text-align: center;
            margin-bottom: 20px;
        }

        #popup {
            text-align: center;
            z-index: 1000;
        }

        button {
            padding: 8px 16px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            table {
                font-size: 14px;
            }

            table th, table td {
                padding: 8px;
            }

            table td img {
                width: 80%; /* Adjust image size */
            }
        }

        @media (max-width: 480px) {
            table {
                font-size: 12px;
            }

            table th, table td {
                padding: 6px;
            }

            table td img {
                width: 100%; /* Full width on small screens */
            }

            /* Make the table scrollable horizontally */
            table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }

            /* Optional: Center the horizontal scroll bar */
            table::-webkit-scrollbar {
                height: 8px;
            }

            table::-webkit-scrollbar-thumb {
                background-color: #007bff;
                border-radius: 4px;
            }

            table::-webkit-scrollbar-track {
                background-color: #f1f1f1;
            }
        }
    </style>
    <style>
        /* Common styles for both buttons */
        button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 25px;
            margin: 5px;
            font-size: 16px;
            font-weight: bold;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        /* Approve button styles */
        .btn-approve {
            background-color: #28a745; /* Light green */
        }

        .btn-approve:hover {
            background-color: #218838; /* Dark green */
            transform: translateY(-2px); /* Adds a subtle hover effect */
        }

        .btn-approve:active {
            background-color: #1e7e34; /* Active state green */
            transform: translateY(0);
        }

        /* Reject button styles */
        .btn-reject {
            background-color: #dc3545; /* Light red */
        }

        .btn-reject:hover {
            background-color: #c82333; /* Dark red */
            transform: translateY(-2px); /* Adds a subtle hover effect */
        }

        .btn-reject:active {
            background-color: #bd2130; /* Active state red */
            transform: translateY(0);
        }

        /* Optional: Add icon alignment */
        button i {
            margin-right: 8px;
        }
    </style>
</head>
<body>
 <!-- Header Section -->
 <?php $isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in']; // Check login state?>
            <nav>
                <ul class='sidebar'>
                <li onclick=hideSidebar()><a href="#"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="m256-200-56-56 224-224-224-224 56-56 224 224 224-224 56 56-224 224 224 224-56 56-224-224-224 224Z"/></svg></a></li>
                    
                    <li><a href="home.php">Home</a></li>
                    <li><a href="all_recipes.php">All Recipes</a></li>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <?php if ($role === 'moderator'): ?>
                        <li><a href="report.php">Report</a></li>
                    <?php endif; ?>
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
                    <?php if ($role === 'moderator'): ?>
                        <li class="hideOnMobile"><a href="report.php">Report</a></li>
                    <?php endif; ?>
                    <li class="hideOnMobile"><a href="about.php">About</a></li>

                    <?php if ($isLoggedIn): ?>
                        <li class="hideOnMobile"><a href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li class="hideOnMobile"><a href="login.php">Login</a></li>
                    <?php endif; ?>

                    <li class="menu-button" onclick=showSidebar()><a href="#"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M120-240v-80h720v80H120Zm0-200v-80h720v80H120Zm0-200v-80h720v80H120Z"/></svg></a></li>

                </ul>

            </nav>

<div class="main-container">
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
            <?php if ($role === 'admin' || $role === 'moderator'): ?>
                <label for="submitted_by">By:</label>
                <input type="text" id="submitted_by" name="submitted_by" required>
            <?php endif; ?>
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
if ($role === 'admin' || $role === 'moderator') {
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
<?php if ($role === 'admin' || $role === 'moderator'): ?>
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
                            <td><?php echo htmlspecialchars($recipe['name']);?></td>
                            <td><img src="<?php echo $recipe['picture']; ?>" alt="Recipe Picture" width="100"></td>
                            
                            <td>
                                <a href="javascript:void(0);" class="view-ingredients" 
                                data-content="<?php echo htmlspecialchars(json_encode($recipe['ingredients']), ENT_QUOTES, 'UTF-8'); ?>" 
                                data-title="Ingredients">
                                    View
                                </a>
                            </td>
                            <td>
                                <a href="javascript:void(0);" class="view-steps" 
                                data-content="<?php echo htmlspecialchars(json_encode($recipe['steps']), ENT_QUOTES, 'UTF-8'); ?>" 
                                data-title="Steps">
                                    View
                                </a>
                            </td>

                            <td><?php echo htmlspecialchars($recipe['submitted_by']); ?></td>
                            <td><?php echo htmlspecialchars($recipe['submit_date']); ?></td>
                            <td>
                                <form action="dashboard.php" method="POST">
                                    <input type="hidden" name="recipe_id" value="<?php echo $recipe['id']; ?>">
                                    <button type="submit" name="action" value="approve" class="btn-approve">Approve</button>
                                    <button type="button" class="btn-reject" onclick="openRejectionPopup(<?php echo $recipe['id']; ?>)">Reject</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No pending recipes to approve/reject.</p>
        <?php endif; ?>
    </section>
    <!-- Rejection Popup -->
    <div id="rejectionPopup" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%); background:#fff; border:1px solid #ccc; padding:20px; z-index:1000; width: 400px;">
        <h3>Rejection Comment</h3>
        <form id="rejectionForm" method="POST" action="dashboard.php">
            <input type="hidden" name="recipe_id" id="rejectionRecipeId">
            <textarea name="rejection_comment" id="rejectionComment" rows="4" placeholder="Enter the reason for rejection" style="width:100%;"></textarea>
            <button type="submit" name="action" value="reject" style="background:red; color:#fff; padding:10px 20px; border:none; cursor:pointer;">Submit</button>
            <button type="button" onclick="closeRejectionPopup()" style="background:gray; color:#fff; padding:10px 20px; border:none; cursor:pointer;">Cancel</button>
        </form>
    </div>

    <div id="popup" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%); padding:20px; background-color:#fff; border:1px solid #ccc;">
        <h4 id="popup-title"></h4>
        <p id="popup-content"></p>
        <button onclick="closePopup()">Close</button>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.view-ingredients, .view-steps').forEach(link => {
                link.addEventListener('click', function () {
                    const content = JSON.parse(this.getAttribute('data-content'));
                    const title = this.getAttribute('data-title');
                    showPopup(content, title);
                });
            });
        });

        function showPopup(content, title) {
            document.getElementById('popup-title').textContent = title;
            document.getElementById('popup-content').textContent = Array.isArray(content) ? content.join(', ') : content;
            document.getElementById('popup').style.display = 'block';
        }

        function closePopup() {
            document.getElementById('popup').style.display = 'none';
        }
    </script>

<?php endif; ?>



<!-- Admin: View Approved Recipes from the Past 7 Days -->
<?php if ($role === 'admin' || $role === 'moderator'): ?>
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
                            <td>
                                <a href="recipe.php?id=<?php echo urlencode($recipe['id']); ?>">
                                    <?php echo htmlspecialchars($recipe['name']);?>
                                </a>
                            </td>

                            <td><img src="<?php echo $recipe['picture']; ?>" alt="Recipe Picture" width="100"></td>
                            
                            <td>
                                <a href="javascript:void(0);" class="view-ingredients" 
                                data-content="<?php echo htmlspecialchars(json_encode($recipe['ingredients']), ENT_QUOTES, 'UTF-8'); ?>" 
                                data-title="Ingredients">
                                    View
                                </a>
                            </td>
                            <td>
                                <a href="javascript:void(0);" class="view-steps" 
                                data-content="<?php echo htmlspecialchars(json_encode($recipe['steps']), ENT_QUOTES, 'UTF-8'); ?>" 
                                data-title="Steps">
                                    View
                                </a>
                            </td>

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
    <div id="popup" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%); padding:20px; background-color:#fff; border:1px solid #ccc;">
        <h4 id="popup-title"></h4>
        <p id="popup-content"></p>
        <button onclick="closePopup()">Close</button>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.view-ingredients, .view-steps').forEach(link => {
                link.addEventListener('click', function () {
                    const content = JSON.parse(this.getAttribute('data-content'));
                    const title = this.getAttribute('data-title');
                    showPopup(content, title);
                });
            });
        });

        function showPopup(content, title) {
            document.getElementById('popup-title').textContent = title;
            document.getElementById('popup-content').textContent = Array.isArray(content) ? content.join(', ') : content;
            document.getElementById('popup').style.display = 'block';
        }

        function closePopup() {
            document.getElementById('popup').style.display = 'none';
        }
    </script>
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
                            <td>
                                <a href="recipe.php?id=<?php echo htmlspecialchars($recipe['id']); ?>">
                                    <?php echo htmlspecialchars($recipe['name']); ?>
                                </a>
                            </td>
                            
                            <td><img src="<?php echo htmlspecialchars($recipe['picture']); ?>" alt="Recipe Picture" width="100"></td>
                           
                            <td>
                                <a href="javascript:void(0);" class="view-ingredients" 
                                data-content="<?php echo htmlspecialchars(json_encode($recipe['ingredients']), ENT_QUOTES, 'UTF-8'); ?>" 
                                data-title="Ingredients">
                                    View
                                </a>
                            </td>
                            <td>
                                <a href="javascript:void(0);" class="view-steps" 
                                data-content="<?php echo htmlspecialchars(json_encode($recipe['steps']), ENT_QUOTES, 'UTF-8'); ?>" 
                                data-title="Steps">
                                    View
                                </a>
                            </td>

                            <td>
                                <?php if ($recipe['status'] === 'rejected' && !empty($recipe['rejection_comment'])): ?>
                                    <a href="javascript:void(0);" class="view-rejection-comment" 
                                    data-content="<?php echo htmlspecialchars($recipe['rejection_comment'], ENT_QUOTES, 'UTF-8'); ?>" 
                                    data-title="Rejection Comment">
                                        Rejected
                                    </a>
                                <?php else: ?>
                                    <?php echo htmlspecialchars(ucfirst($recipe['status'])); ?>
                                <?php endif; ?>
                            </td>

                            <td><?php echo htmlspecialchars($recipe['upload_date']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>You have not uploaded any recipes yet.</p>
        <?php endif; ?>
    </section>

        <!-- Combined Popup for Rejection Comment or General Content -->
        <div id="popup" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%); padding:20px; background-color:#fff; border:1px solid #ccc;">
            <h4 id="popup-title"></h4>
            <p id="popup-content"></p>
            <button onclick="closePopup()">Close</button>
        </div>


        <script>
            document.addEventListener('DOMContentLoaded', () => {
                // Open popup to show rejection comment
                document.querySelectorAll('.view-rejection-comment').forEach(link => {
                    link.addEventListener('click', function () {
                        const content = this.getAttribute('data-content');
                        const title = this.getAttribute('data-title');
                        showPopup(content, title, 'rejection');
                    });
                });

                // Existing view ingredients and steps logic
                document.querySelectorAll('.view-ingredients, .view-steps').forEach(link => {
                    link.addEventListener('click', function () {
                        const content = JSON.parse(this.getAttribute('data-content'));
                        const title = this.getAttribute('data-title');
                        showPopup(content, title, 'general');
                    });
                });
            });

            // Show popup for rejection or general content
            function showPopup(content, title, type) {
                document.getElementById('popup-title').textContent = title;

                // Customize content based on type
                if (type === 'rejection') {
                    document.getElementById('popup-content').textContent = content;
                } else if (type === 'general') {
                    document.getElementById('popup-content').textContent = Array.isArray(content) ? content.join(', ') : content;
                }

                document.getElementById('popup').style.display = 'block';
            }

            // Close the popup
            function closePopup() {
                document.getElementById('popup').style.display = 'none';
            }

        </script>

<?php endif; ?>
    </main>
    <div class="aside-panels-container">
        <?php if ($role === 'user'): ?>
            <div class="aside-panel">
                <h3>Recipe Stats</h3>
                <ul>
                    <li><strong>Total Recipes Uploaded:</strong> <?php echo ($role === 'admin' || $role === 'moderator') ? $total_recipes : $user_total_recipes; ?></li>
                    <li><strong>Pending Approval:</strong> <?php echo ($role === 'admin' || $role === 'moderator') ? $pending_recipes : $user_pending_recipes; ?></li>
                    <li><strong>Approved Recipes:</strong> <?php echo ($role === 'admin' || $role === 'moderator') ? $approved_recipes : $user_approved_recipes; ?></li>
                </ul>
            </div>

            <!-- Recent Activity Log Section -->
            <div class="aside-panel">
                <h3>Recent Activity Log</h3>
                <ul>
                    <?php
                        // Fetch recent activity for the logged-in user
                        $query_activity = "SELECT name, status, DATE_FORMAT(created_at, '%Y-%m-%d') AS activity_date 
                                        FROM recipes 
                                        WHERE submitted_by = ? 
                                        ORDER BY created_at DESC LIMIT 5";
                        $stmt = $conn->prepare($query_activity);
                        $stmt->bind_param("s", $username);
                        $stmt->execute();
                        $result_activity = $stmt->get_result();

                        if ($result_activity->num_rows > 0) {
                            while ($row = $result_activity->fetch_assoc()) {
                                echo "<li><strong>Recipe: </strong>" . htmlspecialchars($row['name']).
                                     " -<strong> Status: </strong>" . ucfirst($row['status'])." </li> <br>";
                            }
                        } else {
                            echo "<li>No recent activity found.</li>";
                        }
                        $stmt->close();
                    ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>


</div>

    

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

<script>
    function openRejectionPopup(recipeId) {
        document.getElementById('rejectionRecipeId').value = recipeId;
        document.getElementById('rejectionPopup').style.display = 'block';
    }

    function closeRejectionPopup() {
        document.getElementById('rejectionPopup').style.display = 'none';
    }
</script>
</body>
</html>



