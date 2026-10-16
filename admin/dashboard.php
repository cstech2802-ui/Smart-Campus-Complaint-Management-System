<?php
require_once "../config/database.php";
session_start();

// Enforce Admin Access Only
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit;
}

$msg = "";

// Handle Complaint Assignment Form Submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["assign_staff"])) {
    $complaint_id = intval($_POST["complaint_id"] ?? 0);
    $staff_id = intval($_POST["staff_id"] ?? 0);
    
    if ($complaint_id > 0 && $staff_id > 0) {
        try {
            $sql = "UPDATE complaints SET assigned_staff_id = :staff_id, status = 'assigned', updated_at = CURRENT_TIMESTAMP WHERE id = :complaint_id";
            $stmt = $conn->prepare($sql);
            $stmt->execute(['staff_id' => $staff_id, 'complaint_id' => $complaint_id]);
            $msg = "Complaint successfully assigned to staff member.";
        } catch (PDOException $e) {
            $msg = "Error assigning complaint: " . $e->getMessage();
        }
    }
}

// Fetch All System Complaints and Available Staff Profiles from Supabase
try {
    $complaints_stmt = $conn->query("SELECT c.*, s.name as student_name, st.name as staff_name 
                                     FROM complaints c 
                                     JOIN users s ON c.student_id = s.id 
                                     LEFT JOIN users st ON c.assigned_staff_id = st.id 
                                     ORDER BY c.created_at DESC");
    $complaints = $complaints_stmt->fetchAll(PDO::FETCH_ASSOC);

    $staff_stmt = $conn->query("SELECT id, name FROM users WHERE role = 'staff' ORDER BY name ASC");
    $staff_members = $staff_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database retrieval error: " . $e->getMessage());
}

include "../includes/header.php";
?>
<div class="dashboard">
    <h2>Admin Management Portal</h2>
    <p>Logged in as: <b><?= htmlspecialchars($_SESSION["name"]) ?></b></p>
    
    <?php if($msg): ?><div class="alert info"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

    <h3>All System Complaints</h3>
    <table class="tracking-table">
        <thead>
            <tr>
                <th>Student</th>
                <th>Issue Details</th>
                <th>Category</th>
                <th>Status</th>
                <th>Current Handler</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($complaints as $c): ?>
                <tr>
                    <td><?= htmlspecialchars($c["student_name"]) ?></td>
                    <td>
                        <b><?= htmlspecialchars($c["title"]) ?></b><br>
                        <small><?= htmlspecialchars($c["description"]) ?></small>
                    </td>
                    <td><?= htmlspecialchars($c["category"]) ?></td>
                    <td><span class="status-badge <?= htmlspecialchars($c["status"]) ?>"><?= ucfirst($c["status"]) ?></span></td>
                    <td><?= $c["staff_name"] ? htmlspecialchars($c["staff_name"]) : "<i>Unassigned</i>" ?></td>
                    <td>
                        <?php if ($c["status"] === 'pending'): ?>
                            <form method="post" style="display:inline-block;">
                                <input type="hidden" name="complaint_id" value="<?= $c["id"] ?>">
                                <select name="staff_id" required>
                                    <option value="">Choose Staff...</option>
                                    <?php foreach ($staff_members as $sm): ?>
                                        <option value="<?= $sm["id"] ?>"><?= htmlspecialchars($sm["name"]) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="btn" name="assign_staff">Assign</button>
                            </form>
                        <?php else: ?>
                            <small class="disabled">No actions pending</small>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p><a href="../logout.php" class="btn alt">Logout</a></p>
</div>
<?php include "../includes/footer.php"; ?>
