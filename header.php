<?php
// Start the session to access session variables
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PPD Kulim</title>
    <style>
        /* Reset CSS */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Global Styles */
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f9f9f9;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background-color: #2c3e50;
            color: #ffffff;
            padding: 1rem;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            z-index: 1000;
        }

        header h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }

        @media (max-width: 768px) {
            header h1 {
                font-size: 2rem;
            }
        }

        /* Navigation Styles */
        nav ul {
            list-style: none;
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            margin-top: 0.5rem;
        }

        nav ul li a {
            text-decoration: none;
            color: #ffffff;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        nav ul li a:hover {
            background-color: #34495e;
        }
    </style>
</head>
<body>
    <header>
        <h1>Pejabat Pendidikan Daerah Kulim</h1>
        <nav>
            <ul>
                <li><a href="index.php">Utama</a></li>
                <li><a href="sekolah.php">Sekolah</a></li>
                <li><a href="aktiviti.php">Aktiviti</a></li>
                <li><a href="maklumbalas.php">Maklum Balas</a></li>
                <li><a href="berita.php">Berita</a></li>
                <li><a href="galeri.php">Galeri</a></li>
                <li><a href="login.php">Admin</a></li>
            </ul>
        </nav>
    </header>
</body>
</html>
