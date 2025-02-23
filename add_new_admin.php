<?php
ob_start();
session_start();
include 'connect.php';
include 'admin_header.php';

// Check if the user is logged in as an admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Initialize variables
$message = "";
$admin_data = [
    'nama' => '',
    'email' => '',
    'gambar' => ''
];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_BCRYPT) : null;
    $tarikh_daftar = date("Y-m-d H:i:s");

    // Handle profile picture upload
    $gambar = '';
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/admin/';
        $fileName = uniqid() . '_' . basename($_FILES['gambar']['name']);
        $uploadFile = $uploadDir . $fileName;

        // Ensure upload directory exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Move the uploaded file
        if (move_uploaded_file($_FILES['gambar']['tmp_name'], $uploadFile)) {
            $gambar = $uploadFile;
        } else {
            $message = "Failed to upload image.";
        }
    }

    // Add new admin
    $stmt = $conn->prepare("INSERT INTO admin (nama, email, password, gambar, tarikh_daftar) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $nama, $email, $password, $gambar, $tarikh_daftar);

    if ($stmt->execute()) {
        $message = "New admin added successfully!";
        header("Location: admin.php"); // Redirect to admin list
        exit;
    } else {
        $message = "Error adding admin: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Admin</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f8fb;
            margin: 0;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .form-container {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        h2 {
            margin-bottom: 20px;
            color: #34495e;
        }
        .profile-picture {
            margin-bottom: 20px;
            position: relative;
            width: 150px;
            height: 150px;
            margin: 0 auto;
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid #ddd;
        }
        .profile-picture img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }
        .form-group label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
            color: #2c3e50;
        }
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="password"],
        .form-group input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .form-actions {
            text-align: center;
        }
        .form-actions button {
            padding: 10px 20px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .form-actions button:hover {
            background: #2980b9;
        }
        .back-link {
            display: block;
            margin-top: 20px;
            text-align: center;
            color: #3498db;
            text-decoration: none;
            font-weight: bold;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
    <script>
        function previewImage(event) {
            const file = event.target.files[0];
            const preview = document.getElementById('profile-preview');

            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    preview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            } else {
                preview.src = 'placeholder.png'; // Fallback to default placeholder
            }
        }
    </script>
</head>
<body>
    <div class="main-content">
        <div class="form-container">
            <h2>Add New Admin</h2>
            <?php if ($message): ?>
                <div class="message" style="background-color: #e74c3c; color: white; padding: 10px; border-radius: 5px;">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="profile-picture">
                    <img id="profile-preview" src="placeholder.png" alt="Profile Picture">
                </div>
                <div class="form-group">
                    <label for="gambar">Profile Picture:</label>
                    <input type="file" name="gambar" id="gambar" accept="image/*" onchange="previewImage(event)">
                </div>
                <div class="form-group">
                    <label for="nama">Name:</label>
                    <input type="text" name="nama" id="nama" value="<?php echo htmlspecialchars($admin_data['nama']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($admin_data['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" name="password" id="password" required>
                </div>
                <div class="form-actions">
                    <button type="submit">Add Admin</button>
                </div>
            </form>
            <a href="admin.php" class="back-link">Back to Admin List</a>
        </div>
    </div>
</body>
</html>
