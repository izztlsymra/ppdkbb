<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Output buffering to prevent header issues
ob_start();

// Check if the user is logged in as an admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Include database connection and admin header
include 'connect.php';
include 'admin_header.php';

// Handle deleting an activity
if (isset($_GET['delete'])) {
    $aktiviti_id = intval($_GET['delete']);
    $result = $conn->query("SELECT gambar_aktiviti FROM aktiviti WHERE aktiviti_id = $aktiviti_id");

    if ($result && $row = $result->fetch_assoc()) {
        if (!empty($row['gambar_aktiviti']) && file_exists("uploads/" . $row['gambar_aktiviti'])) {
            unlink("uploads/" . $row['gambar_aktiviti']);
        }

        $conn->query("DELETE FROM aktiviti WHERE aktiviti_id = $aktiviti_id");
    }

    header("Location: manage_aktiviti.php");
    exit;
}

// Handle editing an activity
if (isset($_POST['edit_aktiviti'])) {
    $aktiviti_id = intval($_POST['aktiviti_id']);
    $nama_aktiviti = $conn->real_escape_string($_POST['nama_aktiviti']);
    $tarikh_aktiviti = $conn->real_escape_string($_POST['tarikh_aktiviti']);
    $lokasi_aktiviti = $conn->real_escape_string($_POST['lokasi_aktiviti']);
    $perincian = $conn->real_escape_string($_POST['perincian']);

    if (isset($_FILES['gambar_aktiviti']) && $_FILES['gambar_aktiviti']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        $file_name = uniqid() . '_' . basename($_FILES['gambar_aktiviti']['name']);
        $upload_path = $upload_dir . $file_name;

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        if (move_uploaded_file($_FILES['gambar_aktiviti']['tmp_name'], $upload_path)) {
            $gambar_aktiviti = $file_name;
            $stmt = $conn->prepare("UPDATE aktiviti SET nama_aktiviti = ?, tarikh_aktiviti = ?, lokasi_aktiviti = ?, perincian = ?, gambar_aktiviti = ? WHERE aktiviti_id = ?");
            $stmt->bind_param("sssssi", $nama_aktiviti, $tarikh_aktiviti, $lokasi_aktiviti, $perincian, $gambar_aktiviti, $aktiviti_id);
        }
    } else {
        $stmt = $conn->prepare("UPDATE aktiviti SET nama_aktiviti = ?, tarikh_aktiviti = ?, lokasi_aktiviti = ?, perincian = ? WHERE aktiviti_id = ?");
        $stmt->bind_param("ssssi", $nama_aktiviti, $tarikh_aktiviti, $lokasi_aktiviti, $perincian, $aktiviti_id);
    }

    $stmt->execute();
    header("Location: manage_aktiviti.php");
    exit;
}

// Handle adding a new activity
if (isset($_POST['add_aktiviti'])) {
    $nama_aktiviti = $conn->real_escape_string($_POST['nama_aktiviti']);
    $tarikh_aktiviti = $conn->real_escape_string($_POST['tarikh_aktiviti']);
    $lokasi_aktiviti = $conn->real_escape_string($_POST['lokasi_aktiviti']);
    $perincian = $conn->real_escape_string($_POST['perincian']);

    $gambar_aktiviti = '';
    if (isset($_FILES['gambar_aktiviti']) && $_FILES['gambar_aktiviti']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        $file_name = uniqid() . '_' . basename($_FILES['gambar_aktiviti']['name']);
        $upload_path = $upload_dir . $file_name;

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        if (move_uploaded_file($_FILES['gambar_aktiviti']['tmp_name'], $upload_path)) {
            $gambar_aktiviti = $file_name;
        }
    }

    $stmt = $conn->prepare("INSERT INTO aktiviti (nama_aktiviti, tarikh_aktiviti, lokasi_aktiviti, perincian, gambar_aktiviti, tarikh_ditambah) 
                            VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("sssss", $nama_aktiviti, $tarikh_aktiviti, $lokasi_aktiviti, $perincian, $gambar_aktiviti);
    $stmt->execute();

    header("Location: manage_aktiviti.php");
    exit;
}

// Pagination setup
$limit = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1);
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

$total_activities_query = "SELECT COUNT(*) AS total FROM aktiviti";
if (!empty($search)) {
    $total_activities_query .= " WHERE nama_aktiviti LIKE '%$search%' OR lokasi_aktiviti LIKE '%$search%' OR perincian LIKE '%$search%'";
}
$total_activities = $conn->query($total_activities_query)->fetch_assoc()['total'];
$total_pages = ceil($total_activities / $limit);

$activities_query = "SELECT *, DATE_FORMAT(tarikh_ditambah, '%d-%m-%Y %H:%i:%s') AS formatted_tarikh_ditambah FROM aktiviti";
if (!empty($search)) {
    $activities_query .= " WHERE nama_aktiviti LIKE '%$search%' OR lokasi_aktiviti LIKE '%$search%' OR perincian LIKE '%$search%'";
}
$activities_query .= " ORDER BY tarikh_ditambah DESC LIMIT $limit OFFSET $offset";
$activities = $conn->query($activities_query);
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Urus Aktiviti</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
        }

        .main-content {
            margin-left: 250px;
            padding: 30px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-top: -65px;
            margin-bottom: 10px;
            font-weight: bold;
            color: #2c3e50;
        }

        .table-container {
            background: white;
            border-radius: 8px;
            padding: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .table th {
            background-color: #2c3e50;
            color: white;
        }

        .action-icons a {
            margin: 0 5px;
            font-size: 1.4rem;
            color: #555;
            text-decoration: none;
        }

        .action-icons a:hover {
            color: #007bff;
        }

        .pagination {
            justify-content: center;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <?php include 'admin_header.php'; ?>
    <div class="main-content">
        <h2>Urus Aktiviti</h2>

        <!-- Search Bar -->
        <form method="GET" action="manage_aktiviti.php" class="mb-3">
            <div class="input-group">
                <input type="text" class="form-control" name="search" placeholder="Cari Aktiviti, Lokasi atau Perincian" value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-primary">Cari</button>
            </div>
        </form>

        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="fas fa-plus"></i> Tambah Aktiviti Baharu
        </button>

        <div class="table-container">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Nama Aktiviti</th>
                        <th>Tarikh Aktiviti</th>
                        <th>Lokasi</th>
                        <th>Perincian</th>
                        <th>Gambar</th>
                        <th>Tarikh Ditambah</th>
                        <th>Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($activities->num_rows > 0): ?>
                        <?php while ($row = $activities->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['nama_aktiviti']); ?></td>
                                <td><?php echo htmlspecialchars(date("d-m-Y", strtotime($row['tarikh_aktiviti']))); ?></td>
                                <td><?php echo htmlspecialchars($row['lokasi_aktiviti']); ?></td>
                                <td><?php echo htmlspecialchars($row['perincian']); ?></td>
                                <td>
                                    <?php if (!empty($row['gambar_aktiviti']) && file_exists("uploads/" . $row['gambar_aktiviti'])): ?>
                                        <img src="uploads/<?php echo htmlspecialchars($row['gambar_aktiviti']); ?>" alt="Gambar Aktiviti" class="img-thumbnail">
                                    <?php else: ?>
                                        Tiada Gambar
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['formatted_tarikh_ditambah']); ?></td>
                                <td class="text-center action-icons">
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#editModal<?php echo htmlspecialchars($row['aktiviti_id']); ?>" title="Kemaskini">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="manage_aktiviti.php?delete=<?php echo urlencode($row['aktiviti_id']); ?>" title="Padam" onclick="return confirm('Adakah anda pasti?')">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>

                            <!-- Edit Modal -->
                            <div class="modal fade" id="editModal<?php echo htmlspecialchars($row['aktiviti_id']); ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <form action="manage_aktiviti.php" method="POST" enctype="multipart/form-data">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Kemaskini Aktiviti</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="aktiviti_id" value="<?php echo htmlspecialchars($row['aktiviti_id']); ?>">
                                                <div class="mb-3">
                                                    <label for="nama_aktiviti" class="form-label">Nama Aktiviti</label>
                                                    <input type="text" class="form-control" name="nama_aktiviti" value="<?php echo htmlspecialchars($row['nama_aktiviti']); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="tarikh_aktiviti" class="form-label">Tarikh Aktiviti</label>
                                                    <input type="date" class="form-control" name="tarikh_aktiviti" value="<?php echo htmlspecialchars($row['tarikh_aktiviti']); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="lokasi_aktiviti" class="form-label">Lokasi</label>
                                                    <input type="text" class="form-control" name="lokasi_aktiviti" value="<?php echo htmlspecialchars($row['lokasi_aktiviti']); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="perincian" class="form-label">Perincian</label>
                                                    <textarea class="form-control" name="perincian" required><?php echo htmlspecialchars($row['perincian']); ?></textarea>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="gambar_aktiviti" class="form-label">Gambar (pilihan)</label>
                                                    <input type="file" class="form-control" name="gambar_aktiviti" accept="image/*">
                                                    <small class="text-muted">Biarkan kosong jika tidak mahu menukar gambar.</small>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                <button type="submit" class="btn btn-primary" name="edit_aktiviti">Kemaskini Aktiviti</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">Tiada aktiviti ditemui.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <nav>
            <ul class="pagination">
                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="manage_aktiviti.php?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>">&laquo; Sebelum</a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="manage_aktiviti.php?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="manage_aktiviti.php?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>">Seterusnya &raquo;</a>
                </li>
            </ul>
        </nav>
    </div>

    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="manage_aktiviti.php" method="POST" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Aktiviti Baharu</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nama_aktiviti" class="form-label">Nama Aktiviti</label>
                            <input type="text" class="form-control" name="nama_aktiviti" required>
                        </div>
                        <div class="mb-3">
                            <label for="tarikh_aktiviti" class="form-label">Tarikh Aktiviti</label>
                            <input type="date" class="form-control" name="tarikh_aktiviti" required>
                        </div>
                        <div class="mb-3">
                            <label for="lokasi_aktiviti" class="form-label">Lokasi</label>
                            <input type="text" class="form-control" name="lokasi_aktiviti" required>
                        </div>
                        <div class="mb-3">
                            <label for="perincian" class="form-label">Perincian</label>
                            <textarea class="form-control" name="perincian" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="gambar_aktiviti" class="form-label">Gambar (pilihan)</label>
                            <input type="file" class="form-control" name="gambar_aktiviti" accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary" name="add_aktiviti">Tambah Aktiviti</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
ob_end_flush();
?>
