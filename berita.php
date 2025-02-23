<?php
session_start();
include 'header.php';
include 'connect.php'; // Ensure this file establishes a connection to your database

// Initialize variables for search, pagination, and filtering
$search = '';
$records_per_page = 5;  // Number of records per page
$current_page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($current_page - 1) * $records_per_page;

if (isset($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
}

// Build query with search filter
$query = "SELECT * FROM berita 
          WHERE tajuk LIKE '%$search%' OR kandungan LIKE '%$search%' 
          ORDER BY tarikh_ditambah DESC 
          LIMIT $offset, $records_per_page";

$result = $conn->query($query);

// Get total number of records for pagination
$total_query = "SELECT COUNT(*) AS total FROM berita 
                WHERE tajuk LIKE '%$search%' OR kandungan LIKE '%$search%'";
$total_result = $conn->query($total_query);
$total_records = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);

/**
 * Function to make URLs in text clickable
 */
function makeLinksClickable($text) {
    return preg_replace(
        '~(http|https|ftp)://[^\s<]+~i',
        '<a href="$0" target="_blank">$0</a>',
        $text
    );
}
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berita - Pejabat Pendidikan Daerah Kulim</title>
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

        /* News List */
        .news-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1rem;
        }

        .news-item {
            background-color: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            text-align: left;
            transition: transform 0.3s ease;
        }

        .news-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        }

        .news-item h3 {
            font-size: 1.6rem;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .news-item p {
            margin: 0.5rem 0;
            color: #555;
            word-wrap: break-word;
            word-break: break-word;
            white-space: normal;
        }

        .news-item img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin-top: 1rem;
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
    </style>
</head>
<body>
    <main>
        <h2>Senarai Berita di Daerah Kulim</h2>

        <!-- Search Form -->
        <form class="search-form" method="get" action="">
            <input type="text" name="search" placeholder="Cari tajuk atau kandungan..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">Cari</button>
        </form>

        <!-- News List -->
        <ul class="news-list">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <li class="news-item">
                        <h3><?php echo htmlspecialchars($row['tajuk']); ?></h3>
                        <p><strong>Penulis:</strong> <?php echo htmlspecialchars($row['penulis']); ?></p>
                        <p><strong>Tarikh:</strong> <?php echo date("d-m-Y", strtotime($row['tarikh_ditambah'])); ?></p>
                        <p><strong>Kandungan:</strong> <?php echo makeLinksClickable(nl2br(htmlspecialchars($row['kandungan']))); ?></p>
                        <?php if (!empty($row['gambar_berita'])): ?>
                            <img src="gambar/<?php echo htmlspecialchars($row['gambar_berita']); ?>" alt="Gambar Berita">
                        <?php endif; ?>
                    </li>
                <?php endwhile; ?>
            <?php else: ?>
                <li class="news-item">Tiada berita ditemui.</li>
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
