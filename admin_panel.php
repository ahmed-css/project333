<?php
session_start();

// Ensure only admins can access this page
$_SESSION['is_admin'] = true;

// Database connection
$host = "localhost";
$dbname = "room_booking";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Add Room
if (isset($_POST['add_room'])) {
    $name = $_POST['name'];
    $capacity = $_POST['capacity'];
    $equipment = $_POST['equipment'];
    $available_timeslots = $_POST['available_timeslots'];
    $image_url = $_POST['image_url'];

    $stmt = $pdo->prepare("INSERT INTO rooms (name, capacity, equipment, available_timeslots, image_url) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$name, $capacity, $equipment, $available_timeslots, $image_url]);
    $success = "Room added successfully!";
}

// Edit Room
if (isset($_POST['edit_room'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $capacity = $_POST['capacity'];
    $equipment = $_POST['equipment'];
    $available_timeslots = $_POST['available_timeslots'];
    $image_url = $_POST['image_url'];

    $stmt = $pdo->prepare("UPDATE rooms SET name = ?, capacity = ?, equipment = ?, available_timeslots = ?, image_url = ? WHERE id = ?");
    $stmt->execute([$name, $capacity, $equipment, $available_timeslots, $image_url, $id]);
    $success = "Room updated successfully!";
}

// Delete Room
if (isset($_GET['delete_room'])) {
    $id = $_GET['delete_room'];

    $stmt = $pdo->prepare("DELETE FROM rooms WHERE id = ?");
    $stmt->execute([$id]);
    $success = "Room deleted successfully!";
}

// Fetch all rooms
$rooms_stmt = $pdo->query("SELECT * FROM rooms");
$rooms = $rooms_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch room for editing if `edit_room` is in the query
$room_to_edit = null;
if (isset($_GET['edit_room'])) {
    $id = $_GET['edit_room'];
    $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
    $stmt->execute([$id]);
    $room_to_edit = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/picocss@1.5.0/dist/pico.min.css">
    <style>
        body {
            background: linear-gradient(to bottom, #f7f9fc, #e1ecf4);
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        h1, h2 {
            color: #333;
            text-align: center;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        form input, form textarea, form button {
            padding: 10px;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        form button {
            background-color: #0078d7;
            color: white;
            border: none;
            cursor: pointer;
        }
        form button:hover {
            background-color: #005bb5;
        }
        ul {
            list-style-type: none;
            padding: 0;
        }
        ul li {
            background: #f9f9f9;
            padding: 10px;
            margin-bottom: 5px;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        ul li a {
            text-decoration: none;
            color: #0078d7;
            font-weight: bold;
            margin-left: 10px;
        }
        ul li a:hover {
            text-decoration: underline;
        }
        .success {
            color: green;
            font-size: 1.2rem;
            margin: 10px 0;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Admin Panel</h1>

        <?php if (isset($success)): ?>
            <p class="success"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>

        <!-- Add or Edit Room -->
        <section>
            <h2><?= $room_to_edit ? 'Edit Room' : 'Add a New Room' ?></h2>
            <form method="POST">
                <?php if ($room_to_edit): ?>
                    <input type="hidden" name="id" value="<?= htmlspecialchars($room_to_edit['id']) ?>">
                <?php endif; ?>
                <input type="text" name="name" placeholder="Room Name" value="<?= $room_to_edit['name'] ?? '' ?>" required>
                <input type="number" name="capacity" placeholder="Capacity" value="<?= $room_to_edit['capacity'] ?? '' ?>" required>
                <textarea name="equipment" placeholder="Equipment (comma-separated)" required><?= $room_to_edit['equipment'] ?? '' ?></textarea>
                <textarea name="available_timeslots" placeholder="Available Timeslots (comma-separated)" required><?= $room_to_edit['available_timeslots'] ?? '' ?></textarea>
                <input type="url" name="image_url" placeholder="Image URL" value="<?= $room_to_edit['image_url'] ?? '' ?>">
                <button type="submit" name="<?= $room_to_edit ? 'edit_room' : 'add_room' ?>">
                    <?= $room_to_edit ? 'Save Changes' : 'Add Room' ?>
                </button>
            </form>
        </section>

        <!-- Existing Rooms -->
        <section>
            <h2>Existing Rooms</h2>
            <ul>
                <?php foreach ($rooms as $room): ?>
                    <li>
                        <?= htmlspecialchars($room['name']) ?> - Capacity: <?= htmlspecialchars($room['capacity']) ?>
                        <a href="?edit_room=<?= $room['id'] ?>">Edit</a>
                        <a href="?delete_room=<?= $room['id'] ?>">Delete</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    </div>
</body>
</html>
