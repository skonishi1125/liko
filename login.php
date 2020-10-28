<?php 
session_start();
require('dbconnect.php');

//自動ログイン処理
if($_COOKIE['sessionid'] != ''){
    $cookieLogin = $db->prepare('SELECT * FROM members WHERE session_id=?');
    $cookieLogin->execute(array($_COOKIE['sessionid']));
    $member = $cookieLogin->fetch();

    $_POST['save'] = 'on';
    $_POST['email'] = $member['email'];//いらんかも

    if($member){
        $_SESSION['id'] = $member['id'];
        $_SESSION['time'] = time();
        $sessionid = hash('sha256', $_POST['email']);

        setcookie('sessionid', $sessionid, time() + 60 * 60 * 24 * 14);

        header('Location:   index.php');
        exit();

    }
}



if(!empty($_POST)){
    if($_POST['email'] != '' && $_POST['password'] != '') {
        $login = $db->prepare('SELECT * FROM members WHERE email=? AND password=?');
        $login->execute(array(
            $_POST['email'],
            hash('sha256',$_POST['password'])
        ));
        $member = $login->fetch();

        //ログイン成功処理
        if($member){
            $_SESSION['id'] = $member['id'];
            $_SESSION['time'] = time();
            $sessionid = hash('sha256', $_POST['email']);

            //自動ログインボックス有効時 onはinput value="on"で設定したから。
            if($_POST['save'] == 'on'){
                setcookie('sessionid', $sessionid ,time()+60*60*24*14);
                //直で2番目の値にhash関数を入れてはいけない
            }

            header('Location:   index.php');
            exit();

        }else{
            $error['login'] = 'failed';
        }

    }else{
        $error['login'] = 'blank';
    }

}
    
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="css/join_style.css">
        <link rel="stylesheet"
        href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
        integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u"
        crossorigin="anonymous">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
        <script src="https://kit.fontawesome.com/50821e33c6.js" crossorigin="anonymous"></script>
        <title>Liko</title>
    </head>
    <body>

        <header>
            <a class="headerImg-link" href="join/index.php">
                <img class="header-logo" src="join/headerlogo2.png">
            </a>
            <div class="header-bars res-phone">
                <a href="join/index.php"><i class="fas fa-user-plus"></i></a>
                <a href=""><i class="fas fa-sign-in-alt"></i></a>                
                <a class="testLogin" href="testLogin.php"><i class="fas fa-sign-in-alt"></i></a>
            </div>

            <div class="header-buttons">
                <a class="testLogin a-right" href="testLogin.php">
                    <i class="fas fa-sign-in-alt"></i>お試しログイン
                </a>            
                <a href=""><i class="fas fa-sign-in-alt"></i>ログイン</a>
                <a href="join/index.php"><i class="fas fa-user-plus"></i>登録する</a>
            </div>
        </header>

        <div class="container">
            <div class="col-md-2"></div>
            <div class="check-wrapper col-md-8">

                <div class="check-title">
                    <h4>ログイン画面</h4>
                </div>

                <div class="check-form">
                    <p>メールアドレスとパスワードが必要です。</p>
                </div>

                <div class="login-form">
                    <form action="" method="post">
                        <p>メールアドレス</p>
                        <input type="text" name="email" size="35"
                        maxlength="255" value="<?php echo h($_POST['email']); ?>">
                        <?php if($error['login'] == 'blank'): ?>
                            <p class="checkRed">メールアドレスとパスワードを記入してください</p>
                        <?php endif; ?>
                        <?php if($error['login'] == 'failed'): ?>
                            <p class="checkRed">ログインに失敗しました。正しくご記入ください</p>
                        <?php endif; ?>
                        <br>
                        <br>
                        <p>パスワード</p>
                        <input type="password" name="password" size="35"
                        maxlength="255" value="<?php echo h($_POST['password']); ?>">
                        <br>
                        <br>
                        <span>次回から自動でログインする</span>
                        <input id="save" type="checkbox" name="save" value="on">
                        <br>
                        <br>
                        <input type="submit" value="ログインする" class="submitBtn fright">
                    </form>  
                </div>               
            </div>
        </div><!-- container -->


        <footer class="login-footer">
            <div class="footer-logo">
                <img src="join/headerlogo.png">
            </div>
            <div class="footer-container">
                <p>2020 ©︎skonishi.</p>
            </div>
        </footer>

        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
                integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa"
                crossorigin="anonymous"></script>
        <script src="css/join_script.js"></script>

    </body>
</html>