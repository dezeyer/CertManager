<?php
$pagename = "Dashboard";

if(!file_exists("sshkey")){
    shell_exec("ssh-keygen -b 2048 -t rsa -f sshkey -q -N \"\"");
}

$data["sshkey"] = file_get_contents("sshkey.pub");
