<?php 
require("connect.php");
include("htm.php");

if(!isset($_GET['id'])) {
	ShowError(400, 'You must specify a username.');
}

if(!isset($_GET['page'])) {
	ShowError(400, 'You must specify the page type.');
}

$stmt = $db->prepare("SELECT id, user_name, nick_name, user_bio, date_created, user_type, admin_level, hide_liked_posts, user_login_ip, email_address FROM users WHERE user_name = ?");
$stmt->bind_param('s', $_GET['id']);
$stmt->execute();
if($stmt->error) {
	ShowError(500, 'There was an error while trying to get the user.');
}
$result = $stmt->get_result();
$users = $result->fetch_assoc();

if($result->num_rows === 0) {
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

?>
<html>
	<?php 
	switch($_GET['page']) {
		case "profile":
			PrintHeader(htmlspecialchars($users['nick_name']).'\'s profile');
		break;
		case "posts":
			PrintHeader(htmlspecialchars($users['nick_name']).'\'s posts');
		break;
		case "likes":
			PrintHeader(htmlspecialchars($users['nick_name']).'\'s likes');
		break;
		case "comments":
			PrintHeader(htmlspecialchars($users['nick_name']).'\'s comments');
	}
	?>
	<body>
		<?php PrintNavBar('profile'); ?>
		<div class="container">
			<div class="page-header">
				<h1><?php echo printUserAvatar($users['id'], '40px'); ?> <?php echo htmlspecialchars($users['nick_name']); ?>'s Profile</h1>
				<p><b>Username:</b> <?php echo $users['user_name'] ?>, <b>Since:</b> <?php echo humanTiming(strtotime($users['date_created'])); ?> 
				<?php
				switch($users['user_type']) {
					case 1:
						echo '<span class="label label-danger">Deleted</span> ';
					break;
					case 2:
						echo '<span class="label label-danger">Banned</span> ';
					break;
					case 3:
						echo '<span class="label label-success">Donator</span> ';
					break;
					case 4:
						echo '<span class="label label-success">Owner</span> ';
				}
				
				if($users['admin_level'] > 0) {
					echo '<span class="label label-success">Admin</span> ';
				}
 				?></p>
			</div>
			<ul class="nav nav-tabs">
				<li <?=$_GET['page'] === 'profile' ? 'class="active"' : ''?>><a href="/users/<?php echo $users['user_name'] ?>">Profile</a></li>
				<li <?=$_GET['page'] === 'posts' ? 'class="active"' : ''?>><a href="/users/<?php echo $users['user_name'] ?>/posts">Posts</a></li>
				<?php if($users['hide_liked_posts'] == 0 || $users['id'] == $user['id']) { ?> <li <?php if($_GET['page'] == 'likes') {echo 'class="active"';} ?>><a href="/users/<?php echo $users['user_name'] ?>/likes">Likes</a></li><?php } ?>
				<li <?=$_GET['page'] === 'comments' ? 'class="active"' : ''?>><a href="/users/<?php echo $users['user_name'] ?>/comments">Comments</a></li>
			</ul>
			<br>
			<?php if($_GET['page'] == 'profile') {?> 
				<?php 
				if($user['admin_level'] > 0) {
					$get_sess = $db->query("SELECT id, date_time, ip FROM sessions WHERE user_id = ".$users['id']." AND website IS NULL ORDER BY date_time DESC");
					$session = mysqli_fetch_array($get_sess);
				?>
				<div class="panel panel-default">
					<div class="panel-heading">
						Information for Admins
					</div>
					<div class="panel-body">
						<p>User ID: <span class="badge"><?=$users['id']?></span></p>
						<p>Email Address: <span class="badge"><?=$users['email_address']?></span></p>
						<p>IP Address: <span class="badge"><?=$users['user_login_ip']?></span></p>
						<p>Last Login Date: <span class="badge"><?=humanTiming(strtotime($session['date_time']))?></span></p>
						<a class="btn btn-danger" href="/users/<?=$users['user_name']?>/delete">Delete User</a> <a class="btn btn-danger" href="/users/<?=$users['user_name']?>/ban">Ban User</a> <a class="btn btn-danger" href="/users/<?=$users['user_name']?>/purge">Purge</a>
					</div>
				</div>
				<?php } ?>
				<div class="panel panel-default">
					<div class="panel-heading">
						User Bio
					</div>
					<div class="panel-body">
						<?php if(!empty($users['user_bio'])) {echo nl2br(htmlspecialchars($users['user_bio']));} else {echo 'This user didn\'t set a Bio yet.';} ?>
					</div>
				</div>
				<div class="panel panel-default">
					<div class="panel-heading">
						Recent Posts
					</div>
					<div class="panel-body">
						<?php 
							$get_posts = $db->query("SELECT id, post_community FROM posts WHERE creator = ".$users['id']." AND is_deleted < 2 ORDER BY date_time DESC LIMIT 5");
							if(mysqli_num_rows($get_posts) !== 0) {
								while($posts = mysqli_fetch_array($get_posts)) {
									PrintPost($posts['id'], 1);
								}
							} else {
								echo 'This user doesn\'t have any posts yet.';
							}
						?>
					</div>
				</div>
				<?php if($users['hide_liked_posts'] == 0 || $users['id'] == $user['id']) { ?>
				<div class="panel panel-default">
					<div class="panel-heading">
						Recent Likes
					</div>
					<div class="panel-body">
						<?php 
							$get_likes = $db->query("SELECT id, post_like FROM likes WHERE like_type = 0 AND post_like NOT IN (SELECT id FROM posts WHERE is_deleted > 1 AND id = post_like) AND creator = ".$users['id']." ORDER BY id DESC LIMIT 5");
							if(mysqli_num_rows($get_likes) != 0) {
								while($like = mysqli_fetch_array($get_likes)) {
									$get_post = $db->query("SELECT id, is_deleted, post_community FROM posts WHERE id = ".$like['post_like']);
									$post = mysqli_fetch_array($get_post);

									if($post['post_community'] != 0) {
										PrintPost($like['post_like'], 1);
									} else {
										PrintPost($like['post_like'], 0);
									}
								}
							} else {
								echo 'This user doesn\'t have any liked posts yet.';
							}
						?>
					</div>
				</div>
			<?php }
			} ?>
			<?php if($_GET['page'] == 'posts') {
				$get_posts = $db->query("SELECT id, post_community FROM posts WHERE creator = ".$users['id']." AND is_deleted < 2 ORDER BY date_time DESC LIMIT 30");
				$post_count = mysqli_num_rows($db->query("SELECT id FROM posts WHERE creator = ".$users['id']." AND is_deleted < 2"));
				?> 
				<div class="panel panel-default">
					<div class="panel-heading">
						All Posts <span class="badge"><?php echo $post_count; ?></span>
					</div>
					<div class="panel-body">
						<?php 
							if(mysqli_num_rows($get_posts) != 0) {
								while($posts = mysqli_fetch_array($get_posts)) {
									PrintPost($posts['id'], 1);
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
			<?php if($_GET['page'] == 'likes') {
				if($users['hide_liked_posts'] == 1 & $users['id'] != $user['id']) {
					exit("This user has hidden their like history.");
				}
				$get_likes = $db->query("SELECT id, post_like FROM likes WHERE like_type = 0 AND post_like NOT IN (SELECT id FROM posts WHERE is_deleted > 1 AND id = post_like) AND creator = ".$users['id']." ORDER BY id DESC LIMIT 30");
				$like_count = mysqli_num_rows($db->query("SELECT id, post_like FROM likes WHERE like_type = 0 AND post_like NOT IN (SELECT id FROM posts WHERE is_deleted > 1 AND id = post_like) AND creator = ".$users['id']));
				?> 
				<div class="panel panel-default">
					<div class="panel-heading">
						All Likes <span class="badge"><?php echo $like_count; ?></span>
					</div>
					<div class="panel-body">
						<?php 
							if(mysqli_num_rows($get_likes) != 0) {
								while($like = mysqli_fetch_array($get_likes)) {
									$get_post = $db->query("SELECT id, is_deleted, post_community FROM posts WHERE id = ".$like['post_like']);
									$post = mysqli_fetch_array($get_post);

									if($post['post_community'] != 0) {
										PrintPost($like['post_like'], 1);
									} else {
										PrintPost($like['post_like'], 0);
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
			<?php if($_GET['page'] == 'comments') {
				$get_comments = $db->query("SELECT * FROM comments WHERE creator = ".$users['id']." AND is_deleted < 2 ORDER BY date_time DESC LIMIT 30");
				$comment_count = mysqli_num_rows($db->query("SELECT id FROM comments WHERE creator = ".$users['id']." AND is_deleted < 2"));
				?> 
				<div class="panel panel-default">
					<div class="panel-heading">
						All Comments <span class="badge"><?php echo $comment_count; ?></span>
					</div>
					<div class="panel-body">
						<?php
							if(mysqli_num_rows($get_comments) != 0) {
								//TODO: Make comments showing into a htm.php function.
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
							if(mysqli_num_rows($get_comments) != 0) {
								echo '<list class="list-group-item"><button class="btn btn-primary">View More</button></list>';
							}
						?>
					</div>
				</div>
			<?php } ?>
		</div>
	</body>
</html>