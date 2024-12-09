<?php
session_start();

// Database connection
$host = "localhost";
$dbname = "room_booking";
$username = "root";
$password = "";
$conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user data
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];

    // Handle profile picture upload
    $profile_picture = $user['profile_picture'];
    if (!empty($_FILES['profile_picture']['name'])) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Validate file type
        $allowed_types = ["jpg", "jpeg", "png", "gif"];
        if (in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                $profile_picture = $target_file;
            } else {
                $error = "Failed to upload profile picture.";
            }
        } else {
            $error = "Invalid file type. Only JPG, JPEG, PNG & GIF files are allowed.";
        }
    }

    // Update user data in the database
    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, profile_picture = ? WHERE id = ?");
    $stmt->execute([$name, $email, $profile_picture, $user_id]);
    $success = "Profile updated successfully!";
    $user['name'] = $name;
    $user['email'] = $email;
    $user['profile_picture'] = $profile_picture;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/picocss@1.5.0/dist/pico.min.css">
    <style>
        body {
            background: linear-gradient(to right, #6a11cb, #2575fc);
            color: white;
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            background: white;
            color: black;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            text-align: center;
        }
        .profile-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 20px;
            border: 3px solid #6a11cb;
        }
        form input[type="file"] {
            margin-top: 10px;
        }
        .success, .error {
            color: white;
            padding: 10px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .success {
            background: #4caf50;
        }
        .error {
            background: #f44336;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>User Profile</h1>

        <!-- Display Profile Picture -->
        <img src="<?= htmlspecialchars($user['profile_picture'] ?? 'default-profile.png') ?>" alt="Profile Picture" class="profile-picture">

        <!-- Success or Error Messages -->
        <?php if (isset($success)): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Profile Form -->
        <form method="POST" enctype="multipart/form-data">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

            <label for="profile_picture">Profile Picture</label>
            <input type="file" id="profile_picture" name="profile_picture" accept="image/*">

            <button type="submit">Save Changes</button>
        </form>
    </div>
</body>
</html>
