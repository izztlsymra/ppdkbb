<?php
session_start();
include 'admin_header.php';
include 'connect.php';

// Check if the user is logged in as an admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Fetch the admin's details from the session or database
$adminId = $_SESSION['admin_id'] ?? null;

// Fetch admin data from the database
$adminData = [];
if ($adminId) {
    $stmt = $conn->prepare("SELECT * FROM admin WHERE admin_id = ?");
    $stmt->bind_param("i", $adminId);
    $stmt->execute();
    $result = $stmt->get_result();
    $adminData = $result->fetch_assoc();
    $stmt->close();
}

// Handle profile update
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_BCRYPT) : null;

    // Handle profile picture upload
    $profilePicture = $adminData['gambar'] ?? '';
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/admin/';
        $fileName = uniqid() . '_' . basename($_FILES['profile_picture']['name']);
        $uploadFile = $uploadDir . $fileName;

        // Ensure upload directory exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $uploadFile)) {
            $profilePicture = $uploadFile;
        } else {
            $message = 'Failed to upload profile picture.';
        }
    }

    // Update admin data
    if ($password) {
        $stmt = $conn->prepare("UPDATE admin SET nama = ?, email = ?, password = ?, gambar = ? WHERE admin_id = ?");
        $stmt->bind_param("ssssi", $name, $email, $password, $profilePicture, $adminId);
    } else {
        $stmt = $conn->prepare("UPDATE admin SET nama = ?, email = ?, gambar = ? WHERE admin_id = ?");
        $stmt->bind_param("sssi", $name, $email, $profilePicture, $adminId);
    }

    if ($stmt->execute()) {
        $message = 'Profile updated successfully!';
        $_SESSION['nama'] = $name;
        $_SESSION['gambar'] = $profilePicture;
        $stmt->close();
        header("Location: profile.php");
        exit;
    } else {
        $message = 'Error updating profile: ' . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Admin</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fc;
            margin: 0;
        }

        .main-content {
            margin-left: 250px;
            padding: 30px;
        }

        .form-container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 0 auto;
        }

        .form-container h2 {
            margin-bottom: 20px;
            color: #34495e;
        }

        .form-group img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 10px;
        }

        .form-actions {
            margin-top: 20px;
            text-align: right;
        }

        .form-actions button {
            padding: 10px 20px;
            background: #3498db;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .form-actions button:hover {
            background: #2980b9;
        }
    </style>
</head>
<body>
<div class="main-content">
    <div class="form-container">
        <h2>Kemaskini Profil</h2>
        <?php if (!empty($message)): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="profile_picture">Gambar Profil:</label><br>
                <img src="<?php echo htmlspecialchars($adminData['gambar'] ?? 'default_profile.png'); ?>" alt="Profil Gambar">
                <input type="file" name="profile_picture" id="profile_picture" accept="image/*">
            </div>
            <div class="form-group">
                <label for="name">Nama:</label>
                <input type="text" name="name" id="name" class="form-control" value="<?php echo htmlspecialchars($adminData['nama'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Emel:</label>
                <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($adminData['email'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Kata Laluan (kosongkan jika tidak ingin tukar):</label>
                <input type="password" name="password" id="password" class="form-control">
            </div>
            <div class="form-actions">
                <button type="submit">Kemaskini Profil</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
