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
      $whitelistStr = "/usr/bin/sudo /usr/local/bin/pihole -w ".$toUnblock."";
      exec($whitelistStr);
      shell_exec($whitelistStr);

      $rmWhitelistStr = "( /usr/bin/sleep ".$time."; /usr/bin/sudo /usr/local/bin/pihole -w -d ".$toUnblock." & ) &";
      exec($rmWhitelistStr);
      shell_exec($rmWhitelistStr);
      // All done!
    }
 function getClientIP(){

 if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) && preg_match('/\b((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)(\.|$)){4}\b/', $_SERVER["HTTP_X_FORWARDED_FOR"])){
        return  $_SERVER["HTTP_X_FORWARDED_FOR"];  
 }else if (array_key_exists('REMOTE_ADDR', $_SERVER)) { 
        return $_SERVER["REMOTE_ADDR"]; 
 }else if (array_key_exists('HTTP_CLIENT_IP', $_SERVER)) {
        return $_SERVER["HTTP_CLIENT_IP"]; 
 }

 return '';
}
 function getHost(){

 if (array_key_exists("HTTP_HOST", $_SERVER)) {
        return  $_SERVER["HTTP_HOST"];
 }
 return '';
}
?>
