<?php 
require("connect.php");
include("htm.php");

if(!isset($_GET['id'])) {
	exit("Please specify a community id.");
}

if(!isset($_GET['offset'])) {
	$offset = 0;
	$date_time = date("Y-m-d H:i:s");
} else {
	$offset = ($_GET['offset'] * 30);
	$date_time = mysqli_real_escape_string($db,$_GET['date_time']);
}

$community_id = mysqli_real_escape_string($db,$_GET['id']);
$get_com_data = $db->query("SELECT * FROM communities WHERE id = $community_id");
$comm_exists = mysqli_num_rows($get_com_data);
$community = mysqli_fetch_array($get_com_data);

if($comm_exists == 0) {
	if(!isset($_GET['offset']) && !isset($_GET['date_time'])) {
		exit('<html>'.PrintHeader('Community doesn\'t exist').'<body>'.PrintNavBar('community').'
			<div class="container">
				<div class="page-header">
					<h1>Community doesn\'t exist.</h1>
				</div>
				<p>The community you\'re looking for doesn\'t seem to exist. Sorry for the inconvinience :(</p>
				</div>
			</body>
		</html>');
	} else {
		exit();
	}
}

$com_owner_data = $db->query("SELECT id, user_name, nick_name FROM users WHERE id = ".$community['community_owner']);
$owner_data = mysqli_fetch_array($com_owner_data);

$cpostdata = $db->query("SELECT * FROM posts WHERE post_community = $community_id AND is_deleted = 0 AND date_time < '$date_time' ORDER BY is_pinned DESC, date_time DESC LIMIT 30 offset $offset");
if(isset($_GET['offset']) && isset($_GET['date_time'])) {
	while($post = mysqli_fetch_array($cpostdata)) {
		printPost($post['id'],0);
	} 
	exit();
}

//Gets the amount of people who joined the community
$like_com_count = mysqli_num_rows($db->query("SELECT id FROM community_joins WHERE community = ".$community['id']));

?>
<html>
	<?php PrintHeader($community['community_name']); ?>
	<body>
		<?php PrintNavBar('home'); ?>
		<div class="container">
			<?php echo '<img src="'.htmlspecialchars($community['community_banner']).'" class="img-rounded" style="width: 100%;height: auto;">'; ?>
			<div class="page-header">
				<h1><?php echo '<img src="'.(empty($community['community_icon']) ? '/img/communityEmpty.png' : htmlspecialchars($community['community_icon'])).'" class="img-rounded" style="width: 50px;height: 50px;"> '.htmlspecialchars($community['community_name']); ?></h1>
				<p><?php echo htmlspecialchars($community['community_desc']); ?></p>
				<?php if(isset($_COOKIE['token_ses_data']) & $user['id'] != $community['community_owner']) { 
					$get_join = $db->query("SELECT id FROM community_joins WHERE creator = ".$user['id']." AND community = $community_id");
					if(mysqli_num_rows($get_join) == 0) {
					?> <div id="join-community"><button id="<?=$community['id']?>" class="btn btn-primary join-community-button"><span class="join-community-button-text">Join Community</span> <span class="badge"><div class="join-community-count"><?=$like_com_count?></div></span></button></div>
					<?php } else { ?>
					<a class="btn btn-primary" href="/communities/<?=$community_id?>/unjoin"><span class="join-community-button-text">Unjoin Community</span> <span class="badge"><div class="join-community-count"><?=$like_com_count?></div></span></a>
					<?php } } ?>
				<?php if(mysqli_num_rows($com_owner_data) != 0) { ?>
				<h4><a href="/users/<?php echo $owner_data['user_name']; ?>"><?php echo printUserAvatar($owner_data['id'], '30px'); ?></a> Community created by <?php echo htmlspecialchars($owner_data['nick_name']); ?></h4> <?php } ?>
			</div>
			<?php if(isset($_COOKIE['token_ses_data'])) { ?> <a class="btn btn-primary" href="/communities/<?php echo $community_id; ?>/post"><span class="badge">+</span> Create post</a><br><br> <?php } ?>
			<div class="panel panel-default">
				<div class="panel-heading">
					All Posts
				</div>
				<div class="panel-body">
					<?php
						if(mysqli_num_rows($cpostdata) == 0) {
							echo 'There aren\'t any posts in this community yet. Why don\'t you make the first one?';
						} else {
							while($post = mysqli_fetch_array($cpostdata)) {
								PrintPost($post['id'], 0);
							}
						}

						if(mysqli_num_rows($cpostdata) != 0) {
							echo '<list id="load-more" class="list-group-item"><button id="load-more-button" data-href="/communities.php?id='.$community['id'].'" date_time="'.date("Y-m-d H:i:s").'" class="btn btn-primary">View More</button></list>';
						}
					?>
				</div>
			</div>
		</div>
	</body>
</html>