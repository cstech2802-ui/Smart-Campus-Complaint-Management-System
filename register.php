<?php 
require_once "config/database.php";
session_start();
$msg = "";
$ok = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $n = trim($_POST["name"] ?? "");
    $e = trim($_POST["email"] ?? "");
    $p = $_POST["password"] ?? "";
    
    if (strlen($n) < 2 || !filter_var($e, FILTER_VALIDATE_EMAIL) || strlen($p) < 6) {
        $msg = "Enter valid details; password must be 6+ characters.";
    } else {
        try {
            $h = password_hash($p, PASSWORD_DEFAULT);
            
            // Postgres PDO prepared statement
            $s = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, 'student')");
            $s->execute([
                'name'     => $n,
                'email'    => $e,
                'password' => $h
            ]);
            
            $ok = "Registration successful.";
        } catch (PDOException $ex) {
            // Checks for unique constraint violation (Postgres error code 23505)
            if ($ex->getCode() == '23505') {
                $msg = "Email already registered.";
            } else {
                $msg = "Registration failed: " . $ex->getMessage();
            }
        }
    }
}

// Helper function to prevent XSS injection attacks
if (!function_exists('e')) {
    function e($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

include "includes/header.php";
?>
<div class="form">
    <h2>Student Registration</h2>
    <?php if($msg): ?>
        <div class="alert bad"><?=e($msg)?></div>
    <?php endif; ?>
    <?php if($ok): ?>
        <div class="alert ok"><?=e($ok)?></div>
    <?php endif; ?>
    
    <form method="post">
        <div class="field">
            <label>Name</label>
            <input name="name" required>
        </div>
        <div class="field">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>
        <div class="field">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <button class="btn">Register</button>
    </form>
</div>
<?php include "includes/footer.php"; ?>
