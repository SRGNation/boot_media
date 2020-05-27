<?php 
require('connect.php');
include('htm.php');

if(isset($_GET['id'])) {
	$community_id = $_GET['id'];
}

if(!isset($_COOKIE['token_ses_data'])) {
	exit('You have to be logged in order to make a post retard.');
}

if(isset($_GET['id'])) {
	$stmt = $db->prepare("SELECT community_name, community_icon, is_hidden, COUNT(*) FROM communities WHERE id = ?");
	$stmt->bind_param('i', $community_id);
	$stmt->execute();
	$result = $stmt->get_result();
	$row = $result->fetch_assoc();

	if($row['COUNT(*)'] == 0 || $row['is_hidden'] == 1) {
		ShowError(404, 'This community doesn\'t exist.');
	}
}

if($_SERVER['REQUEST_METHOD'] == "POST") {
	if($_COOKIE['token_ses_data'] != $_POST['csrftoken']) {
		$err = 'CSRF Check failed.';
	}

	if(empty($_POST['content'])) {
		$err = 'Post content can\'t be empty.';
	}

	if(strlen($_POST['content']) > 2000) {
		$err = 'Post content is too long.';
	}

	if(strlen($_POST['screenshot']) > 200) {
		$err = 'Screenshot image is too long.';
	}

	if (strlen($_POST['content']) > 0 && strlen(trim($_POST['content'])) == 0) {
		$err = 'Post can\'t only contain spaces.';
	}

	if(!isset($_POST['post_type'])) {
		$err = 'You must set who should view your post.';
	}

	if($_POST['post_type'] == 3 && $_POST['communityid'] == 0) {
		$err = 'You can\'t set the post type to "Community followers only" if you\'re not posting to a community.';
	}

	if($_POST['post_type'] == 4 && $user['admin_level'] == 0) {
		$err = 'You can\'t set the post type to "Boot_Media admins only" if you\'re not an admin.';
	}

	if($_POST['use_html'] != 0 && $_POST['use_html'] != 1) {
		$err = 'Invalid HTML option.';
	}

	if($_POST['post_type'] != 0 && $_POST['post_type'] != 1 && $_POST['post_type'] != 2 && $_POST['post_type'] != 3 && $_POST['post_type'] != 4) {
		$err = 'Invalid post type.';
	}

	if(!empty($_POST['screenshot']) && !checkRemoteFile($_POST['screenshot'])) {
		$err = 'Your Screenshot is invalid.';
	}

	$stmt = $db->prepare('SELECT COUNT(*) FROM posts WHERE creator = ? AND date_time > NOW() - INTERVAL 15 SECOND');
	$stmt->bind_param('i', $user['id']);
	$stmt->execute();
	if($stmt->error) {
	    ShowError(500, 'There was an error while grabbing your recent posts.');
	}
	$result = $stmt->get_result();
	$row = $result->fetch_assoc();
	if($row['COUNT(*)'] > 0) {
	    $err = 'You\'re making posts too fast! Please try again in a few seconds.';
	}

	if(!isset($err)) {
		$stmt = $db->prepare("INSERT INTO posts (post_body, post_community, post_type, post_image, uses_html, creator) VALUES (?, ?, ?, ?, ?, ?)");
		$stmt->bind_param('siisii', $_POST['content'], $_POST['communityid'], $_POST['post_type'], $_POST['screenshot'], $_POST['use_html'], $user['id']);
		$stmt->execute();
		if($stmt->error) {
	    	ShowError(500, 'There was an error while posting to the database.');
		}

		exit("<div id=\"main-body\">redirecting...<META HTTP-EQUIV=\"refresh\" content=\"0;URL=".($_POST['communityid'] == 0 ? '/users/'.$user['user_name'].'' : '/communities/'.$_POST['communityid'])."\">");
	} else {
		if($_POST['communityid'] != 0) {
			$community_id = $_POST['communityid'];

			$stmt = $db->prepare("SELECT community_name, community_icon, is_hidden, COUNT(*) FROM communities WHERE id = ?");
			$stmt->bind_param('i', $community_id);
			$stmt->execute();
			$result = $stmt->get_result();
			$row = $result->fetch_assoc();

			if($row['COUNT(*)'] == 0 || $row['is_hidden'] == 1) {
				ShowError(404, 'This community doesn\'t exist.');
			}
		}
	}

}

?>
<html>
	<?php PrintHeader('Create Post'); ?>
	<body>
		<?php PrintNavBar('post'); ?>
        <div class="container">
        <div class="page-header">    
		  <h1>Create Post</h1>
		  <?php if(isset($community_id)) { ?><h3><?php 
		  echo '<img src="'.(empty($row['community_icon']) ? '/img/communityEmpty.png' : htmlspecialchars($row['community_icon'])).'" class="img-rounded" style="width: 40px;height: 40px;"> ';
		  echo htmlspecialchars($row['community_name']); ?><?php } ?></h3>
        </div>
		<?php 

			if(isset($err)) {
				echo '<div class="alert alert-danger"><b>Error!</b> '.$err.'</div>';
			}

		?>
        <div class="panel panel-default">
        <div class="panel-heading">Create Post</div>
        <div class="panel-body">
		<form action="/create_post.php" method="post">
            <input type="hidden" name="csrftoken" value="<?php echo $_COOKIE['token_ses_data']; ?>">
            <input type="hidden" name="communityid" value="<?php if(isset($community_id)) { echo $community_id; } else { echo '0'; } ?>">
            <div class="form-group">
            <label for="content">Post Content</label>
			<textarea class="form-control" type="text" rows="4" maxlength="2000" name="content" placeholder="Post Content goes here."></textarea>
            </div>
            <div class="form-group">
            <label for="post_type">Who can see this post?</label>
			<select class="form-control" name="post_type">
          	    <option value="0">
                Everyone
                </option>
                <option value="1">
                Friends only
                </option>
                <option value="2">
                Followers only
                </option>
                <?php if(isset($_GET['id'])) { ?> <option value="3">
                Community followers only
                </option> <?php } if($user['admin_level'] > 0) { ?>
                <option value="4">
                Boot_Media Admins only
                </option> <?php } ?>
        	    </select>
            </div>
            <div class="form-group">
            <label for="use_html">Do you want this post to use HTML?</label>
			<select class="form-control" name="use_html">
          	    <option value="0">
          	    Don't use HTML
                </option>
                <option value="1">
                Use HTML
                </option>
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