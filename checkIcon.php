<?php 
session_start();
require('dbconnect.php');

if(isset($_SESSION['id']) && $_SESSION['time'] + 3600 > time()) {
    //ログイン確認
    $_SESSION['time'] = time();
    
    $members = $db->prepare('SELECT * FROM members WHERE id=?');
    $members->execute(array($_SESSION['id']));

    $member = $members->fetch();
    //loginでmemberを識別するidをsessionに入れることで、他のファイルでも使用できるようにする
} else {
    header('Location:   login.php');
    exit();
}

//changeIcon.php以外から飛んできた場合
if(!isset($_SESSION['join'])) {
	header('Location:	index.php');
	exit();
}

$defHash = "da39a3ee5e6b4b0d3255bfef95601890afd80709";
//画像が選択された時のみ表示するように設定
//選択していない場合は日付 + defHashの値となる

if($_SESSION['join']['image'] != $_SESSION['join']['time'].$defHash){
    $imageName = $_SESSION['join']['image'];
    list($width, $height, $type, $attr) = getimagesize('member_picture/'.$imageName);

    $newWidth = 0;//新横幅
    $newHeight = 0;//新縦幅
    $w = 100;//最大横幅
    $h = 100;//最大縦幅

    if($h < $height && $w < $width){
        if($w < $h){
            $newWidth = $w;
            $newHeight = $height * ($w / $width);
        } else if($h < $w) {
            $newWidth = $width * ($h / height);
            $newHeight = $h;
        }else{
            if($width < $height){
                $newWidth = $width * ($h / $height);
                $newHeight = $h;
            }else if($height < $width){
                $newWidth = $w;
                $newHeight = $height * ($w / $width);
            }else if($height == $width){
                $newWidth = $w;
                $newHeight = $h;
            }
        }
    }else if($height < $h && $width < $w){
        $newWidth = $width;
        $newHeight = $height;
    }else if($h < $height && $width <= $w){
        $newWidth = $width * ($h / $height);
        $newHeight = $h;
    }else if($height <= $h && $w < $width){
        $newWidth = $w;
        $newHeight = $height * ($w / $width);
    }else if($height == $h && $width < $w){
        $newWidth = $width * ($h / $height);
        $newHeight = $h;
    }else if($height < $h && $width == $w){
        $newWidth = $w;
        $newHeight = $height * ($w / $width);
    }else{
        $newWidth = $width;
        $newHeight = $height;
    }

    $ext = substr($imageName,-4);
    if($ext == 'jpeg' || $ext == 'JPEG'){
       $ext = '.' . $ext;
    }

}


//画像リサイズ
if($ext == '.gif'){
    $baseImage = imagecreatefromgif('member_picture/'.htmlspecialchars($imageName,ENT_QUOTES));
    $image = imagecreatetruecolor($newWidth, $newHeight);
    imagecopyresampled($image, $baseImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    imagegif($image, 'member_picture/'.htmlspecialchars($imageName,ENT_QUOTES));
}else if($ext == '.png' || $ext == '.PNG'){
    $baseImage = imagecreatefrompng('member_picture/'.htmlspecialchars($imageName,ENT_QUOTES));
    $image = imagecreatetruecolor($newWidth, $newHeight);
    imagecopyresampled($image, $baseImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    imagepng($image, 'member_picture/'.$imageName);
}else if($ext == '.jpg' || $ext == '.jpeg'){
    $baseImage = imagecreatefromjpeg('member_picture/'.htmlspecialchars($imageName,ENT_QUOTES));
    $image = imagecreatetruecolor($newWidth, $newHeight);
    imagecopyresampled($image, $baseImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    imagejpeg($image, 'member_picture/'.htmlspecialchars($imageName,ENT_QUOTES));
}

//更新処理
if(!empty($_POST)) {
	$statement = $db->prepare('UPDATE members SET picture=?,modified=NOW() WHERE id=?');
	echo $ret = $statement->execute(array(
		$_SESSION['join']['image'],
		$member['id']
	));

	unset($_SESSION['join']);

	header('Location:	changeResult.php');
	exit();

}


?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="css/style.css">
        <link rel="stylesheet"
        href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
        integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u"
        crossorigin="anonymous">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
        <script src="https://kit.fontawesome.com/50821e33c6.js" crossorigin="anonymous"></script>
        <title>Liko</title>
    </head>
    <body>
        <div id="header">
            <hr>
        </div>
        <header>
            <div class="header-title">
                <img class="header-logo" src="join/headerlogo.png">
            </div>

        </header>

        <div class="modal-background"></div>

        <div class="col-md-2 config-wrapper">

            <div class="config-container">
                <a href="index.php"><i class="fas fa-home"></i>ホーム</a>
                <a href="userpage.php"><i class="fas fa-user-alt"></i>マイページ</a>
                <a href="changeIcon.php"><i class="fas fa-cog"></i>アイコンの変更</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i>ログアウト</a>
                <div class="config-border"></div>

                <div class="search-wrapper">
                    <p>投稿を検索する</p>
                    <form class="searchForm" action="search.php" method="post">
                        <input name="search" type="text" class="searchBox">
                        <input type="submit" value="&#xf002;" class="fas searchIcon">
                    </form>
                </div>
            </div>

            <div class="confUser-container">
            <?php 
                if($ext != 'jpeg' && $ext != '.png' && $ext != '.PNG'
                && $ext != 'JPEG' && $ext != '.gif' && $ext != '.jpg'
                && $ext != '.JPG' ): 
            ?>
                <img class="iconImg" src="member_picture/user.png">
            <?php else: ?>                
                <img class="iconImg" src="member_picture/<?php echo h($member['picture']); ?>">
            <?php endif; ?>
                <p class="userName"><?php echo h($member['name']); ?></p>
            </div>

        </div>

        <div class="tweet-wrapper col-md-10">

            <div class="changeIcon-title">
                <h4>アイコンを変更する</h4>
            </div>

            <div class="changeIcon-form">

	            <form action="" method="post" enctype="multipart/form-data">
	            	<p>アイコン画像</p>
	                <?php if($_SESSION['join']['image'] != $_SESSION['join']['time'].$defHash) :?>
	                	<img class="iconImg" src="member_picture/<?php echo h($imageName); ?>">
	                	<br>
	                	<br>
	                	<p>アイコン使用時のイメージ<br>
	                		(画像は自動で100px * 100pxにリサイズされます)
	                	</p>
	                	<p>黒塗りになっている場合、使用できない拡張子のファイルである可能性があります。<br>
	                		前画面に戻り、再度アップロード作業を行なってください。<br>
	                		現在の画像の拡張子： <?php echo $ext; ?>
	                	</p>
	                <?php else: ?>
	                	<img class="iconImg" src="member_picture/user.png">
	                	<p>未設定(初期アイコン画像が設定されます)</p>
	                <?php endif; ?>
	                <br>
	                <input type="hidden" name="action" value="submit">
	                <a class="changeIcon-back" href="changeIcon.php?action=rewrite">やり直す</a>
	                <input type="submit" value="登録する" class="submitBtn">

	            </form>
	        </div>

        </div>

        <div class="col-md-2"></div>
		<div class="changeIcon-backTimeline col-md-10">
			<a href="index.php">タイムラインへ戻る</a>
		</div>


        <footer class="col-md-offset-2">
                
            <div class="footerMenu-wrapper">
                <button class="col-xs-3" onclick="location.href='index.php'"><i class="fas fa-home"></i></button>
                <button class="col-xs-3 openSearchModal"><i class="fas fa-search"></i></button>
                <button class="col-xs-3 openConfigModal"><i class="fas fa-cog"></i></button>
                <button class="col-xs-3" onclick="location.href='userpage.php'"><i class="fas fa-user-alt"></i></button>
            </div>   

        </footer>


        <div class="col-md-10 col-md-offset-2 searchModal-wrapper">
            <div class="searchModal-container">
                <div class="searchClose-container">
                    <a class="closeBtn"><i class="fas fa-times"></i></a>                    
                </div>
                <div class="searchTitle-container">
                    <h5>投稿を検索する</h5>
                </div>
                <form class="searchForm" action="search.php" method="post">
                    <input name="search" type="text" class="searchBox">
                    <input type="submit" value="&#xf002;" class="fas searchIcon">
                </form>
            </div>
        </div>

        <div class="col-md-10 col-md-offset-2 configModal-wrapper">
            <div class="configModal-container">
                <div class="configClose-container">
                    <a class="closeBtn"><i class="fas fa-times"></i></a>
                </div>
                <div class="configTitle-container">
                    <h5>設定</h5>
                </div>
                <div class="configMenu-container">
                    <a href="changeIcon.php"><i class="fas fa-cog"></i>アイコンの変更
                    </a>
                    <a href="logout.php"><i class="fas fa-sign-out-alt"></i>ログアウト
                    </a>
                </div>
            </div>
        </div>
 
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
                integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa"
                crossorigin="anonymous"></script>
        <script src = "css/script.js"></script>

    </body>
</html>