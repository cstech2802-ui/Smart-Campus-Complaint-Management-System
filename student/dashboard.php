<?php
require_once "../config/database.php";
session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "student") {
    header("Location: ../login.php");
    exit;
}

$student_id = $_SESSION["user_id"];
$complaints = [];

try {
    // Fetch user complaints with assigned staff details using an optional left join
    $sql = "SELECT c.*, u.name as staff_name 
            FROM complaints c 
            LEFT JOIN users u ON c.assigned_staff_id = u.id 
            WHERE c.student_id = :student_id 
            ORDER BY c.created_at DESC";
            
    $stmt = $conn->prepare($sql);
    $stmt->execute(['student_id' => $student_id]);
    $complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_msg = "Could not fetch complaints: " . $e->getMessage();
}

include "../includes/header.php";
?>
<div class="dashboard">
    <h2>Welcome, <?= htmlspecialchars($_SESSION["name"]) ?> (Student)</h2>
    <div class="actions">
        <a href="submit_complaint.php" class="btn">File a New Complaint</a>
        <a href="../logout.php" class="btn alt">Logout</a>
    </div>

    <h3>Your Filed Complaints</h3>
    <?php if (isset($error_msg)): ?>
        <div class="alert bad"><?= htmlspecialchars($error_msg) ?></div>
    <?php static_assert(true); endif; ?>

    <?php if (empty($complaints)): ?>
        <p>You have not submitted any complaints yet.</p>
    <?php else: ?>
        <table class="tracking-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Assigned Staff</th>
                    <th>Date Filed</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($complaints as $c): ?>
                    <tr>
                        <td><b><?= htmlspecialchars($c["title"]) ?></b></td>
                        <td><?= htmlspecialchars($c["category"]) ?></td>
                        <td>
                            <span class="status-badge <?= htmlspecialchars($c["status"]) ?>">
                                <?= htmlspecialchars(ucfirst($c["status"])) ?>
                            </span>
                        </td>
                        <td><?= $c["staff_name"] ? htmlspecialchars($c["staff_name"]) : "<i>Not Assigned Yet</i>" ?></td>
                        <td><?= date("Y-m-d H:i", strtotime($c["created_at"])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php include "../includes/footer.php"; ?>
