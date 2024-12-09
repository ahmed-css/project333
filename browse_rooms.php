<?php
// Start the session
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

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'], $_POST['room_id'])) {
    if (!isset($_SESSION['user_id'])) {
        die("You must be logged in to leave a comment.");
    }

    $comment = $_POST['comment'];
    $room_id = $_POST['room_id'];
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO comments (room_id, user_id, comment) VALUES (?, ?, ?)");
    $stmt->execute([$room_id, $user_id, $comment]);
    $success = "Your comment has been submitted and is awaiting approval!";
}

// Fetch comments with admin responses and profile pictures for each room
$comments_query = "SELECT comments.*, users.name AS user_name, users.profile_picture 
                   FROM comments 
                   JOIN users ON comments.user_id = users.id 
                   WHERE room_id = ? AND is_approved = TRUE 
                   ORDER BY created_at DESC";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Rooms</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/picocss@1.5.0/dist/pico.min.css">
    <style>
        body {
            background-color: #f7f9fc;
        }
        .container {
            max-width: 1000px;
            margin: 50px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 2.5em;
            color: #333;
        }
        ul {
            list-style-type: none;
            padding: 0;
        }
        li {
            margin: 15px 0;
            padding: 15px;
            background: #e0f7fa;
            border: 1px solid #b2ebf2;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .room-thumbnail {
            width: 100%;
            height: auto;
            border-radius: 8px;
            margin-bottom: 15px;
            object-fit: contain;
        }
        .details {
            font-size: 0.9em;
            color: #555;
        }
        .navigation-bar {
            display: flex;
            justify-content: space-around;
            margin-bottom: 20px;
            padding: 10px;
            background: #00796b;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .navigation-bar a {
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            background: #004d40;
            border-radius: 5px;
            font-size: 1.2em;
            transition: background-color 0.3s ease;
        }
        .navigation-bar a:hover {
            background-color: #002d20;
        }
        .comment-box {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 10px;
            background: #f1f1f1;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .comment-box img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }
        .admin-response {
            margin-top: 10px;
            padding: 10px;
            background: #e8f5e9;
            border-left: 3px solid #4caf50;
        }
        .comment-form textarea {
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            resize: none;
        }
        .comment-form button {
            margin-top: 10px;
        }
        .button {
            text-align: center;
            display: inline-block;
            padding: 10px 20px;
            font-size: 1em;
            color: white;
            background: #00796b;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }
        .button:hover {
            background-color: #004d40;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Navigation Bar -->
        <div class="navigation-bar">
            <a href="profile.php">Edit Profile</a>
            <a href="report.php">Usage Statistics</a>
            <a href="userdashboard.php">Bookings Dashboard</a>
        </div>

        <h1>Available Rooms</h1>
        <ul>
            <?php foreach ($rooms as $room): ?>
                <li>
                    <img 
                        src="<?= htmlspecialchars($room['image_url']) ?>" 
                        alt="Room Thumbnail" 
                        class="room-thumbnail"
                    >
                    <h2><?= htmlspecialchars($room['name']) ?></h2>
                    <div class="details">Capacity: <?= htmlspecialchars($room['capacity']) ?></div>
                    <a href="room_details.php?id=<?= $room['id'] ?>" class="button">View Room Details</a>
                    <a href="booking.php" class="button">Book This Room</a>

                    <!-- Comments Section -->
                    <div class="comments">
                        <h3>Comments</h3>
                        <?php
                        $stmt = $conn->prepare($comments_query);
                        $stmt->execute([$room['id']]);
                        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        ?>

                        <?php if ($comments): ?>
                            <?php foreach ($comments as $comment): ?>
                                <div class="comment-box">
                                    <img src="<?= htmlspecialchars($comment['profile_picture']) ?>" alt="User Picture">
                                    <div>
                                        <p>
                                            <strong><?= htmlspecialchars($comment['user_name']) ?>:</strong> 
                                            <?= htmlspecialchars($comment['comment']) ?>
                                        </p>
                                        <?php if (!empty($comment['admin_response'])): ?>
                                            <div class="admin-response">
                                                <p><strong>Admin Response:</strong> <?= htmlspecialchars($comment['admin_response']) ?></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No comments yet. Be the first to comment!</p>
                        <?php endif; ?>
                    </div>

                    <!-- Comment Form -->
                    <div class="comment-form">
                        <h3>Leave a Comment</h3>
                        <form method="POST">
                            <textarea name="comment" rows="3" placeholder="Write your comment here..." required></textarea>
                            <input type="hidden" name="room_id" value="<?= $room['id'] ?>">
                            <button type="submit">Submit</button>
                        </form>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</body>
<!-- Footer Section -->
<footer style="background-color: #004d40; color: #fff; text-align: center; padding: 20px; margin-top: 40px; border-radius: 8px 8px 0 0; box-shadow: 0 -4px 6px rgba(0, 0, 0, 0.2);">
    <div style="margin-bottom: 20px; font-size: 1.1em; line-height: 1.8;">
        <strong>Developed By:</strong><br>
        Abeer Nabeel - 20192830<br>
        Ayesha Zulfiqar - 20183459<br>
        Mariam Aldossri - 20157513<br>
        Omar Fakhro - 202201230<br>
        Ahmed AlTameem - 202006477
    </div>
    <div style="font-size: 1em; font-weight: bold;">
        &copy; <?= date('Y') ?> <span style="color: #f9a825;">Room Booking System</span>. All Rights Reserved.
    </div>
</footer>
</html>
