<?php
require_once "../config/database.php";
session_start();

// Enforce Staff Access Only
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "staff") {
    header("Location: ../login.php");
    exit;
}

$staff_id = $_SESSION["user_id"];
$msg = "";

// Handle Status Updates
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["resolve_issue"])) {
    $complaint_id = intval($_POST["complaint_id"] ?? 0);
    $feedback = trim($_POST["feedback"] ?? "");

    if ($complaint_id > 0 && !empty($feedback)) {
        try {
            $sql = "UPDATE complaints SET status = 'resolved', feedback = :feedback, updated_at = CURRENT_TIMESTAMP 
                    WHERE id = :complaint_id AND assigned_staff_id = :staff_id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                'feedback'     => $feedback,
                'complaint_id' => $complaint_id,
                'staff_id'     => $staff_id
            ]);
            $msg = "Complaint marked as successfully resolved.";
        } catch (PDOException $e) {
            $msg = "Failed to update record: " . $e->getMessage();
        }
    }
}

// Fetch Tasks Assigned to this Staff Account
try {
    $sql = "SELECT c.*, u.name as student_name FROM complaints c 
            JOIN users u ON c.student_id = u.id 
            WHERE c.assigned_staff_id = :staff_id 
            ORDER BY c.updated_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['staff_id' => $staff_id]);
    $my_tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching tasks: " . $e->getMessage());
}

include "../includes/header.php";
?>
<div class="dashboard">
    <h2>Staff Resolution Workspace</h2>
    <p>Welcome back, <b><?= htmlspecialchars($_SESSION["name"]) ?></b></p>

    <?php if($msg): ?><div class="alert ok"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

    <h3>Your Assigned Complaints Workspace</h3>
    <?php if (empty($my_tasks)): ?>
        <p>No complaints have been assigned to your queue yet.</p>
    <?php else: ?>
        <table class="tracking-table">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Details</th>
                    <th>Status</th>
                    <th>Resolution Log / Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($my_tasks as $t): ?>
                    <tr>
                        <td><?= htmlspecialchars($t["student_name"]) ?></td>
                        <td>
                            <b><?= htmlspecialchars($t["title"]) ?></b> (<i><?= htmlspecialchars($t["category"]) ?></i>)<br>
                            <small><?= htmlspecialchars($t["description"]) ?></small>
                        </td>
                        <td><span class="status-badge <?= htmlspecialchars($t["status"]) ?>"><?= ucfirst($t["status"]) ?></span></td>
                        <td>
                            <?php if ($t["status"] === 'assigned'): ?>
                                <form method="post">
                                    <input type="hidden" name="complaint_id" value="<?= $t["id"] ?>">
                                    <input type="text" name="feedback" placeholder="Describe resolution notes..." required style="width:70%; padding:5px;">
                                    <button class="btn" name="resolve_issue">Mark Resolved</button>
                                </form>
                            <?php else: ?>
                                <small><b>Resolution Note:</b> <?= htmlspecialchars($t["feedback"] ?? 'None provided') ?></small>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    <p><a href="../logout.php" class="btn alt">Logout</a></p>
</div>
<?php include "../includes/footer.php"; ?>
