<?php 
require('connect.php');
include('htm.php');

if(isset($_GET['id'])) {
	$post_id = mysqli_real_escape_string($db,$_GET['id']);
}

if(!isset($_COOKIE['token_ses_data'])) {
	exit('You have to be logged in order to make a comment retard.');
}

if(isset($_GET['id'])) {
	$post = $db->query("SELECT creator, is_deleted FROM posts WHERE id = $post_id");
	$row = mysqli_fetch_array($post);

	if(mysqli_num_rows($post) == 0 || $row['is_deleted'] > 1) {
		exit('This post doesn\'t exist.');
	}
}

$get_creator = $db->query("SELECT id, nick_name, user_avatar, user_name FROM users WHERE id = ".$row['creator']);
$creator = mysqli_fetch_array($get_creator);

if($_SERVER['REQUEST_METHOD'] == "POST") {
	if($_COOKIE['token_ses_data'] != $_POST['csrftoken']) {
		$err = "Look, Arian Kordi. I know you have some sort of fetish of trying to hack into every single website I make, but please... Stop it... Please, do something better with your life. Ride a bike, go swimming, play some videogames, get a girlfriend, make another fucking Miiverse clone for all I care. Anything to fill up that empty void of yours. Just, please... Stop trying to hack into my social media websites. It's not funny, or cool. It's just annoying.";
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

	if(!isset($err)) {
		$db->query("INSERT INTO comments (comment_body, comment_post, comment_type, comment_image, creator) VALUES ('".mysqli_real_escape_string($db,$_POST['content'])."', ".mysqli_real_escape_string($db,$_POST['postid']).", ".mysqli_real_escape_string($db,$_POST['comment_type']).", '".mysqli_real_escape_string($db,$_POST['screenshot'])."', ".$user['id'].")");

		exit("<div id=\"main-body\">redirecting...<META HTTP-EQUIV=\"refresh\" content=\"0;URL=/posts/".$_POST['postid']."\">");
	} else {
		$post_id = mysqli_real_escape_string($db,$_POST['postid']);

		$post = $db->query("SELECT * FROM post WHERE id = $post_id");
		$row = mysqli_fetch_array($post);

		if(mysqli_num_rows($post) == 0 || $row['is_deleted'] > 1) {
			exit('This post doesn\'t exist.');
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
            <input type="hidden" name="csrftoken" value="<?php echo $_COOKIE['token_ses_data']; ?>">
            <input type="hidden" name="postid" value="<?php echo $post_id; ?>">
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