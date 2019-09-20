<?php
require("connect.php");
include("htm.php");

if(!isset($_COOKIE['token_ses_data'])) {
	exit("You need to be logged in to edit your account. You're not logged in, so you have no account to edit.");
}

if($user['user_avatar'] == "https://gravatar.com/avatar/".md5($user['email_address'])."?s=96")
	$using_gravatar = true;
else
	$using_gravatar = false;

if($_SERVER["REQUEST_METHOD"] == 'POST') {
	if($_POST['token'] != $_COOKIE['token_ses_data']) {
		$err = 'Your token is invalid.';
	}

	if(strlen($_POST['nick_name']) > 32) {
		$err = 'Nickname is too long.';
	}

	if(strlen($_POST['user_avatar']) > 200) {
		$err = 'Avatar URL is too long.';
	}

	if(strlen($_POST['bio']) > 2000) {
		$err = 'User Bio is too long.';
	}

	if($_POST['hide_liked_posts'] != 0 & $_POST['hide_liked_posts'] != 1) {
		$err = 'Your hide liked posts setting is invalid.';
	}

	$avatar = $_POST['user_avatar'];

	if(isset($_POST['use_gravatar'])) {
		if(empty($user['email_address'])) {
			$err = 'You need an email address to use Gravatar.';
		} else {
			$avatar = "https://gravatar.com/avatar/".md5($user['email_address'])."?s=96";
		}
	}

	if(!isset($err)) {
		$db->query("UPDATE users SET nick_name = '".mysqli_real_escape_string($db,$_POST['nick_name'])."', user_avatar = ".($using_gravatar & !isset($_POST['use_gravatar']) ? 'NULL' : '\''.mysqli_real_escape_string($db,$avatar).'\'').", user_bio = '".mysqli_real_escape_string($db,$_POST['bio'])."', hide_liked_posts = ".$_POST['hide_liked_posts']." WHERE id = ".$user['id']);

		//Updates user settings
		$get_user = $db->query("SELECT * FROM users WHERE id = ".$user['id']);
		$user = mysqli_fetch_array($get_user);
		if(isset($_POST['use_gravatar']))
			$using_gravatar = true;
		else
			$using_gravatar = false;
	}
}

?>
<html>
	<?php PrintHeader('Profile Settings'); ?>
	<body>
	    <?php PrintNavBar('settings'); ?>
	    <div class="container">
	    	<div class="page-header">
				<h1>Profile Settings</h1>
			</div>
			<ul class="nav nav-tabs">
				<li class="active"><a href="/settings/profile">Profile Settings</a></li>
				<li><a href="/settings/account">Account Settings</a></li>
				<li><a href="/settings/sessions">Manage Sessions</a></li>
			</ul>
			<br>
			<form method="post">
				<?php 

				if(isset($err)) {
					echo '<div class="alert alert-danger"><b>Error!</b> '.$err.'</div>';
				}

				?>
				<input type="hidden" name="token" value="<?=$_COOKIE['token_ses_data']?>">
				<div class="form-group">
					<label for="nick_name">Nickname</label>
					<input class="form-control" type="text" name="nick_name" placeholder="Nickname goes here." maxlength="32" value="<?=$user['nick_name']?>">
            	</div>
            	<div class="form-group">
            		<label for="bio">User Bio</label>
					<textarea class="form-control" type="text" rows="5" maxlength="2000" name="bio" placeholder="User Bio goes here."><?=$user['user_bio']?></textarea>
            	</div>
				<div class="form-group">
					<label for="user_avatar">Avatar Image URL</label>
					<input class="form-control" type="text" name="user_avatar" placeholder="User Avatar URL goes here." maxlength="200" value="<?=$user['user_avatar']?>" <?=$using_gravatar ? 'disabled' : ''?>>
					<input type="checkbox" name="use_gravatar" <?=$using_gravatar ? 'checked' : ''?>>
					<label for="use_gravatar">Use Gravatar</label>
            	</div>
            	<div class="form-group">
                <label for="hide_liked_posts">Hide your like history from your profile page?</label>
					<select class="form-control" name="hide_liked_posts">
						<option value="0" <?=$user['hide_liked_posts'] != 0 ? '' : 'selected'?>>
                			Don't hide
                		</option>
                		<option value="1" <?=$user['hide_liked_posts'] != 1 ? '' : 'selected'?>>
                			Do hide
                		</option>
        	    	</select>
            	</div>
            	<input class="btn btn-primary" type="submit" value="Save">
        	</form>
	    </div>
	</body>
</html>