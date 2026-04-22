<?php
require_once 'connection.php';
requireLogin();
requireAdmin();

$msg = '';
$action = $_GET['action'] ?? 'list';
$anime_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['action'] ?? '';

    // ADD ANIME
    if ($postAction === 'add_anime') {
        $title = trim($_POST['title']);
        $desc = trim($_POST['description']);
        $genre = trim($_POST['genre']);
        $status = $_POST['status'];
        $cover = trim($_POST['cover_image']);
        $stmt = mysqli_prepare($con, "INSERT INTO anime (title, description, genre, cover_image, status) VALUES (?,?,?,?,?)");
        mysqli_stmt_bind_param($stmt, "sssss", $title, $desc, $genre, $cover, $status);
        mysqli_stmt_execute($stmt) ? $msg = "✅ Anime added!" : $msg = "❌ Error: " . mysqli_error($con);
    }

    // EDIT ANIME
    if ($postAction === 'edit_anime') {
        $id = (int)$_POST['id'];
        $title = trim($_POST['title']);
        $desc = trim($_POST['description']);
        $genre = trim($_POST['genre']);
        $status = $_POST['status'];
        $cover = trim($_POST['cover_image']);
        $stmt = mysqli_prepare($con, "UPDATE anime SET title=?,description=?,genre=?,cover_image=?,status=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "sssssi", $title, $desc, $genre, $cover, $status, $id);
        mysqli_stmt_execute($stmt) ? $msg = "✅ Anime updated!" : $msg = "❌ Error: " . mysqli_error($con);
        $action = 'list';
    }

    // DELETE ANIME
    if ($postAction === 'delete_anime') {
        $id = (int)$_POST['id'];
        $stmt = mysqli_prepare($con, "DELETE FROM anime WHERE id=?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt) ? $msg = "✅ Anime deleted." : $msg = "❌ Error.";
    }

    // ADD EPISODE
    if ($postAction === 'add_episode') {
        $aid = (int)$_POST['anime_id'];
        $ep_num = (int)$_POST['episode_number'];
        $ep_title = trim($_POST['ep_title']);
        $video_url = trim($_POST['video_url']);
        $stmt = mysqli_prepare($con, "INSERT INTO episodes (anime_id, episode_number, title, video_url) VALUES (?,?,?,?)");
        mysqli_stmt_bind_param($stmt, "iiss", $aid, $ep_num, $ep_title, $video_url);
        mysqli_stmt_execute($stmt) ? $msg = "✅ Episode added!" : $msg = "❌ Error: " . mysqli_error($con);
    }

    // EDIT EPISODE
    if ($postAction === 'edit_episode') {
        $eid = (int)$_POST['ep_id'];
        $ep_num = (int)$_POST['episode_number'];
        $ep_title = trim($_POST['ep_title']);
        $video_url = trim($_POST['video_url']);
        $stmt = mysqli_prepare($con, "UPDATE episodes SET episode_number=?, title=?, video_url=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "issi", $ep_num, $ep_title, $video_url, $eid);
        mysqli_stmt_execute($stmt) ? $msg = "✅ Episode updated!" : $msg = "❌ Error.";
        $action = 'episodes';
    }

    // DELETE EPISODE
    if ($postAction === 'delete_episode') {
        $eid = (int)$_POST['ep_id'];
        $aid = (int)$_POST['anime_id'];
        $stmt = mysqli_prepare($con, "DELETE FROM episodes WHERE id=?");
        mysqli_stmt_bind_param($stmt, "i", $eid);
        mysqli_stmt_execute($stmt) ? $msg = "✅ Episode deleted." : $msg = "❌ Error.";
        $action = 'episodes';
        $anime_id = $aid;
    }
}

// Fetch data
$all_anime = mysqli_fetch_all(mysqli_query($con, "SELECT a.*, COUNT(e.id) AS ep_count FROM anime a LEFT JOIN episodes e ON e.anime_id=a.id GROUP BY a.id ORDER BY a.created_at DESC"), MYSQLI_ASSOC);

$edit_anime = null;
if ($action === 'edit' && $anime_id) {
    $st = mysqli_prepare($con, "SELECT * FROM anime WHERE id=?");
    mysqli_stmt_bind_param($st, "i", $anime_id);
    mysqli_stmt_execute($st);
    $edit_anime = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
}

$episodes = [];
$current_anime = null;
if (($action === 'episodes' || $action === 'add_episode' || $action === 'edit_episode') && $anime_id) {
    $st = mysqli_prepare($con, "SELECT * FROM anime WHERE id=?");
    mysqli_stmt_bind_param($st, "i", $anime_id);
    mysqli_stmt_execute($st);
    $current_anime = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
    $episodes = mysqli_fetch_all(mysqli_query($con, "SELECT * FROM episodes WHERE anime_id=$anime_id ORDER BY episode_number ASC"), MYSQLI_ASSOC);
}

$edit_episode = null;
if ($action === 'edit_episode' && isset($_GET['ep'])) {
    $eid = (int)$_GET['ep'];
    $st = mysqli_prepare($con, "SELECT * FROM episodes WHERE id=?");
    mysqli_stmt_bind_param($st, "i", $eid);
    mysqli_stmt_execute($st);
    $edit_episode = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel – OtakuZone</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700;900&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="main.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body>

<nav class="nav">
    <a href="index.php" class="logo">⛩ OtakuZone</a>
    <div class="nav-links">
        <a href="index.php">← Back to Site</a>
        <a href="admin.php" class="admin-link">Admin Panel</a>
        <span class="greeting">Hi, <?= htmlspecialchars($_SESSION['fname']) ?></span>
        <a href="logout.php" class="btn-nav">Log Out</a>
    </div>
</nav>

<div class="admin-layout">
    <aside class="admin-sidebar">
        <h3>Admin Panel</h3>
        <ul>
            <li><a href="admin.php" class="<?= $action==='list'?'active':'' ?>">📺 Anime List</a></li>
            <li><a href="admin.php?action=add" class="<?= $action==='add'?'active':'' ?>">➕ Add Anime</a></li>
        </ul>
    </aside>

    <main class="admin-main">
        <?php if ($msg): ?>
            <div class="admin-msg <?= str_starts_with($msg,'✅')?'success':'error' ?>"><?= $msg ?></div>
        <?php endif; ?>

        <?php if ($action === 'list'): ?>
        <!-- Anime List -->
        <div class="admin-header">
            <h2>Manage Anime</h2>
            <a href="admin.php?action=add" class="btn-primary">+ Add New Anime</a>
        </div>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead><tr><th>Cover</th><th>Title</th><th>Genre</th><th>Status</th><th>Episodes</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($all_anime as $a): ?>
                <tr>
                    <td><img src="<?= htmlspecialchars($a['cover_image']??'') ?>" alt="" class="table-thumb" onerror="this.style.display='none'"></td>
                    <td><strong><?= htmlspecialchars($a['title']) ?></strong></td>
                    <td><?= htmlspecialchars($a['genre']) ?></td>
                    <td><span class="badge <?= $a['status'] ?>"><?= ucfirst($a['status']) ?></span></td>
                    <td><?= $a['ep_count'] ?> eps</td>
                    <td class="actions">
                        <a href="admin.php?action=edit&id=<?= $a['id'] ?>" class="btn-sm edit">Edit</a>
                        <a href="admin.php?action=episodes&id=<?= $a['id'] ?>" class="btn-sm ep">Episodes</a>
                        <form method="post" style="display:inline" onsubmit="return confirm('Delete this anime?')">
                            <input type="hidden" name="action" value="delete_anime">
                            <input type="hidden" name="id" value="<?= $a['id'] ?>">
                            <button type="submit" class="btn-sm del">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php elseif ($action === 'add'): ?>
        <!-- Add Anime Form -->
        <div class="admin-header"><h2>Add New Anime</h2></div>
        <form method="post" class="admin-form">
            <input type="hidden" name="action" value="add_anime">
            <div class="field"><label>Title *</label><input type="text" name="title" required></div>
            <div class="field"><label>Description</label><textarea name="description" rows="4"></textarea></div>
            <div class="field"><label>Genre (e.g. Action, Adventure)</label><input type="text" name="genre"></div>
            <div class="field"><label>Cover Image (filename or URL)</label><input type="text" name="cover_image" placeholder="e.g. cover.jpg or https://..."></div>
            <div class="field"><label>Status</label>
                <select name="status"><option value="ongoing">Ongoing</option><option value="completed">Completed</option></select>
            </div>
            <button type="submit" class="btn-primary">Add Anime</button>
            <a href="admin.php" class="btn-sm">Cancel</a>
        </form>

        <?php elseif ($action === 'edit' && $edit_anime): ?>
        <!-- Edit Anime Form -->
        <div class="admin-header"><h2>Edit: <?= htmlspecialchars($edit_anime['title']) ?></h2></div>
        <form method="post" class="admin-form">
            <input type="hidden" name="action" value="edit_anime">
            <input type="hidden" name="id" value="<?= $edit_anime['id'] ?>">
            <div class="field"><label>Title *</label><input type="text" name="title" value="<?= htmlspecialchars($edit_anime['title']) ?>" required></div>
            <div class="field"><label>Description</label><textarea name="description" rows="4"><?= htmlspecialchars($edit_anime['description']) ?></textarea></div>
            <div class="field"><label>Genre</label><input type="text" name="genre" value="<?= htmlspecialchars($edit_anime['genre']) ?>"></div>
            <div class="field"><label>Cover Image</label><input type="text" name="cover_image" value="<?= htmlspecialchars($edit_anime['cover_image']) ?>"></div>
            <div class="field"><label>Status</label>
                <select name="status">
                    <option value="ongoing" <?= $edit_anime['status']==='ongoing'?'selected':'' ?>>Ongoing</option>
                    <option value="completed" <?= $edit_anime['status']==='completed'?'selected':'' ?>>Completed</option>
                </select>
            </div>
            <button type="submit" class="btn-primary">Save Changes</button>
            <a href="admin.php" class="btn-sm">Cancel</a>
        </form>

        <?php elseif ($action === 'episodes' && $current_anime): ?>
        <!-- Episodes Manager -->
        <div class="admin-header">
            <h2>Episodes: <?= htmlspecialchars($current_anime['title']) ?></h2>
            <a href="admin.php?action=add_episode&id=<?= $anime_id ?>" class="btn-primary">+ Add Episode</a>
        </div>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead><tr><th>Ep #</th><th>Title</th><th>Video URL</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($episodes as $ep): ?>
                <tr>
                    <td><strong>EP <?= $ep['episode_number'] ?></strong></td>
                    <td><?= htmlspecialchars($ep['title']) ?></td>
                    <td class="url-cell"><?= htmlspecialchars($ep['video_url']) ?></td>
                    <td class="actions">
                        <a href="admin.php?action=edit_episode&id=<?= $anime_id ?>&ep=<?= $ep['id'] ?>" class="btn-sm edit">Edit</a>
                        <form method="post" style="display:inline" onsubmit="return confirm('Delete this episode?')">
                            <input type="hidden" name="action" value="delete_episode">
                            <input type="hidden" name="ep_id" value="<?= $ep['id'] ?>">
                            <input type="hidden" name="anime_id" value="<?= $anime_id ?>">
                            <button type="submit" class="btn-sm del">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($episodes)): ?><tr><td colspan="4" style="text-align:center;padding:20px;color:#888">No episodes yet</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
        <a href="admin.php" class="btn-sm" style="margin-top:15px;display:inline-block">← Back to Anime List</a>

        <?php elseif ($action === 'add_episode' && $current_anime): ?>
        <!-- Add Episode Form -->
        <div class="admin-header"><h2>Add Episode to: <?= htmlspecialchars($current_anime['title']) ?></h2></div>
        <form method="post" class="admin-form">
            <input type="hidden" name="action" value="add_episode">
            <input type="hidden" name="anime_id" value="<?= $anime_id ?>">
            <div class="field"><label>Episode Number *</label><input type="number" name="episode_number" min="1" required value="<?= count($episodes)+1 ?>"></div>
            <div class="field"><label>Episode Title</label><input type="text" name="ep_title" placeholder="e.g. Enter: Naruto Uzumaki!"></div>
            <div class="field"><label>Video URL *</label><input type="url" name="video_url" placeholder="https://..." required></div>
            <button type="submit" class="btn-primary">Add Episode</button>
            <a href="admin.php?action=episodes&id=<?= $anime_id ?>" class="btn-sm">Cancel</a>
        </form>

        <?php elseif ($action === 'edit_episode' && $edit_episode): ?>
        <!-- Edit Episode Form -->
        <div class="admin-header"><h2>Edit Episode <?= $edit_episode['episode_number'] ?></h2></div>
        <form method="post" class="admin-form">
            <input type="hidden" name="action" value="edit_episode">
            <input type="hidden" name="ep_id" value="<?= $edit_episode['id'] ?>">
            <input type="hidden" name="anime_id" value="<?= $anime_id ?>">
            <div class="field"><label>Episode Number *</label><input type="number" name="episode_number" value="<?= $edit_episode['episode_number'] ?>" required></div>
            <div class="field"><label>Episode Title</label><input type="text" name="ep_title" value="<?= htmlspecialchars($edit_episode['title']) ?>"></div>
            <div class="field"><label>Video URL</label><input type="url" name="video_url" value="<?= htmlspecialchars($edit_episode['video_url']) ?>"></div>
            <button type="submit" class="btn-primary">Save Changes</button>
            <a href="admin.php?action=episodes&id=<?= $anime_id ?>" class="btn-sm">Cancel</a>
        </form>
        <?php endif; ?>

    </main>
</div>

<footer class="footer">
    <p>⛩ OtakuZone &copy; <?= date('Y') ?> — Admin Panel</p>
</footer>
</body>
</html>
