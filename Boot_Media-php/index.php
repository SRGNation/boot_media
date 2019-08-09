<?php 
require("connect.php");
include("htm.php");

$get_rec_commun = $db->query("SELECT * FROM communities WHERE is_recommend = 1 LIMIT 4");
$rc_count = mysqli_num_rows($get_rec_commun);

?>
<html>
	<?php PrintHeader('Main'); ?>
	<body>
      <?php PrintNavBar('home'); ?>
		<div class="container">
			<div class="page-header">
				<h1>Boot_Media</h1>
			</div>
			<?php 
			if(!isset($_COOKIE['token_ses_data'])) {
			?>
			<p>Boot_Media is the first website in the world that isn't a fucking Miiverse clone.</p>
			<p><strong>Pros:</strong> It is not a Miiverse clone.<br><strong>Cons:</strong> There are none. This is the best website in the fucking universe simply because it isn't a Miiverse clone.</p>
			<p>Why not join the fun? <a class="btn btn-primary" href="signup.php">Create an Account</a> or <a class="btn btn-primary" href="/login">Login</a> if you already have one.</p> <?php } else {?>
			<p>Welcome to Boot_Media, <?php echo $user['nick_name']; ?>!
			<?php } ?>
			<div class="panel panel-default">
				<div class="panel-heading">
					Recommended Communities
				</div>
				<div class="panel-body">
					<?php if($rc_count == 0) {
						echo 'There are no communities yet.';
					} else {
						while($rec_com = mysqli_fetch_array($get_rec_commun)) {
							echo '<list class="list-group-item"><img src="'.htmlspecialchars($rec_com['community_icon']).'" style="width:50px;height:50px;" class="img-rounded"> <a href="/communities/'.$rec_com['id'].'">'.htmlspecialchars($rec_com['community_name']).'</a></list>';
						}
						echo '<list class="list-group-item"><a href="/communities/recommended">View More</a></list>';
					} ?>
				</div>
			</div>
		</div>
	</body>
</html>