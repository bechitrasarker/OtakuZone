<?php require_once 'connection.php'; ?>
<?php
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($search !== '') {
    $like = "%$search%";
    $stmt = mysqli_prepare($con, "SELECT a.*, ROUND(AVG(r.stars),1) AS avg_stars, COUNT(DISTINCT r.id) AS review_count, COUNT(DISTINCT e.id) AS episode_count FROM anime a LEFT JOIN reviews r ON r.anime_id=a.id LEFT JOIN episodes e ON e.anime_id=a.id WHERE a.title LIKE ? OR a.genre LIKE ? GROUP BY a.id ORDER BY a.created_at DESC");
    mysqli_stmt_bind_param($stmt, "ss", $like, $like);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($con, "SELECT a.*, ROUND(AVG(r.stars),1) AS avg_stars, COUNT(DISTINCT r.id) AS review_count, COUNT(DISTINCT e.id) AS episode_count FROM anime a LEFT JOIN reviews r ON r.anime_id=a.id LEFT JOIN episodes e ON e.anime_id=a.id GROUP BY a.id ORDER BY a.created_at DESC");
}
$animes = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>OtakuZone</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700;900&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="main.css">
</head>
<body>
<nav class="nav">
<a href="index.php" class="logo">&#x26E9; OtakuZone</a>
<form class="search-form" method="get" action="index.php">
<input type="text" name="search" placeholder="Search anime..." value="<?php echo htmlspecialchars($search); ?>">
<button type="submit">&#128269;</button>
</form>
<div class="nav-links">
<a href="index.php">Home</a>
<?php if (isAdmin()): ?><a href="admin.php" class="admin-link">&#9881; Admin</a><?php endif; ?>
<?php if (isLoggedIn()): ?>
<span class="greeting">Hi, <?php echo htmlspecialchars($_SESSION['fname']); ?></span>
<a href="logout.php" class="btn-nav">Log Out</a>
<?php else: ?>
<a href="login.php">Log In</a>
<a href="signup.php" class="btn-nav">Sign Up</a>
<?php endif; ?>
</div>
</nav>
<section class="hero">
<div class="hero-text">
<h1>Your Portal to<br><span>Every Universe</span></h1>
<p>Stream anime. Discover worlds. Join the community.</p>
<a href="#anime-grid" class="btn-hero">Explore Anime</a>
</div>
</section>
<section class="section" id="anime-grid">
<div class="section-header">
<h2><?php echo $search ? 'Results for "'.htmlspecialchars($search).'"' : 'Latest Anime'; ?></h2>
<?php if ($search): ?><a href="index.php" class="clear-search">&#10005; Clear</a><?php endif; ?>
</div>
<?php if (empty($animes)): ?>
<div class="empty-state">No anime found.</div>
<?php else: ?>
<div class="anime-grid">
<?php foreach ($animes as $anime): ?>
<a href="watch.php?anime=<?php echo $anime['id']; ?>" class="anime-card">
<div class="card-img">
<?php if ($anime['cover_image']): ?>
<img src="<?php echo htmlspecialchars($anime['cover_image']); ?>" alt="<?php echo htmlspecialchars($anime['title']); ?>">
<?php else: ?><div class="no-img">&#127916;</div><?php endif; ?>
<div class="card-overlay"><span class="play-btn">&#9654; Watch Now</span></div>
<span class="status-badge <?php echo $anime['status']; ?>"><?php echo ucfirst($anime['status']); ?></span>
</div>
<div class="card-info">
<h3><?php echo htmlspecialchars($anime['title']); ?></h3>
<p class="genre"><?php echo htmlspecialchars($anime['genre']); ?></p>
<div class="card-meta">
<span class="stars">
<?php $s=round($anime['avg_stars']??0); for($i=1;$i<=5;$i++) echo $i<=$s?'★':'☆'; ?>
<small><?php echo $anime['avg_stars'] ? number_format($anime['avg_stars'],1) : 'No reviews'; ?></small>
</span>
<span class="ep-count"><?php echo $anime['episode_count']; ?> eps</span>
</div>
</div>
</a>
<?php endforeach; ?>
</div>
<?php endif; ?>
</section>
<footer class="footer">
<p>&#x26E9; OtakuZone &copy; <?php echo date('Y'); ?></p>
</footer>
</body>
</html>
