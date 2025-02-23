<?php
// Include header and sidebar
include 'admin_header.php';

// Start session only if it's not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'connect.php';

// Check if the user is logged in as an admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Handle updating status and deleting feedback entries
$message = "";
if (isset($_POST['update_status'])) {
    $feedback_id = $_POST['feedback_id'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE maklumbalas SET status = ? WHERE maklumbalas_id = ?");
    $stmt->bind_param("si", $status, $feedback_id);

    if ($stmt->execute()) {
        $message = "Status berjaya dikemaskini.";
    } else {
        $message = "Gagal mengemaskini status.";
    }
    $stmt->close();
}

if (isset($_POST['delete_feedback'])) {
    $feedback_id = $_POST['feedback_id'];

    // Fetch and delete the feedback image if it exists
    $result = $conn->query("SELECT gambar_maklumbalas FROM maklumbalas WHERE maklumbalas_id = $feedback_id");
    if ($result && $row = $result->fetch_assoc()) {
        if (!empty($row['gambar_maklumbalas']) && file_exists("uploads/" . $row['gambar_maklumbalas'])) {
            unlink("uploads/" . $row['gambar_maklumbalas']); // Delete the image file
        }
    }

    $stmt = $conn->prepare("DELETE FROM maklumbalas WHERE maklumbalas_id = ?");
    $stmt->bind_param("i", $feedback_id);

    if ($stmt->execute()) {
        $message = "Maklum balas berjaya dipadam.";
    } else {
        $message = "Gagal memadam maklum balas.";
    }
    $stmt->close();
}

// Ensure filter values are arrays, even if empty
$filter_category = isset($_GET['perincian']) ? (array)$_GET['perincian'] : [];
$filter_office = isset($_GET['pejabat']) ? (array)$_GET['pejabat'] : [];
$filter_date = isset($_GET['date']) ? $_GET['date'] : '';

// Build the query with filters and retrieve feedback data
$query = "SELECT * FROM maklumbalas WHERE 1=1";
if (!empty($filter_category)) {
    $category_filters = array_map(function($cat) use ($conn) {
        return "perincian LIKE '%" . $conn->real_escape_string($cat) . "%'";
    }, $filter_category);
    $query .= " AND (" . implode(" OR ", $category_filters) . ")";
}
if (!empty($filter_office)) {
    $office_filters = array_map(function($off) use ($conn) {
        return "perincian LIKE '%" . $conn->real_escape_string($off) . "%'";
    }, $filter_office);
    $query .= " AND (" . implode(" OR ", $office_filters) . ")";
}
if ($filter_date !== '') {
    $query .= " AND DATE(tarikh_dihantar) = '" . $conn->real_escape_string($filter_date) . "'";
}
$query .= " ORDER BY tarikh_dihantar DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Pengurusan Maklum Balas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Filter Form Styling */
        .filter-form {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 1rem;
            padding-top: 20px;
            flex-wrap: wrap;
            background-color: #f9f9f9;
            padding: 10px;
            border-radius: 8px;
        }

        .filter-section {
            display: flex;
            flex-direction: column;
            gap: 5px;
            margin-right: 20px;
        }

        .filter-section label {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .filter-form button {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }

        .btn-tapis {
            background-color: #3498db;
            color: white;
        }

        .btn-reset {
            background-color: #e74c3c;
            color: white;
        }

        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            background-color: #f9f9f9;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 0.75rem;
            text-align: center;
        }

        th {
            background-color: #2c3e50;
            color: white;
        }

        .img-thumbnail {
            max-width: 120px;
            height: auto;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-icon {
            background: none;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            color: #3498db;
        }

        .btn-icon.delete {
            color: #e74c3c;
        }

        .status.completed {
            color: #27ae60;
        }

        .status.pending {
            color: #f39c12;
        }

        .status.not-reviewed {
            color: #e74c3c;
        }

        /* Fullscreen Image Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal img {
            max-width: 90%;
            max-height: 90%;
            border-radius: 8px;
        }

        .modal-close {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 2rem;
            color: white;
            cursor: pointer;
        }
    </style>
</head>
<body>

    <!-- Page Content -->
    <div class="main-content" style="padding-top: 80px;">
        <h2>Pengurusan Maklum Balas</h2>

        <?php if ($message): ?>
            <p class="message"><?php echo $message; ?></p>
        <?php endif; ?>

        <form method="GET" action="" class="filter-form">
            <div class="filter-section">
                <label>Pejabat Dikunjungi:</label>
                <div>
                    <label><input type="checkbox" name="pejabat[]" value="JPN" <?php if (in_array('JPN', $filter_office)) echo 'checked'; ?>> JPN</label>
                    <label><input type="checkbox" name="pejabat[]" value="PPW" <?php if (in_array('PPW', $filter_office)) echo 'checked'; ?>> PPW</label>
                    <label><input type="checkbox" name="pejabat[]" value="PPD" <?php if (in_array('PPD', $filter_office)) echo 'checked'; ?>> PPD</label>
                </div>
            </div>

            <div class="filter-section">
                <label>Kategori Pelanggan:</label>
                <div>
                    <label><input type="checkbox" name="perincian[]" value="Ibu Bapa" <?php if (in_array('Ibu Bapa', $filter_category)) echo 'checked'; ?>> Ibu Bapa</label>
                    <label><input type="checkbox" name="perincian[]" value="Guru" <?php if (in_array('Guru', $filter_category)) echo 'checked'; ?>> Guru</label>
                    <label><input type="checkbox" name="perincian[]" value="Pelajar" <?php if (in_array('Pelajar', $filter_category)) echo 'checked'; ?>> Pelajar</label>
                </div>
            </div>

            <div class="filter-section">
                <label for="date">Tarikh:</label>
                <input type="date" name="date" id="date" value="<?php echo htmlspecialchars($filter_date); ?>">
            </div>
            
            <button type="submit" class="btn-tapis">Tapis</button>
            <button type="button" class="btn-reset" onclick="window.location.href='admin_maklumbalas.php'">Reset</button>
        </form>

        <!-- Feedback Table -->
        <table>
            <tr>
                <th>ID</th>
                <th>Tarikh Dihantar</th>
                <th>Perincian</th>
                <th>Email</th>
                <th>Cadangan</th>
                <th>Gambar</th>
                <th>Status</th>
                <th>Tindakan</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()) : ?>
                <tr>
                    <td>
                        <a href="view_feedback.php?id=<?php echo $row['maklumbalas_id']; ?>">
                            <?php echo $row['maklumbalas_id']; ?>
                        </a>
                    </td>
                    <td><?php echo $row['tarikh_dihantar']; ?></td>
                    <td><?php echo $row['perincian']; ?></td>
                    <td><?php echo empty($row['email']) ? 'Anonymous' : $row['email']; ?></td>
                    <td><?php echo $row['cadangan'] ?? 'Tiada Cadangan'; ?></td>
                    <td>
                        <?php if (!empty($row['gambar_maklumbalas']) && file_exists("uploads/" . $row['gambar_maklumbalas'])): ?>
                            <img src="uploads/<?php echo $row['gambar_maklumbalas']; ?>" alt="Gambar Maklum Balas" class="img-thumbnail" onclick="showModal(this.src)">
                        <?php else: ?>
                            Tiada Gambar
                        <?php endif; ?>
                    </td>
                    <td class="status <?php echo ($row['status'] == 'Selesai') ? 'completed' : ($row['status'] == 'Sedang Diproses' ? 'pending' : 'not-reviewed'); ?>">
                        <?php echo !empty($row['status']) ? $row['status'] : 'Belum Diproses'; ?>
                    </td>
                    <td>
                        <form action="" method="POST" style="display:inline;">
                            <input type="hidden" name="feedback_id" value="<?php echo $row['maklumbalas_id']; ?>">
                            <select name="status">
                                <option value="Belum Diproses" <?php if ($row['status'] == 'Belum Diproses' || empty($row['status'])) echo 'selected'; ?>>Belum Diproses</option>
                                <option value="Sedang Diproses" <?php if ($row['status'] == 'Sedang Diproses') echo 'selected'; ?>>Sedang Diproses</option>
                                <option value="Selesai" <?php if ($row['status'] == 'Selesai') echo 'selected'; ?>>Selesai</option>
                            </select>
                            <button type="submit" name="update_status" class="btn-icon"><i class="fas fa-edit"></i></button>
                        </form>
                        <form action="" method="POST" style="display:inline;" onsubmit="return confirm('Adakah anda pasti ingin memadam maklum balas ini?');">
                            <input type="hidden" name="feedback_id" value="<?php echo $row['maklumbalas_id']; ?>">
                            <button type="submit" name="delete_feedback" class="btn-icon delete"><i class="fas fa-trash-alt"></i></button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <!-- Image Modal -->
    <div class="modal" id="imageModal">
        <span class="modal-close" onclick="closeModal()">&times;</span>
        <img id="modalImage" src="" alt="Gambar Maklum Balas">
    </div>

    <script>
        function showModal(imageSrc) {
            document.getElementById('modalImage').src = imageSrc;
            document.getElementById('imageModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('imageModal').style.display = 'none';
        }
    </script>
</body>
</html>
