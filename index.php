<?php 
session_start();
require('dbconnect.php');

if(isset($_SESSION['id']) && $_SESSION['time'] + 3600 > time()) {
    //ログインしていることの確認
    $_SESSION['time'] = time();
    
    $members = $db->prepare('SELECT * FROM members WHERE id=?');
    $members->execute(array($_SESSION['id']));

    $member = $members->fetch();
    //loginでmemberを識別するidをsessionに入れることで、他のファイルでも使用できるようにする
} else {
    header('Location:   login.php');
    exit();
}

//メッセージをDBに入れる
if(!empty($_POST)) {
    //画像の有無判定
    $postPicName = $_FILES['postpic']['name'];
    $videoURL = substr($_POST['video'], 0, 23);
    $mobileURL = substr($_POST['video'], 0, 21);
    $videoID = substr($_POST['video'], -13);
    $v = substr(h($post['video']), -11);
    //https://www.youtube.com

    if(!empty($videoURL)){
        if($videoURL != 'https://www.youtube.com' && $mobileURL != 'https://m.youtube.com'){
            $error['video'] = 'type';
        }
        if(substr($videoID,0,2) != 'v='){
            $error['video'] = 'type';
        }
    }

    if(empty($_POST['message'])) {
        $error['message'] = 'blank';
    }

    //どっちも入っていた場合の処理
    if($_POST['message'] != '' && !empty($postPicName)){
        //拡張子チェック
        if(!empty($postPicName)) {
            $ext = substr($postPicName, -4);
            if($ext == 'jpeg' || $ext == 'JPEG'){
                $ext = '.' . $ext;
            }
            if($ext != '.jpg' && $ext !='.png' && $ext !='.PNG' && $ext !='.JPG'
                && $ext !='.gif' && $ext != '.JPEG' && $ext != '.jpeg'){
                    $error['postpic'] = 'type';
            }
        }

        if(empty($error)){
            $postImgTime = date('YmdHis');
            $postPicName = $postImgTime . sha1($postPicName) . $ext;

            move_uploaded_file($_FILES['postpic']['tmp_name'], 'post_picture/' . $postPicName);

            //動画の紹介があった場合
            if(!empty($_POST['video']) ) {
                $message = $db->prepare('INSERT INTO posts SET title=?,member_id=?,
                        message=?, post_pic=?, video=?, reply_post_id=?, created=NOW()');

                if($_POST['reply_post_id'] == 0){
                    $message->execute(array(
                        $_POST['title'],
                        $member['id'],
                        $_POST['message'],
                        $postPicName,
                        $_POST['video'],
                        0
                    ));
                }else{
                    $message->execute(array(
                        $_POST['title'],                    
                        $member['id'],
                        $_POST['message'],
                        $postPicName,
                        $_POST['video'],                          
                        $_POST['reply_post_id'],
                    ));
                }
            }else{
                $message = $db->prepare('INSERT INTO posts SET title=?,member_id=?,
                                 message=?, post_pic=?, reply_post_id=?, created=NOW()');
                if($_POST['reply_post_id'] == 0){
                    $message->execute(array(
                        $_POST['title'],
                        $member['id'],
                        $_POST['message'],
                        $postPicName,
                        0
                    ));
                }else{
                    $message->execute(array(
                        $_POST['title'],                    
                        $member['id'],
                        $_POST['message'],
                        $postPicName,
                        $_POST['reply_post_id']
                    ));
                }      
            }

            /*----------------投稿画像のリサイズ-----------------*/
            $imageName = $postPicName;
            list($width, $height, $type, $attr) = getimagesize('post_picture/'.$imageName);

            $newWidth = 1000;//新しい横幅
            $newHeight = 1000;//新しい縦幅

            function fitContain($resize, $w1, $h1, &$w2, &$h2){
                if($w1 > $h1){
                    $base = $w1;
                } else {
                    $base = $h1;
                }
                //リサイズしたいサイズとの縮小比率
                $rate = ($base / $resize);
                if($rate > 1){
                    $w2 = floor((1 / $rate) * $w1);
                    $h2 = floor((1 / $rate) * $h1);
                } else {
                    $w2 = $w1;
                    $h2 = $h1;
                }
            }         

            fitContain(1000, $width, $height, $newWidth, $newHeight);

            /*--------Exif--------------*/
            //exif読み取り
            $exif_data = exif_read_data('post_picture/'.h($imageName));

            function imageOrientation($filename, $orientation){
                //画像のロード HEICは自動でjpegになるからjpegだけで良い？
                $image = imagecreatefromjpeg($filename);

                //回転角度
                $degrees = 0;
                switch($orientation){
                    case 1:
                        return;
                    case 8:
                        $degrees = 90;
                        break;
                    case 3:
                        $degrees = 180;
                        break;
                    case 6:
                        $degrees = 270;
                        break;
                    case 2:
                        $mode = IMG_FLIP_HORIZONTAL;
                        break;
                    case 7:
                        $degrees = 90;
                        $mode = IMG_FLIP_HORIZONTAL;
                        break;
                    case 4:
                        $mode = IMG_FLIP_VERTICAL;
                        break;
                    case 5:
                        $degrees = 270;
                        $mode = IMG_FLIP_HORIZONTAL;
                        break;
                }
                //反転する
                if(isset($mode)){
                    $image = imageflip($image,$mode);
                }

                //回転させる
                if($degrees > 0){
                    $image = imagerotate($image,$degrees,0);
                }

                //保存,メモリ解放
                imagejpeg($image,$filename);
                imagedestroy($image);

            }               

            //画像リサイズ
            if($ext == '.gif'){
                $baseImage = imagecreatefromgif('post_picture/'.h($imageName));
                $image = imagecreatetruecolor($newWidth, $newHeight);
                imagecopyresampled($image, $baseImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagegif($image, 'post_picture/'.h($imageName));

            }else if($ext == '.png' || $ext == '.PNG'){
                $baseImage = imagecreatefrompng('post_picture/'.h($imageName));
                $image = imagecreatetruecolor($newWidth, $newHeight);
                imagecopyresampled($image, $baseImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagepng($image, 'post_picture/'.h($imageName));

            }else if($ext == '.jpg' || $ext == '.jpeg' || $ext == '.JPG' || $ext == '.JPEG'){
                $baseImage = imagecreatefromjpeg('post_picture/'.h($imageName));
                $image = imagecreatetruecolor($newWidth, $newHeight);
                imagecopyresampled($image, $baseImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagejpeg($image, 'post_picture/'.h($imageName));
                //exif読み取り
                imageOrientation('post_picture/'.h($imageName), $exif_data['Orientation']);                
            }
            /*----------------投稿画像のリサイズ-----------------*/

            header('Location:   index.php');
            exit();

        }
    }



    //メッセージが入っていた場合の処理
    if ($_POST['message'] != '') {
        if(empty($error)){
            if(!empty($_POST['video']) ) {
                $message = $db->prepare('INSERT INTO posts SET title=?, member_id=?, message=?, 
                    video=?, reply_post_id=?, created = NOW()');

                if($_POST['reply_post_id'] == ''){
                    $message->execute(array(
                        $_POST['title'],                
                        $member['id'],
                        $_POST['message'],
                        $_POST['video'],
                        0
                    ));
                }else{
                    $message->execute(array(
                        $_POST['title'],
                        $member['id'],
                        $_POST['message'],
                        $_POST['video'],
                        $_POST['reply_post_id']
                    ));            
                }

            }else{
                $message = $db->prepare('INSERT INTO posts SET title=?, member_id=?, message=?, reply_post_id=?, created = NOW()');

                if($_POST['reply_post_id'] == ''){
                    $message->execute(array(
                        $_POST['title'],                
                        $member['id'],
                        $_POST['message'],
                        0
                    ));
                }else{
                    $message->execute(array(
                        $_POST['title'],
                        $member['id'],
                        $_POST['message'],
                        $_POST['reply_post_id']
                    ));            
                }

            }
            header('Location:   index.php');
            exit();
        }
    }


    //画像が入っていた場合の処理
    if(!empty($postPicName)){
        //拡張子チェック
        if(!empty($postPicName)) {
            $ext = substr($postPicName, -4);
            if($ext == 'jpeg' || $ext == 'JPEG'){
                $ext = '.' . $ext;
            }
            if($ext != '.jpg' && $ext !='.png' && $ext !='.PNG' && $ext !='.JPG'
                && $ext !='.gif' && $ext != '.JPEG' && $ext != '.jpeg'){
                    $error['postpic'] = 'type';
            }
        }

        if(empty($error)){
            //画像ファイルの名前をわからなくする処理
            $postImgTime = date('YmdHis');
            $postPicName = $postImgTime . sha1($postPicName) . $ext;
            move_uploaded_file($_FILES['postpic']['tmp_name'], 'post_picture/' . $postPicName);
            if($videoURL == 'https://www.youtube.com'){
                $message = $db->prepare('INSERT INTO posts SET title=?, member_id=?, post_pic=?,
                    video=?, reply_post_id=?, created=NOW()');
                if($_POST['reply_post_id'] == ''){
                    $message->execute(array(
                        $_POST['title'],                    
                        $member['id'],
                        $postPicName,
                        $_POST['video'],
                        0
                    ));
                }else{
                    $message->execute(array(
                        $_POST['title'],                    
                        $member['id'],
                        $postPicName,
                        $_POST['video'],                        
                        $_POST['reply_post_id'],
                    ));                
                }                

            }else{
                $message = $db->prepare('INSERT INTO posts SET title=?, member_id=?, post_pic=?, reply_post_id=?, created=NOW()');
                if($_POST['reply_post_id'] == ''){
                    $message->execute(array(
                        $_POST['title'],                    
                        $member['id'],
                        $postPicName,
                        0
                    ));
                }else{
                    $message->execute(array(
                        $_POST['title'],                    
                        $member['id'],
                        $postPicName,
                        $_POST['reply_post_id']
                    ));                
                }

            }




            /*----------------投稿画像のリサイズ-----------------*/
            $imageName = $postPicName;
            list($width, $height, $type, $attr) = getimagesize('post_picture/'.$imageName);

            $newWidth = 1000;//新しい横幅
            $newHeight = 1000;//新しい縦幅

            function fitContain($resize, $w1, $h1, &$w2, &$h2){
                if($w1 > $h1){
                    $base = $w1;
                } else {
                    $base = $h1;
                }
                //リサイズしたいサイズとの縮小比率
                $rate = ($base / $resize);
                if($rate > 1){
                    $w2 = floor((1 / $rate) * $w1);
                    $h2 = floor((1 / $rate) * $h1);
                } else {
                    $w2 = $w1;
                    $h2 = $h1;
                }
            }

            fitContain(1000, $width, $height, $newWidth, $newHeight);


            //画像リサイズ
            if($ext == '.gif'){
                $baseImage = imagecreatefromgif('post_picture/'.h($imageName));
                $image = imagecreatetruecolor($newWidth, $newHeight);
                imagecopyresampled($image, $baseImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagegif($image, 'post_picture/'.h($imageName));


            }else if($ext == '.png' || $ext == '.PNG'){
                $baseImage = imagecreatefrompng('post_picture/'.h($imageName));
                $image = imagecreatetruecolor($newWidth, $newHeight);
                imagecopyresampled($image, $baseImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagepng($image, 'post_picture/'.h($imageName));


            }else if($ext == '.jpg' || $ext == '.jpeg'|| $ext == '.JPG' || $ext == '.JPEG'){
                $baseImage = imagecreatefromjpeg('post_picture/'.h($imageName));
                $image = imagecreatetruecolor($newWidth, $newHeight);
                imagecopyresampled($image, $baseImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagejpeg($image, 'post_picture/'.h($imageName));
            }
            /*----------------投稿画像のリサイズ-----------------*/

            header('Location:   index.php');
            exit();
        }
    }
}


//投稿取得

//ページ分け
$page = $_REQUEST['page'];
if($page == ''){
    $page = 1;
}
//-の数対策
$page = max($page, 1);

$counts = $db->query('SELECT COUNT(*) AS cnt FROM posts');
$cnt = $counts->fetch();
$maxPage = ceil($cnt['cnt'] / 10);
$page = min($page, $maxPage);
$start = ($page - 1) * 10;

if($start < 0){
    $start = 0;
}


$posts = $db->prepare('SELECT m.name, m.picture, p.* FROM members m, posts p
                    WHERE m.id = p.member_id ORDER BY p.created DESC  LIMIT ?, 10');
$posts->bindParam(1, $start, PDO::PARAM_INT);
$posts->execute();


//返信
if(isset($_REQUEST['res'])){
    $response = $db->prepare('SELECT m.name, m.picture, p.* FROM members m, posts p
                    WHERE m.id = p.member_id AND p.id=? ORDER BY p.created DESC');
    $response->execute(array($_REQUEST['res']));
    $table = $response->fetch();
    $message = '>>@' . $table['name']. '[' . $table['id'] . '] ';
}


$goodPosts = $db->prepare('SELECT post_id FROM goods WHERE member_id=?');
$goodPosts->bindParam(1, $member['id'], PDO::PARAM_INT);
$goodPosts->execute();


//いいねボタンが押された時の処理
//初期goodを最初に取得しておく
$origins = $db->prepare('SELECT good FROM posts WHERE id=?');
$origins->execute(array(
    $_REQUEST['good']
));
$origin = $origins->fetch();
$defGood = $origin['good'];

if(isset($_REQUEST['good'])) {
    $good = $db->prepare('UPDATE posts SET good=?, modified=NOW()
                WHERE id=?');
    $defGood = $defGood + 1;
    echo $retGood = $good->execute(array(
        $defGood,
        $_REQUEST['good']
    ));

    //いいねテーブルへの格納
    $goodState = $db->prepare('INSERT INTO goods SET member_id=?, post_id=?,created=NOW()');
    echo $goodRet = $goodState->execute(array(
        $member['id'],
        $_REQUEST['good']
    ));

    //echo $goodRetを使わない場合はこちらを採用する　$goodRet = $goodState->fetch();

    header('Location:   index.php');
    exit();

}

//コメント(review機能)
if(isset($_POST['review'])) {
    if(!empty($_POST['review'])) {
        $reviews = $db->prepare('INSERT INTO reviews SET member_id=?, post_id=?,
                member_pic=?, comment=?, created=NOW()');
        $reviews->execute(array(
            $member['id'],
            $_POST['postid'],
            $member['picture'],
            $_POST['review']
        ));

        header('Location:   index.php');
        exit();
        //これないと更新するたび増えていく     
    }else{
        $error['review'] = 'blank';
    }
}

//search.phpで空コメントした場合のエラー
if($_SESSION['review'] == 'blank'){
    $error['review'] = 'blank';
    unset($_SESSION['review']);
}

//アイコン用のext取得
$iconExt = substr($member['picture'],-4);

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
        <div class="col-md-10 col-md-offset-2 tweetModal-wrapper">
            <div class="tweetModal-container">
                <div class="tweetClose-container">
                    <a class="closeBtn"><i class="fas fa-times"></i></a>                    
                </div>
                <div class="tweetTitle-container">
                    <h4>投稿する</h4>
                </div>
                <form action="" method="post" enctype="multipart/form-data">
                    <p><?php echo h($member['name']) ?>さん、こんにちは。</p>
                    <!-- title -->
                    <p><b>タイトル</b></p>
                    <textarea name="title" cols="50" rows="1"><?php echo h($_POST['title']) ?></textarea>
                    <!-- message -->
                    <p><b>内容</b><span class="checkRed">(必須)</span></p>   
                    <textarea name="message" cols="50" rows="5"><?php echo h($_POST['message']) ?></textarea>
                    <input type="hidden" name="reply_post_id" 
                        value="<?php echo h($_REQUEST['res']); ?>">

                    <div>
                        <p><b>添付する画像を選択する</b></p>
                        <input type="file" name="postpic">
                        <p>投稿できる画像はjpeg,png,gifのファイルです。</p>
                    </div>
                    <!-- YouTube動画 -->
                    <p><b>YouTube動画</b></p>
                    <p>URLの末尾がv=[動画のID]で終わるようにしてください。</p>
                    <input type="text" name="video" size="50" value="<?php echo h($_POST['video']) ?>">
                    <br>
                    <div class="fright">
                        <input type="submit" value="投稿する" class="submitBtn">
                    </div>
                </form>
            </div>
        </div>



        <div class="col-md-2 config-wrapper">

            <div class="config-container">
                <?php if($page == 1): ?>
                <a href="#header"><i class="fas fa-home"></i>ホーム</a>
                <?php else: ?>
                <a href="index.php"><i class="fas fa-home"></i>ホーム</a>
                <?php endif; ?>

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
                if($iconExt != 'jpeg' && $iconExt != '.png' && $iconExt != '.PNG'
                && $iconExt != 'JPEG' && $iconExt != '.gif' && $iconExt != '.jpg'
                && $iconExt != '.JPG' ): 
            ?>
                <img class="iconImg" src="member_picture/user.png">
            <?php else: ?>                
                <img class="iconImg" src="member_picture/<?php echo h($member['picture']); ?>">
            <?php endif; ?>
                <p class="userName"><?php echo h($member['name']); ?></p>
                <a class="openCommentModal postButton">投稿する</a>
            </div>

        </div>

        <?php
        $i = 0;
        $goodArray = array();
        while ($goodPost = $goodPosts->fetch() ){
            //echo $goodPost['post_id']. "\n";
            $goodArray[$i] = $goodPost['post_id'];
            $i++;
        };
        ?>

        <div class="tweet-wrapper col-md-10">
            <?php echo $exif_data['Orientation']; ?>
        <?php if(!empty($error)): ?>
            <div class="error-wrapper">
                <div class="error-container">
                    <?php if(!empty($error)): ?>
                    <p class="checkRed">※投稿にエラーがありました。</p>
                    <?php endif; ?>
                    <?php if($error['message'] == 'blank' || $error['review'] == 'blank'): ?>
                    <p class="checkRed">・無記入のままで投稿することはできません。</p>
                    <?php endif; ?>
                    <?php if($error['postpic'] == 'type'): ?>
                    <p class="checkRed">・非対応の画像ファイルです。拡張子を確認ください。</p>
                    <?php endif; ?>
                    <?php if($error['video'] == 'type'): ?>
                    <p class="checkRed">・URLに誤りがあります。現状YouTube動画のみの対応となっています。</p>
                    <p class="checkRed">　投稿例：https://www.youtube.com/watch?v=ABCDEFGHIJK</p>
                    <p class="checkRed">　(URLの末尾がv=[動画のID]で終わるように投稿してください)</p>
                    <?php endif; ?>            
                </div>
            </div>
        <?php endif; ?>

            <div class="col-md-12 mobileUser-wrapper res-phone">

                <div class="userPage-container">

                    <div class="mobileUser-container">
                        <?php 
                            if($iconExt != 'jpeg' && $iconExt != '.png' && $iconExt != '.PNG'
                            && $iconExt != 'JPEG' && $iconExt != '.gif' && $iconExt != '.jpg'
                            && $iconExt != '.JPG' ): 
                        ?>
                            <img src="member_picture/user.png" height="20">
                        <?php else: ?>                
                            <img src="member_picture/<?php echo h($member['picture']); ?>">
                        <?php endif; ?>
                        <span><b><?php echo $member['name'] ?></b>さん、こんにちは！</span>
                    </div>

                </div>
            </div>


            <?php foreach ($posts as $post): ?>

                <div class="col-md-12 post-wrapper">

                    <div class="col-md-2 user-container">
                        <div class="col-xs-1">
                        <?php 
                            $iconExt = substr($post['picture'],-4);
                            if($iconExt != 'jpeg' && $iconExt != '.png' && $iconExt != '.PNG'
                            && $iconExt != 'JPEG' && $iconExt != '.gif' && $iconExt != '.jpg'
                            && $iconExt != '.JPG' ): 
                        ?>
                            <img class="iconImg" src="member_picture/user.png">
                        <?php else: ?>                               
                            <img class="iconImg" src="member_picture/<?php echo h($post['picture']); ?>">
                        <?php endif; ?>
                        </div>

                        <div class="userInfo-container col-xs-9 col-xs-offset-1">
                            <p class="userName"><?php echo h($post['name']); ?></p>
                            <a class="res-phone" href="view.php?id=<?php echo h($post['id']); ?>">
                                Post ID:[<?php echo h($post['id']); ?>]
                            </a>                                 
                            <nobr class="createTime res-phone">[<?php echo h($post['created']); ?>]</nobr>


                        </div>
                    </div>

                    <div class="col-md-10 post-container">
                    <?php if(!empty($post['title'])): ?>
                        <h3><?php echo h($post['title']); ?></h3>
                    <?php endif; ?>

                        <?php if(empty($post['post_pic']) && empty($post['video']) ): ?>
                        <div class="col-md-12 msg-container">
                        <?php else: ?>
                        <div class="col-md-6 msg-container">
                        <?php endif; ?>
                            <p class="post-message">
                            <?php echo nl2br(makeLink(h($post['message']) ) ); ?>
                            </p>
                            <br>
                            <hr>
                            <a class="res-pc" href="view.php?id=<?php echo h($post['id']); ?>">
                                Post ID:[<?php echo h($post['id']); ?>]
                            </a>                         
                            <p class="createTime res-pc">
                                [<?php echo h($post['created']); ?>]                                
                            </p>                            
                        </div>

                        <div class="col-md-6 content-container　embed-responsive">
                            <!-- embed-responsive-16by9をclassに適用すると高さが生まれる-->
                            <?php if(isset($post['video'])): ?>
                            <?php $v = substr($post['video'], -11); ?>
                            <div class="embed-responsive embed-responsive-16by9">
                                <iframe class="contentYoutube embed-responsive-item"
                                    src="<?php echo 'https://www.youtube.com/embed/'. $v; ?>">
                                </iframe>
                            </div>
                            <?php endif; ?>

                            <?php if(isset($post['post_pic'])): ?>
                            <img class="contentImg openImgModal"
                            src="post_picture/<?php echo h($post['post_pic']); ?>">
                            <?php endif; ?>
                        </div>

                        <!--reaction-->
                        <div class="reaction-container col-md-12">                           

                        <!-- いいねボタン -->
                        <?php $goodFlag = in_array($post['id'], $goodArray); ?>
                        <?php if($goodFlag): ?>
                            <div class="good-wrapper">
                                <i class="good fas fa-heart"></i>
                                <nobr>× <?php echo h($post['good']); ?></nobr>
                            </div>
                        <?php else: ?>
                            <a class="preGood" href="index.php?good=<?php echo h($post['id']); ?>">
                                <i class="far fa-heart"></i>
                                <nobr>× <?php echo h($post['good']); ?></nobr> 
                            </a>
                        <?php endif; ?>                            

                        <?php if($_SESSION['id'] == $post['member_id']): ?>
                            <a href="delete.php?id=<?php echo h($post['id']); ?>" class="deleteRed">
                                削除する
                            </a>
                        <?php endif; ?>
                        </div>

                        <!-- review(書き込み)欄 -->
                        <div class="col-md-1">
                        <?php 
                            $iconExt = substr($member['picture'],-4);
                            if($iconExt != 'jpeg' && $iconExt != '.png' && $iconExt != '.PNG'
                            && $iconExt != 'JPEG' && $iconExt != '.gif' && $iconExt != '.jpg'
                            && $iconExt != '.JPG' ): 
                        ?>
                            <img class="commentMyImg" src="member_picture/user.png">
                        <?php else: ?>                             
                            <img class="commentMyImg res-pc" src="member_picture/<?php echo h($member['picture']); ?>">
                        <?php endif; ?>
                        </div>
                        <div class="col-md-11">
                            <form action="" method="post" class="commentForm-wrapper">
                                <textarea id="commentForm" name="review"></textarea>
                                <input type="submit" value="コメントする" class="subComment">
                                <input type="hidden" name="postid" value="<?php echo h($post['id']); ?>">
                                <div class="clear"></div>
                            </form>
                        </div>

                    </div>
                </div><!-- post-wrapper -->


                <!-- review(読み込み)欄 -->
                <?php 
                $revPosts = $db->prepare('SELECT m.name, m.picture, r.* FROM members m, reviews r 
                                    WHERE m.id = r.member_id AND post_id=?');
                $revPosts->execute(array($post['id']));
                ?>

                <?php foreach($revPosts as $revPost): ?>
                    <?php if($revPost['post_id'] == $post['id']): ?>
                    <div class="col-md-10 col-md-offset-2 reviews-wrapper">
                        <div class="reviews-triangle"></div>
                        <div class="reviewsTri-white"></div>
                        <!-- コメント欄の吹き出し部分 -->

                        <div class="col-md-1"></div>
                        <div class="col-md-2 revUser-container">
                            <div class="col-xs-1">
                            <?php 
                                $iconExt = substr($revPost['picture'],-4);
                                if($iconExt != 'jpeg' && $iconExt != '.png' && $iconExt != '.PNG'
                                && $iconExt != 'JPEG' && $iconExt != '.gif' && $iconExt != '.jpg'
                                && $iconExt != '.JPG' ): 
                            ?>
                                <img class="iconImg" src="member_picture/user.png">
                            <?php else: ?>                                
                                <img class="iconImg"
                                src="member_picture/<?php echo h($revPost['picture']); ?>">
                            <?php endif; ?>
                            </div>
                            <div class="revUserRes-wrapeer">
                                <p class="userName res-phone">
                                    <?php echo h($revPost['name']); ?>
                                    <nobr class="res-phone"><?php echo '['. h($revPost['created']).']'; ?></nobr>
                                </p>  
                                <p class="res-phone"><?php echo nl2br(makeLink(h($revPost['comment']))); ?></p>
                            </div>
                         </div>

                        <div class="col-md-9">
                            <p class="userName res-pc">
                                <?php echo h($revPost['name']); ?>
                                <nobr class="res-pc"><?php echo '['. h($revPost['created']).']'; ?></nobr>    
                            </p>  
                            <p class="res-pc"><?php echo nl2br(makeLink(h($revPost['comment']))); ?></p>
                            <?php if($_SESSION['id'] == $revPost['member_id']): ?>                              
                                <a class="deleteRed" href="deleteReview.php?id=<?php echo h($revPost['id']); ?>">削除する</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endforeach; ?>
  

            <div class="page-wrapper col-md-12">
                <?php if($page > 1): ?>
                    <a href="index.php?page=<?php print($page - 1); ?>">前のページへ</a>
                <?php else: ?>
                    <span>前のページへ</span> | 
                <?php endif; ?>

                <?php if($page < $maxPage): ?>
                    <a href="index.php?page=<?php print($page + 1); ?>">次のページへ</a>
                <?php else: ?>
                    <span>次のページへ</span>
                <?php endif; ?>

            </div>

        </div>


        <footer class="col-md-offset-2">
            <div class="footerTweet-wrapper">
                <a class="openCommentModal"><i class="fas fa-pen-fancy"></i></a>
            </div>
                
            </div>
            <div class="footerMenu-wrapper">
                <?php if($page == 1): ?>
                <button class="col-xs-3" onclick="location.href='#header'"><i class="fas fa-home"></i></button>
                <?php else: ?>
                    <button class="col-xs-3" onclick="location.href='index.php'"><i class="fas fa-home"></i></button>
                <?php endif; ?>
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