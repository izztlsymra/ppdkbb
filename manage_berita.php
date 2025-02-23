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

// Set the default timezone to ensure the correct current time
date_default_timezone_set('Asia/Kuala_Lumpur');

// Handle adding a new news article
if (isset($_POST['add_berita'])) {
    $tajuk = $conn->real_escape_string($_POST['tajuk']);
    $kandungan = $conn->real_escape_string($_POST['kandungan']);
    $penulis = $conn->real_escape_string($_POST['penulis']);
    $tarikh_ditambah = date("Y-m-d H:i:s"); // Current date and time

    // Handle file upload
    $gambar_berita = '';
    if (!empty($_FILES['gambar_berita']['name'])) {
        $upload_dir = 'gambar/';
        $gambar_berita = uniqid() . '_' . basename($_FILES['gambar_berita']['name']);
        $upload_file = $upload_dir . $gambar_berita;
        if (move_uploaded_file($_FILES['gambar_berita']['tmp_name'], $upload_file)) {
            // File successfully uploaded
        }
    }

    $conn->query("INSERT INTO berita (tajuk, kandungan, penulis, gambar_berita, tarikh_ditambah) 
                  VALUES ('$tajuk', '$kandungan', '$penulis', '$gambar_berita', '$tarikh_ditambah')");
    header("Location: manage_berita.php");
    exit;
}

// Handle editing a news article
if (isset($_POST['update_berita'])) {
    $berita_id = $conn->real_escape_string($_POST['berita_id']);
    $tajuk = $conn->real_escape_string($_POST['tajuk']);
    $kandungan = $conn->real_escape_string($_POST['kandungan']);
    $penulis = $conn->real_escape_string($_POST['penulis']);

    // Handle file upload
    $gambar_berita = $_POST['existing_gambar_berita'];
    if (!empty($_FILES['gambar_berita']['name'])) {
        $upload_dir = 'gambar/';
        $new_gambar_berita = uniqid() . '_' . basename($_FILES['gambar_berita']['name']);
        $upload_file = $upload_dir . $new_gambar_berita;

        if (move_uploaded_file($_FILES['gambar_berita']['tmp_name'], $upload_file)) {
            if (!empty($gambar_berita) && file_exists("gambar/" . $gambar_berita)) {
                unlink("gambar/" . $gambar_berita); // Delete old image
            }
            $gambar_berita = $new_gambar_berita;
        }
    }

    $conn->query("UPDATE berita 
                  SET tajuk = '$tajuk', kandungan = '$kandungan', penulis = '$penulis', gambar_berita = '$gambar_berita' 
                  WHERE berita_id = '$berita_id'");
    header("Location: manage_berita.php");
    exit;
}

// Handle deleting a news article
if (isset($_GET['delete'])) {
    $berita_id = $conn->real_escape_string($_GET['delete']);
    $result = $conn->query("SELECT gambar_berita FROM berita WHERE berita_id = '$berita_id'");
    $row = $result->fetch_assoc();
    if (!empty($row['gambar_berita']) && file_exists("gambar/" . $row['gambar_berita'])) {
        unlink("gambar/" . $row['gambar_berita']); // Delete the image file
    }
    $conn->query("DELETE FROM berita WHERE berita_id = '$berita_id'");
    header("Location: manage_berita.php");
    exit;
}

// Pagination setup
$limit = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1);
$offset = ($page - 1) * $limit;

// Search functionality
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// Fetch total number of news for pagination
$total_news_query = "SELECT COUNT(*) AS total FROM berita";
if (!empty($search)) {
    $total_news_query .= " WHERE tajuk LIKE '%$search%' OR kandungan LIKE '%$search%'";
}
$total_news = $conn->query($total_news_query)->fetch_assoc()['total'];
$total_pages = ceil($total_news / $limit);

// Fetch news articles for the current page
$news_query = "SELECT *, DATE_FORMAT(tarikh_ditambah, '%d-%m-%Y %H:%i:%s') AS formatted_tarikh FROM berita";
if (!empty($search)) {
    $news_query .= " WHERE tajuk LIKE '%$search%' OR kandungan LIKE '%$search%'";
}
$news_query .= " ORDER BY tarikh_ditambah DESC LIMIT $limit OFFSET $offset";
$berita = $conn->query($news_query);
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Urus Berita</title>
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

        .pagination {
            margin-top: 20px;
            justify-content: center;
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
    </style>
</head>
<body>
    <?php include 'admin_header.php'; ?>

    <div class="main-content">
        <h2>Urus Berita</h2>

        <!-- Search Bar -->
        <form method="GET" action="manage_berita.php" class="mb-3">
            <div class="input-group">
                <input type="text" class="form-control" name="search" placeholder="Cari Tajuk atau Kandungan" value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-primary">Cari</button>
            </div>
        </form>

        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="fas fa-plus"></i> Tambah Berita Baharu
        </button>

        <div class="table-container">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Tajuk</th>
                        <th>Penulis</th>
                        <th>Kandungan</th>
                        <th>Gambar</th>
                        <th>Tarikh Ditambah</th>
                        <th>Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($berita->num_rows > 0): ?>
                        <?php while ($row = $berita->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['tajuk']); ?></td>
                                <td><?php echo htmlspecialchars($row['penulis']); ?></td>
                                <td><?php echo htmlspecialchars($row['kandungan']); ?></td>
                                <td>
                                    <?php if (!empty($row['gambar_berita']) && file_exists("gambar/" . $row['gambar_berita'])): ?>
                                        <img src="gambar/<?php echo htmlspecialchars($row['gambar_berita']); ?>" alt="Gambar Berita" class="img-thumbnail">
                                    <?php else: ?>
                                        Tiada Gambar
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['formatted_tarikh']); ?></td>
                                <td class="action-icons">
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#editModal<?php echo htmlspecialchars($row['berita_id']); ?>" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="manage_berita.php?delete=<?php echo htmlspecialchars($row['berita_id']); ?>" title="Padam" onclick="return confirm('Adakah anda pasti?');">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>

                            <!-- Edit Modal -->
                            <div class="modal fade" id="editModal<?php echo htmlspecialchars($row['berita_id']); ?>" tabindex="-1" aria-labelledby="editModalLabel<?php echo htmlspecialchars($row['berita_id']); ?>" aria-hidden="true">
                                <div class="modal-dialog">
                                    <form action="manage_berita.php" method="POST" enctype="multipart/form-data">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="editModalLabel<?php echo htmlspecialchars($row['berita_id']); ?>">Edit Berita</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="berita_id" value="<?php echo htmlspecialchars($row['berita_id']); ?>">
                                                <div class="mb-3">
                                                    <label for="tajuk<?php echo htmlspecialchars($row['berita_id']); ?>" class="form-label">Tajuk</label>
                                                    <input type="text" class="form-control" id="tajuk<?php echo htmlspecialchars($row['berita_id']); ?>" name="tajuk" value="<?php echo htmlspecialchars($row['tajuk']); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="penulis<?php echo htmlspecialchars($row['berita_id']); ?>" class="form-label">Penulis</label>
                                                    <input type="text" class="form-control" id="penulis<?php echo htmlspecialchars($row['berita_id']); ?>" name="penulis" value="<?php echo htmlspecialchars($row['penulis']); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="kandungan<?php echo htmlspecialchars($row['berita_id']); ?>" class="form-label">Kandungan</label>
                                                    <textarea class="form-control" id="kandungan<?php echo htmlspecialchars($row['berita_id']); ?>" name="kandungan" rows="4" required><?php echo htmlspecialchars($row['kandungan']); ?></textarea>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="gambar_berita<?php echo htmlspecialchars($row['berita_id']); ?>" class="form-label">Gambar (Pilihan)</label>
                                                    <input type="file" class="form-control" id="gambar_berita<?php echo htmlspecialchars($row['berita_id']); ?>" name="gambar_berita">
                                                    <input type="hidden" name="existing_gambar_berita" value="<?php echo htmlspecialchars($row['gambar_berita']); ?>">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                <button type="submit" class="btn btn-primary" name="update_berita">Simpan Perubahan</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">Tiada berita ditemui.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <nav>
            <ul class="pagination justify-content-center">
                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="manage_berita.php?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>">&laquo; Sebelumnya</a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="manage_berita.php?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="manage_berita.php?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>">Seterusnya &raquo;</a>
                </li>
            </ul>
        </nav>
    </div>

    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="manage_berita.php" method="POST" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addModalLabel">Tambah Berita Baharu</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="tajuk" class="form-label">Tajuk Berita</label>
                            <input type="text" class="form-control" name="tajuk" placeholder="Masukkan tajuk berita" required>
                        </div>
                        <div class="mb-3">
                            <label for="penulis" class="form-label">Penulis</label>
                            <input type="text" class="form-control" name="penulis" placeholder="Masukkan nama penulis" required>
                        </div>
                        <div class="mb-3">
                            <label for="kandungan" class="form-label">Kandungan</label>
                            <textarea class="form-control" name="kandungan" rows="4" placeholder="Masukkan kandungan berita" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="gambar_berita" class="form-label">Gambar (Pilihan)</label>
                            <input type="file" class="form-control" name="gambar_berita" accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary" name="add_berita">Tambah Berita</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
