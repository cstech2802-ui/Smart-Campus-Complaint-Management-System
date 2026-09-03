<?php
require_once "../config/database.php";
require_once "../includes/auth.php";

require_role("admin");

$sql = "SELECT
            c.*,
            u.name AS student_name,
            su.name AS staff_name
        FROM complaints c

        JOIN users u
            ON c.user_id = u.id

        LEFT JOIN staff s
            ON c.assigned_staff_id = s.id

        LEFT JOIN users su
            ON s.user_id = su.id

        ORDER BY c.created_at DESC";

$rows = $conn->query($sql);

if (!$rows) {
    die("SQL Error: " . $conn->error);
}

include "../includes/header.php";
?>

<h2>Complaint Management</h2>

<div class="wrap">

<table class="table">

<tr>
    <th>ID</th>
    <th>Student</th>
    <th>Title</th>
    <th>Priority</th>
    <th>Status</th>
    <th>Staff</th>
    <th>Action</th>
</tr>

<?php while ($r = $rows->fetch_assoc()): ?>

<tr>

<td>
    SC-<?= str_pad($r["id"], 4, "0", STR_PAD_LEFT) ?>
</td>

<td>
    <?= e($r["student_name"]) ?>
</td>

<td>
    <?= e($r["title"]) ?>
</td>

<td>
    <?= e($r["priority"]) ?>
</td>

<td>
    <?= e($r["status"]) ?>
</td>

<td>
    <?= e($r["staff_name"] ?? "Not assigned") ?>
</td>

<td>
    <a href="assign.php?id=<?= $r["id"] ?>">
        Assign
    </a>
</td>

</tr>

<?php endwhile; ?>

</table>

</div>

<?php include "../includes/footer.php"; ?>
