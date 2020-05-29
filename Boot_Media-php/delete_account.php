<?php
require("connect.php");
include("htm.php");

if(isset($_GET['id'])) {
	$user_id = $_GET['id'];
} else {
	$user_id = $_POST['user_id'];
}

$stmt = $db->prepare("SELECT id, user_name, nick_name, user_avatar, admin_level FROM users WHERE user_name = ?");
$stmt->bind_param('s', $user_id);
$stmt->execute();
if($stmt->error) {
	ShowError(500, 'There was an error while trying to get the user.');
}
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if(!isset($_COOKIE['token_ses_data'])) {
	ShowError(403, 'You need to be logged in to delete an account.');
}

if($user['id'] != $row['id'] & $row['admin_level'] >= $user['admin_level']) {
	ShowError(403, 'You don\'t have permission to delete this account.');
}

if($_SERVER['REQUEST_METHOD'] == "POST") {
	$db->query("DELETE FROM users WHERE user_name = '".$db->real_escape_string($user_id)."'");
	$db->query("DELETE FROM posts WHERE creator = '".$db->real_escape_string($row['id'])."'");
	$db->query("DELETE FROM comments WHERE creator = '".$db->real_escape_string($row['id'])."'");
	$db->query("DELETE FROM sessions WHERE user_id = '".$db->real_escape_string($row['id'])."'");
	$db->query("DELETE FROM communities WHERE community_owner = '".$db->real_escape_string($row['id'])."'");
	exit("<div id=\"main-body\">redirecting...<META HTTP-EQUIV=\"refresh\" content=\"0;URL=/users/$user_id\">");
}

?>
<html>
	<?php PrintHeader('Delete User'); ?>
	<body>
		<?php PrintNavBar('manage'); ?>
		<div class="container">
				<div class="page-header">
					<h3><?=printUserAvatar($row['id'], '40px')?> Delete <?=htmlspecialchars($row['nick_name'])?></h3>
				</div>
				<form method="post" action="/delete_account.php">
					<div class="alert alert-danger">Are you sure you want to delete this account?</div>
            		<input type="hidden" value="<?=$user_id?>" name="user_id">
            		<input type="hidden" value="<?=$_COOKIE['token_ses_data']?>" name="csrftoken">
					<input class="btn btn-danger" type="submit" value="Delete"> <a class="btn btn-primary" href="/users/<?=$user_id?>">Cancel</a>
				</form>
		</div>
	</body>
</html>