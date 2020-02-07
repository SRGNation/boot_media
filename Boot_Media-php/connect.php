<?php

//connect.php connects the user to a secret high tech database in which they can view everything Boot_Media and its users have to offer.
error_reporting(0);

function rip() {
	echo '
	<html>
	<head>
	<title>Boot_Media</title>
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <link rel="shortcut icon" href="/img/icon.png">
    <script src="/js/jquery-3.2.1.min.js"></script>
    <script src="/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="/js/bootmedia_js.js"></script>
	</head>
	<body>
		<div class="container">
			<h1>Database Error</h1>
			<p>We\'re sorry gamers, but we weren\'t able to connect to the database of Boot_Media as of now. Looks like your memes, cat pictures, and pictures of your latest Minecraft builds will have to wait until another time :(</p>
		</div>
	</body>
</html>
	';
	exit();
}

define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_DATABASE', 'boot_media');
$db = mysqli_connect(DB_SERVER,DB_USERNAME,DB_PASSWORD,DB_DATABASE);

if(!$db) {
	rip();
}

if(isset($_COOKIE['token_ses_data'])) {
	$token_hash = hash('sha512', $_COOKIE['token_ses_data']);
	$check_tok_exists = $db->query("SELECT * FROM sessions WHERE token_hash = '$token_hash'");
	$chk_exists = mysqli_num_rows($check_tok_exists);

	if($chk_exists != 0) {
		$ses_data = mysqli_fetch_array($check_tok_exists);
		$get_user_data = $db->query("SELECT * FROM users WHERE id = ".$ses_data['user_id']);
		$user = mysqli_fetch_array($get_user_data);
	} else {
		setcookie('token_ses_data', '', time() - 3600, '/');
	}
}

$db->query('SET time_zone = America/New_York');
date_default_timezone_set('America/New_York');