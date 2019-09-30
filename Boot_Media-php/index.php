<?php 
require("connect.php");
include("htm.php");

//Gets the recommended communities.
$get_rec_commun = $db->query("SELECT * FROM communities WHERE is_recommend = 1 LIMIT 4");
$rc_count = mysqli_num_rows($get_rec_commun);

//Gets the post feed. If you aren't logged in, the feed will contain posts from the recommended communities. If you are, it will contain posts from users you followed, and the communities you joined. So it's kind of like Reddit!
if(!isset($_COOKIE['token_ses_data'])) {
	$get_feed = $db->query("SELECT id FROM posts WHERE post_community IN (SELECT id FROM communities WHERE is_recommend = 1) AND is_deleted = 0 ORDER BY date_time DESC LIMIT 30");
} else {
	$get_feed = $db->query("SELECT id FROM posts WHERE is_deleted = 0 AND (creator IN (SELECT id FROM users WHERE id = ".$user['id'].") OR post_community IN (SELECT id FROM community_joins WHERE creator = ".$user['id'].")) ORDER BY date_time DESC LIMIT 30");
}
$post_count = mysqli_num_rows($get_feed);

?>
<html>
	<?php PrintHeader('Main'); ?>
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
			<div class="panel panel-default">
				<div class="panel-heading">
					Recommended Communities
				</div>
				<div class="panel-body">
					<?php if($rc_count == 0) {
						echo 'There are no communities yet.';
					} else {
						while($rec_com = mysqli_fetch_array($get_rec_commun)) {
							PrintCommunityList($rec_com['id']);
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
					<?php if($post_count == 0) {
						echo 'There aren\'t any posts in your feed yet. Why don\'t you try following some people and joining communities?';
					} else {
						while($feed = mysqli_fetch_array($get_feed)) {
							PrintPost($feed['id'], 1);
						}

						if($post_count != 0) {
							echo '<list class="list-group-item"><button class="btn btn-primary">View More</button></list>';
						}
					} ?>
				</div>
			</div>
		</div>
	</body>
</html>