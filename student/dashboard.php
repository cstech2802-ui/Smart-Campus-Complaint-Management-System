<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['user_id'];

// Fetch statistics
$stmt = $pdo->prepare("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'Open' THEN 1 ELSE 0 END) as open,
    SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) as in_progress,
    SUM(CASE WHEN status = 'Resolved' THEN 1 ELSE 0 END) as resolved
    FROM complaints WHERE student_id = ?");
$stmt->execute([$student_id]);
$stats = $stmt->fetch();

// Fetch recent complaints
$stmt_recent = $pdo->prepare("SELECT * FROM complaints WHERE student_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt_recent->execute([$student_id]);
$recent_complaints = $stmt_recent->fetchAll();

include '../includes/header.php';
?>

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Student Dashboard</h2>
        <a href="submit_complaint.php" class="btn btn-primary">+ Lodge New Complaint</a>
    </div>

    <!-- Statistics Cards -->
    <div class="row text-center mb-4">
        <div class="col-md-3">
            <div class="card p-3 shadow-sm">
                <h5 class="text-muted">Total Complaints</h5>
                <h3><?php echo $stats['total'] ?? 0; ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 shadow-sm border-warning">
                <h5 class="text-warning">Open</h5>
                <h3><?php echo $stats['open'] ?? 0; ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 shadow-sm border-info">
                <h5 class="text-info">In Progress</h5>
                <h3><?php echo $stats['in_progress'] ?? 0; ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 shadow-sm border-success">
                <h5 class="text-success">Resolved</h5>
                <h3><?php echo $stats['resolved'] ?? 0; ?></h3>
            </div>
        </div>
    </div>

    <!-- Recent Complaints Table -->
    <div class="card">
        <div class="card-header">Recent Complaints History</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Category</th>
                            <th>Title</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($recent_complaints) > 0): ?>
                            <?php foreach ($recent_complaints as $c): ?>
                                <tr>
                                    <td>#<?php echo $c['id']; ?></td>
                                    <td><?php echo htmlspecialchars($c['category']); ?></td>
                                    <td><?php echo htmlspecialchars($c['title']); ?></td>
                                    <td><span class="badge bg-secondary"><?php echo $c['priority']; ?></span></td>
                                    <td>
                                        <span class="badge badge-<?php echo strtolower(str_replace(' ', '-', $c['status'])); ?>">
                                            <?php echo $c['status']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $c['created_at']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center text-muted">No complaints submitted yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
