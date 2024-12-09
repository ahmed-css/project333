<?php
session_start();
$host = "localhost";
$dbname = "room_booking";
$username = "root";
$password = "";
$conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if email exists in the database
    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // If user exists and password is correct
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        header("Location: browse_rooms.php"); // Redirect to dashboard after successful login
    } else {
        echo "Invalid credentials!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/picocss@1.5.0/dist/pico.min.css">
    <style>
        body {
            background: linear-gradient(to bottom, #1e3c72, #2a5298);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: Arial, sans-serif;
            margin: 0;
            color: #fff;
        }
        .container {
            background-color: #fff;
            color: #333;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            padding: 20px;
            max-width: 400px;
            width: 90%;
            text-align: center;
        }
        h1 {
            font-size: 2rem;
            margin-bottom: 20px;
        }
        form {
            margin-top: 20px;
        }
        label {
            display: block;
            text-align: left;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 1rem;
        }
        button {
            width: 100%;
            padding: 10px;
            font-size: 1rem;
            color: #fff;
            background-color: #00796b;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
        button:hover {
            background-color: #004d40;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Login</h1>
        <form method="POST" action="login.php">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>

            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
