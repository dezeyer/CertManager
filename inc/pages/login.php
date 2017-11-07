<body class="hold-transition login-page"><?php
/**
 * Created by PhpStorm.
 * User: simon
 * Date: 23.08.17
 * Time: 18:02
 */
if(isset($_POST["password"]) && !Auth::login($_POST["password"])){
    $data['errmsg'] = "Try an other one!";
}
if (Auth::isloggedin())
    header('Location: '.$_SERVER['REQUEST_URI']);

$tpl->render('login', $data, 'auth', 'Login');
exit();