<?php
/**
 * Created by PhpStorm.
 * User: simon
 * Date: 05.11.17
 * Time: 14:19
 */
session_start();
ini_set("display_errors",true);
error_reporting(-1);

require_once("config.php");
require_once("class/Auth.php");
require_once("class/GetSSL.php");
require_once("class/TaskManager.php");
require_once("class/BackgroundProcess.php");
require_once('libs/smtemplate.php');

$tpl = new SMTemplate();
$pagename = "None";
$data = [];
$data["extrajs"] = "";
$ssl = new GetSSL();
$ssl->setdnsscripts($dnsscripts);

/* Logout */
if(isset($_GET["logout"])){
    session_destroy();
    header('Location: '.$_SERVER['PHP_SELF']);
    die;
}

/* Not Authenticated*/
if(!Auth::isloggedin())
    include "inc/pages/login.php";
else{
    /* Authenticated */
    if (!isset($_GET["p"])){
        /* no page given */
        header('Location: ?p=dash');
    }else{
        /* page given */
        include("inc/pages/".$_GET["p"].".php");
    }
    /*Domain list for Sidebar*/
    $data['domains'] =  $ssl->getdomains();
    /* $_GET for Page and Domain access*/
    $data['get'] =  $_GET;
    /* editcert only */
    $data['dnsscripts'] =  $dnsscripts;
    /* show extended info */
    $data['showhelp'] =  showhelp;
    /* validate adddomain*/
    if(isset($_POST["adddomain"])){
        if($ssl->select($_POST["adddomain"]) != 0){
            header("Location: ?p=certedit&d=".$_POST["adddomain"]);
        }else{
            $data["adddomain"]["value"] = $_POST["adddomain"];
            $data["adddomain"]["error"] = "Validen Domainnamen angeben!";
        }
    }

}
$tpl->render($_GET["p"], $data, 'page', $pagename);