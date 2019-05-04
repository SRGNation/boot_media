<?php
function PrintNavBar($page) {
      global $user;

			echo '<nav class="navbar navbar-inverse">
				<div class="container-fluid">
    				<div class="navbar-header">
      					<a class="navbar-brand" href="/">Boot_Media</a>
    				</div>
    		<ul class="nav navbar-nav">
      		<li><a href="list_communities.php">Communities</a></li>
      		<li><a href="trending.php">Trending</a></li>
    		</ul>';

        if($page != 'signin') {
          if(!isset($_COOKIE['token_ses_data'])) {
    		  echo '<ul class="nav navbar-nav navbar-right">
    	       <li><a href="signup.php"><span class="glyphicon glyphicon-user"></span> Sign Up</a></li>
      		  <li><a href="signin.php"><span class="glyphicon glyphicon-log-in"></span> Login</a></li>
      		  </ul>
  			   </div>';
          } else {
            echo '<ul class="nav navbar-nav navbar-right">
             <li><a href="user_page.php?name='.$user['user_name'].'&page=profile"><span><img src="'.$user['user_avatar'].'" style="width:25px;height:25px" class="img-rounded"></span> User Profile</a></li>
            <li><a href="logout.php?token='.$_COOKIE['token_ses_data'].'"><span class="glyphicon glyphicon-log-in"></span> Logout</a></li>
            <li><a href="profile_settings.php"><span class="glyphicon glyphicon-cog"></span> User Settings</a></li>
            </ul>
           </div>';
          }
        }

  			echo '</nav>';
}

function PrintHeader($name) {
  echo '<head>
  <title>'.$name.' - Boot_Media</title>
  <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="shortcut icon" href="/img/icon.png">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="/js/bootmedia_js.js"></script>
  </head>';
}

function PrintPost($id) {
  global $db;

  $get_post = $db->query("SELECT * FROM posts WHERE id = $id");
  $row = mysqli_fetch_array($get_post);

  if(mysqli_num_rows($get_post) != 0) {
    $get_user = $db->query("SELECT * FROM users WHERE id = ".$row['creator']);
    $user = mysqli_fetch_array($get_user);

    if(strlen($row['post_body']) > 110) {
      $content_st = mb_substr($row['post_body'],0,107).'...';
    } else {
      $content_st = $row['post_body'];
    }

    echo '<li class="list-group-item"><a href="user_page.php"><img src="'.htmlspecialchars($user['user_avatar']).'" class="img-rounded" style="width: 35px;height: 35px;"> </a><a href="/post.php?id='.$row['id'].'">'.htmlspecialchars($content_st).'</a> ';
    if($row['is_pinned'] == 1) {
      echo '<span class="label label-primary">Pinned</span> ';
    }
    if($row['uses_html'] == 1) {
      echo '<span class="label label-primary">Uses HTML</span> ';
    }
    if($row['is_deleted'] == 1) {
      echo '<span class="label label-danger">Deleted</span> ';
    }
    echo '<br><br>';
    if(!empty($row['post_image'])) {
      echo '<img src="'.htmlspecialchars($row['post_image']).'" class="img-rounded" style="width: 50%;height: auto;"></img><br><br>';
    }
    printLikeButton($row['id'], 0);
    echo '<div align="left">Comments <span class="badge">0</span></div></li>';
  }
}

function checkRemoteFile($image) {
    $params = array('http' => array(
                  'method' => 'HEAD'
               ));
     $ctx = stream_context_create($params);
     $fp = @fopen($image, 'rb', false, $ctx);
     if (!$fp) 
        return false;  // Problem with url

    $meta = stream_get_meta_data($fp);
    if ($meta === false)
    {
        fclose($fp);
        return false;  // Problem reading data from url
    }

    $wrapper_data = $meta["wrapper_data"];
    if(is_array($wrapper_data)){
      foreach(array_keys($wrapper_data) as $hh){
          if (substr($wrapper_data[$hh], 0, 19) == "Content-Type: image") // strlen("Content-Type: image") == 19 
          {
            fclose($fp);
            return true;
          }
      }
    }

    fclose($fp);
    return false;
}

function printLikeButton($id, $liketype) {
  global $db;
  global $user;

  $find_lc = $db->query("SELECT * FROM likes WHERE post_like = $id AND like_type = $liketype");
  $like_count = mysqli_num_rows($find_lc);

  $check_if_liked = $db->query("SELECT * FROM likes WHERE post_like = $id AND like_type = $liketype AND creator = ".$user['id']);
  $liked = (mysqli_num_rows($check_if_liked) > 0 ? 1 : 0);

  if($liketype == 0) {
    $p_find = $db->query("SELECT * FROM posts WHERE id = $id");
    $post = mysqli_fetch_array($p_find);
  } elseif($liketype == 1) {

  }

  echo '<button id="'.$id.'" liketype="'.$liketype.'" remove="'.$liked.'" type="button" '.($post['creator'] == $user['id'] || empty($_COOKIE['token_ses_data']) ? 'disabled' : '').' class="btn btn-primary like-button"><span class="like-button-text">'.($liked == 0 ? 'Like' : 'Unlike').'</span> <span class="badge"><div class="like-count">'.$like_count.'</div></span></button> ';
}