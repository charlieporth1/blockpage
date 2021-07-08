<?php
function dns_prefetch() {
echo  <<<EOL
<meta http-equiv="x-dns-prefetch-control" content="on">
<link rel="dns-prefetch" href="//fonts.googleapis.com" />
<link rel="dns-prefetch" href="//fonts.gstatic.com" />
<link rel="dns-prefetch" href="//ajax.googleapis.com" />
<link rel="dns-prefetch" href="//apis.google.com" />
<link rel="dns-prefetch" href="//cdnjs.cloudflare.com" />
<link rel="dns-prefetch" href="//use.fontawesome.com" />
<link rel="dns-prefetch" href="//stackpath.bootstrapcdn.com" />
<link rel="dns-prefetch" href="//ajax.aspnetcdn.com" />
EOL;
}
    function unblock($domain, $time) {
//      $domain = $GLOBALS['url'];
//      $domain = $_GET['unblock'];
      $toUnblock = str_replace('&period;', '.', strval($domain));

      $quote_str = '"';
      $root_home = "/home/charlieporth1_gmail_com";
//      $parallel_ssh = "/usr/bin/parallel -j1 --sshloginfile /home/charlieporth1_gmail_com/.parallel/sshloginfile /bin/bash -c '{}' ::: ' ";
      $parallel_ssh = "/usr/bin/parallel-ssh -h " . $root_home . "/.ssh/parallel_hosts " . $quote_str;
//      $parallel_ssh = "";

      $pihole_cmd_str = "/usr/bin/sudo /usr/local/bin/pihole";
      $pihole_refresh_cmd_str = $pihole_cmd_str . " restartdns reload-lists";


      $whitelistStr = $pihole_cmd_str . " -w ".$toUnblock."; " . $pihole_refresh_cmd_str;
      $rmWhitelistStr = "( /usr/bin/sleep ".$time."; " . $pihole_cmd_str . " -w -d ".$toUnblock."; " . $pihole_refresh_cmd_str . " )&";


      $white_cmd_str = $whitelistStr;
      $rm_white_cmd_str = $quote_str . $rmWhitelistStr . $quote_str;
      $parallel_cmd_str = $parallel_ssh;
      exec($white_cmd_str);
      exec($parallel_cmd_str . $white_cmd_str);

      shell_exec($white_cmd_str);
      shell_exec($parallel_cmd_str . $white_cmd_str);


      exec($rm_white_cmd_str);
      exec($parallel_cmd_str . $rm_white_cmd_str);

      shell_exec($rm_white_cmd_str);
      shell_exec($parallel_cmd_str . $rm_white_cmd_str);
      // All done!
    }

 function getClientIP() {

 if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) && preg_match('/\b((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)(\.|$)){4}\b/', $_SERVER["HTTP_X_FORWARDED_FOR"])) {
        return  $_SERVER["HTTP_X_FORWARDED_FOR"];
 } else if (array_key_exists('REMOTE_ADDR', $_SERVER)) {
        return $_SERVER["REMOTE_ADDR"];
 } else if (array_key_exists('HTTP_CLIENT_IP', $_SERVER)) {
        return $_SERVER["HTTP_CLIENT_IP"];
 }

 return '';
}
 function getHost() {

 if (array_key_exists("HTTP_HOST", $_SERVER)) {
        return  $_SERVER["HTTP_HOST"];
 }
 return '';
}
 function geBlockFullOrgURL() {

 }
function getAndSetURL() {
 global $url, $url_provided, $server_ip;
 if(isset($_GET['url'])) {
    if(strpos($_GET['url'], ':') !== false) {
        // Strip port out of DNS name since PiHole does not deal with ports
        $url = substr($_GET['url'], 0, strpos($_GET['url'], ":"));

        // After stripping out port, then we sanitize/escape the input before doing anything with it
        $url = htmlentities($url, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    } else {
        // There is no port number so we go straight to sanitizing the user input
        $url = htmlentities($_GET['url'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

  $url_provided = true;
//    $url = $_SERVER['SERVER_NAME'];
    if($url == $server_ip) {
        $url = null;
        $url_provided = false;
    } else if($url == '35.232.120.211') {
        $url = null;
        $url_provided = false;
    }

} else {
  $url_provided = false;
  $url = null;
}
}
?>
