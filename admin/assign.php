<?php

require_once "../config/database.php";
require_once "../includes/auth.php";

require_role("admin");

$id = (int)($_GET["id"] ?? 0);
$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $sid = (int)($_POST["staff_id"] ?? 0);
    $status = $_POST["status"] ?? "Open";

    $stmt = $conn->prepare(
        "UPDATE complaints
         SET assigned_staff_id = ?, status = ?
         WHERE id = ?"
    );

    if (!$stmt) {
        die("SQL Error: " . $conn->error);
    }

    $stmt->bind_param("isi", $sid, $status, $id);
    $stmt->execute();

    $msg = "Complaint updated successfully.";
}

$stmt = $conn->prepare(
    "SELECT *
     FROM complaints
     WHERE id = ?"
);

$stmt->bind_param("i", $id);
$stmt->execute();

$c = $stmt->get_result()->fetch_assoc();

if (!$c) {
    die("Complaint not found.");
}

/*
   Get staff name from users table
   through staff.user_id
*/

$staffs = $conn->query(
    "SELECT
        s.id,
        u.name,
        s.department
     FROM staff s
     JOIN users u
        ON s.user_id = u.id
     ORDER BY u.name"
);

if (!$staffs) {
    die("Staff SQL Error: " . $conn->error);
}

include "../includes/header.php";
?>

<div class="form">

<h2>
    Assign SC-<?= str_pad($id, 4, "0", STR_PAD_LEFT) ?>
</h2>

<p>
    <strong><?= e($c["title"]) ?></strong>
    <br>
    <?= e($c["description"]) ?>
</p>

<?php if ($msg): ?>

<div class="alert ok">
    <?= e($msg) ?>
</div>

<?php endif; ?>

<form method="post">

<div class="field">

<label>Staff</label>

<select name="staff_id" required>

<option value="">
    Select Staff
</option>

<?php while ($s = $staffs->fetch_assoc()): ?>

<option value="<?= $s["id"] ?>">

<?= e($s["name"]) ?>
—
<?= e($s["department"]) ?>

</option>

<?php endwhile; ?>

</select>

</div>

<div class="field">

<label>Status</label>

<select name="status">

<option>Open</option>
<option>In Progress</option>
<option>Resolved</option>
<option>Closed</option>

</select>

</div>

<button class="btn">
    Save
</button>

</form>

</div>

<?php include "../includes/footer.php"; ?>
