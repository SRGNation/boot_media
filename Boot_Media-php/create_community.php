<?php 
require('connect.php');
include('htm.php');

if(!isset($_COOKIE['token_ses_data'])) {
	exit('You have to be logged in order to make a community retard.');
}

if($_SERVER['REQUEST_METHOD'] == "POST") {
	if($_COOKIE['token_ses_data'] != $_POST['csrftoken']) {
		$err = 'CSRF Check failed.';
	}

	if(empty($_POST['community_name'])) {
		$err = 'Your community name can\'t be empty.';
	}

	if(strlen($_POST['community_name']) > 64) {
		$err = 'Community name is too long.';
	}

	if(strlen($_POST['community_desc']) > 2000) {
		$err = 'Community description is too long.';
	}

	if(!empty($_POST['community_icon']) && !checkRemoteFile($_POST['community_icon'])) {
		$err = 'Your community icon is invalid.';
	}

	if(!empty($_POST['community_banner']) && !checkRemoteFile($_POST['community_banner'])) {
		$err = 'Your community banner is invalid.';
	}

	if($_POST['is_nsfw'] != 0 && $_POST['is_nsfw'] != 1) {
		$err = 'Your is nsfw setting is invalid.';
	}

	if($_POST['is_nsfw'] != 0 && $_POST['is_nsfw'] != 1) {
		$err = 'Your is nsfw setting is invalid.';
	}

	if($_POST['join_perms'] < 0 || $_POST['join_perms'] > 3) {
		$err = 'Your join perms setting is invalid.';
	}

	if($_POST['view_perms'] < 0 || $_POST['view_perms'] > 3) {
		$err = 'Your view perms setting is invalid.';
	}

	if($_POST['post_perms'] < 0 || $_POST['post_perms'] > 4) {
		$err = 'Your post perms setting is invalid.';
	}

    if(!empty($_FILES['icon']['name'])) {
        $img = $_FILES['icon'];
        $filename = $img['tmp_name'];
        $icon = uploadImage($filename);
        if($icon === null) {
            $err = 'An error occurred while uploading the image.';
        }
    }
    else
    {
        $icon = null;
    }

    if(!empty($_FILES['banner']['name'])) {
        $img = $_FILES['banner'];
        $filename = $img['tmp_name'];
        $banner = uploadImage($filename);
        if($banner === null) {
            $err = 'An error occurred while uploading the image.';
        }
    }
    else
    {
        $banner = null;
    }

    $stmt = $db->prepare('SELECT COUNT(*) FROM communities WHERE community_owner = ? AND date_created > NOW() - INTERVAL 5 MINUTE');
    $stmt->bind_param('i', $user['id']);
    $stmt->execute();
    if($stmt->error) {
        ShowError(500, 'There was an error while grabbing your recent communities.');
    }
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    if($row['COUNT(*)'] > 0) {
        $err = 'You\'re making communities too fast! Please try again in a few minutes.';
    }

	if(!isset($err)) {
		//Inserts community into the database.
        $stmt = $db->prepare("INSERT INTO communities (community_name, community_icon, community_banner, community_desc, is_nsfw, join_perms, view_perms, post_perms, community_owner) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssssiiiii', $_POST['community_name'], $icon, $banner, $_POST['community_desc'], $_POST['is_nsfw'], $_POST['join_perms'], $_POST['view_perms'], $_POST['post_perms'], $user['id']);
        $stmt->execute();
        if($stmt->error) {
            ShowError(500, 'There was an error while creating your community.');
        }

		$get_community = $db->query("SELECT id, community_owner FROM communities WHERE community_owner = ".$user['id']." ORDER BY date_created DESC");
		$community = mysqli_fetch_array($get_community);
		//Inserts the community into your joined communities list.
		$db->query("INSERT INTO community_joins (community, creator) VALUES (".$community['id'].", ".$user['id'].")");
		exit("<div id=\"main-body\">redirecting...<META HTTP-EQUIV=\"refresh\" content=\"0;URL=/communities/".$community['id']."\">");
	}
}

?>
<html>
	<?php PrintHeader('Create Community'); ?>
	<body>
		<?php PrintNavBar('post'); ?>
        <div class="container">
        <div class="page-header">    
		  <h1>Create Community</h1>
        </div>
		<?php 

			if(isset($err)) {
				echo '<div class="alert alert-danger"><b>Error!</b> '.$err.'</div>';
			}

		?>
        <div class="panel panel-default">
        <div class="panel-heading">Create Community</div>
        <div class="panel-body">
		<form method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrftoken" value="<?php echo $_COOKIE['token_ses_data']; ?>">
            <div class="form-group">
            <label for="community_name">The name of your community</label>
        	<input class="form-control" type="text" name="community_name" maxlength="64" placeholder="Community name goes here.">
            </div>
            <div class="form-group">
            <label for="community_desc">The description of your Community</label>
			<textarea class="form-control" type="text" rows="4" maxlength="2000" name="community_desc" placeholder="Community description goes here."></textarea>
            </div>
            <div class="form-group">
                <label for="icon">Community Icon</label>
                <br>
                <label>
                    <span>Image
                        <span>PNG, JPEG and GIF files are allowed.</span>
                    </span>
                    <input type="file" name="icon" accept="image/*" />
                </label>
            </div>
            <div class="form-group">
                <label for="banner">Community Banner</label>
                <br>
                <label>
                    <span>Image
                        <span>PNG, JPEG and GIF files are allowed.</span>
                    </span>
                    <input type="file" name="banner" accept="image/*" />
                </label>
            </div>
            <div class="form-group">
            <label for="is_nsfw">Is your community a NSFW (Not Safe For Work) community?</label>
			<select class="form-control" name="is_nsfw">
          	    <option value="0">
                No, it isn't
                </option>
                <option value="1">
                Yes, it is
                </option>
            </select>
            </div>
            <div class="form-group">
            <label for="join_perms">Who can join your community?</label>
			<select class="form-control" name="join_perms">
          	    <option value="0">
          	    Everyone
                </option>
                <option value="1">
                Followers only
                </option>
                <option value="2">
                Friends only
                </option>
                <option value="3">
                No one, they'll have to request to join
                </option>
            </select>
            </div>
            <div class="form-group">
            <label for="view_perms">Who can view the posts on your community?</label>
			<select class="form-control" name="view_perms">
          	    <option value="0">
          	    Everyone
                </option>
                <option value="1">
                Followers only
                </option>
                <option value="2">
                Friends only
                </option>
                <option value="3">
                Community joiners only
                </option>
            </select>
            </div>
            <div class="form-group">
            <label for="post_perms">Who can post to your community?</label>
			<select class="form-control" name="post_perms">
          	    <option value="0">
          	    Everyone
                </option>
                <option value="1">
                Followers only
                </option>
                <option value="2">
                Friends only
                </option>
                <option value="3">
                Community joiners only
                </option>
                <option value="4">
                Community members only
                </option>
            </select>
            </div>
            <input class="btn btn-primary" type="submit" value="Create">
		    </form>
            </div>
        </div>
        </div>
	</body>
</html>