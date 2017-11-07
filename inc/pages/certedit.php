<?php
/**
 * Created by PhpStorm.
 * User: simon
 * Date: 05.11.17
 * Time: 13:34
 */
if(!isset($_GET["d"])){
    $pagename = "Error";
    $data["error"] = "Keine Domain gewählt!";
}else{
    //set page name
    $pagename = $_GET["d"];
    //select domain
    $select = $ssl->select($_GET["d"]);

    /*
     * Update Config if needed
     */
    $params = $ssl->getconfig();
    if(isset($_POST["VALIDATION"])){
        if($_POST["VALIDATION"] == 0){
            /*
             * DNS Validation
             * we do not have a dnsscript defined at this point, use the first one
             */
            $ssl->setvalidateviadns(array_keys($dnsscripts)[0]);
        }elseif($_POST["VALIDATION"] == 1){
            /*
             * Single ACL
             * use /var/www/html/.well-known/acme-challenge, other systems could proxy .well-known requests here
             */
            $ssl->setsingleacl([]);
        }elseif($_POST["VALIDATION"] == 2){
            /*
             * DNS Validation
             * use /var/www/html/.well-known/acme-challenge, other systems could proxy .well-known requests here
             * setmultipleacl will repeat the first entry for each domain that has no acl set
             */
            $ssl->setmultipleacl([]);
        }
    }
    if(isset($_POST["DNSCONF"])){
        $ssl->setvalidateviadns($_POST["DNSCONF"]);
    }
    if(isset($_POST["ACL"])){
        if($params["USE_SINGLE_ACL"] == "true"){
            $ssl->setsingleacl($_POST["ACL"]);
        }

        if($params["USE_SINGLE_ACL"] == "false"){
            $ssl->setmultipleacl($_POST["ACL"]);
        }
    }

    //update SAN
    if(isset($_POST["SANS"])){
        foreach ($_POST["SANS"] as $key=>$SAN){
            if($ssl->updatesan($key,$SAN) != 1){
               $data["SANupdateError"] = "Validierung der Domain fehlgeschlagen";
            }else{
                $data["SANupdateInfo"] = "SAN geändert";
            }
        }
    }

    //delete SAN
    if(isset($_GET["delsan"])){
        $ssl->delconfigsan($_GET["delsan"]);
        $_SESSION["SANDELETED"] = true;
        header("Location: ?p=certedit&d=".$_GET["d"]);
        exit;
    }
    if(isset($_SESSION["SANDELETED"])){
        unset($_SESSION["SANDELETED"]);
        $data["SANupdateInfo"] = "SAN entfernt";
    }

    //delete SAN
    if(isset($_POST["addsan"])){
        if($ssl->addconfigsan($_POST["addsan"]) == 1){
            $data["SANaddInfo"] = "SAN hinzugefügt";
        }else{
            $data["SANaddError"] = "Validierung der Domain fehlgeschlagen";
            $data["post"]=$_POST;
        }
    }

    //update cert
    if(isset($_GET["getcert"])){
        echo json_encode($ssl->renew());
        exit();
    }

    //force update cert
    if(isset($_GET["forcegetcert"])){
        echo json_encode($ssl->renew(true));
        exit();
    }

    if(isset($_GET["killgetcert"])){
        $ssl->stoprenew();
        exit();
    }
    if(isset($_GET["delcert"])){
        $ssl->delete();
        header("Location: ?p=dash");
        exit;
    }

    //update generic params
    $updateparams = ["DOMAIN_CERT_LOCATION","DOMAIN_KEY_LOCATION","CA_CERT_LOCATION","RELOAD_CMD","note"];
    foreach($updateparams as $updateparam){
        if(isset($_POST[$updateparam])){
            $ssl->updateparam($updateparam,$_POST[$updateparam]);
        }
    }

    /*
     * Read Config after Updating Conf
     */
    $data["params"] = $ssl->getconfig();
    $data["certinfo"] = $ssl->getcertinfo();
    $data["extrajs"] .= '
    
    $(document).ready(function() {
        $("#getcert").click(function(){getCertLog();});
        $("#forcegetcert").click(function(){getCertLog(true);});
        $("#killgetcert").click(function(){getCertStop();});
    });
        
    function getCertLog(force = false) {
        var get = "";
        if(force){
            get = "?p='.$_GET["p"].'&d='.$_GET["d"].'&forcegetcert";
        }else{
            get = "?p='.$_GET["p"].'&d='.$_GET["d"].'&getcert";
        }
        $.get(get, function(data, status) {
            var json = JSON.parse(data);
            console.log(json);
            $("#getssloutput").html(json.log);
            if(json.code > 0){
                setTimeout(function(){
                        getCertLog();
                    },1000);
            }
        });
    }
    
    function getCertStop() {
        $.get("?p='.$_GET["p"].'&d='.$_GET["d"].'&killgetcert",function(data,status){console.log(data);console.log(status);});
    }
    
    $(\'#modal-default\').on(\'hidden.bs.modal\', function () {
        location.reload();
    });';

}