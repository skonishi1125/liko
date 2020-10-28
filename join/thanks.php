<?php 
session_start();
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
        <title>Liko</title>
    </head>
    <body>

        <header>
            <a class="headerImg-link" href="index.php">
                <img class="header-logo" src="headerlogo2.png">
            </a>
            <div class="header-bars res-phone">
                <a href="index.php"><i class="fas fa-user-plus"></i></a>
                <a href="../"><i class="fas fa-sign-in-alt"></i></a>                
                <a class="testLogin" href="index.php"><i class="fas fa-sign-in-alt"></i></a>
                <!-- <i class="fas fa-bars"></i> -->
            </div>

            <div class="header-buttons">
                <a class="testLogin a-right" href="index.php">
                    <i class="fas fa-sign-in-alt"></i>お試しログイン
                </a>            
                <a href="../"><i class="fas fa-sign-in-alt"></i>ログイン</a>
                <a href="index.php"><i class="fas fa-user-plus"></i>登録する</a>
            </div>
        </header>


        <div class="container">
            <div class="col-md-2"></div>
            <div class="check-wrapper col-md-8">

                <div class="check-title">
                    <h4>登録が完了しました。</h4>
                </div>

                <div class="check-form">
                    <p>登録ありがとうございます。Likoをお楽しみください！</p>
                </div>

                <div class="thanks-buttons">
                    <a class="thanks-login" href="../">
                        <i class="fas fa-sign-in-alt"></i>ログイン画面へ
                    </a>
                </div>
            </div>

            <div class="col-md-2"></div>
        </div>        


        <footer class="thanks-footer">
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
        <script src = "join_script.js"></script>

    </body>
</html>