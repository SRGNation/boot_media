<?php
function PrintNavBar($page) {
      global $user;

			echo '<nav class="navbar navbar-inverse">
				<div class="container-fluid">
    				<div class="navbar-header">
      					<a class="navbar-brand" href="/">Boot_Media</a>
    				</div>
    		<ul class="nav navbar-nav">
      		<li><a href="/communities">Communities</a></li>
      		<li><a href="/trending">Trending</a></li>
    		</ul>';

        if($page != 'signin') {
          if(!isset($_COOKIE['token_ses_data'])) {
    		  echo '<ul class="nav navbar-nav navbar-right">
    	       <li><a href="/register"><span class="glyphicon glyphicon-user"></span> Sign Up</a></li>
      		  <li><a href="/login"><span class="glyphicon glyphicon-log-in"></span> Login</a></li>
      		  </ul>
  			   </div>';
          } else {
            echo '<ul class="nav navbar-nav navbar-right">
             <li><a href="/users/'.$user['user_name'].'"><span>'.printUserAvatar($user['id'], '25px').'</span> User Profile</a></li>
            <li><a href="/logout.php?token='.$_COOKIE['token_ses_data'].'"><span class="glyphicon glyphicon-log-in"></span> Logout</a></li>
            <li><a href="/settings/profile"><span class="glyphicon glyphicon-cog"></span> User Settings</a></li>
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
    <meta charset="utf-8">
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <link rel="shortcut icon" href="/img/icon.png">
    <script src="/js/jquery-3.2.1.min.js"></script>
    <script src="/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="/js/bootmedia_js.js"></script>
  </head>';
//<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
}

function PrintCommunityList($id) {
  global $db;

  $get_community = $db->query("SELECT * FROM communities WHERE id = $id");
  $row = mysqli_fetch_array($get_community);

  echo '<list class="list-group-item"><img src="'.(empty($row['community_icon']) ? '/img/communityEmpty.png' : htmlspecialchars($row['community_icon'])).'" style="width:50px;height:50px;" class="img-rounded"> <a href="/communities/'.$row['id'].'">'.htmlspecialchars($row['community_name']).'</a></list>';
}

function PrintPost($id, $show_extra_info) {
  global $db;

  $get_post = $db->query("SELECT * FROM posts WHERE id = $id");
  $row = mysqli_fetch_array($get_post);

  $get_comments = $db->query("SELECT id FROM comments WHERE comment_post = ".$row['id']);
  $ccount = mysqli_num_rows($get_comments);

  $get_comm = $db->query("SELECT id, community_name FROM communities WHERE id = ".$row['post_community']);
  $community = mysqli_fetch_array($get_comm);

  if(mysqli_num_rows($get_post) != 0) {
    $get_user = $db->query("SELECT id, user_avatar, user_name FROM users WHERE id = ".$row['creator']);
    $user = mysqli_fetch_array($get_user);

    if(strlen($row['post_body']) > 110) {
      $content_st = mb_substr($row['post_body'],0,107).'...';
    } else {
      $content_st = $row['post_body'];
    }

    echo '<li class="list-group-item"><a href="/users/'.$user['user_name'].'">'.printUserAvatar($user['id'], '35px').' </a><a href="/posts/'.$row['id'].'">'.htmlspecialchars($content_st).'</a> ';
    if($row['is_pinned'] == 1) {
      echo '<span class="label label-primary">Pinned</span> ';
    }
    if($row['uses_html'] == 1) {
      echo '<span class="label label-primary">Uses HTML</span> ';
    }
    if($row['is_deleted'] > 0) {
      echo '<span class="label label-danger">Deleted</span> ';
    }
    echo '<br><br>';
    if(!empty($row['post_image'])) {
      echo '<img src="'.htmlspecialchars($row['post_image']).'" class="img-rounded" style="width: 50%;height: auto;"></img><br><br>';
    }
    printLikeButton($row['id'], 0);
    echo '<div align="left">Comments <span class="badge">'.$ccount.'</span> <span style="color: #c4c4c4;">'.humanTiming(strtotime($row['date_time'])).''.($show_extra_info == 1 ? ', '.($row['post_community'] == 0 ? htmlspecialchars($user['user_name']).'\'s profile' : htmlspecialchars($community['community_name'])) : '').'</span></div></li>';
  }
}

function printUserAvatar($id, $size) {
  global $db;

  $find_user = $db->query("SELECT id, user_name, user_avatar FROM users WHERE id = $id");
  $user = mysqli_fetch_array($find_user);

  return '<img src="'.(!empty($user['user_avatar']) ? htmlspecialchars($user['user_avatar']) : '/img/walter.png').'" class="img-rounded" style="width: '.$size.';height: '.$size.';">';
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

function generateRandomString($str_size) {
  $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $charactersLength = strlen($characters);
  $randstring = '';
  for ($i = 0; $i < $str_size; $i++) {
    $randstring .= $characters[rand(0, $charactersLength - 1)];
  }
    
  return $randstring;
}

function printLikeButton($id, $liketype) {
  global $db;
  global $user;

  $find_lc = $db->query("SELECT id FROM likes WHERE post_like = $id AND like_type = $liketype");
  $like_count = mysqli_num_rows($find_lc);

  $check_if_liked = $db->query("SELECT id FROM likes WHERE post_like = $id AND like_type = $liketype AND creator = ".$user['id']);
  $liked = (mysqli_num_rows($check_if_liked) > 0 ? 1 : 0);

  if($liketype == 0) {
    $p_find = $db->query("SELECT creator FROM posts WHERE id = $id");
    $post = mysqli_fetch_array($p_find);
  } elseif($liketype == 1) {
    $p_find = $db->query("SELECT creator FROM comments WHERE id = $id");
    $post = mysqli_fetch_array($p_find);
  }

  echo '<button id="'.$id.'" liketype="'.$liketype.'" remove="'.$liked.'" type="button" '.($post['creator'] == $user['id'] || empty($_COOKIE['token_ses_data']) ? 'disabled' : '').' class="btn btn-primary like-button"><span class="like-button-text">'.($liked == 0 ? 'Like' : 'Unlike').'</span> <span class="badge"><div class="like-count">'.$like_count.'</div></span></button> ';
}

function humanTiming($time) {
  #Credit goes to arian for this code.
  #Yes, I may be using the same code as something from a Miiverse clone, but that does NOT make Boot_Media a Miiverse clone >:(
      if(time() - $time >= 345600) {
        return date("m/d/Y g:i A", $time);
    }
    $time = time() - $time;
    if (strval($time) < 1) {
        $time = 1;
    }
    $tokens = array(86400 => 'day', 3600 => 'hour', 60 => 'minute', 1 => 'second');
    foreach ($tokens as $unit => $text){
        if($time < $unit) continue;
        $numberOfUnits = floor($time / $unit);
        if ($time < 2) {
          return 'Just Now';
        }
        return $numberOfUnits.' '.$text.(($numberOfUnits>1)?'s':''). ' ago';
    }
}
