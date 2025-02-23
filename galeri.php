<?php
session_start();
include 'header.php';
include 'connect.php'; // Ensure this file establishes a connection to your database

// Initialize variables for search, pagination, and filtering
$search = '';
$records_per_page = 6;  // Number of gallery items per page
$current_page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($current_page - 1) * $records_per_page;

if (isset($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
}

// Build query with search filter
$query = "SELECT * FROM galeri 
          WHERE tajuk LIKE '%$search%' 
          ORDER BY tarikh_ditambah DESC 
          LIMIT $offset, $records_per_page";

$result = $conn->query($query);

// Get total number of records for pagination
$total_query = "SELECT COUNT(*) AS total FROM galeri WHERE tajuk LIKE '%$search%'";
$total_result = $conn->query($total_query);
$total_records = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galeri - Pejabat Pendidikan Daerah Kulim</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            color: #333;
            background-color: #f4f8fb;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
        }

        main {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin: 2rem auto;
            width: 80%;
            max-width: 900px;
            text-align: center;
        }

        h2 {
            font-size: 2.4rem;
            color: #2c3e50;
            margin-bottom: 1.5rem;
            font-weight: 700;
        }

        /* Search Form */
        .search-form {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 2rem;
        }

        .search-form input {
            padding: 0.8rem;
            font-size: 1rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            flex: 1;
        }

        .search-form button {
            padding: 0.8rem 1.5rem;
            font-size: 1rem;
            background-color: #3498db;
            color: white;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .search-form button:hover {
            background-color: #2980b9;
        }

        /* Gallery Styles */
        .gallery-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            padding: 0;
            list-style: none;
            margin: 0;
        }

        .gallery-item {
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            text-align: left;
            transition: transform 0.3s ease;
        }

        .gallery-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        }

        .gallery-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .gallery-item h4 {
            padding: 10px;
            font-size: 1.2rem;
            color: #333;
        }

        .gallery-item p {
            padding: 0 10px;
            font-size: 0.9rem;
            color: #777;
            margin: 5px 0;
        }

        .gallery-item .date {
            font-style: italic;
            font-size: 0.8rem;
            color: #888;
            padding: 0 10px;
            margin-bottom: 10px;
        }

        /* Pagination */
        .pagination {
            margin-top: 1.5rem;
            display: flex;
            justify-content: center;
            gap: 5px;
        }

        .pagination a {
            padding: 0.5rem 1rem;
            font-size: 1rem;
            color: white;
            background-color: #3498db;
            text-decoration: none;
            border-radius: 5px;
        }

        .pagination a:hover {
            background-color: #2980b9;
        }

        .pagination a.disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        .pagination a.active {
            background-color: #2980b9;
            font-weight: bold;
        }

        /* Button */
        .btn {
            display: inline-block;
            margin-top: 2rem;
            padding: 0.75rem 1.5rem;
            background-color: #3498db;
            color: #fff;
            font-weight: bold;
            border-radius: 8px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <main>
        <h2>Galeri Pejabat Pendidikan Daerah Kulim</h2>

        <!-- Search Form -->
        <form class="search-form" method="get" action="">
            <input type="text" name="search" placeholder="Cari tajuk gambar..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">Cari</button>
        </form>

        <!-- Gallery List -->
        <ul class="gallery-list">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <li class="gallery-item">
                        <img src="gambar/<?php echo htmlspecialchars($row['gambar_galeri']); ?>" alt="<?php echo htmlspecialchars($row['tajuk']); ?>">
                        <h4><?php echo htmlspecialchars($row['tajuk']); ?></h4>
                        <p>Jenis: <?php echo htmlspecialchars($row['jenis']); ?></p>
                        <p><?php echo htmlspecialchars($row['penerangan']); ?></p>
                        <p class="date">Tarikh Ditambah: <?php echo date('d-m-Y H:i:s', strtotime($row['tarikh_ditambah'])); ?></p>
                    </li>
                <?php endwhile; ?>
            <?php else: ?>
                <li class="gallery-item">Tiada gambar ditemui.</li>
            <?php endif; ?>
        </ul>

        <!-- Pagination -->
        <div class="pagination">
            <?php if ($current_page > 1): ?>
                <a href="?page=<?php echo $current_page - 1; ?>&search=<?php echo urlencode($search); ?>">Previous</a>
            <?php else: ?>
                <a class="disabled">Previous</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" 
                   class="<?php echo $i == $current_page ? 'active' : ''; ?>">
                   <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <?php if ($current_page < $total_pages): ?>
                <a href="?page=<?php echo $current_page + 1; ?>&search=<?php echo urlencode($search); ?>">Next</a>
            <?php else: ?>
                <a class="disabled">Next</a>
            <?php endif; ?>
        </div>

    </main>
</body>
</html>

<?php
$conn->close(); // Close the database connection
?>
