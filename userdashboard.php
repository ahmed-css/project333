<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to access this page.");
}

$user_id = $_SESSION['user_id'];

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

// Fetch upcoming bookings
$upcoming_stmt = $pdo->prepare("SELECT rooms.name AS room_name, bookings.booking_date, bookings.timeslot 
                                FROM bookings 
                                JOIN rooms ON bookings.room_id = rooms.id 
                                WHERE bookings.user_id = ? AND bookings.booking_date >= CURDATE() 
                                ORDER BY bookings.booking_date");
$upcoming_stmt->execute([$user_id]);
$upcoming_bookings = $upcoming_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch past bookings
$past_stmt = $pdo->prepare("SELECT rooms.name AS room_name, bookings.booking_date, bookings.timeslot 
                            FROM bookings 
                            JOIN rooms ON bookings.room_id = rooms.id 
                            WHERE bookings.user_id = ? AND bookings.booking_date < CURDATE() 
                            ORDER BY bookings.booking_date DESC");
$past_stmt->execute([$user_id]);
$past_bookings = $past_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/picocss@1.5.0/dist/pico.min.css">
    <style>
        body {
            background: linear-gradient(to right, #ff9a9e, #fad0c4);
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
            background-color: #ff6f61;
            color: white;
        }
        table tr:nth-child(even) {
            background-color: #ffe8e8;
        }
        table tr:hover {
            background-color: #ffcccc;
        }
        .info-box {
            margin: 20px 0;
            padding: 15px;
            background: #ffe8e8;
            border: 1px solid #ff6f61;
            border-radius: 8px;
            text-align: center;
        }
        .info-box p {
            margin: 0;
            color: #d63031;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Your Dashboard</h1>

        <!-- Upcoming Bookings -->
        <section>
            <h2>Upcoming Bookings</h2>
            <table>
                <thead>
                    <tr>
                        <th>Room</th>
                        <th>Date</th>
                        <th>Timeslot</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($upcoming_bookings) > 0): ?>
                        <?php foreach ($upcoming_bookings as $booking): ?>
                            <tr>
                                <td><?= htmlspecialchars($booking['room_name']) ?></td>
                                <td><?= htmlspecialchars($booking['booking_date']) ?></td>
                                <td><?= htmlspecialchars($booking['timeslot']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3">No upcoming bookings.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <!-- Past Bookings -->
        <section>
            <h2>Past Bookings</h2>
            <table>
                <thead>
                    <tr>
                        <th>Room</th>
                        <th>Date</th>
                        <th>Timeslot</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($past_bookings) > 0): ?>
                        <?php foreach ($past_bookings as $booking): ?>
                            <tr>
                                <td><?= htmlspecialchars($booking['room_name']) ?></td>
                                <td><?= htmlspecialchars($booking['booking_date']) ?></td>
                                <td><?= htmlspecialchars($booking['timeslot']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3">No past bookings.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </div>
</body>
</html>

