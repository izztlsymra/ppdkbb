<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in as an admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Include database connection
include 'connect.php';

// Handle deleting a gallery item
if (isset($_GET['delete'])) {
    $galeri_id = intval($_GET['delete']);
    
    $result = $conn->query("SELECT gambar_galeri FROM galeri WHERE galeri_id = $galeri_id");
    if ($result && $row = $result->fetch_assoc()) {
        $gambar_path = 'gambar/' . $row['gambar_galeri'];
        if (!empty($row['gambar_galeri']) && file_exists($gambar_path)) {
            unlink($gambar_path); // Delete the file
        }

        $conn->query("DELETE FROM galeri WHERE galeri_id = $galeri_id");
    }

    header("Location: manage_galeri.php");
    exit;
}

// Handle adding a new gallery item
if (isset($_POST['add_galeri'])) {
    $tajuk = $conn->real_escape_string($_POST['tajuk']);
    $jenis = $conn->real_escape_string($_POST['jenis']);
    $penerangan = $conn->real_escape_string($_POST['penerangan']);
    $tarikh_ditambah = date("Y-m-d H:i:s");

    $gambar_galeri = '';
    if (!empty($_FILES['gambar_galeri']['name'])) {
        $upload_dir = 'gambar/';
        $gambar_galeri = uniqid() . '_' . basename($_FILES['gambar_galeri']['name']);
        $upload_file = $upload_dir . $gambar_galeri;

        if (move_uploaded_file($_FILES['gambar_galeri']['tmp_name'], $upload_file)) {
            // File successfully uploaded
        } else {
            $gambar_galeri = ''; // Reset if upload failed
        }
    }

    $stmt = $conn->prepare("INSERT INTO galeri (tajuk, jenis, penerangan, gambar_galeri, tarikh_ditambah) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $tajuk, $jenis, $penerangan, $gambar_galeri, $tarikh_ditambah);
    $stmt->execute();
    header("Location: manage_galeri.php");
    exit;
}

// Handle editing a gallery item
if (isset($_POST['edit_galeri'])) {
    $galeri_id = intval($_POST['galeri_id']);
    $tajuk = $conn->real_escape_string($_POST['tajuk']);
    $jenis = $conn->real_escape_string($_POST['jenis']);
    $penerangan = $conn->real_escape_string($_POST['penerangan']);

    $update_query = "UPDATE galeri SET tajuk = ?, jenis = ?, penerangan = ?";

    // Handle file upload and update the image if a new one is provided
    if (!empty($_FILES['gambar_galeri']['name'])) {
        $upload_dir = 'gambar/';
        $new_gambar_galeri = uniqid() . '_' . basename($_FILES['gambar_galeri']['name']);
        $upload_file = $upload_dir . $new_gambar_galeri;

        if (move_uploaded_file($_FILES['gambar_galeri']['tmp_name'], $upload_file)) {
            // Delete the old image
            $result = $conn->query("SELECT gambar_galeri FROM galeri WHERE galeri_id = $galeri_id");
            if ($result && $row = $result->fetch_assoc()) {
                $old_gambar_path = 'gambar/' . $row['gambar_galeri'];
                if (file_exists($old_gambar_path)) {
                    unlink($old_gambar_path);
                }
            }

            // Add image to the update query
            $update_query .= ", gambar_galeri = ?";
        } else {
            $new_gambar_galeri = null;
        }
    }

    $update_query .= " WHERE galeri_id = ?";
    $stmt = $conn->prepare($update_query);

    if (isset($new_gambar_galeri)) {
        $stmt->bind_param("ssssi", $tajuk, $jenis, $penerangan, $new_gambar_galeri, $galeri_id);
    } else {
        $stmt->bind_param("sssi", $tajuk, $jenis, $penerangan, $galeri_id);
    }

    $stmt->execute();
    header("Location: manage_galeri.php");
    exit;
}

// Pagination setup
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1);
$offset = ($page - 1) * $limit;

// Search functionality
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

$total_galeri_query = "SELECT COUNT(*) AS total FROM galeri";
if (!empty($search)) {
    $total_galeri_query .= " WHERE tajuk LIKE '%$search%' OR penerangan LIKE '%$search%'";
}
$total_galeri = $conn->query($total_galeri_query)->fetch_assoc()['total'];
$total_pages = ceil($total_galeri / $limit);

$galeri_query = "SELECT *, DATE_FORMAT(tarikh_ditambah, '%d-%m-%Y %H:%i:%s') AS formatted_tarikh FROM galeri";
if (!empty($search)) {
    $galeri_query .= " WHERE tajuk LIKE '%$search%' OR penerangan LIKE '%$search%'";
}
$galeri_query .= " ORDER BY tarikh_ditambah DESC LIMIT $limit OFFSET $offset";
$galeri = $conn->query($galeri_query);
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Urus Galeri</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
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
            margin-bottom: 20px;
            font-weight: bold;
            color: #2c3e50;
        }
        .table-container {
            background: white;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .table th {
            background-color: #2c3e50;
            color: white;
        }
        .img-thumbnail {
            max-width: 100px;
            height: auto;
            border-radius: 5px;
        }
        .action-icons a {
            margin: 0 5px;
            color: #555;
            font-size: 1.2rem;
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
        <h2>Urus Galeri</h2>

        <!-- Search Bar -->
        <form method="GET" action="manage_galeri.php" class="mb-3">
            <div class="input-group">
                <input type="text" class="form-control" name="search" placeholder="Cari Tajuk atau Penerangan" value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-primary">Cari</button>
            </div>
        </form>

        <!-- Add Button -->
        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="fas fa-plus"></i> Tambah Galeri Baharu
        </button>

        <div class="table-container">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Tajuk</th>
                        <th>Jenis</th>
                        <th>Penerangan</th>
                        <th>Gambar</th>
                        <th>Tarikh Ditambah</th>
                        <th>Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $galeri->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['tajuk']); ?></td>
                            <td><?php echo htmlspecialchars($row['jenis']); ?></td>
                            <td><?php echo htmlspecialchars($row['penerangan']); ?></td>
                            <td>
                                <?php if (!empty($row['gambar_galeri']) && file_exists("gambar/" . $row['gambar_galeri'])): ?>
                                    <img src="gambar/<?php echo htmlspecialchars($row['gambar_galeri']); ?>" alt="Gambar Galeri" class="img-thumbnail">
                                <?php else: ?>
                                    Tiada Gambar
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['formatted_tarikh']); ?></td>
                            <td class="action-icons">
                                <a href="#" data-bs-toggle="modal" data-bs-target="#editModal<?php echo htmlspecialchars($row['galeri_id']); ?>" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="manage_galeri.php?delete=<?php echo htmlspecialchars($row['galeri_id']); ?>" title="Padam" onclick="return confirm('Adakah anda pasti?');">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </td>
                        </tr>

                        <!-- Edit Gallery Modal -->
                        <div class="modal fade" id="editModal<?php echo htmlspecialchars($row['galeri_id']); ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <form action="manage_galeri.php" method="POST" enctype="multipart/form-data">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Kemaskini Galeri</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="galeri_id" value="<?php echo htmlspecialchars($row['galeri_id']); ?>">
                                            <div class="mb-3">
                                                <label for="tajuk" class="form-label">Tajuk</label>
                                                <input type="text" class="form-control" name="tajuk" value="<?php echo htmlspecialchars($row['tajuk']); ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="jenis" class="form-label">Jenis</label>
                                                <select name="jenis" class="form-control" required>
                                                    <option value="Foto" <?php echo $row['jenis'] === 'Foto' ? 'selected' : ''; ?>>Foto</option>
                                                    <option value="Video" <?php echo $row['jenis'] === 'Video' ? 'selected' : ''; ?>>Video</option>
                                                    <option value="Infografik" <?php echo $row['jenis'] === 'Infografik' ? 'selected' : ''; ?>>Infografik</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="penerangan" class="form-label">Penerangan</label>
                                                <textarea class="form-control" name="penerangan" required><?php echo htmlspecialchars($row['penerangan']); ?></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label for="gambar_galeri" class="form-label">Gambar (pilihan)</label>
                                                <input type="file" class="form-control" name="gambar_galeri" accept="image/*">
                                                <small class="text-muted">Biarkan kosong jika tidak mahu menukar gambar.</small>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                            <button type="submit" class="btn btn-primary" name="edit_galeri">Kemaskini Galeri</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <nav>
            <ul class="pagination">
                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="manage_galeri.php?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>">&laquo; Sebelum</a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="manage_galeri.php?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="manage_galeri.php?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>">Seterusnya &raquo;</a>
                </li>
            </ul>
        </nav>
    </div>

    <!-- Add Gallery Modal -->
    <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="manage_galeri.php" method="POST" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addModalLabel">Tambah Galeri Baharu</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="tajuk" class="form-label">Tajuk</label>
                            <input type="text" class="form-control" name="tajuk" required>
                        </div>
                        <div class="mb-3">
                            <label for="jenis" class="form-label">Jenis</label>
                            <select name="jenis" class="form-control" required>
                                <option value="Foto">Foto</option>
                                <option value="Video">Video</option>
                                <option value="Infografik">Infografik</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="penerangan" class="form-label">Penerangan</label>
                            <textarea class="form-control" name="penerangan" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="gambar_galeri" class="form-label">Gambar</label>
                            <input type="file" class="form-control" name="gambar_galeri" accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary" name="add_galeri">Tambah Galeri</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
