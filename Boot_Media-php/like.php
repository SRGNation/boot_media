<?php
require('connect.php');
include('htm.php');

if(empty($_COOKIE['token_ses_data'])) {
	exit("You're not logged in.");
}

if($_POST['likeType'] != 0 && $_POST['likeType'] != 1) {
	exit("Invalid like type.");
}

if($_POST['remove'] != 0 && $_POST['remove'] != 1) {
	exit("Invalid remove type.");
}

$id = mysqli_real_escape_string($db,$_POST['post']);
$like_type = $_POST['likeType'];
$remove = $_POST['remove'];

if($like_type == 0) {
	$get_post = $db->query("SELECT id FROM posts WHERE id = $id AND is_deleted < 2");
} else {
	$get_post = $db->query("SELECT id FROM comments WHERE id = $id AND is_deleted < 2");
}

if(mysqli_num_rows($get_post) == 0) {
	exit("The post you're trying to like doesn't exist or was deleted.");
}

if($remove == 0) {
	$check_liked = $db->query("SELECT * FROM likes WHERE post_like = $id AND like_type = $like_type AND creator = ".$user['id']);
	if(mysqli_num_rows($check_liked) > 0) {
		exit("You already liked this post.");
	} 

	$db->query("INSERT INTO likes (creator, post_like, like_type) VALUES (".$user['id'].", $id, $like_type)");
	echo 'success';
} else {
	$check_liked = $db->query("SELECT * FROM likes WHERE post_like = $id AND like_type = $like_type AND creator = ".$user['id']);
	if(mysqli_num_rows($check_liked) == 0) {
		exit("You already unliked this post.");
	} 

	$db->query("DELETE FROM likes WHERE post_like = $id AND like_type = $like_type AND creator = ".$user['id']);
	echo 'success';
}