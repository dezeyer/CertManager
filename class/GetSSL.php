<?php

/**
 * Created by PhpStorm.
 * User: simon
 * Date: 04.11.17
 * Time: 21:26
 */
class GetSSL
{
    //Make sure to protect $GetSSLDir from external access, since there are all your certivicates stored!
    private $GetSSLDir = GETSSLDIR;
    private $GetSSLBin;
    private $debug = false;

    private $domainname = "";
    private $dnsscripts = array();

    /**
     * GetSSL constructor.
     * @param $domainname
     */
    public function __construct()
    {
        if(!is_dir($this->GetSSLDir)){
            mkdir($this->GetSSLDir);
        }
        //Set GetSSl Bin
        $this->GetSSLBin = $this->GetSSLDir."/getssl";
        if(!file_exists($this->GetSSLBin)){
            shell_exec("curl --silent https://raw.githubusercontent.com/srvrco/getssl/master/getssl > ".$this->GetSSLBin." ; chmod 777 ".$this->GetSSLBin."; ".$this->GetSSLBin." -f;");
        }
        $this->GetSSLBin = $this->GetSSLBin." -w ".$this->GetSSLDir." ";

        if(file_exists($this->GetSSLDir."/getssl.cfg") && strpos(file_get_contents($this->GetSSLDir."/getssl.cfg"),"ssh() {command ssh -i \"sshkey\" -o 'BatchMode yes' \"$@\"}") === false){
            file_put_contents(
                $this->GetSSLDir."/getssl.cfg",
                file_get_contents($this->GetSSLDir."/getssl.cfg").
                    "\n\n#Added by CertManager, do not change!\nssh() {command ssh -i \"sshkey\" -o 'BatchMode yes' \"$@\"}\n"

            );
        }
    }

    /**
     * @param $domainname
     * @return int 0 no valid domain
     * @return int 1 domain config already exists
     * @return int 2 creating domain config
     */
    public function select($domainname)
    {
        //check if domain is valid
        if (preg_match("/(?=^.{4,253}$)(^((?!-)[a-zA-Z0-9-]{1,63}(?<!-)\.)+[a-zA-Z]{2,63}$)/", $domainname, $output) != 1) {
            //$this->debug($output[0], "no valid domain");
            return 0;
        }

        $this->domainname = $domainname;

        //if domain config does not exist, create it, else select domain config folder
        $output = shell_exec($this->GetSSLBin . "-c " . $domainname);

        if (strpos("domain config already exists", $output) !== 0) {
            $this->debug($output, "domain config already exists, return 1");
            return 1;
        } elseif (strpos("creating domain config", $output) !== 0) {
            $this->debug($output, "creating domain config, return 2");
            $params["USE_SINGLE_ACL"] = "true";
            $this->generateConfigFile($params);
            return 2;
        }
        return 3;
    }

    /**
     * @return int 0 no domain seleceted
     * @return int 1 config deleted
     */
    public function delete()
    {
        if (!$this->isdomainselected())
            return 0;
        exec("rm -r " . $this->GetSSLDir ."/". $this->domainname, $output);
        $this->debug($output, "Config deleted");
        return 1;
    }

    public function listall()
    {
        if (!$this->isdomainselected())
            return 0;
        exec($this->GetSSLBin . "-a", $output);
        $this->debug($output);
        return 1;
    }

    public function getconfig()
    {
        if (!$this->isdomainselected())
            return 0;
        $config = file_get_contents($this->GetSSLDir ."/". $this->domainname . "/getssl.cfg");
        $this->debug($config);
        $params = [];

        //pharse params from config line by line
        foreach (explode("\n", $config) as $conf_zeile) {
            //KEY="VAL"
            preg_match("/(^[A-Z_]*)=\"(.*)\"/i", $conf_zeile, $rawparam);
            if (count($rawparam) > 0)
                if ($rawparam[1] == "SANS")
                    $params["SANS"] = preg_split("/\s?,\s?/i", $rawparam[2]);
                else
                    $params[$rawparam[1]] = $rawparam[2];
        }
        $params["SANS"] = array_filter($params["SANS"]);

        if (!isset($params["SANS"])) {
            $params["SANS"] = array();
        }

        //ACL multiline shit
        preg_match_all("/\nACL=\('(.*)'.*\)\n/s", $config, $rawparam);
        if (isset($rawparam[1][0])) {
            preg_match_all("/\s*'?([^']*)'?/", $rawparam[1][0], $output_array);
            foreach ($output_array[1] as $item) {
                if ($item != "" && trim($item) != "#") {
                    $params["ACL"][] = $item;
                }
            }
        }

        //some default things...
        if (!isset($params["USE_SINGLE_ACL"]) && !isset($params["VALIDATE_VIA_DNS"])) {
            $params["USE_SINGLE_ACL"] = "false";
            unset($params["VALIDATE_VIA_DNS"]);

        }
        if (isset($params["USE_SINGLE_ACL"])) {
            unset($params["VALIDATE_VIA_DNS"]);
        }
        if (isset($params["VALIDATE_VIA_DNS"]) && $params["VALIDATE_VIA_DNS"] == "true") {
            unset($params["USE_SINGLE_ACL"]);
        }

        if (file_exists($this->GetSSLDir ."/". $this->domainname . "/note.txt")) {
            $params["note"] = file_get_contents($this->GetSSLDir ."/". $this->domainname . "/note.txt");
        }
        return $params;
    }

    public function addconfigsan($SAN)
    {
        if (!$this->isdomainselected())
            return 0;
        $config = $this->getconfig();
        if (preg_match("/(?=^.{4,253}$)(^((?!-)[a-zA-Z0-9-]{1,63}(?<!-)\.)+[a-zA-Z]{2,63}$)/", $SAN) != 0) {
            $config["SANS"][] = $SAN;
            $this->generateConfigFile($config);
            return 1;
        }
        return 0;
    }

    public function delconfigsan($sanid)
    {
        if (!$this->isdomainselected())
            return 0;
        $config = $this->getconfig();
        unset($config["SANS"][$sanid]);
        $this->generateConfigFile($config);
        return 1;
    }

    public function updatesan($key, $SAN)
    {
        if (!$this->isdomainselected())
            return 0;
        $config = $this->getconfig();
        if (preg_match("/(?=^.{4,253}$)(^((?!-)[a-zA-Z0-9-]{1,63}(?<!-)\.)+[a-zA-Z]{2,63}$)/", $SAN) != 0) {
            $config["SANS"][$key] = $SAN;
            $this->generateConfigFile($config);
            return 1;
        }
        return 0;
    }

    public function setsingleacl($aclarray)
    {
        if (!$this->isdomainselected())
            return 0;
        $params["USE_SINGLE_ACL"] = "true";
        if(count($aclarray) != 0)
            $params["ACL"] = $aclarray;
        $this->generateConfigFile($params);
        return 1;
    }

    public function setmultipleacl($aclarray)
    {
        if (!$this->isdomainselected())
            return 0;
        $params["USE_SINGLE_ACL"] = "false";
        if(count($aclarray) != 0)
            $params["ACL"] = $aclarray;
        $this->generateConfigFile($params);
        return 1;
    }

    /**
     * @param array $dnsscripts
     */
    public function setdnsscripts(array $dnsscripts)
    {
        $this->dnsscripts = $dnsscripts;
    }

    public function setvalidateviadns($dnsconfigid)
    {
        if (!$this->isdomainselected())
            return 0;
        $params["VALIDATE_VIA_DNS"] = "true";
        $params["DNSCONF"] = $dnsconfigid;
        $this->generateConfigFile($params);
        return 1;
    }

    public function updateparam($key, $value)
    {
        $params[$key] = $value;
        $this->generateConfigFile($params);
    }

    public function getdomains()
    {
        return array_diff(scandir($this->GetSSLDir), array('..', '.', 'getssl', 'getssl.cfg', 'scripts', 'account.key'));
    }

    public function getcertinfo()
    {
        if (!$this->isdomainselected())
            return 0;
        $cmd = "openssl x509 -noout -text -in $this->GetSSLDir/$this->domainname/$this->domainname.crt";
        //exec($cmd,$output);
        $output = shell_exec($cmd);
        //print_r($output . "\n");
        return ($output);

    }

    public function renew($force = false)
    {
        if (!$this->isdomainselected())
            return 0;
        $taskname = "forcerenewal_".$this->domainname;
        if($force){
            $cmd = $this->GetSSLBin . " -f " . $this->domainname . "";
        }else{
            $cmd = $this->GetSSLBin . " " . $this->domainname . "";
        }

        return TaskManager::get($cmd,$taskname);
    }

    public function stoprenew()
    {
        if (!$this->isdomainselected())
            return 0;
        $taskname = "forcerenewal_".$this->domainname;

        TaskManager::killTask($taskname);
        return 1;
    }

    private function generateConfigFile($params)
    {
        $oldparams = $this->getconfig();
        if (isset($params["USE_SINGLE_ACL"])) {
            unset($oldparams["VALIDATE_VIA_DNS"]);
        } elseif (isset($params["VALIDATE_VIA_DNS"])) {
            unset($oldparams["USE_SINGLE_ACL"]);
        }
        $params = array_replace($oldparams, $params);
        $config = "#GENERATED BY CertManager\n";

        //SET SANS

        if (isset($params["SANS"])) {
            $config .= "SANS=\"";
            $i = 0;
            foreach ($params["SANS"] as $SAN) {
                $config .= $SAN . ",";
                $i++;
            }
            if ($i > 0)
                $config = substr($config, 0, -1);
            $config .= "\"\n";
        }

        if(!isset($params["ACL"])){
            $params["ACL"][0] = dirname( dirname(__FILE__) )."/.well-known/acme-challenge";
        }

        //SET ACL/DNS
        if (isset($params["USE_SINGLE_ACL"])) {
            if ($params["USE_SINGLE_ACL"] == "false" && isset($params["ACL"])) {

                $aclstring = "ACL=(";
                for ($i = 0; $i < count($params["SANS"]) + 1; $i++) {
                    if (isset($params["ACL"][$i]) && trim($params["ACL"][$i]) != "")
                        $aclstring .= "'" . $params["ACL"][$i] . "'\n";
                    else
                        $aclstring .= "'" . $params["ACL"][0] . "'\n";
                }
                $aclstring .= ")";
                $config .= "USE_SINGLE_ACL=\"false\"\n";
                $config .= $aclstring . "\n\n";
                #preg_replace("/#?ACL=\('.*'\)/s", $aclstring, $config);
                #preg_replace("/#?USE_SINGLE_ACL=\"(true|false)\"\n/s", "USE_SINGLE_ACL=\"false\"\n", $config);
                #preg_replace("/#?VALIDATE_VIA_DNS=\"(true|false)\"\n/s", "#VALIDATE_VIA_DNS=\"false\"\n", $config);
            } elseif ($params["USE_SINGLE_ACL"] == "true" && isset($params["ACL"])) {

                $config .= "USE_SINGLE_ACL=\"true\"\n";
                $config .= "ACL=('" . $params["ACL"][0] . "')\n\n";

                #preg_replace("/#?ACL=\('.*'\)/s", $aclstring, $config);
                #preg_replace("/#?USE_SINGLE_ACL=\"(true|false)\"\n/s", "USE_SINGLE_ACL=\"true\"\n", $config);
                #preg_replace("/#?VALIDATE_VIA_DNS=\"(true|false)\"\n/s", "#VALIDATE_VIA_DNS=\"false\"\n", $config);

            }
        } elseif (isset($params["VALIDATE_VIA_DNS"]) && $params["VALIDATE_VIA_DNS"] == true) {

            $config .= "VALIDATE_VIA_DNS=\"true\"\n";
            $config .= "DNSCONF=\"" . $params["DNSCONF"] . "\"\n";
            $config .= "DNS_ADD_COMMAND=\"" . $this->dnsscripts[$params["DNSCONF"]]["DNS_ADD_COMMAND"] . "\"\n";
            $config .= "DNS_DEL_COMMAND=\"" . $this->dnsscripts[$params["DNSCONF"]]["DNS_DEL_COMMAND"] . "\"\n";

        }

        //update generic params
        $updateparams = ["DOMAIN_CERT_LOCATION", "DOMAIN_KEY_LOCATION", "CA_CERT_LOCATION", "RELOAD_CMD"];
        foreach ($updateparams as $updateparam) {
            if (isset($params[$updateparam]) && trim($params[$updateparam]) == "") {
                unset($params[$updateparam]);
            }
            if (isset($params[$updateparam])) {
                $config .= $updateparam . "=\"" . $params[$updateparam] . "\"\n";
            }
        }
        if (isset($params["note"])) {
            file_put_contents($this->GetSSLDir ."/". $this->domainname . "/note.txt", $params["note"]);
        }

        file_put_contents($this->GetSSLDir ."/". $this->domainname . "/getssl.cfg", $config);
    }

    private
    function isdomainselected()
    {
        if ($this->domainname == "") {
            $this->debug("", "No Domain selected");
            return false;
        }
        return true;
    }

    private
    function debug($output, $comment = "")
    {
        if ($this->debug) {
            print_r("-----------------------------------\n");
            $trace = debug_backtrace();
            //print_r($trace);
            print_r("called {$trace[count($trace)-1]['class']}::{$trace[count($trace)-1]['function']}\n");
            print_r("Domain: " . $this->domainname . "\n");
            if ($comment != "") {
                print_r("Comment: " . $comment . "\n");
            }
            print_r($output);
        }
    }
}