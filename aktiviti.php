<?php
session_start();
include 'header.php';
include 'connect.php';

// Initialize variables for search, pagination, and filtering
$search = '';
$records_per_page = 5;
$current_page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($current_page - 1) * $records_per_page;

if (isset($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
}

// Build the query to fetch activities based on the search filter
$query = "SELECT * FROM aktiviti WHERE 
    (nama_aktiviti LIKE '%$search%' OR 
    lokasi_aktiviti LIKE '%$search%')
    ORDER BY tarikh_aktiviti DESC 
    LIMIT $offset, $records_per_page";

$result = $conn->query($query);

// Get total records for pagination
$total_query = "SELECT COUNT(*) AS total FROM aktiviti WHERE 
    (nama_aktiviti LIKE '%$search%' OR lokasi_aktiviti LIKE '%$search%')";
$total_result = $conn->query($total_query);
$total_records = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aktiviti - Pejabat Pendidikan Daerah Kulim</title>
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

        /* Search Form Styles */
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

        /* Activity Card Styles */
        .activity-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1rem;
        }

        .activity-item {
            background-color: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            text-align: left;
            transition: transform 0.3s ease;
        }

        .activity-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        }

        .activity-item h3 {
            font-size: 1.6rem;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .activity-item p {
            margin: 0.5rem 0;
            color: #555;
        }

        .activity-item img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin-top: 1rem;
        }

        /* Pagination Styles */
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
    </style>
</head>
<body>
    <main>
        <h2>Senarai Aktiviti di Daerah Kulim</h2>

        <!-- Search Form -->
        <form class="search-form" method="get" action="">
            <input type="text" name="search" placeholder="Cari aktiviti atau lokasi..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">Cari</button>
        </form>

        <!-- Activity List -->
        <ul class="activity-list">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <li class="activity-item">
                        <h3><?php echo htmlspecialchars($row['nama_aktiviti']); ?></h3>
                        <p><strong>Tarikh:</strong> <?php echo date('d-m-Y', strtotime($row['tarikh_aktiviti'])); ?></p>
                        <p><strong>Lokasi:</strong> <?php echo htmlspecialchars($row['lokasi_aktiviti']); ?></p>
                        <p><strong>Perincian:</strong> <?php echo htmlspecialchars($row['perincian']); ?></p>
                        <?php if (!empty($row['gambar_aktiviti'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($row['gambar_aktiviti']); ?>" alt="Gambar Aktiviti">
                        <?php endif; ?>
                    </li>
                <?php endwhile; ?>
            <?php else: ?>
                <li class="activity-item">Tiada aktiviti ditemui.</li>
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
