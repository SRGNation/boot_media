<?php 
require("connect.php");
include("htm.php");

if(!isset($_GET['id'])) {
	exit("Please specify a username.");
}

if(!isset($_GET['page'])) {
	exit("Please specify the type of profile.");
}

$username = mysqli_real_escape_string($db,$_GET['id']);
$get_use_data = $db->query("SELECT id, user_name, nick_name, user_bio FROM users WHERE user_name = '$username'");
$user_exists = mysqli_num_rows($get_use_data);
$users = mysqli_fetch_array($get_use_data);

if($user_exists == 0) {
	exit('<html>'.PrintHeader('User doesn\'t exist').'<body>'.PrintNavBar('user').'
		<div class="container">
			<div class="page-header">
				<h1>User doesn\'t exist.</h1>
			</div>
			<p>The user you\'re looking for doesn\'t seem to exist. Sorry for the inconvinience :(</p>
		</div>
	</body>
</html>');
}

//Get posts
$get_posts = $db->query("SELECT id, post_community FROM posts WHERE creator = ".$users['id']." AND is_deleted < 2 ORDER BY date_time DESC LIMIT 5");

//Get likes
$get_likes = $db->query("SELECT id, post_like FROM likes WHERE like_type = 0 AND creator = ".$users['id']." ORDER BY id DESC LIMIT 5");

if($_GET['page'] == 'posts') {
	$get_posts = $db->query("SELECT id, post_community FROM posts WHERE creator = ".$users['id']." AND is_deleted < 2 ORDER BY date_time DESC LIMIT 30");
} 
elseif($_GET['page'] == 'likes') {
	$get_likes = $db->query("SELECT id, post_like FROM likes WHERE like_type = 0 AND creator = ".$users['id']." ORDER BY id DESC LIMIT 30");
}
elseif($_GET['page'] == 'comments') {
	$get_comments = $db->query("SELECT * FROM comments WHERE creator = ".$users['id']." AND is_deleted < 2 ORDER BY date_time DESC LIMIT 30");
}

?>
<html>
	<?php 
	if($_GET['page'] == 'profile') {PrintHeader(htmlspecialchars($users['nick_name']).'\'s profile');}
	elseif($_GET['page'] == 'posts') {PrintHeader(htmlspecialchars($users['nick_name']).'\'s posts');}
	elseif($_GET['page'] == 'likes') {PrintHeader(htmlspecialchars($users['nick_name']).'\'s likes');}
	elseif($_GET['page'] == 'comments') {PrintHeader(htmlspecialchars($users['nick_name']).'\'s comments');}
	?>
	<body>
		<?php PrintNavBar('profile'); ?>
		<div class="container">
			<div class="page-header">
				<h1><?php echo printUserAvatar($users['id'], '40px'); ?> <?php echo htmlspecialchars($users['nick_name']) ?>'s Profile</h1>
			</div>
			<ul class="nav nav-tabs">
				<li <?php if($_GET['page'] == 'profile') {echo 'class="active"';} ?>><a href="/users/<?php echo $users['user_name'] ?>">Profile</a></li>
				<li <?php if($_GET['page'] == 'posts') {echo 'class="active"';} ?>><a href="/users/<?php echo $users['user_name'] ?>/posts">Posts</a></li>
				<li <?php if($_GET['page'] == 'likes') {echo 'class="active"';} ?>><a href="/users/<?php echo $users['user_name'] ?>/likes">Likes</a></li>
				<li <?php if($_GET['page'] == 'comments') {echo 'class="active"';} ?>><a href="/users/<?php echo $users['user_name'] ?>/comments">Comments</a></li>
			</ul>
			<br>
			<?php if($_GET['page'] == 'profile') {?> 
				<div class="panel panel-default">
					<div class="panel-heading">
						User Bio
					</div>
					<div class="panel-body">
						<?php if(!empty($users['user_bio'])) {echo htmlspecialchars($users['user_bio']);} else {echo 'This user didn\'t set a Bio yet.';} ?>
					</div>
				</div>
				<div class="panel panel-default">
					<div class="panel-heading">
						Recent Posts
					</div>
					<div class="panel-body">
						<?php 
							if(mysqli_num_rows($get_posts) != 0) {
								while($posts = mysqli_fetch_array($get_posts)) {
									if($posts['post_community'] != 0) { 
										PrintPost($posts['id'], 1);
									} else {
										PrintPost($posts['id'], 0);
									}
								}
							} else {
								echo 'This user doesn\'t have any posts yet.';
							}
						?>
					</div>
				</div>
				<div class="panel panel-default">
					<div class="panel-heading">
						Recent Likes
					</div>
					<div class="panel-body">
						<?php 
							if(mysqli_num_rows($get_likes) != 0) {
								while($like = mysqli_fetch_array($get_likes)) {
									$get_post = $db->query("SELECT id, is_deleted, post_community FROM posts WHERE id = ".$like['like_type']);
									$post = mysqli_fetch_array();
									if($post['is_deleted'] < 2) {
										if($post['post_community'] != 0) {
											PrintPost($like['post_like'], 1);
										} else {
											PrintPost($like['post_like'], 0);
										}
									}
								}
							} else {
								echo 'This user doesn\'t have any liked posts yet.';
							}
						?>
					</div>
				</div>
			<?php } ?>
			<?php if($_GET['page'] == 'posts') {?> 
				<div class="panel panel-default">
					<div class="panel-heading">
						All Posts <span class="badge"><?php echo mysqli_num_rows($get_posts); ?></span>
					</div>
					<div class="panel-body">
						<?php 
							if(mysqli_num_rows($get_posts) != 0) {
								while($posts = mysqli_fetch_array($get_posts)) {
									if($posts['post_community'] != 0) { 
										PrintPost($posts['id'], 1);
									} else {
										PrintPost($posts['id'], 0);
									}
								}
							} else {
								echo 'This user doesn\'t have any posts yet.';
							}
							if(mysqli_num_rows($get_posts) != 0) {
								echo '<list class="list-group-item"><button class="btn btn-primary">View More</button></list>';
							}
						?>
					</div>
				</div>
			<?php } ?>
			<?php if($_GET['page'] == 'likes') {?> 
				<div class="panel panel-default">
					<div class="panel-heading">
						All Likes <span class="badge"><?php echo mysqli_num_rows($get_likes); ?></span>
					</div>
					<div class="panel-body">
						<?php 
							if(mysqli_num_rows($get_likes) != 0) {
								while($like = mysqli_fetch_array($get_likes)) {
									$get_post = $db->query("SELECT id, is_deleted, post_community FROM posts WHERE id = ".$like['like_type']);
									$post = mysqli_fetch_array();
									if($post['is_deleted'] < 2) {
										if($post['post_community'] != 0) {
											PrintPost($like['post_like'], 1);
										} else {
											PrintPost($like['post_like'], 0);
										}
									}
								}
							} else {
								echo 'This user doesn\'t have any liked posts yet.';
							}
							if(mysqli_num_rows($get_likes) != 0) {
								echo '<list class="list-group-item"><button class="btn btn-primary">View More</button></list>';
							}
						?>
					</div>
				</div>
			<?php } ?>
			<?php if($_GET['page'] == 'comments') {?> 
				<div class="panel panel-default">
					<div class="panel-heading">
						All Comments <span class="badge"><?php echo mysqli_num_rows($get_comments);?></span>
					</div>
					<div class="panel-body">
						<?php
							if(mysqli_num_rows($get_comments) != 0) {
								while($comment = mysqli_fetch_array($get_comments)) {
									$get_user = $db->query("SELECT id, user_avatar, user_name FROM users WHERE id = ".$comment['creator']);
									$creator = mysqli_fetch_array($get_user);

									$get_post = $db->query("SELECT id, creator, post_body FROM posts WHERE id = ".$comment['comment_post']." AND is_deleted < 2");
									$post = mysqli_fetch_array($get_post);

									if(strlen($post['post_body']) > 32) {
									  $content_st = mb_substr($post['post_body'],0,29).'...';
									} else {
									  $content_st = $post['post_body'];
									}

									echo '<li class="list-group-item"><a href="/users/'.$creator['user_name'].'">'.printUserAvatar($comment['creator'], '35px').'</a> <a href="/posts/'.$comment['comment_post'].'">'.htmlspecialchars($comment['comment_body']).'</a><br><br>';
									printLikeButton($comment['id'], 1);
									echo '<div align="left"><span style="color: #c4c4c4;">'.humanTiming(strtotime($comment['date_time'])).''.(mysqli_num_rows($get_post) != 0 ? ', ('.$content_st.')' : '').'</span></div></li>';
								}
							} else {
								echo 'This user didn\'t comment anything yet.';
							}
						?>
					</div>
				</div>
			<?php } ?>
		</div>
	</body>
</html>