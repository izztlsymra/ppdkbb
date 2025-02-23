<?php
session_start();
include 'connect.php';
include 'admin_header.php';

// Check if the user is logged in as an admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Fetch the current admin profile
$admin_id = $_SESSION['admin_id'];
$stmt = $conn->prepare("SELECT * FROM admin WHERE admin_id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$current_admin = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch all admins
$result = $conn->query("SELECT * FROM admin");
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile</title>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f0f4f9;
            margin: 0;
        }
        .main-content {
            margin-left: 250px;
            padding: 30px;
        }
        .card {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 30px;
        }
        .card h2 {
            font-size: 1.8rem;
            color: #2c3e50;
            margin-bottom: 20px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        .profile-picture {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;
        }
        .profile-picture img, 
        .table-container table tbody tr td img {
            display: block;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid #3498db;
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        .profile-picture img:hover, 
        .table-container table tbody tr td img:hover {
            transform: scale(1.1);
        }
        .card p {
            font-size: 1rem;
            color: #555;
            margin: 10px 0;
        }
        .card p strong {
            color: #2c3e50;
        }
        .card a {
            display: inline-block;
            margin-top: 15px;
            padding: 12px 20px;
            font-size: 1rem;
            color: #fff;
            background-color: #3498db;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .card a:hover {
            background-color: #2c81c2;
        }
        .table-container {
            overflow-x: auto;
            margin-top: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #ffffff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        table th, table td {
            padding: 15px;
            border: 1px solid #e0e0e0;
            text-align: center;
            font-size: 1rem;
        }
        table th {
            background-color: #3498db;
            color: #ffffff;
            text-transform: uppercase;
            font-weight: bold;
        }
        table tbody tr:hover {
            background-color: #f8fbff;
        }
        .add-button {
            display: inline-block;
            margin-top: 15px;
            padding: 12px 20px;
            font-size: 1rem;
            color: #fff;
            background-color: #27ae60;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: 0.3s ease;
        }
        .add-button:hover {
            background-color: #1e8449;
        }
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.8);
        }
        .modal-content {
            margin: 5% auto;
            display: block;
            max-width: 500px;
            border-radius: 10px;
        }
        .modal img {
            width: 100%;
            border-radius: 10px;
        }
        .close {
            position: absolute;
            top: 20px;
            right: 30px;
            color: #ffffff;
            font-size: 30px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover,
        .close:focus {
            color: #ff0000;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="main-content">
        <!-- Admin Profile Section -->
        <div class="card">
            <h2>My Profile</h2>
            <div class="profile-picture">
                <img src="<?php echo !empty($current_admin['gambar']) ? htmlspecialchars($current_admin['gambar']) : 'default_profile.png'; ?>" 
                     alt="Profile Picture" onclick="openModal(this)">
                <div>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($current_admin['nama']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($current_admin['email']); ?></p>
                    <p><strong>Registered on:</strong> 
                        <?php 
                        $datetime = new DateTime($current_admin['tarikh_daftar']);
                        echo $datetime->format('d-m-Y H:i:s'); 
                        ?>
                    </p>
                </div>
            </div>
            <a href="edit_admin_profile.php?id=<?php echo $admin_id; ?>">Edit My Profile</a>
        </div>

        <!-- Admin List Section -->
        <div class="card">
            <h2>Admin List</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Profile Picture</th>
                            <th>Registration Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($admin = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $admin['admin_id']; ?></td>
                                <td><?php echo htmlspecialchars($admin['nama']); ?></td>
                                <td><?php echo htmlspecialchars($admin['email']); ?></td>
                                <td>
                                    <img src="<?php echo !empty($admin['gambar']) ? htmlspecialchars($admin['gambar']) : 'default_profile.png'; ?>" 
                                         alt="Admin Picture" onclick="openModal(this)">
                                </td>
                                <td>
                                    <?php 
                                    $datetime = new DateTime($admin['tarikh_daftar']);
                                    echo $datetime->format('d-m-Y H:i:s'); 
                                    ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <a href="add_new_admin.php" class="add-button">Add New Admin</a>
        </div>
    </div>

    <!-- Modal -->
    <div id="imageModal" class="modal">
        <span class="close" onclick="closeModal()">&times;</span>
        <img class="modal-content" id="modalImage">
    </div>

    <script>
        function openModal(imageElement) {
            const modal = document.getElementById("imageModal");
            const modalImage = document.getElementById("modalImage");

            modal.style.display = "block";
            modalImage.src = imageElement.src;
        }

        function closeModal() {
            const modal = document.getElementById("imageModal");
            modal.style.display = "none";
        }
    </script>
</body>
</html>