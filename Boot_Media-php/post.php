<?php 
require("connect.php");
include("htm.php");

if(!isset($_GET['id'])) {
	exit("Please specify a post id.");
}
if(!isset($_GET['view_html'])) {
	$view_html = false;
} else {
	$view_html = true;
}

$post_id = mysqli_real_escape_string($db,$_GET['id']);
$get_pos_data = $db->query("SELECT * FROM posts WHERE id = $post_id AND is_deleted < 2");
$post_exists = mysqli_num_rows($get_pos_data);
$post = mysqli_fetch_array($get_pos_data);

if($post_exists == 0) {
	exit('<html>'.PrintHeader('Post doesn\'t exist').'<body>'.PrintNavBar('community').'
		<div class="container">
			<div class="page-header">
				<h1>Post doesn\'t exist.</h1>
			</div>
			<p>The post you\'re looking for doesn\'t seem to exist. Sorry for the inconvinience :(</p>
		</div>
	</body>
</html>');
}

$pos_owner_data = $db->query("SELECT id, user_name, nick_name, user_avatar, admin_level FROM users WHERE id = ".$post['creator']);
$owner_data = mysqli_fetch_array($pos_owner_data);

$community = $db->query("SELECT community_name, community_icon, community_owner FROM communities WHERE id = ".$post['post_community']." AND is_hidden = 0");
$com_row = mysqli_fetch_array($community);

$get_comments = $db->query("SELECT * FROM comments WHERE comment_post = ".$post_id);
$ccount = mysqli_num_rows($get_comments);

//Checks to see if you're a community admin
if(!empty($_COOKIE['token_ses_data']) && $com_row['community_owner'] == $user['id']) {
	$community_admin = 1;
} else {
	$community_admin = 0;
}

?>
<html>
	<?php PrintHeader($owner_data['nick_name'].'\'s post'); ?>
	<head>
		<?php PrintNavBar('post'); ?>
		<div class="container">
			<div class="page-header">
				<h1><a href="/users/<?php echo $owner_data['user_name']; ?>"><?php echo printUserAvatar($owner_data['id'], '40px').'</a> '.htmlspecialchars($owner_data['nick_name']); ?>'s post</h1>
				<?php if(mysqli_num_rows($community) != 0) { ?><h3><?php 
				echo '<a href="/communities/'.$post['post_community'].'"><img src="'.(empty($com_row['community_icon']) ? '/img/communityEmpty.png' : htmlspecialchars($com_row['community_icon'])).'" class="img-rounded" style="width: 40px;height: 40px;"></a> ';
				echo htmlspecialchars($com_row['community_name']); ?><?php } ?></h3>
				<p>Posted <?php echo humanTiming(strtotime($post['date_time'])); ?><?=($post['is_deleted'] > 0 ? ' <span class="label label-danger">Deleted</span>' : '')?><?=($post['is_pinned'] == 1 ? ' <span class="label label-primary">Pinned</span>' : '')?></p>
			</div>
		<?php if($post['uses_html'] == 1 && $view_html == false && $owner_data['id'] != $user['id']) {
			?>
		<div class="alert alert-danger"><b>Warning!</b> This post uses HTML, so there might be malicious code inside it. So if you <b>REALLY</b> trust this post, then you can continue <a href="/posts/<?php echo $_GET['id']; ?>/html">here</a>!</div>
			<?php
		}?>
		<p><?php if($post['uses_html'] == 0 || ($view_html == false && $owner_data['id'] != $user['id'])) {echo nl2br(htmlspecialchars($post['post_body']));} else {echo htmlspecialchars_decode($post['post_body'], ENT_HTML5);} ?></p>
		<?php
		if(!empty($post['post_image'])) {
			echo '<img src="'.htmlspecialchars($post['post_image']).'" class="img-rounded" style="width: 70%;height: auto;"></img><br><br>';
		}
			printLikeButton($post['id'], 0);
		if($user['id'] == $owner_data['id'] || $user['admin_level'] > $owner_data['admin_level'] || $community_admin == 1) {
			echo '<a class="btn btn-danger" href="/posts/'.$post_id.'/delete">Delete</a>';
		}
		?>
			<br><br>
			<?php if(isset($_COOKIE['token_ses_data'])) { ?> <a class="btn btn-primary" href="/posts/<?php echo $post_id; ?>/comment"><span class="badge">+</span> Create comment</a><br><br> <?php } ?>
			<div class="panel panel-default">
				<div class="panel-heading">
					Comments <span class="badge"><?php echo $ccount; ?></span>
				</div>
				<div class="panel-body">
					<?php 
						if($ccount !== 0) {
							while($comment = mysqli_fetch_array($get_comments)) {
								$get_user = $db->query("SELECT id, user_avatar, user_name FROM users WHERE id = ".$comment['creator']);
								$creator = mysqli_fetch_array($get_user);

								echo '<li class="list-group-item"><a href="/users/'.$creator['user_name'].'">'.printUserAvatar($comment['creator'], '35px').'</a> '.htmlspecialchars($comment['comment_body']).'<br><br>';
								printLikeButton($comment['id'], 1);
								echo '<div align="left"><span style="color: #c4c4c4;">'.humanTiming(strtotime($comment['date_time'])).'</span></div></li>';
							}
						} else {
							echo 'There are no comments on this post yet.';
						}
					?>
				</div>
			</div>
		</div>
	</head>
</html>