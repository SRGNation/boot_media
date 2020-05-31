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

function ShowError($type, $content) {
  http_response_code($type);
  echo '<html>
    '.PrintHeader('Error').'
      <body>
      '.PrintNavBar('error').'
      <div class="container">
        <div class="page-header">
          <h1>Error</h1>
        </div>
        <p>'.htmlspecialchars($content).'</p>
      </div>
    </body>
  </html>';
  exit();
}

function PrintCommunityList($id) {
  global $db;

  $stmt = $db->prepare("SELECT id, community_icon, community_name FROM communities WHERE id = ?");
  $stmt->bind_param('i', $id);
  $stmt->execute();
  if($stmt->error) {
    return null;
  }
  $result = $stmt->get_result();
  if($result->num_rows === 0) {
    return null;
  }
  $row = $result->fetch_assoc();

  echo '<list class="list-group-item"><img src="'.(empty($row['community_icon']) ? '/img/communityEmpty.png' : htmlspecialchars($row['community_icon'])).'" style="width:50px;height:50px;" class="img-rounded"> <a href="/communities/'.$row['id'].'">'.htmlspecialchars($row['community_name']).'</a></list>';
}

function PrintPost($id, $show_extra_info) {
  global $db;

  //TODO: Make everything fit onto this one statement to make it CLEANER!!!!!!!!!
  $stmt = $db->prepare("SELECT id, is_pinned, uses_html, is_deleted, post_image, date_time, post_body, post_community, creator, (SELECT COUNT(*) FROM comments WHERE comment_post = posts.id) AS comment_count FROM posts WHERE id = ? AND is_deleted < 2");
  $stmt->bind_param('i', $id);
  $stmt->execute();
  if($stmt->error) {
    return null;
  }
  $result = $stmt->get_result();
  if($result->num_rows === 0) {
    return null;
  }
  $row = $result->fetch_assoc();

  $stmt = $db->prepare("SELECT id, community_name FROM communities WHERE id = ?");
  $stmt->bind_param('i', $row['post_community']);
  $stmt->execute();
  if($stmt->error) {
    return null;
  }
  $result = $stmt->get_result();
  $crow = $result->fetch_assoc();

  $stmt = $db->prepare("SELECT id, user_avatar, user_name FROM users WHERE id = ?");
  $stmt->bind_param('i', $row['creator']);
  $stmt->execute();
  if($stmt->error) {
    return null;
  }
  $result = $stmt->get_result();
  $user = $result->fetch_assoc();

  if(strlen($row['post_body']) > 200) {
    $content_st = mb_substr($row['post_body'],0,197).'...';
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
  echo '<div align="left">Comments <span class="badge">'.htmlspecialchars($row['comment_count']).'</span> <span style="color: #c4c4c4;">'.humanTiming(strtotime($row['date_time'])).''.($show_extra_info == 1 ? ', '.($row['post_community'] == 0 ? htmlspecialchars($user['user_name']).'\'s profile' : htmlspecialchars($crow['community_name'])) : '').'</span></div></li>';
}

function printUserAvatar($id, $size) {
  global $db;

  $stmt = $db->prepare("SELECT id, user_name, user_avatar FROM users WHERE id = ?");
  $stmt->bind_param('i', $id);
  $stmt->execute();
  if($stmt->error) {
    return '<img src="/img/walter.png" class="img-rounded" style="width: '.$size.';height: '.$size.';">';
  }
  $result = $stmt->get_result();
  if($result->num_rows === 0) {
    return '<img src="/img/walter.png" class="img-rounded" style="width: '.$size.';height: '.$size.';">';
  }
  $user = $result->fetch_assoc();

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

  //TODO: Maybe put this all in one prepare statement if possible.
  $stmt = $db->prepare("SELECT COUNT(*) FROM likes WHERE post_like = ? AND like_type = ?");
  $stmt->bind_param('ii', $id, $liketype);
  $stmt->execute();
  if($stmt->error) {
    return null;
  }
  $result = $stmt->get_result();
  $count = $result->fetch_assoc();

  if(!empty($_COOKIE['token_ses_data'])) {
    $stmt = $db->prepare("SELECT id FROM likes WHERE post_like = ? AND like_type = ? AND creator = ?");
    $stmt->bind_param('iii', $id, $liketype, $user['id']);
    $stmt->execute();
    if($stmt->error) {
      return null;
    }
    $result = $stmt->get_result();
    $liked = ($result->num_rows > 0 ? 1 : 0);
  } else {
    $liked = 0;
  }

  $stmt = $db->prepare("SELECT creator FROM ".($liketype === 0 ? 'posts' : 'comments')." WHERE id = ?");
  $stmt->bind_param('i', $id);
  $stmt->execute();
  if($stmt->error) {
    return null;
  }
  $result = $stmt->get_result();
  $post = $result->fetch_assoc();

  echo '<button id="'.$id.'" liketype="'.$liketype.'" remove="'.$liked.'" type="button" '.($post['creator'] === $user['id'] || empty($_COOKIE['token_ses_data']) ? 'disabled' : '').' class="btn btn-primary like-button"><span class="like-button-text">'.($liked == 0 ? 'Like' : 'Unlike').'</span> <span class="badge"><div class="like-count">'.$count['COUNT(*)'].'</div></span></button> ';
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
  foreach ($tokens as $unit => $text) {
    if($time < $unit) continue;
    $numberOfUnits = floor($time / $unit);
    if ($time < 2) {
      return 'Just Now';
    }
    return $numberOfUnits.' '.$text.(($numberOfUnits>1)?'s':''). ' ago';
  }
}

function uploadImage($filename) {
  #Code was partly taken from Cedar

  $handle = fopen($filename, "r");
  $data = fread($handle, filesize($filename));
  $pvars = array('file' => (exif_imagetype($filename) == 1 ? 'data:image/gif;base64,' : (exif_imagetype($filename) == 2 ? 'data:image/jpg;base64,' : (exif_imagetype($filename) == 3 ? 'data:image/png;base64,' : (exif_imagetype($filename) == 6 ? 'data:image/bmp;base64,' : '')))) . base64_encode($data), 'upload_preset' => CLOUDINARY_UPLOADPRESET);
  $timeout = 30;
  $curl = curl_init();
  curl_setopt($curl, CURLOPT_URL, 'https://api.cloudinary.com/v1_1/'. urlencode(CLOUDINARY_CLOUDNAME) .'/auto/upload');
  curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
  curl_setopt($curl, CURLOPT_POST, 1);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($curl, CURLOPT_POSTFIELDS, $pvars);
  $out = curl_exec($curl);
  curl_close ($curl);
  $pms = json_decode($out,true);

  if (@$image=$pms['secure_url']) {
    return $image;
  } else {
    return null;
  }
}