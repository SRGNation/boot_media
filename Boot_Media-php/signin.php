<?php
require("connect.php");
include("htm.php");

if(isset($_GET['website'])) {
	$website = mysqli_real_escape_string($db,$_GET['website']);
	$get_website = $db->query("SELECT id, title, name, url, status FROM websites WHERE title = '$website'");
	$websites = mysqli_fetch_array($get_website);

	if(mysqli_num_rows($get_website) == 0) {
		exit("That website doesn't exist.");
	}

	if(isset($_COOKIE['token_ses_data'])) {
        $find_token = $db->query("SELECT id, token_hash, website FROM sessions WHERE user_id = ".$user['id']." AND website = '".mysqli_real_escape_string($db,$_GET['website'])."'");
        $row = mysqli_fetch_array($find_token);
        if(mysqli_num_rows($find_token) != 0) {
        	exit("<div id=\"main-body\">redirecting...<META HTTP-EQUIV=\"refresh\" content=\"0;URL=".$websites['url']."".$row['token_hash']."\">");
        } else {
			$token = generateRandomString(60);
        	$db->query("INSERT INTO sessions (user_id, token_hash, website) VALUES (".$user['id'].", '$token', '$website')");
        	exit("<div id=\"main-body\">redirecting...<META HTTP-EQUIV=\"refresh\" content=\"0;URL=".$websites['url']."".$token."\">");
        }
	}
}

if($_SERVER["REQUEST_METHOD"] == 'POST') {
	if(isset($_POST['website'])) {
		$website = mysqli_real_escape_string($db,$_POST['website']);
	}

	if(isset($_COOKIE['token_ses_data'])) {
		$err = 'You\'re already logged in retard.';
	}

    $check_u = $db->query("SELECT id FROM users WHERE user_name = '".mysqli_real_escape_string($db,$_POST['user_name'])."'");
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

    if(isset($_POST['redirect'])) {
    	$redirect = $_POST['redirect'];
    }

    if($thing_exist != 0) {
		$find_password = $db->query("SELECT user_pass FROM users WHERE user_name = '".mysqli_real_escape_string($db,$_POST['user_name'])."'");
		$user_pass = mysqli_fetch_array($find_password);

		if(!password_verify($_POST['password'],$user_pass['user_pass'])) {
			$err = 'The password is incorrect.';
		}
	}

	if(!isset($err)) {
		$get_user_id = $db->query("SELECT id FROM users WHERE user_name = '".mysqli_real_escape_string($db,$_POST['user_name'])."'");
		$user = mysqli_fetch_array($get_user_id);

		$token = generateRandomString(60);
        $token_hash = hash('sha512', $token);

        $db->query("INSERT INTO sessions (token_hash, user_id, ip) VALUES ('$token_hash', ".$user['id'].", '".$_SERVER['REMOTE_ADDR']."')");
        $db->query("UPDATE users SET user_login_ip = '".$_SERVER['REMOTE_ADDR']."' WHERE id = ".$user['id']);
        setcookie('token_ses_data', $token, time() + (86400 * 364), '/');

        /*if(isset($website)) {
			$get_website = $db->query("SELECT id, title, name, url, status FROM websites WHERE title = '$website'");
			$websites = mysqli_fetch_array($get_website);

			if(mysqli_num_rows($get_website) == 0) {
				exit("That website doesn't exist.");
			}

        	$find_token = $db->query("SELECT id, token_hash, website FROM sessions WHERE user_id = ".$user['id']." AND website = '".mysqli_real_escape_string($db,$_POST['website'])."'");
        	$row = mysqli_fetch_array($find_token);
        	if(mysqli_num_rows($find_token) != 0) {
        		exit("<div id=\"main-body\">redirecting...<META HTTP-EQUIV=\"refresh\" content=\"0;URL=".$websites['url']."".$row['token_hash']."\">");
        	} else {
				$token = generateRandomString(60);
        		$db->query("INSERT INTO sessions (user_id, token_hash, website) VALUES (".$user['id'].", '$token', '".mysqli_real_escape_string($db,$_POST['website'])."')");
        		exit("<div id=\"main-body\">redirecting...<META HTTP-EQUIV=\"refresh\" content=\"0;URL=".$websites['url']."".$token."\">");
        	}
        }*/

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
				<p>Sign in to an account to make posts, join communities, message people, and much, much more. If you don't have an account already, then you can <a href="/register">create one</a>.</p>
			</div>
			<form method="post" action="/login">
				<?php 

				if(isset($err)) {
					echo '<div class="alert alert-danger"><b>Error!</b> '.$err.'</div>';
				}

				if(isset($website)) { 
					echo '<input type="hidden" name="website" value="'.$website.'">';
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