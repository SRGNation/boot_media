<?php 
require("connect.php");
include("htm.php");

$get_rec_commun = $db->query("SELECT id FROM communities WHERE is_recommend = 1 LIMIT 10");
$rc_count = mysqli_num_rows($get_rec_commun);

$get_pop_commun = $db->query("SELECT id FROM communities WHERE is_hidden = 0 AND is_recommend = 0 AND is_nsfw = 0 AND view_perms = 0".(isset($_COOKIE['token_ses_data']) ? " AND id NOT IN (SELECT id FROM community_joins WHERE creator = ".$user['id'].")" : "")." ORDER BY (SELECT COUNT(*) FROM posts WHERE post_community = communities.id AND is_deleted = 0) DESC, (SELECT COUNT(*) FROM community_joins WHERE community = communities.id) DESC, communities.date_created DESC LIMIT 10");
$pop_count = mysqli_num_rows($get_pop_commun);

?>
<html>
	<?php PrintHeader('Communities'); ?>
	<body>
    <?php PrintNavBar('communities'); ?>
		<div class="container">
			<div class="page-header">
				<h1>Communities</h1>
			</div>
			<?php if(isset($_COOKIE['token_ses_data'])) { ?> <a class="btn btn-primary" href="/communities/create"><span class="badge">+</span> Create community</a><br><br> <?php } ?>
			<div class="panel panel-default">
				<div class="panel-heading">
					Recommended Communities
				</div>
				<div class="panel-body">
					<?php if($rc_count == 0) {
						echo 'There are no communities yet.';
					} else {
						while($rec_com = mysqli_fetch_array($get_rec_commun)) {
							PrintCommunityList($rec_com['id']);
						}
						echo '<list class="list-group-item"><a href="/communities/recommended">View All</a></list>';
					} ?>
				</div>
			</div>
			<div class="panel panel-default">
				<div class="panel-heading">
					Popular Communities
				</div>
				<div class="panel-body">
					<?php if($pop_count == 0) {
						echo 'There are no communities yet.';
					} else {
						while($pop_com = mysqli_fetch_array($get_pop_commun)) {
							PrintCommunityList($pop_com['id']);
						}
						echo '<list class="list-group-item"><a href="/communities/popular">View All</a></list>';
					} ?>
				</div>
			</div>
		</div>
	</body>
</html>