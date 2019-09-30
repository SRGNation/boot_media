<?php
if(isset($_GET['type'])) {
	$type = $_GET['type'];
} else {
	$type = $_POST['type'];
}

if(isset($_GET['id'])) {
	$post_id = $_GET['id'];
} else {
	$post_id = $_POST['post_id'];
}

if($type != 'post') {
	include("404.php");
	exit();
}

require("connect.php");
include("htm.php");

if(!isset($_COOKIE['token_ses_data'])) {
	exit('You need to be logged in to delete something.');
}

if($type == 'post') {
	//The title of the webpage
	$title = 'Delete post';
	//Gets the post itself
	$get_post = $db->query("SELECT id, creator, post_community, is_deleted FROM posts WHERE id = ".mysqli_real_escape_string($db,$post_id)." AND is_deleted < 2");
	if(mysqli_num_rows($get_post) == 0) {
		exit('Post couldn\'t be found.');
	}
	$post = mysqli_fetch_array($get_post);
	//Gets the creator of the post.
	$get_creator = $db->query("SELECT id, user_name, nick_name, user_avatar, admin_level FROM users WHERE id = ".$post['creator']);
	$creator = mysqli_fetch_array($get_creator);
	//Gets the community the post is in.
	$get_community = $db->query("SELECT id, community_owner FROM communities WHERE id = ".$post['post_community']." AND is_hidden = 0");
	$community = mysqli_fetch_array($get_community);
	if($community['community_owner'] == $user['id']) {
		$community_admin = 1;
	} else {
		$community_admin = 0;
	}
	if($community_admin == 1 & $post['is_deleted'] == 1 & $user['admin_level'] == 0) {
		exit('This post has already been deleted.');
	}
	//Checks to see if your admin level is higher than the creator's admin level, if it's not, it will check to see if you're a community admin, and if you're not a community admin, it will check to see if you created the post, and if you did not create the post, then... Well you get the picture.
	if($user['admin_level'] <= $creator['admin_level'] & $community_admin == 0 & $user['id'] != $creator['id']) {
		exit('You don\'t have permission to delete this post.');
	}

	if($_SERVER['REQUEST_METHOD'] == "POST") {
		if(isset($_POST['delete_type']) & $user['admin_level'] == 0) {
			$err = 'You can\'t set the deletion type because you\'re not an admin.';
		}

		if($_COOKIE['token_ses_data'] != $_POST['csrftoken']) {
			$err = "Look, Arian Kordi. I know you have some sort of fetish of trying to hack into every single website I make, but please... Stop it... Please, do something better with your life. Ride a bike, go swimming, play some videogames, get a girlfriend, make another fucking Miiverse clone for all I care. Anything to fill up that empty void of yours. Just, please... Stop trying to hack into my social media websites. It's not funny, or cool. It's just annoying.";
		}

		if(!isset($err)) {
			if($post['creator'] == $user['id']) {
				$delete_type = 2;
			} else {
				$delete_type = 1;
			}

			if(!isset($_POST['delete_type']) || (isset($delete_type) & $delete_type == 2)) {
				$db->query("UPDATE posts SET is_deleted = $delete_type WHERE id = ".mysqli_real_escape_string($db,$post_id));
				$db->query("INSERT INTO audit_logs (type, target, source, community) VALUES (0, ".mysqli_real_escape_string($db,$post_id).", ".$user['id'].", ".$post['post_community'].")");
				exit("<div id=\"main-body\">redirecting...<META HTTP-EQUIV=\"refresh\" content=\"0;URL=/posts/$post_id\">");
			} else {
				if($_POST['delete_type'] == 0) {
					$db->query("UPDATE posts SET is_deleted = 3 WHERE id = ".mysqli_real_escape_string($db,$post_id));
					$db->query("INSERT INTO audit_logs (type, target, source, community) VALUES (0, ".mysqli_real_escape_string($db,$post_id).", ".$user['id'].", ".$post['post_community'].")");
					exit("<div id=\"main-body\">redirecting...<META HTTP-EQUIV=\"refresh\" content=\"0;URL=/posts/$post_id\">");
				} else {
					$db->query("DELETE FROM posts WHERE id =".mysqli_real_escape_string($db,$post_id));
					$db->query("INSERT INTO audit_logs (type, target, source, community) VALUES (1, ".mysqli_real_escape_string($db,$post_id).", ".$user['id'].", ".$post['post_community'].")");
					exit("<div id=\"main-body\">redirecting...<META HTTP-EQUIV=\"refresh\" content=\"0;URL=/posts/$post_id\">");
				}
			}
		}
	}
}

?>
<html>
	<?php PrintHeader($title); ?>
	<body>
		<?php PrintNavBar('delete'); ?>
		<div class="container">
			<?php if($type == 'post')  { ?>
				<div class="page-header">
					<h1>Delete post</h1>
					<h3><?php echo printUserAvatar($creator['id'], '40px'); ?> <?php echo htmlspecialchars($creator['nick_name']); ?>'s Post</h3>
				</div>
				<form method="post" action="/delete.php">
				<?php 
					if(isset($err)) {
						echo '<div class="alert alert-danger"><b>Error!</b> '.$err.'</div>';
					}
				?>
				<div class="alert alert-danger">Are you sure you want to delete this post?</div>
            	<div class="form-group">
            	<input type="hidden" value="post" name="type">
            	<input type="hidden" value="<?=$post_id?>" name="post_id">
            	<input type="hidden" value="<?=$_COOKIE['token_ses_data']?>" name="csrftoken">
            	<?php if($user['admin_level'] > 0 & $user['id'] != $creator['id']) { ?>
            	<label for="delete_type">What type of post deletion do you want?</label>
				<select class="form-control" name="delete_type">
          		    <option value="0">
            	    	Soft delete (Will stay on the database.)
            	    </option>
            	    <option value="1">
            	    	Permanent delete (Will get deleted off the database.)
            	    </option>
        	    </select>
        		<?php } ?>
            </div>
				<input class="btn btn-danger" type="submit" value="Delete"> <a class="btn btn-primary" href="/posts/<?=$_GET['id']?>">Cancel</a>
				</form>
			<?php } ?>
		</div>
	</body>
</html>