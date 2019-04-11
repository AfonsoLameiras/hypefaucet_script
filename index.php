<?php
include 'core.php'; 

if (isset($_GET['r']) && !isset($_COOKIE['ref'])) {
	$reff = $mysqli->real_escape_string($_GET['r']);
	setcookie('ref',  $reff, time()+86400000);
}

if (isset($_POST['address'])) { 
// save address    
	$address = $mysqli->real_escape_string($_POST['address']);
	if (!isset($_COOKIE['address'])) {
		setcookie('address', $address, time()+1000000);
	} 
// check captcha
	if (!isset($alert)) {
		if (isset($_POST['g-recaptcha-response']) && $faucet['captcha'] == 2) {
			$CaptchaCheck = json_decode(captcha_check($_POST['g-recaptcha-response']))->success; 
			if (!$CaptchaCheck) {
				$alert = "<center><img style='max-width: 200px;' src='template/img/bots.png'><br><div class='alert alert-warning'>Invalid Captcha</div></center>"; 
			} 
		} elseif (isset($_POST["adcopy_challenge"]) && isset($_POST["adcopy_response"])&& $faucet['captcha'] == 1) {
			$solvemedia_response = solvemedia_check_answer($privkey,
				$_SERVER["REMOTE_ADDR"],
				$_POST["adcopy_challenge"],
				$_POST["adcopy_response"],
				$hashkey);
			if (!$solvemedia_response->is_valid) { 
				$alert = "<center><img style='max-width: 200px;' src='template/img/bots.png'><br><div class='alert alert-warning'>Invalid Captcha</div></center>";
			} 
		} else {
			$alert = "<center><img style='max-width: 200px;' src='template/img/bots.png'><br><div class='alert alert-warning'>Invalid Captcha</div></center>";
		}
	}
	if (check_blocked_ip($ip) == 'blocked') {
		$alert = "<center><img style='max-width: 200px;' src='template/img/bots.png'><br><div class='alert alert-warning'>Your Ip Is Blocked. Please Contact Admin.</div></center>";
	}
	if (check_blocked_address($address) == 'blocked') {
		$alert = "<center><img style='max-width: 200px;' src='template/img/bots.png'><br><div class='alert alert-warning'>Your Address Is Blocked. Please Contact Admin.</div></center>";
	}
// check ip with ip hub
	if (!isset($alert) && !empty($iphub_api)) {
		if (iphub() == 'bad') {
			$alert = "<center><img style='max-width: 200px;' src='template/img/bots.png'><br><div class='alert alert-warning'>Your Ip Is Blocked By IpHub</div></center>";
		}
	}
// check if this address can claims
	if (!isset($alert)) {
		$claim = checkaddress($address);
		if ($claim == 'ok') {
			$address_claim = 'ok';
		} else {
			$wait= 1;
			$alert = "<div class='alert alert-info'>You have to wait</div><br><div id='CountDownTimer' data-timer='" . checkaddress($address) . "' style='width: 100%;'></div>";
		}
	}
// check if this ip can claims
	if (!isset($alert)) {
		$claim = checkip($ip);
		if ($claim == 'ok') {
			$ip_claim = 'ok';
		} else {
			$wait= 1;
			$alert = "<div class='alert alert-info'>You have to wait</div><br><div id='CountDownTimer' data-timer='" . checkip($ip) . "' style='width: 100%;'></div>";
		}
	}
// if both ip and btc address can claim
	if (!isset($alert) && $ip_claim == 'ok' && $address_claim == 'ok') {
// check if user select click a link
		if (isset($_POST['link']) && $config_link['status'] == 'on') {
			$key = get_token(10); 
			for ($i=1; $i <= count($link) ; $i++) { 
				if (!isset($_COOKIE[$i])) {
					$mysqli->query("INSERT INTO link (bitcoin_address, sec_key) VALUES ('$address', '$key')");
					log_user($address, $ip);
					setcookie($i, 'visited', time() + 86400);
					$url = $link[$i];
					$go = str_replace("{key}",$key,$url);
					$goo = file_get_contents($go);
					header("Location: ". $goo);
					echo '<script> window.location.href="' .$goo. '"; </script>';
					die();
				}
			}
		} else {
			if (isset($_COOKIE['ref']) && $address !== $_COOKIE['ref']) {
				$ref = $mysqli->real_escape_string($_COOKIE['ref']);
			}
			$send = send_hub($address, $ip);
			if ($send['success'] == true) {
				log_user($address, $ip);
				$send_reward = 1;
			}
			$alert = "<center><img style='max-width: 200px;' src='template/img/trophy.png'><br>{$send['html']}</center>"; 
		}
	} 		
} 
// check if user has completed a short link
if (isset($_GET['k'])) {
	$key = $mysqli->real_escape_string($_GET['k']);;
	$check = $mysqli->query("SELECT * FROM link WHERE sec_key = '$key'");
	if ($check->num_rows == 1) { 
		$check = $check->fetch_assoc();
		$address = $check['bitcoin_address'];
		$mysqli->query("DELETE FROM link WHERE sec_key = '$key'");
		if (isset($_COOKIE['ref']) && $address !== $_COOKIE['ref']) {
			$ref = $mysqli->real_escape_string($_COOKIE['ref']);
		}
		$send = send_link($address, $ip); 
		if ($send['success'] == true) {
			$send_reward = 1;
		}
		$alert = "<center><img style='max-width: 200px;' src='template/img/trophy.png'><br>{$send['html']}</center>";
	} else {
		$alert = "<center><img style='max-width: 200px;' src='template/img/bots.png'><br><div class='alert alert-warning'>Invalid Key !</div></center>";
	}
}
$_SESSION['name_tk'] = get_token(70);
$_SESSION['token'] = get_token(70);
$_SESSION['add'] = get_token(50);
$his = his();
$totalusers = $mysqli->query("SELECT COUNT(id) FROM address_list")->fetch_row()[0];
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title><?=$faucet['name']?> - <?=$faucet['description']?></title> 
	<link rel="shortcut icon" href="template//img/favicon.ico" type="image/x-icon">
	<link rel="icon" href="template/img/favicon.ico" type="template/image/x-icon">
	<link href="https://fonts.googleapis.com/css?family=Saira+Extra+Condensed" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="template/css/main.css"> 
	<link rel="stylesheet" href="template/css/countdown.css"> 
	<style type="text/css"> 
	body { 
		font-family: 'Saira Extra Condensed', sans-serif;
		font-weight:400;
		color:#fff;
		font-size:0.875em;
		letter-spacing:0.063em;
	}
	h1 {
		font-weight:900;font-size:3.25em;margin-bottom:30px;text-shadow:2px 3px 0px #898999;line-height:1.2;
	}
	div {
		border-radius: 20px 20px 20px 20px;
	}
	img, iframe {
		max-width: 100%;
	}
	.login {
		background-color: rgba(250, 250, 250, 0.5);
		padding-top: 20px;
		padding-bottom: 20px;
	}
	.alert {
		margin-bottom: 20px;
	} 
</style>
</head>
<body> 
	<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
		<a class="navbar-brand" href="index.php"><?=$faucet['name']?></a>
		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarColor01" aria-controls="navbarColor01" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>

		<div class="collapse navbar-collapse" id="navbarColor01">
			<ul class="navbar-nav mr-auto">
				<li class="nav-item active">
					<a class="nav-link" href="index.php"><i class="fa fa-home" aria-hidden="true"></i> Home <span class="sr-only">(current)</span></a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="http://coinbox.club/threads/free-coinbox-faucet-script.5/"><i class="fa fa-bolt" aria-hidden="true"></i> BuzzFaucet Script</a>
				</li>
			</ul>
			<ul class="navbar-nav ml-auto">
				<li class="nav-item active">
					<a class="nav-link" href="#"><i class="fa fa-balance-scale" aria-hidden="true"></i> Faucet Balance: <?=balance()?> <?=$currency?> <span class="sr-only">(current)</span></a>
				</li>
			</ul>
		</div>
	</nav>

	<center>
		<?=$ad['top']?>
	</center>
	<h1 class="text-center">Welcome to <?=$faucet['name']?></h1>
	<div class="container-fluid" style="margin-top: 30px;">
		<div class="row">
			<div class="col-sm-3 text-center" style="margin-top: 20px;">
				<?=$ad['left']?>
			</div>
			<div class="col-sm-6 login">
				<div class="alert alert-success text-center" style="margin-top: 10px;">
					<p><i class="fa fa-trophy" aria-hidden="true"></i> Claim <?=$faucet['reward']?> satoshi every <?=floor($faucet['time']/60)?> minutes .</p>
				</div>
				<center>
					<?=$ad['above-form']?>
				</center>
				<?php if (isset($alert)) { ?>
				<div class="modal fade" id="alert" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
					<div class="modal-dialog" role="document">
						<div class="modal-content"> 
							<div class="modal-body">
								<?=$alert?>  
							</div>
						</div>
					</div>
				</div>
				<?php } if (checkip($ip) == 'ok') { ?>
				<form action="" method="post">
					<div class="form-group">
						<span class="badge badge-warning control-label">Your Bitcoin Address</span>
						<div class="form-group">
							<div class="input-group">
								<div class="input-group-addon"><img src="template/img/wallet.png" width="40px"></div>
								<input type="text" class="form-control" name="address" <?php if(isset($_COOKIE['address'])) {echo "value='" . $_COOKIE['address'] . "'";} else {echo 'placeholder="Must be linked to FaucetHub first"'; } ?> style="border-radius: 0px 20px 20px 0px;">
							</div>
						</div>
					</div> 
					<center>
						<?=$ad['bottom']?> 
					</center>
					<div class="form-group">
						<span class="badge badge-danger control-label">Complete Captcha</span>
						<div class="form-group">
							<div class="input-group">
								<div class="input-group-addon"><img src="template/img/captcha.png" width="40px"></div>
								<?php if ($faucet['captcha'] == 1) { 
									echo solvemedia_get_html($your_challenge_key);
								} else {     
									echo '<div class="g-recaptcha" data-sitekey="' .$publickey. '" style="margin-left: 3px;"></div>';
								} 
								?>
							</div>
						</div>
					</div>
					<?php if ($config_link['status'] == 'on') { for ($i=1; $i <= count($link) ; $i++) { if (!isset($_COOKIE[$i])) { ?>
					<label class="custom-control custom-checkbox mb-2 mr-sm-2 mb-sm-0">
						<input type="checkbox" name="link" value="yes" class="custom-control-input" checked>
						<span class="custom-control-indicator"></span>
						<span class="custom-control-description"><i class="fa fa-gift" aria-hidden="true"></i> <strong>I want to click<font color="#F67F7F">SHORT LINK</font>and receive<font color="#F67F7F"> + <?=$config_link['reward']?> satoshi bounus</font></strong></span>
					</label> 
					<?php break; } }} ?>
					<button id="embed-submit" type="button" class="btn btn-warning btn-lg btn-block" style="margin-bottom: 20px;" data-toggle="modal" data-target="#next"><i class="fa fa-paper-plane" aria-hidden="true"></i> <strong>Claim Free Bitcoin</strong></button>
					<div class="modal fade bd-example-modal-lg" id="next" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
						<div class="modal-dialog modal-lg" role="document">
							<div class="modal-content">
								<div class="modal-header">
									<h5 class="modal-title" id="exampleModalLabel">Final Step</h5>
									<button type="button" class="close" data-dismiss="modal" aria-label="Close">
										<span aria-hidden="true">&times;</span>
									</button>
								</div>
								<div class="modal-body">
									<?=$ad['modal']?>
								</div>
								<div class="modal-footer">
									<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
									<button type="submit" class="btn btn-primary">Claim Your Coin</button>
								</div>
							</div>
						</div>
					</div>
					<code>Ref link: <?=$faucet['url']?>?r=Your_bitcoin_address</code>
				</form>
				<?php } else { $wait= 1; echo "<div class='alert alert-info'>You have to wait</div><br><div id='CountDownTimer' data-timer='" . checkip($ip) . "' style='width: 100%;'></div>"; } ?> 
				<a href='http://coinbox.club' target='_blank'><img src='https://i.imgur.com/1lIFvh1.png' /></a>
				<!---You can keep this banner to support us, thanks-->
			</div>
			<div class="col-sm-3 text-center" style="margin-top: 20px;">
				<?=$ad['right']?>
			</div>
		</div>
	</div>
	<br>
	<div class="container-fluid text-center" style="background-image: url(template/img/stat.png);background-repeat: no-repeat;background-position: center center;background-size: cover;">
		<div class="row">
			<div class="col-md-3">
				<center>
					<img src="template/img/user.png" style="max-width: 100%">
				</center>
				<h2 style="color: white;">Total Users:</h2>
				<h3 style="color: white;"><?=$totalusers?> happy users.</h3>
			</div>
			<div class="col-md-6">
				<h3 style="color: white;"><i class="fa fa-area-chart" aria-hidden="true"></i> Lastest Payouts</h3>
				<div class="list-group">
					<?php for ($i=0; $i < 5 ; $i++) { ?>
					<a href="https://faucethub.io/balance/<?=$his[$i]['to']?>" target="_blank" class="list-group-item  list-group-item-action active">
						Sent <?=$his[$i]['amount']?> satoshi to <?=$his[$i]['to']?> at <?=$his[$i]['date']?>
					</a>
					<?php } ?> 
				</div>
			</div> 
		</div>
	</div>

	<footer class="text-center">
		<p><font color='white'>&copy; 2017 
			<a href='<?=$faucet['url']?>'><?=$faucet['name']?></a>, Script by <a href='http://coinbox.club' id='copyright' title="Download.our script for free">BuzzFaucet</a></font>
		</p>
		<!---Please do not remove the link to support us, thanks-->
	</footer> 
	<script src="https://www.google.com/recaptcha/api.js" async defer></script> 
	<script src="https://use.fontawesome.com/7002d3875b.js"></script>
	<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
	<?php if (isset($alert)) { ?>
	<script type='text/javascript'>$('#alert').modal('show');</script>
	<?php  } ?>
	<script type="text/javascript" src="template/js/site.js"></script>
	<script type="text/javascript">
		$("#DateCountdown").TimeCircles();
		$("#CountDownTimer").TimeCircles({ time: { Days: { show: false }, Hours: { show: false } }});
		$("#PageOpenTimer").TimeCircles();

		var updateTime = function(){
			var date = $("#date").val();
			var time = $("#time").val();
			var datetime = date + ' ' + time + ':00';
			$("#DateCountdown").data('date', datetime).TimeCircles().start();
		}
		$("#date").change(updateTime).keyup(updateTime);
		$("#time").change(updateTime).keyup(updateTime); 
		$(".startTimer").click(function() {
			$("#CountDownTimer").TimeCircles().start();
		});
		$(".stopTimer").click(function() {
			$("#CountDownTimer").TimeCircles().stop();
		}); 
		$(".fadeIn").click(function() {
			$("#PageOpenTimer").fadeIn();
		});
		$(".fadeOut").click(function() {
			$("#PageOpenTimer").fadeOut();
		});   
	</script>
	<script type="text/javascript" src="template/js/anti.js"></script>
</body>
</html>
<?php
$mysqli->close();
?>
