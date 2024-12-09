<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Database connection
$pdo = new PDO('mysql:host=localhost;dbname=room_booking', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $booking_id = $_POST['booking_id'];

    // Delete the selected booking
    $stmt = $pdo->prepare('DELETE FROM bookings WHERE id = ? AND user_id = ?');
    if ($stmt->execute([$booking_id, $user_id])) {
        $success = "Booking canceled successfully!";
    } else {
        $error = "Failed to cancel booking. Please try again.";
    }
}

// Fetch user bookings
$stmt = $pdo->prepare('
    SELECT bookings.id, rooms.name, bookings.booking_date, bookings.timeslot 
    FROM bookings 
    JOIN rooms ON bookings.room_id = rooms.id 
    WHERE bookings.user_id = ?
');
$stmt->execute([$user_id]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cancel Booking</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/picocss@1.5.0/dist/pico.min.css">
    <style>
        body {
            background: linear-gradient(to bottom, #ff7f50, #ff6347);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            font-family: Arial, sans-serif;
            color: #fff;
        }
        .container {
            background: #fff;
            color: #333;
            padding: 20px;
            max-width: 400px;
            width: 90%;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            text-align: center;
        }
        h1 {
            font-size: 2rem;
            margin-bottom: 20px;
        }
        select, button, a {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            font-size: 1rem;
            border-radius: 5px;
            border: 1px solid #ccc;
            text-align: center;
        }
        button {
            background-color: #f44336;
            color: #fff;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }
        button:hover {
            background-color: #d32f2f;
        }
        a {
            background-color: #00796b;
            color: white;
            text-decoration: none;
            display: inline-block;
        }
        a:hover {
            background-color: #004d40;
        }
        .message {
            font-size: 1rem;
            margin-top: 15px;
        }
        .success {
            color: green;
        }
        .error {
            color: red;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Your Bookings</h1>

        <?php if (isset($success)): ?>
            <p class="message success"><?= htmlspecialchars($success) ?></p>
        <?php elseif (isset($error)): ?>
            <p class="message error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <?php if (!empty($bookings)): ?>
            <form method="POST">
                <label for="booking_id">Select a Booking to Cancel:</label>
                <select name="booking_id" id="booking_id" required>
                    <?php foreach ($bookings as $booking): ?>
                        <option value="<?= $booking['id'] ?>">
                            <?= htmlspecialchars($booking['name']) ?> - <?= htmlspecialchars($booking['booking_date']) ?> (<?= htmlspecialchars($booking['timeslot']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Cancel Booking</button>
                <a href="booking.php">Back to Booking Page</a>
            </form>
        <?php else: ?>
            <p class="message">You have no bookings to cancel.</p>
        <?php endif; ?>
    </div>
</body>
</html>
