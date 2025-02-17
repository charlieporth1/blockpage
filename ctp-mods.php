<?php
//if (empty($conf)) {
//    require('config.php');
//}
$ctp_mods_imported = true;

function dns_prefetch()
{
    echo <<<EOL
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

function getClientIP()
{

    if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) && preg_match('/\b((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)(\.|$)){4}\b/', $_SERVER["HTTP_X_FORWARDED_FOR"])) {
        return $_SERVER["HTTP_X_FORWARDED_FOR"];
    } else if (array_key_exists('REMOTE_ADDR', $_SERVER)) {
        return $_SERVER["REMOTE_ADDR"];
    } else if (array_key_exists('HTTP_CLIENT_IP', $_SERVER)) {
        return $_SERVER["HTTP_CLIENT_IP"];
    } else {
        return '';
    }
}

function unblockLogger($args)
{
    $log_file = "/var/log/pihole-unblock-block-blockpage.log";
    $log_cmd = "echo \"[\$(date)]:  $args \" | sudo tee -a " . $log_file;
    shell_exec($log_cmd);
}

function unblock($domain, $time, $conf)
{
    $sudo_bin = '/bin/sudo';
//      $domain = $_GET['unblock'];
    $domain = default_value($domain, $GLOBALS['url']);
    $domain = default_value($domain, getHost());

    $time = default_value($time, 7200);
    $toUnblock = str_replace('&period;', '.', strval($domain));

    $quote_str = ""; //escapeshellarg('');

    $parallel_hosts_file = $conf['parallel_hosts_full_file_path'];
    $parallel_ssh = "/usr/bin/parallel-ssh --host=" . $parallel_hosts_file . " " . $quote_str;

    $client_ip = getClientIP();
    $end_cmd = "";
    unblockLogger("Ran client_ip, $client_ip, domain $domain, toUnblock $toUnblock, time $time");

    $pihole_cmd_str = $sudo_bin . " /usr/local/bin/pihole";
    $ctp_dns_clear_cache_cmd_str = "/usr/local/bin/ctp-dns --flush-cache-all";
    $pihole_refresh_cmd_str = $pihole_cmd_str . " restartdns reload-lists; " . $ctp_dns_clear_cache_cmd_str;

    $sleep_cmd_str = " ( /usr/bin/sleep " . $time . "; ";
    $whitelistStr = $pihole_cmd_str . " -w " . $toUnblock . "; " . $pihole_refresh_cmd_str . $end_cmd;
    $rmWhitelistStr = $pihole_cmd_str . " -w -d " . $toUnblock . "; " . $pihole_refresh_cmd_str . $end_cmd . " ) &";

    $white_cmd_str = $whitelistStr;
    $rm_white_cmd_str = $rmWhitelistStr;

    $parallel_cmd_str = $parallel_ssh . $quote_str;


    shell_exec($white_cmd_str);
    shell_exec($sleep_cmd_str . $rm_white_cmd_str);

    unblockLogger($white_cmd_str);
    unblockLogger($sleep_cmd_str . $rm_white_cmd_str);

    if ($conf['is_parallel_pihole'] == true) {

        $white_cmd_str_p = $parallel_cmd_str . $white_cmd_str . $quote_str;
        $rm_white_cmd_str_p = $sleep_cmd_str . $parallel_cmd_str . $rm_white_cmd_str . $quote_str;

        unblockLogger($white_cmd_str_p);
        unblockLogger($rm_white_cmd_str_p);

        shell_exec($white_cmd_str_p);
        shell_exec($rm_white_cmd_str_p);
    }
    // All done!
}

function default_value($var, $default)
{
    return empty($var) ? $default : $var;
}

function getHost()
{
    if (array_key_exists("HTTP_HOST", $_SERVER)) {
        return $_SERVER["HTTP_HOST"];
    } else if (array_key_exists('SERVER_NAME', $_SERVER)) {
        return $_SERVER["SERVER_NAME"];
    } else {
        return '';
    }
}

function get_server_ip()
{
    $out = shell_exec("dig +short myip.opendns.com @resolver1.opendns.com");
    return $out;
}

function get_internal_server_ip()
{
    $sudo_bin = '/bin/sudo';
    $out = shell_exec($sudo_bin . ' /bin/ip route | grep src | awk -F \'src\' \'{print $NF; exit}\' | awk \'{print $1}\'');
    return $out;
}

function geBlockFullOrgURL()
{
    return "";
}

function unSetURL($url, $url_provided, $server_ip)
{
    $internal_ip = get_internal_server_ip();
    if ($url === $server_ip) {
        $url = null;
        $url_provided = false;
    } else if ($url === '' or empty($url)) {
        $url = null;
        $url_provided = false;
    } else if ($url === get_server_ip() || $url === $internal_ip) {
        $url = null;
        $url_provided = false;
    }
}

function getAndSetURL()
{
    global $url, $url_provided, $server_ip;

//    $server_ip = default_value($server_ip, get_server_ip());
    if (isset($_GET['url']) && !empty($_GET['url'])) {
        $org_url = $_GET['url'];
        if (strpos($_GET['url'], ':') !== false) {

            // Strip port out of DNS name since PiHole does not deal with ports
            $url = substr($_GET['url'], 0, strpos($_GET['url'], ":"));

            // After stripping out port, then we sanitize/escape the input before doing anything with it
            $url = htmlentities($url, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        } else {
            // There is no port number so we go straight to sanitizing the user input
            $url = htmlentities($_GET['url'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        $url_provided = true;
//
        unSetURL($url, $url_provided, $server_ip);

    } else {
        $org_url = "";
        $url_provided = true;
        $url = default_value(getHost(), null);

        unSetURL($url, $url_provided, $server_ip);
    }
    unblockLogger("url $url, url_provided $url_provided, _GET['url'] / org_url $org_url");
}

function is_empty($var, $array = null)
{
    if (is_array($array) && isset($array)) {
        $var_in_array = $array[$var];
        if (array_key_exists($var, $array)) {
            return (!isset($var_in_array) || empty($var_in_array));
        } else {
            return false;
        }
    } else {
        return !isset($var) || empty($var);
    }
}

?>