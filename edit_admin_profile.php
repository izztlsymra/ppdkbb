<?php
ob_start();
session_start();
include 'connect.php';
include 'admin_header.php';

// Check if the user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Get admin ID from session (ensure this matches your database column)
$admin_id = $_SESSION['admin_id'];

// Fetch current admin details
$stmt = $conn->prepare("SELECT nama, email, gambar FROM admin WHERE admin_id = ?"); // Change 'id' to 'admin_id'
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin_data = $result->fetch_assoc();
$stmt->close();

// Initialize variables
$message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_BCRYPT) : null;

    // Handle profile picture upload
    $gambar = $admin_data['gambar'];
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

            // Delete old profile picture if it exists
            if (!empty($admin_data['gambar']) && file_exists($admin_data['gambar'])) {
                unlink($admin_data['gambar']);
            }
        } else {
            $message = "Failed to upload image.";
        }
    }

    // Update admin profile
    if ($password) {
        $stmt = $conn->prepare("UPDATE admin SET nama = ?, email = ?, password = ?, gambar = ? WHERE admin_id = ?"); // Change 'id' to 'admin_id'
        $stmt->bind_param("ssssi", $nama, $email, $password, $gambar, $admin_id);
    } else {
        $stmt = $conn->prepare("UPDATE admin SET nama = ?, email = ?, gambar = ? WHERE admin_id = ?"); // Change 'id' to 'admin_id'
        $stmt->bind_param("sssi", $nama, $email, $gambar, $admin_id);
    }

    if ($stmt->execute()) {
        $message = "Profile updated successfully!";
        $_SESSION['admin_name'] = $nama; // Update session name
        header("Location: edit_admin_profile.php"); // Refresh page
        exit;
    } else {
        $message = "Error updating profile: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Admin Profile</title>
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
                preview.src = '<?php echo !empty($admin_data["gambar"]) ? $admin_data["gambar"] : "placeholder.png"; ?>';
            }
        }
    </script>
</head>
<body>
    <div class="main-content">
        <div class="form-container">
            <h2>Edit Admin Profile</h2>
            <?php if ($message): ?>
                <div class="message" style="background-color: #2ecc71; color: white; padding: 10px; border-radius: 5px;">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="profile-picture">
                    <img id="profile-preview" src="<?php echo !empty($admin_data['gambar']) ? $admin_data['gambar'] : 'placeholder.png'; ?>" alt="Profile Picture">
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
                    <label for="password">New Password (Leave blank if unchanged):</label>
                    <input type="password" name="password" id="password">
                </div>
                <div class="form-actions">
                    <button type="submit">Update Profile</button>
                </div>
            </form>
            <a href="admin.php" class="back-link">Back to Admin List</a>
        </div>
    </div>
</body>
</html>
