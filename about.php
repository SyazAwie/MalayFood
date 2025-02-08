<?php 
session_start(); // Start the session
$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in']; // Check login state

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

    <style>
    body {
        
        font-family: 'Open Sans', sans-serif;
        color: #f9f9f9;
    }
    main {
        padding: 30px;
        background: #ffffff;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.4);
        max-width: 1200px;
        margin: 40px auto;
    }
    .section {
        margin-bottom: 20px;
        padding: 20px;
        border-radius: 8px;
        background-color: #416a9a;
    }
    .section h2 {
        margin-top: 0;
        color: #2c3e50;
    }
    /* Unique background colors for each section */
    .section:nth-child(1) {
        background-color: #5c85d6;
    }
    .section:nth-child(2) {
        background-color: #4e78b8;
    }
    .section:nth-child(3) {
        background-color: #416a9a;
    }
    .section:nth-child(4) {
        background-color: #355c7c;
    }
    /* Objective Section Enhancements */
    .section.objective {
        font-size: 18px;
        line-height: 1.6;
        color: #f5f5f5;
        background-color: #4e78b8; /* Ensure high contrast for readability */
    }
    .vision-mission {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
    }
    .vision-mission .section {
        flex: 1 1 calc(50% - 20px);
    }
    .social-links {
        display: flex;
        justify-content: center;
        gap: 20px;
        margin-top: 20px;
    }
    .social-links a {
        text-decoration: none;
        color: #555;
        font-size: 24px;
        transition: color 0.3s;
    }
    .social-links a:hover {
        color: #0073e6;
    }
    .image-section {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 30px;
    width: 100%; /* Ensure it adapts to the parent container width */
    height: 450px; /* Maintain height as needed */
    text-align: center;
}

.image-section img {
    max-width: 100%; /* Ensure image scales properly */
    max-height: 100%;
    border-radius: 10px;
}


</style>


</head>
<body>
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
                    <li class="hideOnMobile"><a href="about.php">Abouut</a></li>

                    <?php if ($isLoggedIn): ?>
                        <li class="hideOnMobile"><a href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li class="hideOnMobile"><a href="login.php">Login</a></li>
                    <?php endif; ?>

                    <li class="menu-button" onclick=showSidebar()><a href="#"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M120-240v-80h720v80H120Zm0-200v-80h720v80H120Zm0-200v-80h720v80H120Z"/></svg></a></li>

                </ul>

            </nav>

            <main>
        <div class="image-section">
            <img src="jabatan-warisan-negara.png" alt="Jabatan Warisan Negara">
        </div>

        <div class="section">
            <h2>PENUBUHAN</h2>
            <p>Bermula daripada Bahagian Warisan di bawah Kementerian Kebudayaan, Kesenian dan Warisan (KEKKWA) dan dinaikkan taraf sebagai Jabatan pada 1 Mac 2006. Jabatan ini bertanggungjawab memelihara dan mengekalkan warisan negara seperti yang termaktub di bawah Akta Warisan Kebangsaan 2005.</p>
        </div>

        <div class="section">
            <h2>AKTA</h2>
            <p>Akta Warisan Kebangsaan 2005 & lain-lain Akta.</p>
        </div>

        <div class="section">
            <h2>KAJIAN PERJAWATAN UTAMA</h2>
            <p>Waran Perjawatan KPKM WP Bil. E 71/2013.</p>
        </div>

        <div class="vision-mission">
            <div class="section">
                <h2>VISION</h2>
                <p>Peneraju dalam penerokaan dan pengekalan khazanah warisan ke arah pembentukan teras jati diri bangsa dan penjanaan ekonomi negara.</p>
            </div>
            <div class="section">
                <h2>MISSION</h2>
                <p>Meneroka, memulihara dan memelihara warisan negara ke arah pembangunannya yang mampan sehingga terserlah di persada dunia.</p>
            </div>
        </div>

        <div class="section">
            <h2>OBJECTIVE</h2>
            <ul>
                <li>Memulihara, memelihara dan melindungi warisan kebudayaan dan warisan semula jadi melalui penyelidikan, pendokumentasian, penguatkuasaan dan menggalakkan kesedaran terhadap warisan.</li>
                <li>Memperluaskan portfolio butiran warisan yang dipelihara untuk disenaraikan dalam Buku Daftar Warisan Kebangsaan dan mengembangkan pengetahuan sedia ada dan baru melalui penyelidikan arkeologi, konservasi dan Warisan Tidak Ketara.</li>
                <li>Berkongsi pengetahuan mengenai perkembangan warisan Malaysia melalui pendidikan, untuk menggalakkan budaya penyelidikan dan analisis, serta bersama-sama meneroka Khazanah Warisan Negara.</li>
                <li>Berusaha mengangkat Warisan Negara sehingga mendapatkan pengiktirafan dunia.</li>
            </ul>
        </div>

        <div class="social-links">
            <a href="https://www.facebook.com/Jabatanwarisannegara/" target="_blank"><i class="fab fa-facebook"></i></a>
            <a href="https://x.com/jabatanwarisan?mx=2" target="_blank"><i class="fab fa-twitter"></i></a>
            <a href="https://www.youtube.com/channel/UCu-gGkeJ4j-GTgwSoHwY5Mw" target="_blank"><i class="fab fa-youtube"></i></a>
        </div>
    </main>



            <footer>
                <p>&copy; <?php echo date("Y"); ?> Malay Traditional Food Heritage System. All Rights Reserved.</p>
            </footer>

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

</body>
</html>