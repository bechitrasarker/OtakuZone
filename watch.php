<?php
require_once 'connection.php';

$anime_id = isset($_GET['anime']) ? (int)$_GET['anime'] : 0;
$ep_id = isset($_GET['ep']) ? (int)$_GET['ep'] : 0;

// Get anime info
$stmt = mysqli_prepare($con, "SELECT a.*, ROUND(AVG(r.stars),1) AS avg_stars, COUNT(DISTINCT r.id) AS review_count FROM anime a LEFT JOIN reviews r ON r.anime_id = a.id WHERE a.id = ? GROUP BY a.id");
mysqli_stmt_bind_param($stmt, "i", $anime_id);
mysqli_stmt_execute($stmt);
$anime = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$anime) {
    header("Location: index.php");
    exit();
}

// Get all episodes
$ep_result = mysqli_query($con, "SELECT * FROM episodes WHERE anime_id = $anime_id ORDER BY episode_number ASC");
$episodes = mysqli_fetch_all($ep_result, MYSQLI_ASSOC);

// Current episode
$current_ep = null;
if ($ep_id) {
    foreach ($episodes as $ep) {
        if ($ep['id'] == $ep_id) { $current_ep = $ep; break; }
    }
}
if (!$current_ep && !empty($episodes)) {
    $current_ep = $episodes[0];
}

// Handle review submission
$review_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isLoggedIn()) {
    $stars = (int)$_POST['stars'];
    $comment = trim($_POST['comment']);
    $uid = $_SESSION['user_id'];

    // Upsert
    $chk = mysqli_prepare($con, "SELECT id FROM reviews WHERE user_id=? AND anime_id=?");
    mysqli_stmt_bind_param($chk, "ii", $uid, $anime_id);
    mysqli_stmt_execute($chk);
    mysqli_stmt_store_result($chk);

    if (mysqli_stmt_num_rows($chk) > 0) {
        $upd = mysqli_prepare($con, "UPDATE reviews SET stars=?, comment=? WHERE user_id=? AND anime_id=?");
        mysqli_stmt_bind_param($upd, "isii", $stars, $comment, $uid, $anime_id);
        mysqli_stmt_execute($upd);
        $review_msg = "Review updated!";
    } else {
        $ins = mysqli_prepare($con, "INSERT INTO reviews (user_id, anime_id, stars, comment) VALUES (?,?,?,?)");
        mysqli_stmt_bind_param($ins, "iiis", $uid, $anime_id, $stars, $comment);
        mysqli_stmt_execute($ins);
        $review_msg = "Review submitted!";
    }
    // Refresh avg
    $stmt2 = mysqli_prepare($con, "SELECT ROUND(AVG(stars),1) AS avg_stars, COUNT(*) AS review_count FROM reviews WHERE anime_id=?");
    mysqli_stmt_bind_param($stmt2, "i", $anime_id);
    mysqli_stmt_execute($stmt2);
    $rdata = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt2));
    $anime['avg_stars'] = $rdata['avg_stars'];
    $anime['review_count'] = $rdata['review_count'];
}

// User's existing review
$user_review = null;
if (isLoggedIn()) {
    $uid = $_SESSION['user_id'];
    $rv = mysqli_prepare($con, "SELECT * FROM reviews WHERE user_id=? AND anime_id=?");
    mysqli_stmt_bind_param($rv, "ii", $uid, $anime_id);
    mysqli_stmt_execute($rv);
    $user_review = mysqli_fetch_assoc(mysqli_stmt_get_result($rv));
}

// All reviews
$rev_result = mysqli_query($con, "SELECT r.*, u.fname, u.lname FROM reviews r JOIN users u ON u.id = r.user_id WHERE r.anime_id = $anime_id ORDER BY r.created_at DESC LIMIT 20");
$all_reviews = mysqli_fetch_all($rev_result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($anime['title']) ?> – OtakuZone</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700;900&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="main.css">
    <link rel="stylesheet" href="watch.css">
</head>
<body>

<nav class="nav">
    <a href="index.php" class="logo">⛩ OtakuZone</a>
    <form class="search-form" method="get" action="index.php">
        <input type="text" name="search" placeholder="Search anime...">
        <button type="submit">🔍</button>
    </form>
    <div class="nav-links">
        <a href="index.php">Home</a>
        <?php if (isAdmin()): ?><a href="admin.php" class="admin-link">⚙ Admin</a><?php endif; ?>
        <?php if (isLoggedIn()): ?>
            <span class="greeting">Hi, <?= htmlspecialchars($_SESSION['fname']) ?></span>
            <a href="logout.php" class="btn-nav">Log Out</a>
        <?php else: ?>
            <a href="login.php">Log In</a>
            <a href="signup.php" class="btn-nav">Sign Up</a>
        <?php endif; ?>
    </div>
</nav>

<div class="watch-layout">

    <!-- LEFT: Player + Info -->
    <div class="player-col">
        <div class="player-wrap">
            <?php if ($current_ep && $current_ep['video_url']): ?>
                <video controls autoplay class="video-player" key="<?= $current_ep['id'] ?>">
                    <source src="<?= htmlspecialchars($current_ep['video_url']) ?>" type="video/mp4">
                    Your browser does not support video.
                </video>
            <?php elseif (!empty($episodes)): ?>
                <div class="no-video">No video available for this episode.</div>
            <?php else: ?>
                <div class="no-video">No episodes added yet.</div>
            <?php endif; ?>
        </div>

        <?php if ($current_ep): ?>
        <div class="ep-now-info">
            <h2><?= htmlspecialchars($anime['title']) ?></h2>
            <p class="ep-label">Episode <?= $current_ep['episode_number'] ?><?= $current_ep['title'] ? ': ' . htmlspecialchars($current_ep['title']) : '' ?></p>
        </div>
        <?php endif; ?>

        <!-- Anime Details -->
        <div class="anime-details">
            <div class="details-left">
                <img src="<?= htmlspecialchars($anime['cover_image'] ?? '') ?>" alt="" onerror="this.style.display='none'" class="detail-cover">
            </div>
            <div class="details-right">
                <h3><?= htmlspecialchars($anime['title']) ?></h3>
                <p class="genre-tag"><?= htmlspecialchars($anime['genre']) ?></p>
                <p class="desc"><?= htmlspecialchars($anime['description']) ?></p>
                <div class="rating-display">
                    <span class="big-stars">
                        <?php
                        $s = round($anime['avg_stars'] ?? 0);
                        for ($i=1;$i<=5;$i++) echo $i<=$s ? '★' : '☆';
                        ?>
                    </span>
                    <span><?= $anime['avg_stars'] ? number_format($anime['avg_stars'],1) . '/5' : 'No reviews yet' ?></span>
                    <span class="muted">(<?= $anime['review_count'] ?? 0 ?> reviews)</span>
                </div>
            </div>
        </div>

        <!-- Review Form -->
        <div class="review-section">
            <h3>Rate & Review</h3>
            <?php if ($review_msg): ?>
                <div class="success-msg"><?= htmlspecialchars($review_msg) ?></div>
            <?php endif; ?>
            <?php if (isLoggedIn()): ?>
            <form method="post" class="review-form">
                <div class="star-picker" id="starPicker">
                    <?php for ($i=1;$i<=5;$i++): ?>
                        <input type="radio" name="stars" id="s<?=$i?>" value="<?=$i?>" <?= ($user_review && $user_review['stars']==$i) ? 'checked' : '' ?> required>
                        <label for="s<?=$i?>">★</label>
                    <?php endfor; ?>
                </div>
                <textarea name="comment" placeholder="Write your review..." rows="3"><?= $user_review ? htmlspecialchars($user_review['comment']) : '' ?></textarea>
                <button type="submit" class="btn-primary"><?= $user_review ? 'Update Review' : 'Submit Review' ?></button>
            </form>
            <?php else: ?>
                <p class="muted">Please <a href="login.php">log in</a> to leave a review.</p>
            <?php endif; ?>

            <!-- Reviews List -->
            <?php if (!empty($all_reviews)): ?>
            <div class="reviews-list">
                <?php foreach ($all_reviews as $rev): ?>
                <div class="review-item">
                    <div class="rev-header">
                        <strong><?= htmlspecialchars($rev['fname'] . ' ' . $rev['lname']) ?></strong>
                        <span class="rev-stars">
                            <?php for ($i=1;$i<=5;$i++) echo $i<=$rev['stars'] ? '★' : '☆'; ?>
                        </span>
                    </div>
                    <?php if ($rev['comment']): ?>
                        <p><?= htmlspecialchars($rev['comment']) ?></p>
                    <?php endif; ?>
                    <small class="muted"><?= date('M j, Y', strtotime($rev['created_at'])) ?></small>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- RIGHT: Episode List -->
    <div class="episode-col">
        <div class="ep-header">
            <h3>Episodes <span class="ep-total"><?= count($episodes) ?></span></h3>
        </div>
        <div class="ep-list">
            <?php if (empty($episodes)): ?>
                <p class="muted" style="padding:15px;">No episodes yet.</p>
            <?php else: ?>
                <?php foreach ($episodes as $ep): ?>
                <a href="watch.php?anime=<?= $anime_id ?>&ep=<?= $ep['id'] ?>"
                   class="ep-item <?= ($current_ep && $current_ep['id'] == $ep['id']) ? 'active' : '' ?>">
                    <span class="ep-num">EP <?= $ep['episode_number'] ?></span>
                    <span class="ep-title"><?= htmlspecialchars($ep['title'] ?? 'Episode ' . $ep['episode_number']) ?></span>
                    <?php if ($current_ep && $current_ep['id'] == $ep['id']): ?>
                        <span class="now-playing">▶</span>
                    <?php endif; ?>
                </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</div>

<footer class="footer">
    <p>⛩ OtakuZone &copy; <?= date('Y') ?> — The Quiet Corner for Every Otaku</p>
</footer>

</body>
</html>
