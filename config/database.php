<?php 
require_once "config/database.php";
session_start();
$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $pass = $_POST["password"] ?? "";
    
    try {
        // Postgres-compatible PDO prepared statement
        $s = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = :email LIMIT 1");
        $s->execute(['email' => $email]);
        $u = $s->fetch(PDO::FETCH_ASSOC);
        
        // Checks both hashed passwords and the plaintext default demo logins
        if ($u && (password_verify($pass, $u["password"]) || $pass === $u["password"])) {
            $_SESSION["user_id"] = $u["id"];
            $_SESSION["name"] = $u["name"];
            $_SESSION["role"] = $u["role"];
            
            header("Location: " . ($u["role"] === "admin" ? "admin/dashboard.php" : ($u["role"] === "staff" ? "staff/dashboard.php" : "student/dashboard.php")));
            exit;
        } 
        $msg = "Invalid email or password.";
    } catch (PDOException $e) {
        $msg = "Database error: " . $e->getMessage();
    }
}

// Helper function to prevent XSS injection attacks
function e($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

include "includes/header.php";
?>
<div class="form">
    <h2>Login</h2>
    <?php if($msg): ?>
        <div class="alert bad"><?=e($msg)?></div>
    <?php endif; ?>
    
    <form method="post">
        <div class="field">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>
        <div class="field">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <button class="btn">Login</button>
    </form>
    <p>Demo accounts are loaded into Supabase.</p>
</div>
<?php include "includes/footer.php"; ?>
