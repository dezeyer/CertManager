<?php
/**
 * Created by PhpStorm.
 * User: simon
 * Date: 05.11.17
 * Time: 17:16
 */
define("PASSWORD","123456");
define("GETSSLDIR",dirname( dirname(__FILE__) )."/certstest");
define("showhelp",false);
$dnsscripts = [
    /*
     * example for cloudflare. for each domain is one script needed, providing auth details
     * domain and key is given by $1 & $2
     *
     * fulldomain="$1"
     * token="$2"
     * email="email@example.com"
     * key="key"
     *
     * see: https://github.com/srvrco/getssl/wiki/DNS-Challenge-example
     */
    "cf" => [
        "name" => "Cloudflare",
        "DNS_ADD_COMMAND" => dirname( dirname(__FILE__) )."/scripts/cf_add_acme_challenge",
        "DNS_DEL_COMMAND" => dirname( dirname(__FILE__) )."/scripts/cf_del_acme_challenge",
    ]
];