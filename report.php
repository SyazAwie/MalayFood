<?php
    session_start();
    require 'db_connection.php'; // Include your database connection

    // Check if user is logged in
    if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
        header("Location: login.php");
        exit();
    }
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

    // Fetch total approved and rejected recipe counts
    $approvedQuery = "SELECT COUNT(*) as total FROM recipes WHERE status = 'approved'";
    $stmtApproved = $pdo->prepare($approvedQuery);
    $stmtApproved->execute();
    $approvedCount = $stmtApproved->fetch(PDO::FETCH_ASSOC)['total'];

    $rejectedQuery = "SELECT COUNT(*) as total FROM recipes WHERE status = 'rejected'";
    $stmtRejected = $pdo->prepare($rejectedQuery);
    $stmtRejected->execute();
    $rejectedCount = $stmtRejected->fetch(PDO::FETCH_ASSOC)['total'];

    // Total number of recipes
    $totalRecipes = $approvedCount + $rejectedCount;

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
        <title>Report</title>
        <link rel="stylesheet" href="reportStyle.css">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
        
    </head>

    <body>
        <?php $isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in']; // Check login state?>
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

       
 <main>
 <div class="global-print-container">
    <button class="print-button" onclick="printReport()">Print All Reports</button>
</div>
            <!--list of total recipes-->
            <section id="report1" class="report-summary printable">
                <h2>Recipes by Origin</h2>
                <p><strong>Total Recipes:</strong> <?php echo $totalRecipes; ?></p>

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
            </section>
            
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

                .report-summary p {
                    font-size: 18px; /* Adjust font size if needed */
                    margin-bottom: 8px;
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

                .report-summary ul {
                    padding: 0; /* Remove default padding */
                    text-align: left; /* Align text to the left */
                    margin-left: 0; /* Ensure no unintended margin */
                }

                .report-summary ul li {
                    font-size: 18px; /* Adjust font size if needed */
                    margin-bottom: 8px; /* Space between list items */
                }
            </style>

            <!--- graph section-->
            <!-- Include Chart.js library -->
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

            <section id="report2" class="report-summary printable">
                <h2>Recipe Approval Summary</h2>
                <p><strong>Total Recipes:</strong> <?php echo $totalRecipes; ?></p>
                <ul>
                    <li><strong>Approved:</strong> <?php echo $approvedCount; ?></li>
                    <li><strong>Rejected:</strong> <?php echo $rejectedCount; ?></li>
                </ul>

                <!-- Add a chart container -->
                <canvas id="approvalChart" width="400" height="400"></canvas>
                
            </section>

            <script>
                // Prepare data for Chart.js
                const approvalLabels = ['Approved', 'Rejected'];
                const approvalData = [<?php echo $approvedCount; ?>, <?php echo $rejectedCount; ?>];

                // Create a chart
                const ctx = document.getElementById('approvalChart').getContext('2d');
                new Chart(ctx, {
                    type: 'pie', // Pie chart to visualize approval status
                    data: {
                        labels: approvalLabels,
                        datasets: [{
                            label: 'Recipe Approval Status',
                            data: approvalData,
                            backgroundColor: [
                                'rgba(75, 192, 192, 0.2)',  // Approved (green)
                                'rgba(255, 99, 132, 0.2)'   // Rejected (red)
                            ],
                            borderColor: [
                                'rgba(75, 192, 192, 1)',
                                'rgba(255, 99, 132, 1)'
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
            <section id="report3" class="report-summary printable">
                <h2>Recipe Summary by Origin</h2>
                <p><strong>Total Recipes:</strong> <?php echo $totalRecipes; ?></p>

                <canvas id="recipeChart" width="600" height="400"></canvas>

                <!-- Add a print button -->
                <div class="print-button-container">
                <button class="toggle-button" onclick="toggleChartType()">Toggle Chart Type</button>
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

                .toggle-button, .print-button {
                background-color: #007bff;
                color: white;
                border: none;
                padding: 12px 25px;
                font-size: 1rem;
                border-radius: 8px;
                cursor: pointer;
                transition: background-color 0.3s ease;
                margin: 5px;
                }

                .toggle-button:hover, .print-button:hover {
                    background-color: #0056b3;
                }
            </style>

            <!-- Include Chart.js and Datalabels Plugin -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
<script>
            // Chart Data
            const recipeLabels = <?php echo json_encode(array_keys($originCount)); ?>;
            const recipeCounts = <?php echo json_encode(array_values($originCount)); ?>;

            // Initial Chart Type
            let currentChartType = 'bar';

            // Chart Configuration
            const chartConfig = {
                type: currentChartType,
                data: {
                    labels: recipeLabels,
                    datasets: [{
                        label: 'Number of Recipes',
                        data: recipeCounts,
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
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        title: {
                            display: true,
                            text: 'Recipe Summary by Origin',
                            font: {
                                size: 20,
                                weight: 'bold'
                            }
                        },
                        datalabels: {
                            display: true,
                            color: '#000',
                            anchor: 'center',
                            align: 'center',
                            font: {
                                size: 14,
                                weight: 'bold'
                            },
                            formatter: (value) => value // Show raw number directly
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
                },
                plugins: [ChartDataLabels] // Enable Datalabels Plugin
            };

            // Initialize Chart
            let recipeChart = new Chart(document.getElementById('recipeChart'), chartConfig);

            // Toggle Chart Type Function
            function toggleChartType() {
                // Toggle between 'bar' and 'pie'
                currentChartType = (currentChartType === 'bar') ? 'pie' : 'bar';

                // Destroy the current chart instance
                recipeChart.destroy();

                // Update the chart configuration
                chartConfig.type = currentChartType;

                // Update options for pie chart
                if (currentChartType === 'pie') {
                    chartConfig.options.scales = {}; // Remove scales for pie chart
                    chartConfig.options.plugins.datalabels.formatter = (value, ctx) => {
                        const dataset = ctx.chart.data.datasets[0];
                        const total = dataset.data.reduce((sum, val) => sum + val, 0);
                        const percentage = ((value / total) * 100).toFixed(1) + '%';
                        return `${value} (${percentage})`; // Show number and percentage
                    };
                } else {
                    // Restore scales for bar chart
                    chartConfig.options.scales = {
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
                    };
                    chartConfig.options.plugins.datalabels.formatter = (value) => value; // Show raw number directly
                }

                // Reinitialize the chart with the new type
                recipeChart = new Chart(document.getElementById('recipeChart'), chartConfig);
            }
</script>
        </main>
        <footer>
            <p>&copy; <?php echo date("Y"); ?> Malay Traditional Food Heritage System. All Rights Reserved.</p>
        </footer>

        <!-- Script for sidebar -->
        <script>
            function showSidebar(){
                const sidebar = document.querySelector('.sidebar')
                    sidebar.style.display = 'flex'
            }
            function hideSidebar(){
                const sidebar = document.querySelector('.sidebar')
                    sidebar.style.display = 'none'
                    
            }
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
async function printReport() {
    const canvases = Array.from(document.querySelectorAll('canvas'));
    const canvasParents = canvases.map(canvas => canvas.parentNode);
    const canvasClones = canvases.map(canvas => canvas.cloneNode(true));

    // Save original dimensions
    const originalDimensions = canvases.map(canvas => ({
        width: canvas.style.width || `${canvas.width}px`,
        height: canvas.style.height || `${canvas.height}px`
    }));

    const imagePromises = canvases.map(async (canvas, index) => {
        return new Promise(resolve => {
            const img = new Image();
            img.className = 'print-chart-image';
            img.onload = resolve;
            img.src = canvas.toDataURL('image/png');
            canvasParents[index].replaceChild(img, canvas);
        });
    });

    await Promise.all(imagePromises);
    window.print();

    window.onafterprint = () => {
    canvases.forEach((canvas, index) => {
        const restoredCanvas = canvasClones[index];
        restoredCanvas.style.width = originalDimensions[index].width;
        restoredCanvas.style.height = originalDimensions[index].height;

        canvasParents[index].replaceChild(restoredCanvas, canvasParents[index].querySelector('img'));
        restoredCanvas.getContext('2d'); // Force re-layout
    });
};

}



</script>




<style>
@media print {
    /* Hide navigation and footer */
    nav,
    footer {
        display: none !important;
    }

    /* Ensure printable sections are visible */
    .printable {
        display: block !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 20px !important;
        page-break-before: always;
    }

    /* Ensure other content is not hidden */
    body > * {
        display: block !important;
    }
    
}
@media print {
    .chart-container, canvas {
        width: 100% !important;
        height: auto !important;
    }
}


</style>




    </body>
</html>
