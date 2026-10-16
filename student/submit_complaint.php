<?php
require_once "../config/database.php";
session_start();

// Ensure only logged-in students can access this page
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "student") {
    header("Location: ../login.php");
    exit;
}

$msg = "";
$ok = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $student_id = $_SESSION["user_id"];

    if (empty($title) || empty($description) || empty($category)) {
        $msg = "Please fill in all required fields.";
    } else {
        try {
            // Postgres-compatible insert statement
            $sql = "INSERT INTO complaints (student_id, title, description, category, status) 
                    VALUES (:student_id, :title, :description, :category, 'pending')";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                'student_id'  => $student_id,
                'title'       => $title,
                'description' => $description,
                'category'    => $category
            ]);

            $ok = "Complaint submitted successfully!";
        } catch (PDOException $e) {
            $msg = "Submission failed: " . $e->getMessage();
        }
    }
}

include "../includes/header.php";
?>
<div class="form">
    <h2>Submit New Complaint</h2>
    <?php if($msg): ?><div class="alert bad"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <?php if($ok): ?><div class="alert ok"><?= htmlspecialchars($ok) ?></div><?php endif; ?>

    <form method="post">
        <div class="field">
            <label>Complaint Title</label>
            <input type="text" name="title" required>
        </div>
        <div class="field">
            <label>Category</label>
            <select name="category" required>
                <option value="Hostel">Hostel</option>
                <option value="Academic">Academic</option>
                <option value="IT Services">IT Services</option>
                <option value="Infrastructure">Infrastructure</option>
            </select>
        </div>
        <div class="field">
            <label>Description</label>
            <textarea name="description" rows="5" required></textarea>
        </div>
        <button class="btn">Submit Complaint</button>
    </form>
    <p><a href="dashboard.php">← Back to Dashboard</a></p>
</div>
<?php include "../includes/footer.php"; ?>
