<?php 
require("connect.php");
include("htm.php");

if(!isset($_GET['id'])) {
	exit("Please specify a community id.");
}

if(!isset($_GET['offset'])) {
	$offset = 0;
	$date_time = null;
} else {
	$offset = ($_GET['offset'] * 30);
	$date_time = $_GET['date_time'];
}

$stmt = $db->prepare("SELECT id, community_icon, community_banner, community_name, community_desc, community_owner, COUNT(*) FROM communities WHERE id = ?");
$stmt->bind_param('i', $_GET['id']);
$stmt->execute();
$result = $stmt->get_result();
$community = $result->fetch_assoc();

if($community['COUNT(*)'] == 0) {
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

$stmt = $db->prepare("SELECT id, user_name, nick_name, COUNT(*) FROM users WHERE id = ?");
$stmt->bind_param('i', $community['community_owner']);
$stmt->execute();
$result = $stmt->get_result();
$communityod = $result->fetch_assoc();

$cpostdata = $db->query("SELECT id FROM posts WHERE post_community = ".$community['id']." AND is_deleted = 0".($offset > 0 ? " AND date_time < '".$db->real_escape_string($date_time)."'" : '')." ORDER BY is_pinned DESC, date_time DESC LIMIT 30 offset ".$db->real_escape_string($offset));

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
			<?php if(!empty($community['community_banner'])) {echo '<img src="'.htmlspecialchars($community['community_banner']).'" class="img-rounded" style="width: 100%;height: auto;">';} ?>
			<div class="page-header">
				<h1><?php echo '<img src="'.(empty($community['community_icon']) ? '/img/communityEmpty.png' : htmlspecialchars($community['community_icon'])).'" class="img-rounded" style="width: 50px;height: 50px;"> '.htmlspecialchars($community['community_name']); ?></h1>
				<p><?php echo htmlspecialchars($community['community_desc']); ?></p>
				<?php 
				if(isset($_COOKIE['token_ses_data']) & $user['id'] != $community['community_owner']) { 
					$get_join = $db->query("SELECT id FROM community_joins WHERE creator = ".$user['id']." AND community = ".$community['id']);
					if(mysqli_num_rows($get_join) == 0) {?> 
						<div id="join-community"><button id="<?=$community['id']?>" class="btn btn-primary join-community-button"><span class="join-community-button-text">Join Community</span> <span class="badge"><div class="join-community-count"><?=$like_com_count?></div></span></button></div>
					<?php } else { ?>
						<a class="btn btn-primary" href="/communities/<?=$community['id']?>/unjoin"><span class="join-community-button-text">Unjoin Community</span> <span class="badge"><div class="join-community-count"><?=$like_com_count?></div></span></a>
					<?php } 
				} ?>
				<?php if($communityod['COUNT(*)'] != 0) { ?>
				<h4><a href="/users/<?=$communityod['user_name']?>"><?php echo printUserAvatar($communityod['id'], '30px'); ?></a> Community created by <?=htmlspecialchars($communityod['nick_name'])?></h4> <?php } ?>
			</div>
			<?php if(isset($_COOKIE['token_ses_data'])) { ?> <a class="btn btn-primary" href="/communities/<?=$community['id']?>/post"><span class="badge">+</span> Create post</a><br><br> <?php } ?>
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
							echo '<li class="list-group-item load-more"><button id="load-more-button" data-href="/communities.php?id='.$community['id'].'" date_time="'.date("Y-m-d H:i:s").'" class="btn btn-primary">View More</button></li>';
						}
					?>
				</div>
			</div>
		</div>
	</body>
</html>