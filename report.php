<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to access this page.");
}

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

// Fetch room popularity
$room_popularity_stmt = $pdo->query("SELECT rooms.name, COUNT(bookings.id) AS booking_count 
                                     FROM bookings 
                                     JOIN rooms ON bookings.room_id = rooms.id 
                                     GROUP BY rooms.id 
                                     ORDER BY booking_count DESC");
$room_popularity = $room_popularity_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch most popular time slots
$timeslot_popularity_stmt = $pdo->query("SELECT timeslot, COUNT(id) AS booking_count 
                                         FROM bookings 
                                         GROUP BY timeslot 
                                         ORDER BY booking_count DESC");
$timeslot_popularity = $timeslot_popularity_stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Usage Statistics</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/picocss@1.5.0/dist/pico.min.css">
    <style>
        body {
            background: linear-gradient(to bottom, #ffefba, #ffffff);
            font-family: 'Arial', sans-serif;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        h1, h2 {
            text-align: center;
            color: #333;
        }
        table {
            width: 100%;
            margin-top: 15px;
            border-collapse: collapse;
        }
        table th, table td {
            padding: 12px;
            text-align: center;
            border: 1px solid #ddd;
        }
        table th {
            background-color: #f4a261;
            color: white;
        }
        table tr:nth-child(even) {
            background-color: #ffe8d6;
        }
        table tr:hover {
            background-color: #ffccbc;
        }
        .chart-container {
            margin: 20px 0;
            text-align: center;
        }
        .chart {
            width: 100%;
            max-width: 600px;
            height: auto;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Room Usage Statistics</h1>

        <!-- Room Popularity -->
        <section>
            <h2>Most Popular Rooms</h2>
            <table>
                <thead>
                    <tr>
                        <th>Room</th>
                        <th>Bookings</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($room_popularity as $room): ?>
                        <tr>
                            <td><?= htmlspecialchars($room['name']) ?></td>
                            <td><?= htmlspecialchars($room['booking_count']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <!-- Time Slot Popularity -->
        <section>
            <h2>Most Popular Time Slots</h2>
            <table>
                <thead>
                    <tr>
                        <th>Time Slot</th>
                        <th>Bookings</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($timeslot_popularity as $timeslot): ?>
                        <tr>
                            <td><?= htmlspecialchars($timeslot['timeslot']) ?></td>
                            <td><?= htmlspecialchars($timeslot['booking_count']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </div>
</body>
</html>

