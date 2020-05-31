<?php
//TODO: Put all this information in a settings.php file.
//Database settings
const DB_SERVER = 'localhost';
const DB_USERNAME = 'root';
const DB_PASSWORD = '';
const DB_DATABASE = 'boot_media';

//General settings
const CONTACT_EMAIL = null;
const TIMEZONE = 'America/New_York';

// Cloudinary settings.
const CLOUDINARY_CLOUDNAME = 'reverb';
const CLOUDINARY_UPLOADPRESET = 'reverb-mobile';

//connect.php connects the user to a secret high tech database in which they can view everything Boot_Media and its users have to offer.
error_reporting(E_ALL);

function rip() {
    http_response_code(500);
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
$db = mysqli_connect(DB_SERVER,DB_USERNAME,DB_PASSWORD,DB_DATABASE);

if(!$db) {
	rip();
}

date_default_timezone_set(TIMEZONE);
$db->query('SET time_zone = "' . $db->real_escape_string(TIMEZONE) . '"');

if(isset($_COOKIE['token_ses_data'])) {
	$token_hash = hash('sha512', $_COOKIE['token_ses_data']);

	$stmt = $db->prepare("SELECT COUNT(*), user_id FROM sessions WHERE token_hash = ?");
	$stmt->bind_param('s', $token_hash);
	$stmt->execute();
	$result = $stmt->get_result();
	$row = $result->fetch_assoc();

	if($row['COUNT(*)'] !== 0) {
		$stmt = $db->prepare("SELECT id, user_name, nick_name, email_address, user_avatar, date_created, user_type, admin_level, user_bio, hide_liked_posts FROM users WHERE id = ?");
		$stmt->bind_param('i', $row['user_id']);
		$stmt->execute();
		$result = $stmt->get_result();
		$user = $result->fetch_assoc();
	} else {
		setcookie('token_ses_data', '', time() - 3600, '/');
	}
}