<?php
require('config.php');
require('ctp-mods.php');

$url =  "{$_SERVER['HTTP_HOST']}";
$bpLocal = $conf['blockpage_url'];

$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

if(empty($conf['blockpage_url'])) {
    die("blockpage_url cannot be blank. Check config.php.");
}

echo <<<EOL
<form action="$bpLocal" method="get" id="urlpass">
    <input type="hidden" name="url" value="$url">
    <input type="hidden" name="url_full_address" value="$actual_link">
</form>
<script>
document.getElementById('urlpass').submit();
</script>
EOL;
?>
