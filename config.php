<?php
$dbHost = "localhost";
$dbUser = "user name";
$dbPW = "pass";
$dbName = "database name";
$mysqli = mysqli_connect($dbHost, $dbUser, $dbPW, $dbName);
if(mysqli_connect_errno()){
	echo "Failed to connect to MySQL: " . mysqli_connect_error();
} 
// change it now
$admin_username = 'admin';
$admin_pass = 'admin';

// basic info
$faucet['name'] = 'BuzzFaucet';
$faucet['description'] = 'The most rentable free Bitcoin Faucet';
$currency = 'BTC';
$currency_name = 'satoshi';
$faucet['captcha'] = 1; // 1 for solvemedia captcha and 2 for recaptcha
// bitcaptcha setting
$bitcaptcha['id'] = '';
$bitcaptcha['key'] = '';
// config solve media captcha
$your_challenge_key = '';
$privkey= '';
$hashkey= '';
//recaptcha setting
$secretkey = ''; // your recaptcha private key
$publickey = ''; // your recaptcha public key

// iphub setting, use it to block proxy. get your api at iphub.info
$iphub_api = 'ODA6cXgyY004NzlLdTlvaExrNHJpY2pSaGFtWVdoZWFNVGU=';

// config your reward
$faucet['reward'] = 11; // your faucet's reward
$faucet['time'] = 60;  // time to wait beetwen 2 claims, in second.
$faucet['ref'] = 15;
$faucet['url'] = 'http://coinbox.club/demo';

$faucethub_api = "";

// config your short link, read full instruction at http://coinbox.club/
$config_link['status'] = 'on'; // turn on or off short link bounus
$config_link['reward'] = 3; // short link bounus amout
     //  start config short link api
$link[1] = "https://miniurl.io/st?api=c6884c837ea2cf6bf3ac8d88e56b9391452fc4b0&url=http://buzzfaucet.ga/link.php?k={key}&format={text}";
$link[6] = "https://miniurl.io/st?api=c6884c837ea2cf6bf3ac8d88e56b9391452fc4b0&url=http://buzzfaucet.ga/link.php?k={key}&format={text}";

$link[2] = "https://miniurl.io/st?api=c6884c837ea2cf6bf3ac8d88e56b9391452fc4b0&url=http://buzzfaucet.ga/link.php?k={key}&format={text}";

$link[4] = "https://miniurl.io/st?api=c6884c837ea2cf6bf3ac8d88e56b9391452fc4b0&url=http://buzzfaucet.ga/link.php?k={key}&format={text}";

$link[3] = "https://miniurl.io/st?api=c6884c837ea2cf6bf3ac8d88e56b9391452fc4b0&url=http://buzzfaucet.ga/link.php?k={key}&format={text}";

$link[5] = "https://miniurl.io/st?api=c6884c837ea2cf6bf3ac8d88e56b9391452fc4b0&url=http://buzzfaucet.ga/link.php?k={key}&format={text}";
$link[6] = "https://miniurl.io/st?api=c6884c837ea2cf6bf3ac8d88e56b9391452fc4b0&url=http://buzzfaucet.ga/link.php?k={key}&format={text}"; 

// ad spaces
$ad['top'] = 'ad top';
$ad['left'] = 'ad left';
$ad['right'] = 'ad right';
$ad['above-form'] = 'ad above form';
$ad['bottom'] = 'ad bottom';
$ad['modal'] = '<font color="red">ad modal</font>';
?>   
