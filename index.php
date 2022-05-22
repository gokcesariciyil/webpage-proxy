<?php
/**
 *
 * @package      WebPageProxy
 * @since        1.0.0
 * @link         https://github.com/gokcesariciyil/webpage-proxy
 * @author       Gökçe SARIÇİYİL <gsrcyl@gmail.com>
 * @copyright    Copyright (c) 2022, Gökçe SARIÇİYİL
 * @license      https://github.com/gokcesariciyil/webpage-proxy/blob/main/LICENSE MIT License
 *
 */
require_once "support/web_browser.php";
require_once "support/tag_filter.php";

// Security Layer
function getError($message) {
	echo '<h3>'.$message.'</h3>'; die();
}

function clientIp()   {  
	if (!empty($_SERVER['HTTP_CLIENT_IP'])) {  
		$ip	= $_SERVER['HTTP_CLIENT_IP'];  
	} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){  
		$ip	= $_SERVER['HTTP_X_FORWARDED_FOR'];  
	} else{  
		$ip	= $_SERVER['REMOTE_ADDR'];  
	}  
	return $ip;  
}

if(!isset($_GET['id'])) {
	getError('ID not found');
}

$_GET['id'] = intval($_GET['id']);

if(strlen($_GET['id']) < 5) {
	getError('Wrong ID');
}

// Retrieve the standard HTML parsing array for later use.
$htmloptions = TagFilter::GetHTMLOptions();

// Retrieve a URL (emulating Firefox by default).
$url = "https://www.sahibinden.com/print/archive/{$_GET['id']}?fillMode=full&pageCount=1&direction=horisental";
$web = new WebBrowser();
$result = $web->Process($url);

// Check for connectivity and response errors.
if (!$result["success"])
{
	getError("Error retrieving URL.  " . $result["error"] . "\n");
}

if ($result["response"]["code"] != 200)
{
	getError("Error retrieving URL.  Server returned:  " . $result["response"]["code"] . " " . $result["response"]["meaning"] . "\n");
}

// Get the final URL after redirects.
$baseurl = $result["url"];

// Use TagFilter to parse the content.
$html = TagFilter::Explode($result["body"], $htmloptions);

// Retrieve a pointer object to the root node.
$root = $html->Get();

if(!empty($root)) {
	$root = str_replace("<head>", "
		<head>\n
			<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/>\n
			<style>
				* { font-family:  'Roboto Flex' !important; } \n
				.archive .title {
					line-height: 65px !important;
				}
			</style>\n", $root);
}

echo $root;
