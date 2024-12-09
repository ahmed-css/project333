<?php
session_start();

// Ensure admin access
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    die("Access denied: Admins only.");
}

// Database connection
$host = "localhost";
$dbname = "room_booking";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Approve comment
if (isset($_POST['approve'])) {
    $comment_id = $_POST['comment_id'];
    $stmt = $conn->prepare("UPDATE comments SET is_approved = TRUE WHERE id = ?");
    $stmt->execute([$comment_id]);
}

// Respond to comment
if (isset($_POST['respond'])) {
    $comment_id = $_POST['comment_id'];
    $admin_response = $_POST['admin_response'];
    $stmt = $conn->prepare("UPDATE comments SET admin_response = ? WHERE id = ?");
    $stmt->execute([$admin_response, $comment_id]);
}

// Delete comment
if (isset($_POST['delete'])) {
    $comment_id = $_POST['comment_id'];
    $stmt = $conn->prepare("DELETE FROM comments WHERE id = ?");
    $stmt->execute([$comment_id]);
}

// Fetch unapproved comments
$stmt = $conn->query("SELECT comments.*, rooms.name AS room_name, users.name AS user_name 
                      FROM comments 
                      JOIN rooms ON comments.room_id = rooms.id 
                      JOIN users ON comments.user_id = users.id 
                      WHERE is_approved = FALSE 
                      ORDER BY created_at DESC");
$unapproved_comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch approved comments
$stmt = $conn->query("SELECT comments.*, rooms.name AS room_name, users.name AS user_name 
                      FROM comments 
                      JOIN rooms ON comments.room_id = rooms.id 
                      JOIN users ON comments.user_id = users.id 
                      WHERE is_approved = TRUE 
                      ORDER BY created_at DESC");
$approved_comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Comments</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/picocss@1.5.0/dist/pico.min.css">
    <style>
        body {
            background: linear-gradient(to right, #6a11cb, #2575fc);
            color: #fff;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: #ffffff;
            color: #333;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }
        h1, h2 {
            text-align: center;
            color: #444;
        }
        .comment-section {
            margin-top: 20px;
            border-bottom: 2px solid #ddd;
            padding-bottom: 20px;
        }
        .comment-box {
            background: #f9f9f9;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .comment-box p {
            margin: 10px 0;
        }
        .comment-box textarea {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            resize: none;
        }
        .comment-box button {
            margin-top: 10px;
            background: #2575fc;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
        }
        .comment-box button:hover {
            background: #6a11cb;
        }
        .admin-response {
            background: #e8f5e9;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            border-left: 5px solid #4caf50;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Manage Comments</h1>

        <div class="comment-section">
            <h2>Unapproved Comments</h2>
            <?php if ($unapproved_comments): ?>
                <?php foreach ($unapproved_comments as $comment): ?>
                    <div class="comment-box">
                        <p><strong>Room:</strong> <?= htmlspecialchars($comment['room_name']) ?></p>
                        <p><strong><?= htmlspecialchars($comment['user_name']) ?>:</strong> <?= htmlspecialchars($comment['comment']) ?></p>
                        <form method="POST">
                            <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                            <button type="submit" name="approve">Approve</button>
                            <button type="submit" name="delete" style="background: #f44336;">Delete</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No unapproved comments.</p>
            <?php endif; ?>
        </div>

        <div class="comment-section">
            <h2>Approved Comments</h2>
            <?php if ($approved_comments): ?>
                <?php foreach ($approved_comments as $comment): ?>
                    <div class="comment-box">
                        <p><strong>Room:</strong> <?= htmlspecialchars($comment['room_name']) ?></p>
                        <p><strong><?= htmlspecialchars($comment['user_name']) ?>:</strong> <?= htmlspecialchars($comment['comment']) ?></p>
                        <form method="POST">
                            <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                            <textarea name="admin_response" rows="2" placeholder="Write a response..."></textarea>
                            <button type="submit" name="respond">Respond</button>
                            <button type="submit" name="delete" style="background: #f44336;">Delete</button>
                        </form>
                        <?php if (!empty($comment['admin_response'])): ?>
                            <div class="admin-response">
                                <p><strong>Admin Response:</strong> <?= htmlspecialchars($comment['admin_response']) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No approved comments yet.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
