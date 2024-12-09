<?php
session_start();

// Database connection
$host = "localhost";
$dbname = "room_booking";
$username = "root";
$password = "";
$conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);

// Fetch all rooms
$query = "SELECT * FROM rooms";
$stmt = $conn->prepare($query);
$stmt->execute();
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $user_id = $_SESSION['user_id'] ?? null;

    if ($_POST['action'] === 'book') {
        $room_id = $_POST['room_id'];
        $booking_date = $_POST['booking_date'];
        $timeslot = $_POST['timeslot'];

        if (!$user_id) {
            die("You must be logged in to make a booking.");
        }

        // Validate timeslot
        $room_query = "SELECT available_timeslots FROM rooms WHERE id = ?";
        $room_stmt = $conn->prepare($room_query);
        $room_stmt->execute([$room_id]);
        $room = $room_stmt->fetch(PDO::FETCH_ASSOC);

        if ($room && !in_array($timeslot, explode(', ', $room['available_timeslots']))) {
            $error = "Invalid timeslot selected.";
        } else {
            $query = "INSERT INTO bookings (user_id, room_id, booking_date, timeslot) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->execute([$user_id, $room_id, $booking_date, $timeslot]);
            $success = "Booking successful!";
        }
    } elseif ($_POST['action'] === 'cancel') {
        $booking_id = $_POST['booking_id'];
        if ($user_id && $booking_id) {
            $cancel_query = "DELETE FROM bookings WHERE id = ? AND user_id = ?";
            $cancel_stmt = $conn->prepare($cancel_query);
            $cancel_stmt->execute([$booking_id, $user_id]);
            $success = "Booking canceled successfully!";
        } else {
            $error = "Failed to cancel the booking.";
        }
    }
}

// Fetch user's bookings
$bookings_query = "SELECT bookings.id, rooms.name AS room_name, bookings.booking_date, bookings.timeslot 
                   FROM bookings 
                   JOIN rooms ON bookings.room_id = rooms.id 
                   WHERE bookings.user_id = ?";
$bookings_stmt = $conn->prepare($bookings_query);
$bookings_stmt->execute([$_SESSION['user_id'] ?? 0]);
$bookings = $bookings_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Booking</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/picocss@1.5.0/dist/pico.min.css">
    <style>
        body {
            background: linear-gradient(to right, #6a11cb, #2575fc);
            color: white;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: white;
            color: #333;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 400px;
            text-align: center;
        }
        .container h1 {
            font-size: 2rem;
            margin-bottom: 20px;
        }
        .container select, .container input, .container button {
            width: 100%;
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 1rem;
        }
        .container button {
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }
        .container button:hover {
            background-color: #0056b3;
        }
        .error {
            color: red;
            margin-top: 10px;
        }
        .success {
            color: green;
            margin-top: 10px;
        }
    </style>
    <script>
        function updateTimeslots() {
            const rooms = <?= json_encode($rooms) ?>;
            const roomId = document.getElementById('room_id').value;
            const timeslotSelect = document.getElementById('timeslot');

            timeslotSelect.innerHTML = '';

            const selectedRoom = rooms.find(room => room.id == roomId);
            if (selectedRoom) {
                const timeslots = selectedRoom.available_timeslots.split(', ');
                timeslots.forEach(timeslot => {
                    const option = document.createElement('option');
                    option.value = timeslot;
                    option.textContent = timeslot;
                    timeslotSelect.appendChild(option);
                });
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Room Booking</h1>
        <form method="POST">
            <input type="hidden" name="action" value="book">
            <label for="room_id">Select Room:</label>
            <select name="room_id" id="room_id" onchange="updateTimeslots()" required>
                <option value="">-- Select a Room --</option>
                <?php foreach ($rooms as $room): ?>
                    <option value="<?= $room['id'] ?>"><?= htmlspecialchars($room['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <label for="booking_date">Booking Date:</label>
            <input type="date" name="booking_date" id="booking_date" required>

            <label for="timeslot">Time Slot:</label>
            <select name="timeslot" id="timeslot" required>
                <option value="">-- Select a Time Slot --</option>
            </select>

            <button type="submit">Book</button>
        </form>

        <!-- Cancel Booking Section -->
        <form method="POST">
            <input type="hidden" name="action" value="cancel">
            <label for="booking_id">Cancel Booking:</label>
            <select name="booking_id" id="booking_id" required>
                <option value="">-- Select a Booking --</option>
                <?php foreach ($bookings as $booking): ?>
                    <option value="<?= $booking['id'] ?>">
                        <?= htmlspecialchars($booking['room_name']) ?> - <?= htmlspecialchars($booking['booking_date']) ?> (<?= htmlspecialchars($booking['timeslot']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="cancel-button">Cancel</button>
        </form>

        <?php if (isset($success)): ?>
            <p class="success"><?= htmlspecialchars($success) ?></p>
        <?php elseif (isset($error)): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
    </div>
</body>
</html>
