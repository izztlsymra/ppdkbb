<?php
include 'connect.php';
include 'admin_header.php';

// Check if the user is logged in as an admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Get feedback ID from the URL
$feedback_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($feedback_id <= 0) {
    die("Maklum balas tidak dijumpai.");
}

// Fetch feedback data from the database
$stmt = $conn->prepare("SELECT * FROM maklumbalas WHERE maklumbalas_id = ?");
$stmt->bind_param("i", $feedback_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Maklum balas tidak dijumpai.");
}

$feedback = $result->fetch_assoc();
$stmt->close();
$conn->close();

// Parse the "perincian" field
$perincian_data = [];
if (!empty($feedback['perincian'])) {
    preg_match_all('/(\w+):\s*([^,]+)/', $feedback['perincian'], $matches, PREG_SET_ORDER);
    foreach ($matches as $match) {
        $key = trim($match[1]);
        $value = trim($match[2]);
        $perincian_data[$key] = $value;
    }
}

// Helper function to get parsed data safely
function getPerincianValue($key, $perincian_data) {
    return isset($perincian_data[$key]) ? htmlspecialchars($perincian_data[$key]) : 'Tidak tersedia';
}
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maklum Balas - ID <?php echo $feedback_id; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f8fb;
            margin: 0;
            padding: 0;
        }
        .main-content {
            margin-left: 250px; /* Adjust to match sidebar width */
            padding: 20px;
            transition: all 0.3s ease-in-out;
        }
        .container {
            max-width: 900px;
            margin: 50px auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            padding: 20px;
            font-size: 16px;
            line-height: 1.6;
        }
        h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 20px;
        }
        .section {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }
        .field-group {
            display: flex;
            flex-direction: column;
        }
        .field-group label {
            font-weight: bold;
            color: #34495e;
            margin-bottom: 5px;
        }
        .field {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            font-size: 14px;
            color: #555;
        }
        .actions {
            text-align: center;
            margin-top: 20px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #3498db;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            margin: 5px;
        }
        .btn:hover {
            background: #2980b9;
        }
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
            }
            .section {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <h2>Maklum Balas Pelanggan - ID <?php echo $feedback_id; ?></h2>
            
            <!-- Visit Details Section -->
            <div class="section">
                <div class="field-group">
                    <label>Tarikh Kunjungan:</label>
                    <div class="field"><?php echo getPerincianValue('Tarikh', $perincian_data); ?></div>
                </div>
                <div class="field-group">
                    <label>Masa Kunjungan:</label>
                    <div class="field"><?php echo getPerincianValue('Masa', $perincian_data); ?></div>
                </div>
                <div class="field-group">
                    <label>Pejabat Dikunjungi:</label>
                    <div class="field"><?php echo getPerincianValue('Pejabat', $perincian_data); ?></div>
                </div>
                <div class="field-group">
                    <label>Kategori Pelanggan:</label>
                    <div class="field"><?php echo getPerincianValue('Pelanggan', $perincian_data); ?></div>
                </div>
            </div>

            <!-- Feedback Ratings Section -->
            <div class="section">
                <div class="field-group">
                    <label>Kemahiran Pegawai:</label>
                    <div class="field"><?php echo htmlspecialchars($feedback['kemahiran_pegawai']); ?></div>
                </div>
                <div class="field-group">
                    <label>Kesungguhan Pegawai:</label>
                    <div class="field"><?php echo htmlspecialchars($feedback['kesungguhan_pegawai']); ?></div>
                </div>
                <div class="field-group">
                    <label>Kemudahan Mendapat Pegawai:</label>
                    <div class="field"><?php echo htmlspecialchars($feedback['kemudahan_mendapat_pegawai']); ?></div>
                </div>
                <div class="field-group">
                    <label>Layanan Perkhidmatan:</label>
                    <div class="field"><?php echo htmlspecialchars($feedback['layanan_perkhidmatan']); ?></div>
                </div>
            </div>

            <div class="section">
                <div class="field-group">
                    <label>Penerangan Jelas:</label>
                    <div class="field"><?php echo htmlspecialchars($feedback['penerangan_jelas']); ?></div>
                </div>
                <div class="field-group">
                    <label>Masa Menunggu:</label>
                    <div class="field"><?php echo htmlspecialchars($feedback['masa_menunggu']); ?></div>
                </div>
                <div class="field-group">
                    <label>Risalah Maklumat:</label>
                    <div class="field"><?php echo htmlspecialchars($feedback['risalah_maklumat']); ?></div>
                </div>
                <div class="field-group">
                    <label>Kualiti Perkhidmatan:</label>
                    <div class="field"><?php echo htmlspecialchars($feedback['kualiti_perkhidmatan']); ?></div>
                </div>
            </div>

            <!-- Additional Information Section -->
            <div class="section">
                <div class="field-group">
                    <label>Cadangan:</label>
                    <div class="field"><?php echo nl2br(htmlspecialchars($feedback['cadangan'])); ?></div>
                </div>
                <div class="field-group">
                    <label>Email:</label>
                    <div class="field"><?php echo htmlspecialchars($feedback['email']); ?></div>
                </div>
            </div>

            <!-- Actions -->
            <div class="actions">
                <a href="admin_maklumbalas.php" class="btn">Kembali</a>
            </div>
        </div>
    </div>
</body>
</html>
