<?php 
require('connect.php');
include('htm.php');

if(isset($_GET['id'])) {
	$post_id = $_GET['id'];
}

if(!isset($_COOKIE['token_ses_data'])) {
	exit('You have to be logged in order to make a comment retard.');
}

if(isset($_GET['id'])) {
	$stmt->prepare("SELECT creator, is_deleted, COUNT(*) FROM posts WHERE id = ?");
	$stmt->bind_param('i', $post_id);
	$stmt->execute();
	$result = $stmt->get_result();
	$row = $result->fetch_assoc();

	if($row['COUNT(*)'] == 0 || $row['is_deleted'] > 1) {
		ShowError(404, 'This post doesn\'t exist.');
	}
}

//TODO: Store this information in the row variable also.
$stmt->prepare("SELECT id, nick_name, user_avatar, user_name FROM users WHERE id = ?");
$stmt->bind_param('i', $row['creator']);
$stmt->execute();
$result = $stmt->get_result();
$creator = $result->fetch_assoc();

if($_SERVER['REQUEST_METHOD'] == "POST") {
	if($_COOKIE['token_ses_data'] != $_POST['csrftoken']) {
		$err = 'CSRF Check failed.';
	}

	if(empty($_POST['content'])) {
		$err = 'Comment content can\'t be empty.';
	}

	if(strlen($_POST['content']) > 2000) {
		$err = 'Comment content is too long.';
	}

	if(strlen($_POST['screenshot']) > 200) {
		$err = 'Screenshot image is too long.';
	}

	if (strlen($_POST['content']) > 0 && strlen(trim($_POST['content'])) == 0) {
		$err = 'Comment can\'t only contain spaces.';
	}

	if(!isset($_POST['comment_type'])) {
		$err = 'You must set who should view your comment.';
	}

	if($_POST['comment_type'] == 3 && $user['admin_level'] == 0) {
		$err = 'You can\'t set the comment type to "Boot_Media admins only" if you\'re not an admin.';
	}

	if($_POST['comment_type'] != 0 && $_POST['comment_type'] != 1 && $_POST['comment_type'] != 2 && $_POST['comment_type'] != 3) {
		$err = 'Invalid comment type.';
	}

	if(!empty($_POST['screenshot']) && !checkRemoteFile($_POST['screenshot'])) {
		$err = 'Your Screenshot is invalid.';
	}

	$stmt = $db->prepare('SELECT COUNT(*) FROM comments WHERE creator = ? AND date_time > NOW() - INTERVAL 15 SECOND');
	$stmt->bind_param('i', $user['id']);
	$stmt->execute();
	if($stmt->error) {
	    ShowError(500, 'There was an error while grabbing your recent comments.');
	}
	$result = $stmt->get_result();
	$row = $result->fetch_assoc();
	if($row['COUNT(*)'] > 0) {
	    $err = 'You\'re making comments too fast! Please try again in a few seconds.';
	}

	if(!isset($err)) {
		$stmt->prepare("INSERT INTO comments (comment_body, comment_post, comment_type, comment_image, creator) VALUES (?, ?, ?, ?, ?)");
		$stmt->bind_param('siisi', $_POST['content'], $_POST['postid'], $_POST['comment_type'], $_POST['screenshot'], $user['id']);
		$stmt->execute();
		if($stmt->error) {
	    	ShowError(500, 'There was an error while posting to the database.');
		}

		exit("<div id=\"main-body\">redirecting...<META HTTP-EQUIV=\"refresh\" content=\"0;URL=/posts/".$_POST['postid']."\">");
	} else {
		$post_id = $_POST['postid'];

		$stmt->prepare("SELECT creator, is_deleted, COUNT(*) FROM posts WHERE id = ?");
		$stmt->bind_param('i', $post_id);
		$stmt->execute();
		$result = $stmt->get_result();
		$row = $result->fetch_assoc();

		$stmt->prepare("SELECT id, nick_name, user_avatar, user_name FROM users WHERE id = ?");
		$stmt->bind_param('i', $row['creator']);
		$stmt->execute();
		$result = $stmt->get_result();
		$creator = $result->fetch_assoc();

		if($row['COUNT(*)'] == 0 || $row['is_deleted'] > 1) {
			ShowError(404, 'This post doesn\'t exist.');
		}
	}

}

?>
<html>
	<?php PrintHeader('Create Comment'); ?>
	<body>
		<?php PrintNavBar('post'); ?>
        <div class="container">
        <div class="page-header">    
		  <h1>Create Comment</h1>
		  <h3><?php echo printUserAvatar($creator['id'], '40px'); ?> <?php echo htmlspecialchars($creator['nick_name']); ?>'s Post</h3>
        </div>
		<?php 

			if(isset($err)) {
				echo '<div class="alert alert-danger"><b>Error!</b> '.$err.'</div>';
			}

		?>
        <div class="panel panel-default">
        <div class="panel-heading">Create Comment</div>
        <div class="panel-body">
		<form action="/create_comment.php" method="post">
            <input type="hidden" name="csrftoken" value="<?=$_COOKIE['token_ses_data']?>">
            <input type="hidden" name="postid" value="<?=$post_id?>">
            <div class="form-group">
            <label for="content">Comment Content</label>
			<textarea class="form-control" type="text" rows="4" maxlength="2000" name="content" placeholder="Comment Content goes here."></textarea>
            </div>
            <div class="form-group">
            <label for="comment_type">Who can see this comment?</label>
			<select class="form-control" name="comment_type">
          	    <option value="0">
                Everyone
                </option>
                <option value="1">
                Friends only
                </option>
                <option value="2">
                Followers only
                </option>
				<?php if($user['admin_level'] > 0) { ?>
                <option value="3">
                Boot_Media Admins only
                </option> <?php } ?>
        	    </select>
            </div>
            <div class="form-group">
            <label for="screenshot">Screenshot</label>
        	<input class="form-control" type="text" name="screenshot" placeholder="Screenshot goes here.">
            </div>
            <input class="btn btn-primary" type="submit" value="Create">
		    </form>
            </div>
        </div>
        </div>
	</body>
</html>