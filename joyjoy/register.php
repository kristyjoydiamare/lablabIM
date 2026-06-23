<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'config.php';

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // VALIDATION
    if (empty($username)) $errors[] = "Username is required.";
    if (empty($email)) $errors[] = "Email is required.";
    if (empty($password)) $errors[] = "Password is required.";

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    // CHECK USERNAME EXISTS
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $errors[] = "Username already exists.";
    }

    // CHECK EMAIL EXISTS
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $errors[] = "Email already exists.";
    }

    // INSERT USER
    if (empty($errors)) {

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("
            INSERT INTO users (username, email, password_hash, role)
            VALUES (?, ?, ?, 'user')
        ");

        $stmt->bind_param("sss", $username, $email, $hash);

        if ($stmt->execute()) {
            header("Location: login.php");
            exit;
        } else {
            $errors[] = "Registration failed.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
</head>
<body>

<h2>Register</h2>

<?php foreach ($errors as $e): ?>
    <p style="color:red"><?= $e ?></p>
<?php endforeach; ?>

<form method="POST">
    <input name="username" placeholder="Username" required><br><br>
    <input name="email" placeholder="Email" required><br><br>
    <input type="password" name="password" placeholder="Password" required><br><br>
    <input type="password" name="confirm_password" placeholder="Confirm Password" required><br><br>
    <button type="submit">Register</button>
</form>

</body>
</html>