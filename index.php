<?php
session_start();
include 'connect.php';
include 'header.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Fetch the logged-in user's name from the session
$admin_name = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Pengguna';
$current_date = date("d F Y"); // Current date in Malay format
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Utama - PPD Kulim Bandar Baharu</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            color: #333;
            background-color: #f4f8fb;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Hero Section */
        .hero {
            position: relative;
            background-image: url('images/kulim-education.jpg'); /* Replace with your image path */
            background-size: cover;
            background-position: center;
            height: 450px;
            border-radius: 0 0 30px 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5); /* Semi-transparent overlay for better readability */
            border-radius: 0 0 30px 30px;
        }

        .hero-content {
            position: relative;
            color: white;
            text-align: center;
            z-index: 1;
            max-width: 600px;
            padding-top: 120px;
        }

        .hero h2 {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.3);
        }

        .hero p {
            font-size: 1.3rem;
            font-weight: 500;
            margin-bottom: 2rem;
        }

        .hero .btn {
            font-size: 1.2rem;
            padding: 0.8rem 2rem;
            background-color: #e67e22;
            color: white;
            border: none;
            border-radius: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-decoration: none;
        }

        .hero .btn:hover {
            background-color: #d35400;
        }

        /* Main Content */
        main {
            padding: 2rem;
            margin: 0 auto;
            width: 80%;
            max-width: 900px;
            text-align: center;
        }

        .info-section {
            margin-top: 1.5rem; /* Reduced the top margin to move the box closer to the hero section */
            padding: 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-align: left;
        }

        .info-section h3 {
            font-size: 1.8rem;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 1rem;
        }

        .info-section p {
            font-size: 1.1rem;
            color: #555;
            margin: 0.5rem 0;
            display: flex;
            align-items: center;
        }

        .info-section p i {
            font-size: 1.3rem;
            color: #3498db;
            margin-right: 10px;
        }

        /* Footer Styling */
        footer {
            background-color: #2c3e50;
            color: #dfe6e9;
            text-align: center;
            padding: 1rem 0;
            font-size: 1rem;
            width: 100%;
            margin-top: auto;
            box-shadow: 0 -4px 8px rgba(0, 0, 0, 0.1);
        }

        footer p {
            margin: 0;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            main {
                padding: 1.5rem;
                width: 90%;
            }

            .hero h2 {
                font-size: 2rem;
            }

            .hero p {
                font-size: 1rem;
            }

            footer {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h2>Selamat Datang, <?php echo htmlspecialchars($admin_name); ?>!</h2>
            <p>Hari ini adalah <strong><?php echo $current_date; ?></strong>.</p>
            <p>Kami komited untuk menyediakan maklumat dan kemas kini terkini kepada anda. Gunakan sistem ini untuk pengurusan pendidikan di kawasan Kulim Bandar Baharu.</p>
            <a href="galeri.php" class="btn">Lihat Galeri</a>
        </div>
    </section>

    <!-- Main Content -->
    <main>
        <div class="info-section">
            <h3>Maklumat Pejabat</h3>
            <p><i class="fas fa-map-marker-alt"></i> <strong>Alamat:</strong> Jalan Sultan Badlishah, Kawasan Perusahaan Kulim, 09000 Kulim, Kedah Darul Aman.</p>
            <p><i class="fas fa-phone"></i> <strong>Telefon:</strong> 04-4907662/ 04-4957671</p>
            <p><i class="fas fa-fax"></i> <strong>Faks:</strong> 04-4913387</p>
            <p><i class="fas fa-envelope"></i> <strong>Email:</strong> adminppdkbb@moe.gov.my</p>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <p>&copy; 2025 PPD Kulim Bandar Baharu</p>
    </footer>
</body>
</html>
