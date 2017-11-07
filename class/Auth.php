<?php
/**
 * Created by PhpStorm.
 * User: simon
 * Date: 22.08.17
 * Time: 21:38
 */

class Auth
{
    public static function login($password){
        if($password == PASSWORD){
            $_SESSION["loggedin"] = true;
            return true;
        }else{
            return false;
        }
    }

    public static function isloggedin(){
        if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
            return true;
        }
        return false;
    }

    public static function logout(){
        session_destroy();
        unset($_SESSION["loggedin"]);
    }

}