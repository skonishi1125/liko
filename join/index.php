<?php 
session_start();
require('../dbconnect.php');

if(!empty($_POST)){
    //フォームが空でなかった場合の処理
    if($_POST['name'] == ''){
        $error['name'] = 'blank';
    }
    if($_POST['email'] == ''){
        $error['email'] = 'blank';
    }
    if(strlen($_POST['password']) < 4 ){
        $error['password'] = 'length';
    }
    if($_POST['password'] == ''){
        $error['password'] = 'blank';
    }

    //画像受け渡し。$_FILESという特別な変数を利用する
    //$_FILE['formでつけたname=の名前']['元々用意された名前name,type,sizeなど']を指定できる
    $fileName = $_FILES['image']['name'];
    if(!empty($fileName)) {
        $ext = substr($fileName, -4);
        if($ext != '.jpg' && $ext !='.png' && $ext !='.PNG'
            && $ext !='.gif' && $ext != 'JPEG' && $ext != 'jpeg' && $ext != '.JPG'){
                $error['image'] = 'type';
        }
    }

    //$errorに何もない場合(重複の時！)
    if(empty($error)){
        $member = $db->prepare('SELECT COUNT(*) AS cnt FROM members WHERE email=?');
        $member->execute(array($_POST['email']));
        $record = $member->fetch();

        if($record['cnt'] > 0){
            $error['email'] = 'duplicate';
        }

    }

    //$errorに何もない場合、エラーがなかった場合(checkへと進む処理)
    if(empty($error)){
        //画像アップ
        $postImgTime = date('YmdHis');
        if($ext == 'jpeg' || $ext == 'JPEG'){
            $ext = '.' . $ext;
        }
        $image = $postImgTime . sha1($_FILES['image']['name']).$ext;

        //ファイル名を分からなくする処理
        move_uploaded_file($_FILES['image']['tmp_name'], '../member_picture/'.$image);
        $_SESSION['join'] = $_POST;
        $_SESSION['join']['image'] = $image;
        $_SESSION['join']['time'] = $postImgTime;
        //セッションにPOSTの値を保存して、次の画面へ
        //imageはパス用の名前を保存、timeは画像の有無判定に使用する
        header('Location:   check.php');
        exit();
    }
}

//書き直し処理
if($_REQUEST['action'] == 'rewrite'){
    $_POST = $_SESSION['join'];
    $error['rewrite'] = true;
}


    
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="../css/join_style.css">
        <link rel="stylesheet"
        href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
        integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u"
        crossorigin="anonymous">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
        <script src="https://kit.fontawesome.com/50821e33c6.js" crossorigin="anonymous"></script>
        <script src="../css/jquery.bgswitcher.js"></script>
        <title>Liko</title>
    </head>
    <body>


        <div id="top"></div>

        <header>
            <a class="headerImg-link" href="index.php">
                <img class="header-logo" src="headerlogo2.png">
            </a>
            <div class="header-bars res-phone">
                <a href="#top"><i class="fas fa-user-plus"></i></a>
                <a href="../"><i class="fas fa-sign-in-alt"></i></a>                
                <a class="testLogin" href="../testLogin.php"><i class="fas fa-sign-in-alt"></i></a>                            
                <!-- <i class="fas fa-bars"></i> -->
            </div>

            <div class="header-buttons">
                <a class="testLogin a-right" href="../testLogin.php">
                    <i class="fas fa-sign-in-alt"></i>お試しログイン
                </a>            
                <a href="../"><i class="fas fa-sign-in-alt"></i>ログイン</a>
                <a href="#top"><i class="fas fa-user-plus"></i>登録する</a>
            </div>
        </header>

        <div class="subHeader-wrapper bg-switcher">
            <div class="subHeader-logo">
                <img src="headerlogo.png">
                <p>スキを共有しましょう</p>
            </div>

            <div class="register-wrapper">
                <div class="register-title">
                    <h4>登録して投稿しよう</h4>
                </div>
                <div class="register-form">
                    <form action="" method="post" enctype="multipart/form-data">
                        <p><b>ハンドルネーム</b></p>
                        <input type="text" name="name" size="35" 
                        maxlength="255" value="<?php echo h($_POST['name']); ?>">
                        <?php //フォームに入れた値を残しておく処理：valueに$_POSTの値を入れておく ?>
                        <?php if($error['name'] == 'blank') : ?>
                            <p class="checkRed">※未入力</p>
                        <?php endif; ?>

                        <p><b>メールアドレス</b></p>
                        <input type="text" name="email" size="35" 
                        maxlength="255" value="<?php echo h($_POST['email']); ?>">
                        <?php if($error['email'] == 'blank') : ?>
                            <p class="checkRed">※未入力</p>
                        <?php endif; ?>
                        <?php if($error['email'] == 'duplicate') : ?>
                            <p class="checkRed">※既に使用されているメールアドレスです。</p>
                        <?php endif; ?>

                        <p><b>パスワード</b> (4文字以上)</p>
                        <input type="password" name="password" size="10" 
                        maxlength="20" value="<?php echo h($_POST['password']); ?>">
                        <?php if($error['password'] == 'blank') : ?>
                            <p class="checkRed">※未入力</p>
                        <?php endif; ?>
                        <?php if($error['password'] == 'length') : ?>
                            <p class="checkRed">※パスワードは4文字以上としてください</p>
                        <?php endif; ?>

                        <p><b>アイコン画像の選択</b> ( jpeg, gif, pngに対応)</p>
                        <input class="inputIcon" type="file" name="image" size="35">
                        <?php if($error['image'] == 'type') : ?>
                            <p class="checkRed">※対応していない拡張子のファイルです。</p>
                        <?php endif; ?>
                        <?php if(!empty($error)) : ?>
                            <p class="checkRed">※恐れ入りますが、画像を改めて指定してください</p>
                        <?php endif; ?>
                        <input class="submitBtn fright" type="submit" value="入力内容を確認する">
                    </form>                    
                </div>
            </div>

        </div>

        <div class="res-phone container">
            <div class="col-md-12 resRegi-wrapper">
                <div class="center-bar"></div>                
                <div class="contents-title">
                    <h2>登録して投稿しよう</h2>
                </div>

                <div class="resregi-form">
                    <form action="" method="post" enctype="multipart/form-data">
                        <p><b>ハンドルネーム</b></p>
                        <input type="text" name="name" size="35" 
                        maxlength="255" value="<?php echo h($_POST['name']); ?>">
                        <?php //フォームに入れた値を残しておく処理：valueに$_POSTの値を入れておく ?>
                        <?php if($error['name'] == 'blank') : ?>
                            <p class="checkRed">※未入力</p>
                        <?php endif; ?>

                        <p><b>メールアドレス</b></p>
                        <input type="text" name="email" size="35" 
                        maxlength="255" value="<?php echo h($_POST['email']); ?>">
                        <?php if($error['email'] == 'blank') : ?>
                            <p class="checkRed">※未入力</p>
                        <?php endif; ?>
                        <?php if($error['email'] == 'duplicate') : ?>
                            <p class="checkRed">※既に使用されているメールアドレスです。</p>
                        <?php endif; ?>

                        <p><b>パスワード</b> (4文字以上)</p>
                        <input type="password" name="password" size="10" 
                        maxlength="20" value="<?php echo h($_POST['password']); ?>">
                        <?php if($error['password'] == 'blank') : ?>
                            <p class="checkRed">※未入力</p>
                        <?php endif; ?>
                        <?php if($error['password'] == 'length') : ?>
                            <p class="checkRed">※パスワードは4文字以上としてください</p>
                        <?php endif; ?>

                        <p><b>アイコン画像の選択</b> ( jpeg, gif, pngに対応)</p>
                        <input class="inputIcon" type="file" name="image" size="35">
                        <?php if($error['image'] == 'type') : ?>
                            <p class="checkRed">※対応していない拡張子のファイルです。</p>
                        <?php endif; ?>
                        <?php if(!empty($error)) : ?>
                            <p class="checkRed">※恐れ入りますが、画像を改めて指定してください</p>
                        <?php endif; ?>
                        <br>
                        <input class="submitBtn fright" type="submit" value="入力内容を確認する">
                    </form>                    
                </div>

            </div>

        </div>



        <div class="container">

            <div class="col-md-12 intro-wrapper">
                <div class="center-bar"></div>
                <div class="contents-title">
                    <h2>Likoとは</h2>
                </div>
                <div class="col-md-8 pic-container">
                    <img src="intro1.png">
                </div>
                <div class="col-md-4 message-container">
                    <p>Likoでは様々な人たちが「スキ」だと感じたことについての投稿を確認できます。</p>
                    <p>自分の好きなものを投稿して、感想を語り合うことも可能です。</p>
                    <p>好きな場所や動画、食べ物だけでなくキャラクターや有名人など、投稿する内容はユーザーの自由です。<br>プチブログのような使い方も。</p>
                </div>
            </div>

        </div><!-- container -->

        <div class="backYellow">
            <div class="container">

                <div class="col-md-12 intro-wrapper">
                    <div class="center-bar"></div>
                    <div class="contents-title">
                        <h2>基本機能について</h2>
                    </div>
                    <div class="col-md-4 message-container">
                        <p>自分の好みの投稿に「いいね！」したり、コメントをつけることができます。</p>
                        <p>また、自分と同じものが好きな人を探すことができる検索機能、プロフィールアイコンの変更機能なども。</p>
                        <br>
                        <p>自分の好きなものについて、ユーザーと語り合いましょう！</p>
                    </div>
                    <div class="col-md-8 pic-container">
                        <img src="index1.png">
                    </div>

                </div>

            </div><!-- container -->
        </div>

        <div class="container">
            <div class="center-bar"></div>
            <div class="contents-title">
                <h2>FAQ</h2>
            </div>

            <div class="qaTitle-wrapper col-md-12" id="accordion_menu" data-toggle="collapse" 
                href="#menu01" aria-controls="#menu01" aria-expanded="false">
                <h4>このサイトについて</h4>
            </div>

            <div class="qaAnswer-wrapper collapse col-md-12" id="menu01" 
                data-parent="#accordion_menu">
                <p>ポートフォリオ用のSNSサイトとなります。</p>
                <p>突然メンテナンスを行ったり、サービスが停止することがございます。ご了承ください。</p>
            </div>


            <div class="qaTitle-wrapper col-md-12" id="accordion_menu" data-toggle="collapse" 
                href="#menu02" aria-controls="#menu02" aria-expanded="false">
                <h4>情報の取扱いについて</h4>          
            </div>

            <div class="qaAnswer-wrapper collapse col-md-12" id="menu02" 
                data-parent="#accordion_menu">
                <p>メールアドレスについては管理者から確認が可能です。<br>メールアドレスはユーザーのログインのみにしか利用せず、管理者がその他の用途に利用することはありません。</p>
                <p>パスワードはハッシュ関数「sha256」を利用した暗号化形式を採用し保存しています。<br>管理者から確認することはできないような仕組みで管理しています。</p>
                <p>自分のメールアドレスなどを利用せずにサービスを利用したい場合は、架空のアドレスを登録することでもサービスの利用が可能です。<br>
                (ログイン時に利用しますので、忘れないように注意してください)</p>
            </div>

            <div class="qaTitle-wrapper col-md-12" id="accordion_menu" data-toggle="collapse" 
                href="#menu03" aria-controls="#menu03" aria-expanded="false">
                <h4>バグやエラーが発生した場合</h4>          
            </div>

            <div class="qaAnswer-wrapper collapse col-md-12" id="menu03" 
                data-parent="#accordion_menu">
                <p>下記のアドレスまで伝えて頂ければ幸いです。確認が出来次第、返信いたします。</p>
                <p>email : skonishi1125@gmail.com</p>
            </div>            

        </div><!-- container -->

        <div class="container">
            <div class="center-bar"></div>
            <div class="contents-title">
                <h2>はじめてみよう</h2>
            </div>            

            <div class="start-wrapper">
                <div class="col-md-4 startButtons">
                    <a class="button" href="#top"><i class="fas fa-user-plus"></i>登録する</a>
                </div>
                <div class="col-md-4 startButtons">
                    <a class="button" href="../login.php"><i class="fas fa-sign-in-alt"></i>ログイン</a>                 
                </div>

                <div class="col-md-4 startButtons">
                    <a class="regiButton" href="../testLogin.php"><i class="fas fa-sign-in-alt"></i>お試しログイン</a>
                </div>
            </div>

            <div class="start-notes col-md-12">
                <h4>お試しログインについて</h4>
                <p>ユーザー登録作業をスキップし、テストアカウントでLikoへログインすることができます。</p>
                <p>Likoの仕組みを確認したい場合はこちらでのログインをお試しください。</p>
            </div>

        </div><!-- container -->        

        <div class="container">
            <div class="col-md-12 top-btn">
                <a href="#top">
                    Topへ戻る
                    <i class="fas fa-angle-up"></i>
                </a>
            </div>
        </div>


        <footer>
            <div class="footer-logo">
                <img src="headerlogo.png">
            </div>
            <div class="footer-container">
                <p>2020 ©︎skonishi.</p>
            </div>
        </footer>

        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
                integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa"
                crossorigin="anonymous"></script>
        <script src = "../css/join_script.js"></script>
        <script src="jquery.bgswitcher.js"></script>
        <script>
        jQuery(function($) {
            $('.bg-switcher').bgSwitcher({
                images: ['head1.png','head2.png','head3.png','head4.png'], // 切り替え画像
                Interval: 3000, //切り替えの間隔 1000=1秒
                start: true, //$.fn.bgswitcher(config)をコールした時に切り替えを開始する
                loop: true, //切り替えをループする
                shuffle: true, //背景画像の順番をシャッフルする
                effect: "fade", //エフェクトの種類 "fade" "blind" "clip" "slide" "drop" "hide"
                duration: 3000, //エフェクトの時間 1000=1秒
                easing: "swing", //エフェクトのイージング "swing" "linear"
            });
        });
        </script>            

    </body>
</html>