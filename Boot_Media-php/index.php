<?php 
require("connect.php");
include("htm.php");

//Gets the recommended communities.
$stmt = $db->prepare("SELECT id FROM communities WHERE is_recommend = 1 LIMIT 4");
$stmt->execute();
if($stmt->error) {
	ShowError(500, 'There was an error while grabbing the recommended communities.');
}
$rcresult = $stmt->get_result();

if(!isset($_GET['offset'])) {
	$offset = 0;
	$date_time = null;
} else {
	$offset = ($_GET['offset'] * 30);
	$date_time = $_GET['date_time'];
}

//Gets the post feed. If you aren't logged in, the feed will contain posts from the recommended communities. If you are, it will contain posts from users you followed, and the communities you joined. So it's kind of like Reddit!
if(!isset($_COOKIE['token_ses_data'])) {
	$postdata = $db->query("SELECT id FROM posts WHERE post_community IN (SELECT id FROM communities WHERE is_recommend = 1) AND is_deleted = 0".($offset > 0 ? " AND date_time < '".$db->real_escape_string($date_time)."'" : '')." ORDER BY date_time DESC LIMIT 30 offset ".$db->real_escape_string($offset));
	/*$stmt = $db->prepare("SELECT id FROM posts WHERE post_community IN (SELECT id FROM communities WHERE is_recommend = 1) AND is_deleted = 0 ORDER BY date_time DESC LIMIT 30");
	$stmt->execute();
	if($stmt->error) {
		ShowError(500, 'There was an error while grabbing the posts from the recommended communities.');
	}
	$presult = $stmt->get_result();*/
} else {
	$postdata = $db->query("SELECT id FROM posts WHERE is_deleted = 0 AND (creator IN (SELECT id FROM users WHERE id = ".$user['id'].") OR post_community IN (SELECT community FROM community_joins WHERE creator = ".$user['id']."))".($offset > 0 ? " AND date_time < '".$db->real_escape_string($date_time)."'" : '')." ORDER BY date_time DESC LIMIT 30 offset ".$db->real_escape_string($offset));
	/*$stmt = $db->prepare("SELECT id FROM posts WHERE is_deleted = 0 AND (creator IN (SELECT id FROM users WHERE id = ?) OR post_community IN (SELECT community FROM community_joins WHERE creator = ?)) ORDER BY date_time DESC LIMIT 30");
	$stmt->bind_param('ii', $user['id'], $user['id']);
	$stmt->execute();
	if($stmt->error) {
		ShowError(500, 'There was an error while grabbing the posts from your personal feed.');
	}
	$presult = $stmt->get_result();*/
}

if(isset($_GET['offset']) && isset($_GET['date_time'])) {
	while($post = mysqli_fetch_array($postdata)) {
		printPost($post['id'],0);
	} 
	exit();
}

?>
<html>
	<?php PrintHeader('Home'); ?>
	<body>
      <?php PrintNavBar('home'); ?>
		<div class="container">
			<div class="page-header">
				<h1>Boot_Media</h1>
			</div>
			<?php 
			if(!isset($_COOKIE['token_ses_data'])) {
			?>
			<p>Boot_Media is the first website in the world that isn't a fucking Miiverse clone.</p>
			<p><strong>Pros:</strong> It is not a Miiverse clone.<br><strong>Cons:</strong> There are none. This is the best website in the fucking universe simply because it isn't a Miiverse clone.</p>
			<p>Why not join the fun? <a class="btn btn-primary" href="signup.php">Create an Account</a> or <a class="btn btn-primary" href="/login">Login</a> if you already have one.</p> <?php } else {?>
			<p>Welcome to Boot_Media, <?php echo $user['nick_name']; ?>!
			<?php } ?>
			<div class="panel panel-default visible-lg">
				<div class="panel-heading">
					Recommended Communities
				</div>
				<div class="panel-body">
					<?php if($rcresult->num_rows == 0) {
						echo 'There are no communities yet.';
					} else {
						while($row = $rcresult->fetch_assoc()) {
							PrintCommunityList($row['id']);
						}
						echo '<list class="list-group-item"><a href="/communities/recommended">View More</a></list>';
					} ?>
				</div>
			</div>
			<?php if(isset($_COOKIE['token_ses_data'])) { ?> <a class="btn btn-primary" href="/post"><span class="badge">+</span> Create post</a><br><br> <?php } ?>
			<div class="panel panel-default">
				<div class="panel-heading">
					Post feed
				</div>
				<div class="panel-body">
					<?php if(mysqli_num_rows($postdata) == 0) {
						echo 'There aren\'t any posts in your feed yet. Why don\'t you try following some people and joining communities?';
					} else {
						while($post = mysqli_fetch_array($postdata)) {
							PrintPost($post['id'], 1);
						}
						if(mysqli_num_rows($postdata) != 0) {
							echo '<li class="list-group-item load-more"><button id="load-more-button" data-href="/index.php?i=0" date_time="'.date("Y-m-d H:i:s").'" class="btn btn-primary">View More</button></li>';
						}
					} ?>
				</div>
			</div>
		</div>
	</body>
</html>