<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in as an admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Ensure admin details are set in the session
$adminName = isset($_SESSION['nama']) ? $_SESSION['nama'] : 'Admin'; // Default to 'Admin' if not set
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Basic Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        /* Sidebar Styling */
        .sidebar {
            height: 100vh;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #2c3e50;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            color: white;
            padding: 1rem;
        }

        .sidebar .logo-container {
            text-align: center;
            margin-bottom: 1rem;
        }

        .sidebar .logo-container img {
            max-width: 150px; /* Increase the size of the logo */
            height: auto;
            margin-bottom: 15px;
        }

        .sidebar .logo {
            font-size: 1.5rem; /* Adjusted font size */
            font-weight: bold;
            color: #3498db;
        }

        .sidebar nav ul {
            list-style: none;
            flex-grow: 1;
        }

        .sidebar nav ul li {
            margin: 0.5rem 0;
        }

        .sidebar nav ul li a {
            display: flex;
            align-items: center;
            color: white;
            text-decoration: none;
            font-size: 0.85rem; /* Reduced font size */
            font-weight: bold;
            padding: 0.5rem;
            border-radius: 5px;
            transition: background 0.3s;
        }

        .sidebar nav ul li a:hover {
            background-color: #3498db;
        }

        .sidebar nav ul li a i {
            width: 20px; /* Smaller icon size */
            height: 20px;
            margin-right: 0.5rem;
            font-size: 1rem; /* Adjusted icon size */
        }

        .sidebar .logout {
            background-color: #e74c3c;
            color: white;
            padding: 0.5rem;
            text-align: center;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.85rem; /* Reduced font size */
            font-weight: bold;
            margin-top: auto;
            transition: background 0.3s;
        }

        .sidebar .logout:hover {
            background-color: #c0392b;
        }

        /* Main Content Area */
        .main-content {
            margin-left: 250px;
            padding: 2rem;
            transition: margin-left 0.3s;
        }

        /* Header Styling */
        .main-header {
            background-color: #ffffff;
            padding: 1rem 2rem;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            border-bottom: 1px solid #eaeaea;
            position: fixed;
            width: calc(100% - 250px);
            top: 0;
            left: 250px;
            z-index: 1000;
            transition: left 0.3s;
        }

        .main-header .profile {
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            color: black;
        }

        .main-header .profile span {
            font-size: 0.9rem;
            font-weight: bold;
        }

        /* Responsive Sidebar */
        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }

            .main-content {
                margin-left: 200px;
            }

            .main-header {
                width: calc(100% - 200px);
                left: 200px;
            }
        }

        @media (max-width: 576px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .main-header {
                width: 100%;
                left: 0;
                position: relative;
            }

            .main-content {
                margin-left: 0;
                padding-top: 4rem;
            }

            .sidebar nav ul {
                flex-direction: row;
                flex-wrap: wrap;
            }

            .sidebar nav ul li {
                margin: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo-container">
            <img src="gambar/logo.png" alt="PPD Logo"> <!-- Update path to logo -->
            <div class="logo">PPDKBB</div>
        </div>
        <nav>
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="admin.php"><i class="fas fa-user"></i> Admin</a></li>
                <li><a href="admin_maklumbalas.php"><i class="fas fa-comments"></i> Maklum Balas</a></li>
                <li><a href="chart.php"><i class="fas fa-chart-line"></i> Carta</a></li>
                <li><a href="manage_sekolah.php"><i class="fas fa-school"></i> Pengurusan Sekolah</a></li>
                <li><a href="manage_aktiviti.php"><i class="fas fa-calendar-alt"></i> Pengurusan Aktiviti</a></li>
                <li><a href="manage_berita.php"><i class="fas fa-newspaper"></i> Pengurusan Berita</a></li>
                <li><a href="manage_galeri.php"><i class="fas fa-images"></i> Pengurusan Galeri</a></li>
            </ul>
        </nav>
        <form action="logout.php" method="POST" class="logout">
            <button type="submit" style="width: 100%; background: none; border: none; color: inherit; font-size: inherit;">Logout</button>
        </form>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <header class="main-header">
            <div class="profile">
                <span><?php echo htmlspecialchars($adminName); ?></span>
            </div>
        </header>
    </div>
</body>
</html>
