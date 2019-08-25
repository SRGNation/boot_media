<?php
require("connect.php");

$get_session = $db->query("SELECT id, user_id, website, token_hash FROM sessions WHERE token_hash = '".mysqli_real_escape_string($db,$_GET['token'])."'");
$token = mysqli_fetch_array($get_session);

if(mysqli_num_rows($get_session) == 0) {
	echo json_encode(array('success' => 0), JSON_FORCE_OBJECT);
	exit();
}

$get_user = $db->query("SELECT id, user_name, nick_name, email_address FROM users WHERE id = ".$token['user_id']);
$user = mysqli_fetch_array($get_user);

if(mysqli_num_rows($get_user) == 0) {
	echo json_encode(array('success' => 0), JSON_FORCE_OBJECT);
	exit();
}

echo json_encode(array('success' => 1, 'user' => array('id' => $user['id'], 'user_name' => $user['user_name'], 'nick_name' => $user['nick_name'], 'email_address' => $user['email_address'])), JSON_FORCE_OBJECT);