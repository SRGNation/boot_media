<?php
require('connect.php');
include('htm.php');

if(empty($_COOKIE['token_ses_data'])) {
	exit("You're not logged in.");
}

$id = mysqli_real_escape_string($db,$_POST['community']);

$communities = $db->query("SELECT id, join_perms, community_owner FROM communities WHERE id = $id AND is_hidden = 0");
$community = mysqli_fetch_array($communities);

if(mysqli_num_rows($communities) == 0) {
	exit("This community doesn't exist or was hidden.");
}

if($community['join_perms'] == 1) {
	$check_following = $db->query("SELECT id FROM follows WHERE creator = ".$user['id']." AND target = ".$community['community_owner']);
	if(mysqli_num_rows($check_following) == 0) {
		exit("You don't have permission to join this community.");
	}
}

if($community['join_perms'] == 2) {
	exit("You don't have permission to join this community.");
}

if($community['join_perms'] == 3) {
	exit("You don't have permission to join this community.");
}

$check_joined = $db->query("SELECT id FROM community_joins WHERE creator = ".$user['id']." AND community = ".$community['id']);
if(mysqli_num_rows($check_joined) > 0) {
	exit("You already joined this community.");
}

$db->query("INSERT INTO community_joins (community, creator) VALUES (".$community['id'].", ".$user['id'].")");
exit("success");