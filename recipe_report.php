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

// Fetch all approved recipes
$sql = "SELECT * FROM recipes WHERE status = 'approved' ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare data for the report
$totalRecipes = count($recipes);
$originCount = [];

// Count recipes by origin
foreach ($recipes as $recipe) {
    $origin = $recipe['origin'];
    if (isset($originCount[$origin])) {
        $originCount[$origin]++;
    } else {
        $originCount[$origin] = 1;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipe Report</title>
    <link rel="stylesheet" href="report.css"> <!-- Link to your CSS file -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    
    <script>
        function printReport() {
            window.print();
        }
    </script>
</head>
<body>
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
                    <li><a href="recipe_report">About</a></li>
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


    <!--list of total recipes-->
    
    <section class="report-summary">
    <h2>Summary</h2>
    <p><strong>Total Recipes:</strong> <?php echo $totalRecipes; ?></p>
    <h3>Recipes by Origin:</h3>

    <?php foreach ($originCount as $origin => $count): ?>
        <div class="origin-section">
            <h4><?php echo htmlspecialchars($origin); ?> (<?php echo $count; ?> recipes):</h4>
            <ul>
                <?php foreach ($recipes as $recipe): ?>
                    <?php if ($recipe['origin'] === $origin): ?>
                        <li><?php echo htmlspecialchars($recipe['name']); ?></li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endforeach; ?>
    

<!-- Inline CSS for styling -->
<style>
    .report-summary {
        text-align: center;
        font-family: 'Roboto', sans-serif;
        margin: 20px auto;
        padding: 20px;
        background: #f9f9f9;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .report-summary h2 {
        font-size: 2rem;
        color: #333;
    }

    .origin-section {
        text-align: left;
        margin: 20px auto;
        padding: 10px;
        background: #ffffff;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .origin-section h4 {
        color: #555;
        font-size: 1.3rem;
        margin-bottom: 10px;
    }

    .origin-section ul {
        list-style: none;
        padding: 0;
        font-size: 1rem;
        color: #333;
    }

    .origin-section li {
        padding: 5px 0;
        border-bottom: 1px dashed #ddd;
    }

    .origin-section li:last-child {
        border-bottom: none;
    }

    .print-button-container {
        margin-top: 20px;
    }

    .print-button {
        background-color: #28a745;
        color: white;
        border: none;
        padding: 10px 20px;
        font-size: 1rem;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .print-button:hover {
        background-color: #218838;
    }
</style>

<script>
    // Function to print the report
    function printReport() {
        window.print();
    }
</script>


    <!--- graph section-->

    <!-- Include Chart.js library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<section class="report-summary">
    <h2>Summary</h2>
    <p><strong>Total Recipes:</strong> <?php echo $totalRecipes; ?></p>
    <h3>Recipes by Origin:</h3>
    <ul>
        <?php foreach ($originCount as $origin => $count): ?>
            <li><?php echo htmlspecialchars($origin) . ': ' . $count; ?></li>
        <?php endforeach; ?>
    </ul>

    <!-- Add a chart container -->
    <canvas id="originChart" width="400" height="400"></canvas>
    
    <!-- Print report -->
    <div class="print-button-container">
        <button class="print-button" onclick="printReport()">Print Report</button>
    </div>
</section>

<script>
    // Prepare data for Chart.js
    const originLabels = <?php echo json_encode(array_keys($originCount)); ?>;
    const originData = <?php echo json_encode(array_values($originCount)); ?>;

    // Create a chart
    const ctx = document.getElementById('originChart').getContext('2d');
    new Chart(ctx, {
        type: 'pie', // You can change this to 'bar', 'line', etc.
        data: {
            labels: originLabels,
            datasets: [{
                label: 'Recipes by Origin',
                data: originData,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(255, 206, 86, 0.2)',
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(153, 102, 255, 0.2)',
                    'rgba(255, 159, 64, 0.2)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                }
            }
        }
    });
</script>

<!--bar chart-->



<section class="report-summary">
    <h2>Recipe Summary by Origin</h2>
    <p><strong>Total Recipes:</strong> <?php echo $totalRecipes; ?></p>

    <h3>Recipes by Origin</h3>
    <canvas id="recipeChart" width="600" height="400"></canvas>

    <!-- Add a print button -->
    <div class="print-button-container">
        <button class="print-button" onclick="printReport()">Print Report</button>
    </div>

    <!-- Add this button where you want the "Print Report" option -->
<div class="print-button-container">
    <button class="print-button" onclick="printGraphsAndStats()">Print Graphs and Statistics</button>
</div>
</section>

<!-- Enhanced CSS -->
<style>
    .report-summary {
        text-align: center;
        font-family: 'Roboto', sans-serif;
        margin: 20px auto;
        padding: 30px;
        background: linear-gradient(to bottom, #f8f9fa, #e9ecef);
        border-radius: 12px;
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        max-width: 800px;
    }

    .report-summary h2 {
        font-size: 2.5rem;
        color: #343a40;
        margin-bottom: 10px;
    }

    .report-summary h3 {
        font-size: 1.8rem;
        color: #495057;
        margin-bottom: 20px;
    }

    .print-button-container {
        margin-top: 30px;
    }

    .print-button {
        background-color: #007bff;
        color: white;
        border: none;
        padding: 12px 25px;
        font-size: 1rem;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .print-button:hover {
        background-color: #0056b3;
    }
</style>

<!-- Include Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Check if the originCount PHP variable exists and is valid
    const recipeLabels = <?php echo json_encode(array_keys($originCount)); ?>;
    const recipeCounts = <?php echo json_encode(array_values($originCount)); ?>;

    // Error Handling: Ensure data is available
    if (recipeLabels.length === 0 || recipeCounts.length === 0) {
        console.error("No recipe data available to display on the chart.");
        document.getElementById('recipeChart').style.display = "none";
    } else {
        // Recipe data for the chart
        const recipeData = {
            labels: recipeLabels, // Origins (e.g., ["Malaysian", "Italian"])
            datasets: [{
                label: 'Number of Recipes',
                data: recipeCounts, // Recipe counts (e.g., [3, 2])
                backgroundColor: [
                    'rgba(255, 99, 132, 0.5)',
                    'rgba(54, 162, 235, 0.5)',
                    'rgba(255, 206, 86, 0.5)',
                    'rgba(75, 192, 192, 0.5)',
                    'rgba(153, 102, 255, 0.5)',
                    'rgba(255, 159, 64, 0.5)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)'
                ],
                borderWidth: 1,
                borderRadius: 5
            }]
        };

        // Chart configuration
        const config = {
            type: 'bar', // Use a 'bar' chart
            data: recipeData,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    title: {
                        display: true,
                        text: 'Recipes Distribution by Origin',
                        font: {
                            size: 20,
                            weight: 'bold'
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Recipes',
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Recipe Origins',
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        }
                    }
                }
            }
        };

        // Render the chart
        const recipeChart = new Chart(
            document.getElementById('recipeChart'),
            config
        );
    }

    // Print function
    function printReport() {
        window.print();
    }
</script>



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

<!--print button graph-->

<script>
    function printGraphsAndStats() {
        // Hide all sections except the report-summary sections
        const allSections = document.querySelectorAll("body > *");
        allSections.forEach(section => {
            if (!section.classList.contains("report-summary")) {
                section.style.display = "none";
            }
        });

        // Print the document
        window.print();

        // Restore all sections after printing
        allSections.forEach(section => {
            section.style.display = "";
        });
    }
</script>



</body>
</html>
