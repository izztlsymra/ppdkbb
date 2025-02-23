<?php
session_start();
include 'admin_header.php';
include 'connect.php';

// Check if the user is logged in as an admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Fetch data from the database for dashboard statistics
$totalUsers = $totalFeedback = $todayFeedback = $totalActivities = 0;
$positiveFeedbackPercent = $negativeFeedbackPercent = 0;
$feedbackBelumDiproses = $feedbackSedangDiproses = $feedbackSelesai = 0;
$totalFeedbackProcessed = 0;
$mostActiveDay = "No Data";
$averageRating = 0;

// Total Users
$result = $conn->query("SELECT COUNT(*) AS count FROM admin");
if ($row = $result->fetch_assoc()) {
    $totalUsers = $row['count'];
}

// Total Feedback
$result = $conn->query("SELECT COUNT(*) AS count FROM maklumbalas");
if ($row = $result->fetch_assoc()) {
    $totalFeedback = $row['count'];
}

// Today's Feedback
$result = $conn->query("SELECT COUNT(*) AS count FROM maklumbalas WHERE DATE(tarikh_dihantar) = CURDATE()");
if ($row = $result->fetch_assoc()) {
    $todayFeedback = $row['count'];
}

// Total Activities
$result = $conn->query("SELECT COUNT(*) AS count FROM aktiviti");
if ($row = $result->fetch_assoc()) {
    $totalActivities = $row['count'];
}

// Feedback Processed
$result = $conn->query("SELECT COUNT(*) AS count FROM maklumbalas WHERE status != 'Belum Diproses'");
if ($row = $result->fetch_assoc()) {
    $totalFeedbackProcessed = $row['count'];
}

// Positive Feedback
$result = $conn->query("SELECT COUNT(*) AS positiveCount, (SELECT COUNT(*) FROM maklumbalas) AS totalCount FROM maklumbalas WHERE kemahiran_pegawai >= 4 AND kesungguhan_pegawai >= 4 AND layanan_perkhidmatan >= 4");
if ($row = $result->fetch_assoc()) {
    $positiveFeedbackPercent = ($row['totalCount'] > 0) ? round(($row['positiveCount'] / $row['totalCount']) * 100) : 0;
}

// Negative Feedback
$result = $conn->query("SELECT COUNT(*) AS negativeCount, (SELECT COUNT(*) FROM maklumbalas) AS totalCount FROM maklumbalas WHERE kemahiran_pegawai <= 3 OR kesungguhan_pegawai <= 3 OR layanan_perkhidmatan <= 3");
if ($row = $result->fetch_assoc()) {
    $negativeFeedbackPercent = ($row['totalCount'] > 0) ? round(($row['negativeCount'] / $row['totalCount']) * 100) : 0;
}

// Feedback by Status
$result = $conn->query("SELECT status, COUNT(*) AS count FROM maklumbalas GROUP BY status");
while ($row = $result->fetch_assoc()) {
    switch ($row['status']) {
        case 'Belum Diproses':
            $feedbackBelumDiproses = $row['count'];
            break;
        case 'Sedang Diproses':
            $feedbackSedangDiproses = $row['count'];
            break;
        case 'Selesai':
            $feedbackSelesai = $row['count'];
            break;
    }
}

// Most Active Feedback Day
$result = $conn->query("SELECT DATE(tarikh_dihantar) AS feedbackDay, COUNT(*) AS feedbackCount FROM maklumbalas GROUP BY feedbackDay ORDER BY feedbackCount DESC LIMIT 1");
if ($row = $result->fetch_assoc()) {
    $mostActiveDay = $row['feedbackDay'];
}

// Average Feedback Rating
$result = $conn->query("SELECT AVG((kemahiran_pegawai + kesungguhan_pegawai + layanan_perkhidmatan) / 3) AS avgRating FROM maklumbalas");
if ($row = $result->fetch_assoc()) {
    $averageRating = round($row['avgRating'], 2);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Papan Pemuka Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fc;
            margin: 0;
        }

        .main-content {
            margin-left: 250px;
            padding: 30px; 
        }

        h2 {
            font-size: 2rem;
            font-weight: 600;
            color: #34495e;
            margin-bottom: 15px;
            text-align: center;
        }

        .card-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
        }

        .card {
    background: #fff;
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    text-align: center;
    padding: 15px;
    margin: 0;
    margin-bottom: 15px; /* Space at the bottom of each box */
    transition: all 0.3s ease;
    border-left: 5px solid transparent;
}


        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .card h5 {
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 0.5px;
        }

        .card p {
            font-size: 1.5rem;
            font-weight: bold;
            color: #2c3e50;
            margin: 0;
        }

        .card i {
            font-size: 1.5rem;
            margin-bottom: 8px;
            color: #3498db;
        }

        .light-blue { border-left-color: #007bff; } /* Bright blue */
.orange { border-left-color: #fd7e14; } /* Deep orange */
.red { border-left-color: #dc3545; } /* Vibrant red */
.green { border-left-color: #28a745; } /* Fresh green */
.purple { border-left-color: #6f42c1; } /* Rich purple */
.yellow { border-left-color: #ffc107; } /* Vibrant yellow */


        .footer {
            margin-top: 20px;
            text-align: center;
            color: #95a5a6;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>

<div class="main-content">
    <h2>Papan Pemuka Admin</h2>

    <!-- Row 1: Main Metrics -->
    <div class="card-container">
        <div class="card light-blue">
            <i class="fas fa-calendar-day"></i>
            <h5>Hari Paling Aktif</h5>
            <p>
                <?php echo date('d', strtotime($mostActiveDay)); ?><br>
                <?php echo date('F Y', strtotime($mostActiveDay)); ?>
            </p>
        </div>
        <div class="card orange">
            <i class="fas fa-comments"></i>
            <h5>Jumlah Maklum Balas</h5>
            <p><?php echo $totalFeedback; ?></p>
        </div>
        <div class="card blue">
            <i class="fas fa-comment-alt"></i>
            <h5>Maklum Balas Hari Ini</h5>
            <p><?php echo $todayFeedback; ?></p>
        </div>
    </div>

    <!-- Row 2: Feedback Status -->
    <div class="card-container">
        <div class="card red">
            <i class="fas fa-times-circle"></i>
            <h5>Belum Diproses</h5>
            <p><?php echo $feedbackBelumDiproses; ?></p>
        </div>
        <div class="card yellow">
            <i class="fas fa-sync-alt"></i>
            <h5>Sedang Diproses</h5>
            <p><?php echo $feedbackSedangDiproses; ?></p>
        </div>
        <div class="card green">
            <i class="fas fa-check-circle"></i>
            <h5>Selesai</h5>
            <p><?php echo $feedbackSelesai; ?></p>
        </div>
        <div class="card purple">
            <i class="fas fa-tasks"></i>
            <h5>Maklum Balas Diproses</h5>
            <p><?php echo $totalFeedbackProcessed; ?></p>
        </div>
    </div>

    <!-- Row 3: Additional Metrics -->
    <div class="card-container">
        <div class="card green">
            <i class="fas fa-thumbs-up"></i>
            <h5>Maklum Balas Positif</h5>
            <p><?php echo $positiveFeedbackPercent; ?>%</p>
        </div>
        <div class="card red">
            <i class="fas fa-thumbs-down"></i>
            <h5>Maklum Balas Negatif</h5>
            <p><?php echo $negativeFeedbackPercent; ?>%</p>
        </div>
        <div class="card yellow">
            <i class="fas fa-star"></i>
            <h5>Purata Penilaian</h5>
            <p><?php echo $averageRating; ?></p>
        </div>
    </div>
</div>

<div class="footer">
    <p>© 2025 Papan Pemuka Admin. Semua Hak Cipta Terpelihara.</p>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.6.0/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
