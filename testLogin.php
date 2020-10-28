<?php 
session_start();
require('dbconnect.php');
//テストアカウントでのログイン

$_POST['email'] = 'test';
$_POST['password'] = 'test';

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
