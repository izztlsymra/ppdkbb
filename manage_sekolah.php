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

// Handle adding a new school
if (isset($_POST['add_school'])) {
    $kod_sekolah = $conn->real_escape_string($_POST['kod_sekolah']);
    $nama_sekolah = $conn->real_escape_string($_POST['nama_sekolah']);
    $no_telefon = $conn->real_escape_string($_POST['no_telefon']);
    $tarikh_ditambah = date("Y-m-d");

    $conn->query("INSERT INTO sekolah (kod_sekolah, nama_sekolah, no_telefon, tarikh_ditambah) 
                  VALUES ('$kod_sekolah', '$nama_sekolah', '$no_telefon', '$tarikh_ditambah')");
    header("Location: manage_sekolah.php");
    exit;
}

// Handle deleting a school
if (isset($_GET['delete'])) {
    $kod_sekolah = $conn->real_escape_string($_GET['delete']);
    $conn->query("DELETE FROM sekolah WHERE kod_sekolah = '$kod_sekolah'");
    header("Location: manage_sekolah.php");
    exit;
}

// Handle updating a school
if (isset($_POST['update_school'])) {
    $kod_sekolah = $conn->real_escape_string($_POST['kod_sekolah']);
    $nama_sekolah = $conn->real_escape_string($_POST['nama_sekolah']);
    $no_telefon = $conn->real_escape_string($_POST['no_telefon']);

    $conn->query("UPDATE sekolah 
                  SET nama_sekolah = '$nama_sekolah', no_telefon = '$no_telefon' 
                  WHERE kod_sekolah = '$kod_sekolah'");
    header("Location: manage_sekolah.php");
    exit;
}

// Pagination setup
$limit = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1);
$offset = ($page - 1) * $limit;

// Search functionality
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// Fetch the total number of schools for pagination
$total_schools_query = "SELECT COUNT(*) AS total FROM sekolah";
if (!empty($search)) {
    $total_schools_query .= " WHERE kod_sekolah LIKE '%$search%' OR nama_sekolah LIKE '%$search%'";
}
$total_schools = $conn->query($total_schools_query)->fetch_assoc()['total'];
$total_pages = ceil($total_schools / $limit);

// Fetch schools for the current page
$schools_query = "SELECT kod_sekolah, nama_sekolah, no_telefon, DATE_FORMAT(tarikh_ditambah, '%d-%m-%Y') AS tarikh_ditambah 
                  FROM sekolah";
if (!empty($search)) {
    $schools_query .= " WHERE kod_sekolah LIKE '%$search%' OR nama_sekolah LIKE '%$search%'";
}
$schools_query .= " ORDER BY tarikh_ditambah DESC LIMIT $limit OFFSET $offset";
$schools = $conn->query($schools_query);
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Urus Sekolah</title>
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
        <h2>Urus Sekolah</h2>

        <!-- Search Bar -->
        <form method="GET" action="manage_sekolah.php" class="mb-3">
            <div class="input-group">
                <input type="text" class="form-control" name="search" placeholder="Cari berdasarkan Kod Sekolah atau Nama Sekolah" value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-primary">Cari</button>
            </div>
        </form>

        <!-- Add Button -->
        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="fas fa-plus"></i> Tambah Sekolah Baharu
        </button>

        <div class="table-container">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Kod Sekolah</th>
                        <th>Nama Sekolah</th>
                        <th>No Telefon</th>
                        <th>Tarikh Ditambah</th>
                        <th>Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($schools->num_rows > 0): ?>
                        <?php while ($row = $schools->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['kod_sekolah']); ?></td>
                                <td><?php echo htmlspecialchars($row['nama_sekolah']); ?></td>
                                <td><?php echo htmlspecialchars($row['no_telefon']); ?></td>
                                <td><?php echo htmlspecialchars($row['tarikh_ditambah']); ?></td>
                                <td class="text-center action-icons">
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#editModal<?php echo htmlspecialchars($row['kod_sekolah']); ?>" title="Kemaskini">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="manage_sekolah.php?delete=<?php echo urlencode($row['kod_sekolah']); ?>" title="Padam" onclick="return confirm('Adakah anda pasti?')">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>

                            <!-- Edit Modal -->
                            <div class="modal fade" id="editModal<?php echo htmlspecialchars($row['kod_sekolah']); ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <form action="manage_sekolah.php" method="POST">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Kemaskini Sekolah</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="kod_sekolah" value="<?php echo htmlspecialchars($row['kod_sekolah']); ?>">
                                                <div class="mb-3">
                                                    <label for="nama_sekolah" class="form-label">Nama Sekolah</label>
                                                    <input type="text" class="form-control" name="nama_sekolah" value="<?php echo htmlspecialchars($row['nama_sekolah']); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="no_telefon" class="form-label">No Telefon</label>
                                                    <input type="text" class="form-control" name="no_telefon" value="<?php echo htmlspecialchars($row['no_telefon']); ?>" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                <button type="submit" class="btn btn-primary" name="update_school">Simpan Perubahan</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">Tiada sekolah dijumpai.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <nav>
            <ul class="pagination">
                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="manage_sekolah.php?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>">&laquo; Sebelum</a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="manage_sekolah.php?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="manage_sekolah.php?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>">Seterusnya &raquo;</a>
                </li>
            </ul>
        </nav>
    </div>

    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="manage_sekolah.php" method="POST">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Sekolah Baharu</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="kod_sekolah" class="form-label">Kod Sekolah</label>
                            <input type="text" class="form-control" name="kod_sekolah" required>
                        </div>
                        <div class="mb-3">
                            <label for="nama_sekolah" class="form-label">Nama Sekolah</label>
                            <input type="text" class="form-control" name="nama_sekolah" required>
                        </div>
                        <div class="mb-3">
                            <label for="no_telefon" class="form-label">No Telefon</label>
                            <input type="text" class="form-control" name="no_telefon" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary" name="add_school">Tambah Sekolah</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
