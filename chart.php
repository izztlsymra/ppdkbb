<?php
session_start();
include 'connect.php';
include 'admin_header.php'; // Sertakan header

// Semak jika pengguna log masuk sebagai admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Kira skor purata untuk carta maklum balas
$chart_query = "
    SELECT 
        AVG(kemahiran_pegawai) AS avg_kemahiran_pegawai,
        AVG(kesungguhan_pegawai) AS avg_kesungguhan_pegawai,
        AVG(kemudahan_mendapat_pegawai) AS avg_kemudahan_mendapat_pegawai,
        AVG(layanan_perkhidmatan) AS avg_layanan_perkhidmatan,
        AVG(penerangan_jelas) AS avg_penerangan_jelas,
        AVG(masa_menunggu) AS avg_masa_menunggu,
        AVG(risalah_maklumat) AS avg_risalah_maklumat,
        AVG(kualiti_perkhidmatan) AS avg_kualiti_perkhidmatan
    FROM maklumbalas;
";
$chart_result = $conn->query($chart_query);
$chart_data = $chart_result->fetch_assoc();

$average_scores = [
    'Kemahiran Pegawai' => round($chart_data['avg_kemahiran_pegawai'], 2),
    'Kesungguhan Pegawai' => round($chart_data['avg_kesungguhan_pegawai'], 2),
    'Kemudahan Mendapat Pegawai' => round($chart_data['avg_kemudahan_mendapat_pegawai'], 2),
    'Layanan Perkhidmatan' => round($chart_data['avg_layanan_perkhidmatan'], 2),
    'Penerangan Jelas' => round($chart_data['avg_penerangan_jelas'], 2),
    'Masa Menunggu' => round($chart_data['avg_masa_menunggu'], 2),
    'Risalah Maklumat' => round($chart_data['avg_risalah_maklumat'], 2),
    'Kualiti Perkhidmatan' => round($chart_data['avg_kualiti_perkhidmatan'], 2),
];

$conn->close();
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carta Maklum Balas</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f8fb;
            margin: 0;
        }
        .main-content {
            margin-left: 250px;
            padding: 30px;
        }
        .chart-container {
            width: 80%;
            margin: auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #34495e;
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="chart-container">
            <h2>Purata Penilaian Soalan Maklum Balas</h2>
            <canvas id="feedbackChart"></canvas>
        </div>
    </div>

    <?php include 'footer.php'; // Sertakan footer ?>

    <script>
        const labels = <?php echo json_encode(array_keys($average_scores)); ?>;
        const data = <?php echo json_encode(array_values($average_scores)); ?>;
        
        const ctx = document.getElementById('feedbackChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Purata Penilaian',
                    data: data,
                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: { 
                        beginAtZero: true, 
                        max: 5 
                    }
                },
                plugins: { 
                    legend: { display: false } 
                }
            }
        });
    </script>
</body>
</html>
