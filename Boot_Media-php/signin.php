<?php
require("connect.php");
include("htm.php");

if($_SERVER["REQUEST_METHOD"] == 'POST') {

	if(isset($_COOKIE['token_ses_data'])) {
		exit("You're already logged in retard.");
	}

    $check_u = $db->query("SELECT * FROM users WHERE user_name = '".mysqli_real_escape_string($db,$_POST['user_name'])."'");
    $thing_exist = mysqli_num_rows($check_u);

    if(empty($_POST['user_name'])) {
    	$err = 'Username can\'t be empty.';
    }

    if(empty($_POST['password'])) {
    	$err = 'Password can\'t be empty.';
    }

    if($thing_exist == 0) {
    	$err = 'That user doesn\'t exist.';
    }

    if($thing_exist != 0) {
		$find_password = $db->query("SELECT user_pass FROM users WHERE user_name = '".mysqli_real_escape_string($db,$_POST['user_name'])."'");
		$user_pass = mysqli_fetch_array($find_password);

		if(!password_verify($_POST['password'],$user_pass['user_pass'])) {
			$err = 'The password is incorrect.';
		}
	}

	if(!isset($err)) {
		$get_user_id = $db->query("SELECT * FROM users WHERE user_name = '".mysqli_real_escape_string($db,$_POST['user_name'])."'");
		$user = mysqli_fetch_array($get_user_id);

		#This allows you to generate a string with random characters, then hashes that string to store it in the database.
		#This code is not mine (Well most of it isn't).
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randstring = '';
        for ($i = 0; $i < 60; $i++) {
            $randstring .= $characters[rand(0, $charactersLength - 1)];
        }
            
        $token = $randstring;
        $token_hash = hash('sha512', $token);

        $db->query("INSERT INTO sessions (token_hash, user_id, ip) VALUES ('$token_hash', ".$user['id'].", '".$_SERVER['REMOTE_ADDR']."')");
        $db->query("UPDATE users SET user_login_ip = '".$_SERVER['REMOTE_ADDR']."' WHERE id = ".$user['id']);
        setcookie('token_ses_data', $token, time() + (86400 * 364), '/');
		exit("<div id=\"main-body\">redirecting...<META HTTP-EQUIV=\"refresh\" content=\"0;URL=/\">");

	}

}

?>
<html>
	<?php PrintHeader('Sign In'); ?>
	<body>
	    <?php PrintNavBar('signin'); ?>
		<div class="container">
			<div class="page-header">
				<h1>Sign In</h1>
				<p>Sign in to an account to make posts, join communities, message people, and much, much more. If you don't have an account already, then you can <a href="/signup.php">create one</a>.</p>
			</div>
			<form method="post">
				<?php 

				if(isset($err)) {
					echo '<div class="alert alert-danger"><b>Error!</b> '.$err.'</div>';
				}

				?>
				<div class="form-group">
					<label for="user_name">Username</label>
					<input class="form-control" type="text" name="user_name" maxlength="32" placeholder="Username">
				</div>
				<div class="form-group">
					<label for="password">Password</label>
					<input class="form-control" type="password" name="password" maxlength="32" placeholder="Password">
				</div>
				<input type="submit" class="btn btn-primary" value="Sign In">
			</form>
		</div>
	</body>
</html>