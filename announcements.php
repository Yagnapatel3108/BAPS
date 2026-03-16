<?php
require_once 'config.php';
checkRole(['Saint', 'Nirdheshak', 'Agresar', 'Nirikshak', 'Karyakar', 'Sah-Karyakar']);

$success = $error = '';

// Handle new announcement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $title   = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    if ($title && $content) {
        $stmt = $pdo->prepare("INSERT INTO announcements (title, content, created_by) VALUES (?,?,?)");
        $stmt->execute([$title, $content, $_SESSION['user_id']]);
        $success = "Announcement posted successfully!";
    } else {
        $error = "Please fill in all fields.";
    }
}

// Fetch all
$announcements = $pdo->query("
    SELECT a.*, u.full_name AS author
    FROM announcements a
    JOIN users u ON a.created_by = u.id
    ORDER BY a.created_at DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements — BAPS Bal Pravrutti</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div id="wrapper">
    <?php include 'sidebar.php'; ?>
    <div id="content">
        <?php include 'header.php'; ?>
        <div class="main-content">

            <!-- Header -->
            <div class="page-header flex items-center justify-between">
                <div>
                    <h2>📢 Announcements</h2>
                    <p>View and manage all BAPS Bal Pravrutti announcements</p>
                </div>
                <?php if ($_SESSION['role_name'] === 'Saint'): ?>
                <button class="btn-primary-custom" onclick="openModal('addModal')">
                    <i class="bi bi-plus-lg"></i> Post New
                </button>
                <?php endif; ?>
            </div>

            <?php if ($success): ?>
            <div class="alert-custom alert-success"><i class="bi bi-check-circle"></i><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
            <div class="alert-custom alert-error"><i class="bi bi-exclamation-circle"></i><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if (empty($announcements)): ?>
            <div class="card">
                <div class="card-body" style="text-align:center;padding:60px 24px;">
                    <i class="bi bi-megaphone" style="font-size:48px;color:var(--text-dim);display:block;margin-bottom:14px;"></i>
                    <p style="color:var(--text-muted);">No announcements have been posted yet.</p>
                    <?php if ($_SESSION['role_name'] === 'Saint'): ?>
                    <button class="btn-primary-custom" style="margin-top:12px;" onclick="openModal('addModal')">Post First Announcement</button>
                    <?php endif; ?>
                </div>
            </div>
            <?php else: ?>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:20px;">
                <?php foreach ($announcements as $a): ?>
                <div class="card" style="position:relative;overflow:hidden;">
                    <div style="position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--primary),var(--primary-light));"></div>
                    <div class="card-body">
                        <h5 style="font-size:16px;font-weight:700;margin-bottom:8px;color:var(--text-primary);">
                            <?php echo htmlspecialchars($a['title']); ?>
                        </h5>
                        <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
                            <span class="badge-custom badge-orange fs-12">
                                <i class="bi bi-person"></i>
                                <?php echo htmlspecialchars($a['author']); ?>
                            </span>
                            <span class="badge-custom badge-blue fs-12">
                                <i class="bi bi-calendar3"></i>
                                <?php echo date('d M Y', strtotime($a['created_at'])); ?>
                            </span>
                        </div>
                        <p style="color:var(--text-muted);font-size:14px;line-height:1.7;margin:0;">
                            <?php echo nl2br(htmlspecialchars($a['content'])); ?>
                        </p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- Add Announcement Modal -->
<?php if ($_SESSION['role_name'] === 'Saint'): ?>
<div class="modal-overlay" id="addModal">
    <div class="modal-box">
        <div class="modal-header">
            <h5><i class="bi bi-megaphone me-2" style="color:var(--primary-light)"></i>Post New Announcement</h5>
            <button class="modal-close" onclick="closeModal('addModal')"><i class="bi bi-x-lg"></i></button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="modal-body">
                <div class="form-group-dark">
                    <label class="form-label-dark">Title</label>
                    <input type="text" name="title" class="form-control-dark" placeholder="Announcement title…" required>
                </div>
                <div class="form-group-dark">
                    <label class="form-label-dark">Content</label>
                    <textarea name="content" class="form-control-dark" rows="5" placeholder="Write your announcement here…" required style="resize:vertical;"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary-custom" onclick="closeModal('addModal')">Cancel</button>
                <button type="submit" class="btn-primary-custom"><i class="bi bi-send"></i> Post Announcement</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
function openModal(id)  { document.getElementById(id).classList.add('active'); }
function closeModal(id) { document.getElementById(id).classList.remove('active'); }
document.querySelectorAll('.modal-overlay').forEach(m => {
    m.addEventListener('click', function(e) { if (e.target === m) m.classList.remove('active'); });
});
</script>
</body>
</html>
