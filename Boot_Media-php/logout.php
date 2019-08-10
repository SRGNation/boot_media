<?php
require("connect.php");

if(!isset($_GET['token'])) {
	exit('Please specify your token.');
}

$token_hash = hash('sha512', $_GET['token']);
$check_tok_exists = $db->query("SELECT * FROM sessions WHERE token_hash = '$token_hash'");
$chk_exists = mysqli_num_rows($check_tok_exists);

if($chk_exists != 0) {
	setcookie('token_ses_data', '', time() - 3600, '/');
	$db->query("DELETE FROM sessions WHERE token_hash = '$token_hash'");
	exit('<div id="main-body">redirecting...<META HTTP-EQUIV="refresh" content="0;URL=/">');
} else {
	exit('Your token is invalid.');
}

?>