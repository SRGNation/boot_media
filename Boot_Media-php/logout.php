<?php
require("connect.php");
include("htm.php");

if(!isset($_GET['token'])) {
	ShowError(400, 'You must specify a token.');
}

$token_hash = hash('sha512', $_GET['token']);

$stmt = $db->prepare("SELECT COUNT(*) FROM sessions WHERE token_hash = ?");
$stmt->bind_param('s', $token_hash);
$stmt->execute();
if($stmt->error) {
	ShowError(500, 'An error occured while trying to log out.');
}
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if($row['COUNT(*)'] !== 0) {
	setcookie('token_ses_data', '', time() - 3600, '/');
	$db->query("DELETE FROM sessions WHERE token_hash = '".mysqli_real_escape_string($db,$token_hash)."'");
	exit('<div id="main-body">redirecting...<META HTTP-EQUIV="refresh" content="0;URL=/">');
} else {
	ShowError(404, 'That token could not be found.');
}

?>