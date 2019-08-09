<?php 
require('connect.php');
include('htm.php');

if(isset($_GET['id'])) {
	$community_id = mysqli_real_escape_string($db,$_GET['id']);
}

if(!isset($_COOKIE['token_ses_data'])) {
	exit('You have to be logged in order to make a post retard.');
}

if(isset($_GET['id'])) {
	$community = $db->query("SELECT community_name, community_icon, is_hidden FROM communities WHERE id = $community_id");
	$row = mysqli_fetch_array($community);

	if(mysqli_num_rows($community) == 0 || $row['is_hidden'] == 1) {
		exit('This community doesn\'t exist.');
	}
}

if($_SERVER['REQUEST_METHOD'] == "POST") {
	if($_COOKIE['token_ses_data'] != $_POST['csrftoken']) {
		$err = "Look, Arian Kordi. I know you have some sort of fetish of trying to hack into every single website I make, but please... Stop it... Please, do something better with your life. Ride a bike, go swimming, play some videogames, get a girlfriend, make another fucking Miiverse clone for all I care. Anything to fill up that empty void of yours. Just, please... Stop trying to hack into my social media websites. It's not funny, or cool. It's just annoying.";
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

	if(!isset($err)) {
		$db->query("INSERT INTO posts (post_body, post_community, post_type, post_image, uses_html, creator) VALUES ('".mysqli_real_escape_string($db,$_POST['content'])."', ".mysqli_real_escape_string($db,$_POST['communityid']).", ".mysqli_real_escape_string($db,$_POST['post_type']).", '".mysqli_real_escape_string($db,$_POST['screenshot'])."', ".mysqli_real_escape_string($db,$_POST['use_html']).", ".$user['id'].")");

		exit("<div id=\"main-body\">redirecting...<META HTTP-EQUIV=\"refresh\" content=\"0;URL=".($_POST['communityid'] == 0 ? '/users/'.$user['user_name'].'' : '/communities/'.$_POST['communityid'])."\">");
	} else {
		if($_POST['communityid'] != 0) {
			$community_id = mysqli_real_escape_string($db,$_POST['communityid']);

			$community = $db->query("SELECT * FROM communities WHERE id = $community_id");
			$row = mysqli_fetch_array($community);

			if(mysqli_num_rows($community) == 0 || $row['is_hidden'] == 1) {
				exit('This community doesn\'t exist.');
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
		  echo '<img src="'.htmlspecialchars($row['community_icon']).'" class="img-rounded" style="width: 40px;height: 40px;"> ';
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