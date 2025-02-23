<?php
session_start();
include 'connect.php';
include 'header.php';

// Initialize variables
$submissionMessage = "";
$submissionStatus = false;

// Enable detailed error reporting for debugging
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Generate a unique token for the form if it doesn't already exist
if (empty($_SESSION['form_token'])) {
    $_SESSION['form_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Check if the form token is valid
        if (empty($_POST['form_token']) || $_POST['form_token'] !== $_SESSION['form_token']) {
            throw new Exception("Duplicate form submission detected.");
        }

        // Sanitize and retrieve form input
        $tarikh_kunjungan = $_POST['tarikh_kunjungan'];
        $masa_kunjungan = $_POST['masa_kunjungan'];
        $pejabat_dikunjungi = $conn->real_escape_string($_POST['pejabat_dikunjungi']);
        $kategori_pelanggan = $conn->real_escape_string($_POST['kategori_pelanggan']);
        $kemahiran_pegawai = intval($_POST['kemahiran_pegawai']);
        $kesungguhan_pegawai = intval($_POST['kesungguhan_pegawai']);
        $kemudahan_mendapat_pegawai = intval($_POST['kemudahan_mendapat_pegawai']);
        $layanan_perkhidmatan = intval($_POST['layanan_perkhidmatan']);
        $penerangan_jelas = intval($_POST['penerangan_jelas']);
        $masa_menunggu = intval($_POST['masa_menunggu']);
        $risalah_maklumat = intval($_POST['risalah_maklumat']);
        $kualiti_perkhidmatan = intval($_POST['kualiti_perkhidmatan']);
        $cadangan = $conn->real_escape_string($_POST['cadangan']);
        $email = !empty($_POST['email']) ? $conn->real_escape_string($_POST['email']) : null;

        // Handle file upload
        $gambar_maklumbalas = null;
        if (isset($_FILES['gambar_maklumbalas']) && $_FILES['gambar_maklumbalas']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/';
            $fileName = uniqid() . '_' . basename($_FILES['gambar_maklumbalas']['name']);
            $uploadFile = $uploadDir . $fileName;

            // Ensure upload directory exists
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Move the uploaded file to the server and save the file path
            if (move_uploaded_file($_FILES['gambar_maklumbalas']['tmp_name'], $uploadFile)) {
                $gambar_maklumbalas = $fileName;  // Store only the file name in the database
            } else {
                throw new Exception("Failed to upload image.");
            }
        }

        // Combine "perincian" field
        $perincian = "Pejabat: $pejabat_dikunjungi, Pelanggan: $kategori_pelanggan, Tarikh: $tarikh_kunjungan, Masa: $masa_kunjungan";

        // Prepare SQL query
        $stmt = $conn->prepare("INSERT INTO maklumbalas (
            perincian, kemahiran_pegawai, kesungguhan_pegawai, kemudahan_mendapat_pegawai,
            layanan_perkhidmatan, penerangan_jelas, masa_menunggu, risalah_maklumat,
            kualiti_perkhidmatan, cadangan, email, gambar_maklumbalas, tarikh_dihantar, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'Belum Diproses')");

        // Bind parameters
        $stmt->bind_param(
            "siiiiiiiisss", 
            $perincian, 
            $kemahiran_pegawai, 
            $kesungguhan_pegawai,
            $kemudahan_mendapat_pegawai, 
            $layanan_perkhidmatan, 
            $penerangan_jelas, 
            $masa_menunggu,
            $risalah_maklumat, 
            $kualiti_perkhidmatan, 
            $cadangan, 
            $email, 
            $gambar_maklumbalas
        );

        // Execute statement
        $stmt->execute();

        // Clear form token to prevent resubmission
        unset($_SESSION['form_token']);

        $submissionMessage = "Maklum balas berjaya dihantar!";
        $submissionStatus = true;

        // Regenerate token for the next submission
        $_SESSION['form_token'] = bin2hex(random_bytes(32));
    } catch (Exception $e) {
        $submissionMessage = "Ralat: " . $e->getMessage();
        $submissionStatus = false;
    }

    // Close statement and connection
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borang Maklum Balas PPD Kulim</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f8fb;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            flex-direction: column;
        }
        main {
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            width: 90%;
            max-width: 900px;
            margin-top: 100px;
        }
        h2 {
            color: #2c3e50;
            text-align: center;
        }
        form {
            width: 100%;
        }
        label {
            font-weight: bold;
            margin-top: 1rem;
            display: block;
            color: #333;
        }
        input, select, textarea {
            width: 100%;
            padding: 0.75rem;
            margin-top: 0.5rem;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        th {
            background: #f0f0f0;
            font-weight: bold;
        }
        td:first-child {
            text-align: left;
            padding-left: 15px;
            font-weight: bold;
            color: #555;
        }
        input[type="radio"] {
            margin-left: 0;
        }
        input[type="submit"] {
            background-color: #3498db;
            color: #fff;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            padding: 0.75rem;
            margin-top: 1rem;
            width: 100%;
        }
        input[type="submit"]:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
<main>
    <h2>Maklum Balas Pelanggan</h2>
    <form action="" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="form_token" value="<?= $_SESSION['form_token'] ?>">
        <label>Tarikh Kunjungan:</label>
        <input type="date" name="tarikh_kunjungan" id="currentDate" required>
        
        <label>Masa Kunjungan:</label>
        <input type="time" name="masa_kunjungan" id="currentTime" required>

        <label>Pejabat Dikunjungi:</label>
        <select name="pejabat_dikunjungi" required>
            <option value="">Sila Pilih</option>
            <option value="PPD">PPD</option>
            <option value="PPW">PPW</option>
            <option value="JPN">JPN</option>
        </select>

        <label>Kategori Pelanggan:</label>
        <select name="kategori_pelanggan" required>
            <option value="">Sila Pilih</option>
            <option value="Pelajar">Pelajar</option>
            <option value="Guru">Guru</option>
            <option value="Ibu Bapa">Ibu Bapa</option>
            <option value="Lain-lain">Lain-lain</option>
        </select>

        <table>
            <tr>
                <th>Perkara</th>
                <th>1</th>
                <th>2</th>
                <th>3</th>
                <th>4</th>
                <th>5</th>
            </tr>
            <?php
            $aspects = [
                "kemahiran_pegawai" => "Kemahiran pegawai menyelesaikan masalah",
                "kesungguhan_pegawai" => "Kesungguhan pegawai menyelesaikan masalah",
                "kemudahan_mendapat_pegawai" => "Kemudahan mendapatkan pegawai",
                "layanan_perkhidmatan" => "Layanan semasa berurusan",
                "penerangan_jelas" => "Penerangan yang diberikan jelas",
                "masa_menunggu" => "Masa menunggu sesuai",
                "risalah_maklumat" => "Risalah/maklumat yang diberikan",
                "kualiti_perkhidmatan" => "Kualiti perkhidmatan keseluruhan"
            ];
            foreach ($aspects as $name => $label) {
                echo "<tr><td>$label</td>";
                for ($i = 1; $i <= 5; $i++) {
                    echo "<td><input type='radio' name='$name' value='$i' required></td>";
                }
                echo "</tr>";
            }
            ?>
        </table>

        <label>Cadangan:</label>
        <textarea name="cadangan" rows="4"></textarea>

        <label>Gambar:</label>
        <input type="file" name="gambar_maklumbalas" accept="image/*">

        <label>Email (Opsional):</label>
        <input type="email" name="email">

        <input type="submit" value="Hantar Maklum Balas">
    </form>
</main>

<!-- JavaScript to Set Current Date and Time -->
<script>
    document.getElementById("currentDate").valueAsDate = new Date();
    document.getElementById("currentTime").value = new Date().toTimeString().slice(0, 5);
</script>

<!-- JavaScript Alert for Submission Status -->
<?php if ($submissionMessage): ?>
    <script>
        alert("<?= $submissionMessage ?>");
    </script>
<?php endif; ?>
</body>
</html>
