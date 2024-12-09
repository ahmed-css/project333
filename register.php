<?php
// Database connection setup
$host = "localhost";
$dbname = "room_booking";
$username = "root";
$password = "";
$conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);

$success_message = "";

// Handling the registration form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Advanced Email Validation for *****@uob.edu.bh format
    if (!preg_match("/^[a-zA-Z0-9._%+-]+@uob\.edu\.bh$/", $email)) {
        $error_message = "Please use a valid UoB email address (e.g., username@uob.edu.bh).";
    } else {
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Check if email already exists
        $query = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $error_message = "This email is already registered.";
        } else {
            // Insert into the database
            $query = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->execute([$name, $email, $hashedPassword]);
            $success_message = "Registration successful! You can now log in.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
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
            background-color: #ff4500;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
        button:hover {
            background-color: #cc3700;
        }
        .success, .error {
            font-size: 1.2rem;
            margin: 10px 0;
        }
        .success {
            color: green;
        }
        .error {
            color: red;
        }
        .login-button {
            margin-top: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
        }
        .login-button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Register</h1>
        <?php if (!empty($success_message)): ?>
            <p class="success"><?= htmlspecialchars($success_message) ?></p>
            <a href="login.php" class="login-button">Go to Login</a>
        <?php elseif (!empty($error_message)): ?>
            <p class="error"><?= htmlspecialchars($error_message) ?></p>
        <?php endif; ?>

        <form method="POST" action="register.php">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" placeholder="Enter your name" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" placeholder="Enter your email (e.g., username@uob.edu.bh)" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>

            <button type="submit">Register</button>
        </form>
    </div>
</body>
</html>
