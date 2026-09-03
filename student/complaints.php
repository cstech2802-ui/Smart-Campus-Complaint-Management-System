<?php
require_once "../config/database.php";
require_once "../includes/auth.php";

require_role("student");

$user_id = $_SESSION["user_id"];

$sql = "SELECT
            c.*,
            u.name AS staff_name
        FROM complaints c
        LEFT JOIN staff s
            ON c.assigned_staff_id = s.id
        LEFT JOIN users u
            ON s.user_id = u.id
        WHERE c.user_id = ?
        ORDER BY c.created_at DESC";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("SQL Error: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();

$rows = $stmt->get_result();

include "../includes/header.php";
?>

<h2>My Complaints</h2>

<div class="wrap">

<table class="table">

<tr>
    <th>ID</th>
    <th>Title</th>
    <th>Status</th>
    <th>Staff</th>
    <th>Created</th>
    <th>Feedback</th>
</tr>

<?php while ($r = $rows->fetch_assoc()): ?>

<tr>

<td>
    SC-<?= str_pad($r["id"], 4, "0", STR_PAD_LEFT) ?>
</td>

<td>
    <?= e($r["title"]) ?>
</td>

<td>
    <?= e($r["status"]) ?>
</td>

<td>
    <?= e($r["staff_name"] ?? "Not assigned") ?>
</td>

<td>
    <?= e($r["created_at"]) ?>
</td>

<td>

<?php if ($r["status"] === "Resolved"): ?>

<a href="feedback.php?id=<?= $r["id"] ?>">
    Give Feedback
</a>

<?php else: ?>

—

<?php endif; ?>

</td>

</tr>

<?php endwhile; ?>

</table>

</div>

<?php include "../includes/footer.php"; ?>
