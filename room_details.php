<?php
// Database connection
$host = "localhost";
$dbname = "room_booking";
$username = "root";
$password = "";
$conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);

// Check if the 'id' parameter is set
if (!isset($_GET['id']) || empty($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<h1 style='text-align:center; color:red;'>Room ID is missing or invalid!</h1>";
    exit;
}

// Fetch room details
$id = $_GET['id'];
$query = "SELECT * FROM rooms WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$id]);
$room = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$room) {
    echo "<h1 style='text-align:center; color:red;'>Room not found!</h1>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($room['name']) ?> - Room Details</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/picocss@1.5.0/dist/pico.min.css">
    <style>
        body {
            background: linear-gradient(to bottom, #6a11cb, #2575fc);
            color: #fff;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #fff;
            color: #333;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            text-align: center;
        }
        .room-image {
            width: 100%;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        h1 {
            font-size: 2.5em;
            margin-bottom: 20px;
            color: #444;
        }
        p {
            font-size: 1.2em;
            color: #555;
        }
        .info {
            text-align: left;
            margin-top: 20px;
        }
        .info strong {
            color: #333;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            font-size: 1.1em;
            color: #fff;
            background: #2575fc;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .button:hover {
            background: #6a11cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="<?= htmlspecialchars($room['image_url']) ?>" alt="Room Image" class="room-image">
        <h1><?= htmlspecialchars($room['name']) ?></h1>
        <div class="info">
            <p><strong>Capacity:</strong> <?= htmlspecialchars($room['capacity']) ?></p>
            <p><strong>Equipment:</strong> <?= htmlspecialchars($room['equipment']) ?></p>
            <p><strong>Available Timeslots:</strong> <?= htmlspecialchars($room['available_timeslots']) ?></p>
        </div>
        <a href="browse_rooms.php" class="button">Back to Rooms</a>
    </div>
</body>
</html>
