<?php
require("connect.php");
include("htm.php");

if($_SERVER["REQUEST_METHOD"] == 'POST') {
	if(empty($_POST['user_name'])) {
		$err = 'Username is required.';
	}

	if(empty($_POST['nick_name'])) {
		$_POST['nick_name'] = $_POST['user_name'];
	}

	if(empty($_POST['password'])) {
		$err = 'Password is required.';
	}

	if(strlen($_POST['user_name']) > 32) {
		$err = 'Username is too long.';
	}

    if(!preg_match('/^[a-zA-Z0-9_-]+$/', $_POST['user_name'])) {
        $err = 'Your username can only contain letters, numbers, dashes, and underscores.';
    }

	if(strlen($_POST['email']) > 200) {
		$err = 'Email address is too long.';
	}

	if(strlen($_POST['nick_name']) > 32) {
		$err = 'Nickname is too long.';
	}

	if(strlen($_POST['avatar_url']) > 200) {
		$err = 'Avatar URL is too long.';
	}

	if(strlen($_POST['password']) > 32) {
		$err = 'Password is too long.';
	}

	if(strlen($_POST['password_rt']) > 32) {
		$err = 'Password retype is too long.';
	}

    if($_POST['password'] !== $_POST['password_rt']) {
        $err = 'Passwords don\'t match';
    }

    if(!empty($_POST['avatar_url']) && !checkRemoteFile($_POST['avatar_url'])) {
    	$err = 'Avatar URL is invalid';
    } 

    $check_u = $db->query("SELECT * FROM users WHERE user_name = '".mysqli_real_escape_string($db,$_POST['user_name'])."'");
    $thing_exist = mysqli_num_rows($check_u);

    if($thing_exist > 0) {
    	$err = 'That username already exists.';
    }

    if(!isset($err)) {
        $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
    	$db->query("INSERT INTO users (user_name, user_pass, email_address, user_avatar, nick_name) VALUES ('".mysqli_real_escape_string($db,$_POST['user_name'])."', '$password_hash', '".mysqli_real_escape_string($db,$_POST['email'])."', '".mysqli_real_escape_string($db,$_POST['avatar_url'])."', '".mysqli_real_escape_string($db,$_POST['nick_name'])."')");
    	?>
    	<html>
			<?php PrintHeader('Main'); ?>
		<body>
     		 <?php PrintNavBar('home'); ?>
			<div class="container">
			<p>Good job! You created an account! You can now <a class="btn btn-primary" href="/login">Login</a> to it to enjoy everything Boot_Media has to offer!</p>
			</body>
		</html>
    	<?php
    	exit();
    }
}

?>
<html>
	<?php PrintHeader('Sign Up'); ?>
	<body>
	    <?php PrintNavBar('signin'); ?>
		<div class="container">
			<div class="page-header">
				<h1>Sign Up</h1>
				<p>With an account, you can make posts, join communities, message people, and much, much more. So why not join in on the fun by creating one?</p>
			</div>
			<form method="post">
				<?php 

				if(isset($err)) {
					echo '<div class="alert alert-danger"><b>Error!</b> '.$err.'</div>';
				}

				?>
				<div class="form-group">
					<label for="user_name">Username (Required)</label>
					<p>This is used for identifying who you are. It will be used when searching, and it will be used as a url link to your profile. Reminder: Usernames cannot be changed, so when you pick a username, make sure it's the one you really want.</p>
					<input class="form-control" type="text" name="user_name" maxlength="32" placeholder="Username">
				</div>
				<div class="form-group">
					<label for="email">Email</label>
					<p>This will be used for if you forgot your password and you want to reset it, and will also be used to get your Gravatar if you have one. This won't show on your user profile.</p>
					<input class="form-control" type="text" name="email" maxlength="200" placeholder="something@website.com">
				</div>
				<div class="form-group">
					<label for="nick_name">Nickname</label>
					<p>If you decide to leave this blank, your nickname will just be your Username.</p>
					<input class="form-control" type="text" name="nick_name" maxlength="32" placeholder="Nickname">
				</div>
				<div class="form-group">
					<label for="avatar_url">Avatar Image URL</label>
					<p>If you're not sure how image URL's work, click <a href="/posts.php?id=bruh">here</a> for a guide on how to set your Avatar Image URL.</p>
					<input class="form-control" type="text" name="avatar_url" maxlength="200" placeholder="Avatar Image URL">
				</div>
				<div class="form-group">
					<label for="password">Password (Required)</label>
					<input class="form-control" type="password" name="password" maxlength="32" placeholder="Password">
				</div>
				<div class="form-group">
					<label for="password_rt">Password Retype (Required)</label>
					<input class="form-control" type="password" name="password_rt" maxlength="32" placeholder="Password Retype">
				</div>
				<input type="submit" class="btn btn-primary" value="Sign Up">
			</form>
		</div>
	</body>
</html>