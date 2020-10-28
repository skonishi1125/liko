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

if(!empty($_FILES)) {
    $fileName = $_FILES['image']['name'];
    if(!empty($fileName)){
        $ext = substr($fileName, -4);
        if($ext != '.jpg' && $ext !='.png' && $ext !='.PNG' 
                && $ext !='.gif' && $ext != 'JPEG' && $ext != 'jpeg' && $ext != '.JPG'){
                $error['image'] = 'type';        
        }
    }

    if(empty($error)) {
        $postImgTime = date('YmdHis');
        if($ext == 'jpeg' || $ext == 'JPEG'){
            $ext = '.' . $ext;
        }
        $_SESSION['ext'] = $ext;

        $image = $postImgTime . sha1($_FILES['image']['name']).$ext; 
        
        move_uploaded_file($_FILES['image']['tmp_name'], 'member_picture/'.$image);

        $_SESSION['join'] = $_POST;
        $_SESSION['join']['image'] = $image;
        $_SESSION['join']['time'] = $postImgTime;

        header('Location:   checkIcon.php');
        exit();

    }

}

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
                if($iconExt != 'jpeg' && $iconExt != '.png' && $iconExt != '.PNG'
                && $iconExt != 'JPEG' && $iconExt != '.gif' && $iconExt != '.jpg'
                && $iconExt != '.JPG' ): 
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
                    <p>アイコン画像を選択してください。<br>
                        使用可能なファイルはjpg,jpeg,png,gifファイルです。
                    </p>
                    <input type="file" name="image" size="35">
                    <?php if($error['image'] == 'type'): ?>
                        <p class="checkRed">使用できないファイルが選択されました。再度お試しください。</p>
                    <?php endif; ?>
                    <br>
                    <input type="submit" class="submitBtn" value="確認画面へ">
                    <br>
                </form>
            </div>

        </div>

        <div class="changeIcon-backTimeline　col-md-offset-2 col-md-10">
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