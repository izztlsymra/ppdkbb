<?php
session_start();
include 'header.php';
include 'connect.php'; // Make sure this file establishes a connection to your database

// Initialize search, category filters, and pagination variables
$search = '';
$category = '';
$records_per_page = 20;
$current_page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($current_page - 1) * $records_per_page;

if (isset($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
}
if (isset($_GET['category'])) {
    $category = $conn->real_escape_string($_GET['category']);
}

// Base query
$query = "SELECT * FROM sekolah WHERE 
    (kod_sekolah LIKE '%$search%' OR 
    nama_sekolah LIKE '%$search%' OR 
    no_telefon LIKE '%$search%')";

// Add category filter if selected
if (!empty($category)) {
    switch ($category) {
        case 'SJKT':
            $query .= " AND nama_sekolah LIKE '%SEKOLAH JENIS KEBANGSAAN (TAMIL)%'";
            break;
        case 'SJKC':
            $query .= " AND nama_sekolah LIKE '%SEKOLAH JENIS KEBANGSAAN (CINA)%'";
            break;
        case 'SK':
            $query .= " AND nama_sekolah LIKE '%SEKOLAH KEBANGSAAN%'";
            break;
        case 'SMK':
            $query .= " AND nama_sekolah LIKE '%SEKOLAH MENENGAH KEBANGSAAN%'";
            break;
        case 'SMA':
            $query .= " AND nama_sekolah LIKE '%SEKOLAH MENENGAH AGAMA%'";
            break;
        case 'Maktab':
            $query .= " AND nama_sekolah LIKE '%MAKTAB%'";
            break;
        case 'Kolej':
            $query .= " AND nama_sekolah LIKE '%KOLEJ%'";
            break;
        case 'Rendah':
            $query .= " AND nama_sekolah LIKE '%RENDAH%'";
            break;
        case 'Maahad':
            $query .= " AND nama_sekolah LIKE '%MAAHAD%'";
            break;
        case 'Madrasah':
            $query .= " AND nama_sekolah LIKE '%MADRASAH%'";
            break;
    }
}

// Count total records for pagination
$total_query = "SELECT COUNT(*) as total FROM ($query) AS temp_table";
$total_result = $conn->query($total_query);
$total_records = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);

// Add LIMIT for pagination
$query .= " LIMIT $offset, $records_per_page";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sekolah - Pejabat Pendidikan Daerah Kulim</title>
    <style>
        /* Global Styles */
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f8fb;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        /* Main Content Styles */
        main {
            background: linear-gradient(135deg, #f4f8fb 40%, #ffffff);
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            padding: 3rem;
            margin-top: 2rem;
            width: 80%;
            max-width: 900px;
        }

        main h2 {
            font-size: 2.2rem;
            color: #2c3e50;
            margin-bottom: 1.5rem;
            font-weight: 700;
            text-transform: uppercase;
            text-align: center;
        }

        /* Search Form Styles */
        .search-form {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
            gap: 10px;
        }

        .search-form input,
        .search-form select {
            padding: 0.75rem;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 100%;
        }

        .search-form button {
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            border: none;
            background-color: #3498db;
            color: white;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .search-form button:hover {
            background-color: #2980b9;
        }

        /* Table Styles */
        .school-table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            border-radius: 10px;
            overflow: hidden;
        }

        .school-table th, .school-table td {
            padding: 15px;
            text-align: center;
            border: 1px solid #ddd;
        }

        .school-table th {
            background-color: #2c3e50;
            color: white;
            font-weight: bold;
        }

        .school-table tbody tr:nth-child(odd) {
            background-color: #f9f9f9;
        }

        .school-table tbody tr:hover {
            background-color: #e6f7ff;
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
        <h2>Senarai Sekolah di Daerah Kulim</h2>
        
        <!-- Search and Filter Form -->
        <form class="search-form" method="get" action="">
            <input type="text" name="search" placeholder="Cari sekolah..." value="<?php echo htmlspecialchars($search); ?>">
            <select name="category">
                <option value="">Semua Jenis</option>
                <option value="SJKT" <?php if ($category == 'SJKT') echo 'selected'; ?>>SJKT</option>
                <option value="SJKC" <?php if ($category == 'SJKC') echo 'selected'; ?>>SJKC</option>
                <option value="SK" <?php if ($category == 'SK') echo 'selected'; ?>>SK</option>
                <option value="SMK" <?php if ($category == 'SMK') echo 'selected'; ?>>SMK</option>
                <option value="SMA" <?php if ($category == 'SMA') echo 'selected'; ?>>SMA</option>
                <option value="Maktab" <?php if ($category == 'Maktab') echo 'selected'; ?>>MAKTAB</option>
                <option value="Kolej" <?php if ($category == 'Kolej') echo 'selected'; ?>>KOLEJ</option>
                <option value="Rendah" <?php if ($category == 'Rendah') echo 'selected'; ?>>RENDAH</option>
                <option value="Maahad" <?php if ($category == 'Maahad') echo 'selected'; ?>>MAAHAD</option>
                <option value="Madrasah" <?php if ($category == 'Madrasah') echo 'selected'; ?>>MADRASAH</option>
            </select>
            <button type="submit">Cari</button>
        </form>
        
        <table class="school-table">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Kod Sekolah</th>
                    <th>Nama Sekolah</th>
                    <th>No. Telefon</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php $counter = $offset + 1; ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $counter++; ?></td>
                            <td><?php echo htmlspecialchars($row['kod_sekolah']); ?></td>
                            <td><?php echo htmlspecialchars($row['nama_sekolah']); ?></td>
                            <td><?php echo htmlspecialchars($row['no_telefon']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">Tiada sekolah ditemui.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="pagination">
            <?php if ($current_page > 1): ?>
                <a href="?page=<?php echo $current_page - 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>">Previous</a>
            <?php else: ?>
                <a class="disabled">Previous</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>" 
                   <?php if ($i == $current_page) echo 'style="background-color: #2980b9;"'; ?>>
                   <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <?php if ($current_page < $total_pages): ?>
                <a href="?page=<?php echo $current_page + 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>">Next</a>
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
