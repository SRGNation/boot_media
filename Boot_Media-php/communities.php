<?php 
require("connect.php");
include("htm.php");

if(!isset($_GET['id'])) {
	exit("Please specify a community id.");
}

$community_id = mysqli_real_escape_string($db,$_GET['id']);
$get_com_data = $db->query("SELECT * FROM communities WHERE id = $community_id");
$comm_exists = mysqli_num_rows($get_com_data);
$community = mysqli_fetch_array($get_com_data);

if($comm_exists == 0) {
	exit('<html>'.PrintHeader('Community doesn\'t exist').'<body>'.PrintNavBar('community').'
		<div class="container">
			<div class="page-header">
				<h1>Community doesn\'t exist.</h1>
			</div>
			<p>The community you\'re looking for doesn\'t seem to exist. Sorry for the inconvinience :(</p>
		</div>
	</body>
</html>');
}

$com_owner_data = $db->query("SELECT id, user_name, nick_name FROM users WHERE id = ".$community['community_owner']);
$owner_data = mysqli_fetch_array($com_owner_data);

$cpostdata = $db->query("SELECT * FROM posts WHERE post_community = $community_id AND is_deleted = 0 ORDER BY is_pinned DESC, date_time DESC LIMIT 30");

?>
<html>
	<?php PrintHeader($community['community_name']); ?>
	<body>
      <?php PrintNavBar('home'); ?>
		<div class="container">
			<?php echo '<img src="'.htmlspecialchars($community['community_banner']).'" class="img-rounded" style="width: 100%;height: auto;">'; ?>
			<div class="page-header">
				<h1><?php echo '<img src="'.htmlspecialchars($community['community_icon']).'" class="img-rounded" style="width: 50px;height: 50px;"> '.htmlspecialchars($community['community_name']); ?></h1>
				<p><?php echo htmlspecialchars($community['community_desc']); ?></p>
				<?php if(mysqli_num_rows($com_owner_data) != 0) { ?>
				<h4><?php echo printUserAvatar($owner_data['id'], '30px'); ?> Community created by <?php echo $owner_data['nick_name']; ?></h4> <?php } ?>
			</div>
			<?php if(isset($_COOKIE['token_ses_data'])) { ?> <a class="btn btn-primary" href="/communities/<?php echo $community_id; ?>/post"><span class="badge">+</span> Create post</a><br><br> <?php } ?>
			<div class="panel panel-default">
				<div class="panel-heading">
					All Posts
				</div>
				<div class="panel-body">
					<?php
						if(mysqli_num_rows($cpostdata) == 0) {
							echo 'There doesn\'t seem to be any posts in this community yet. Why don\'t you make the first one?';
						} else {
							while($post = mysqli_fetch_array($cpostdata)) {
								PrintPost($post['id']);
							}
						}

						if(mysqli_num_rows($cpostdata) != 0) {
							echo '<list class="list-group-item"><button class="btn btn-primary">View More</button></list>';
						}
					?>
				</div>
			</div>
		</div>
	</body>
</html>