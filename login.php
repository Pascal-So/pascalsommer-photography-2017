<?php
// #################### LOGIN SYSTEM ################
//
// requires phpseclib
//
// set session variable "access_granted" to 1
//

include_once("app/config.php");
if($config['database_format_version'] != '2017') {
    die("Admin interface disabled.");
}

session_start();
$target_address = "upload.php";

function redirect($address){
	header("Location: ".$address);
	die();
}

if(isset($_SESSION["access_granted"]) && $_SESSION["access_granted"]==1){
	redirect($target_address);
}

include('app/dbConn.php');
set_include_path(get_include_path() . PATH_SEPARATOR . "phpseclib");
include('Crypt/RSA.php');

$rsa = new Crypt_RSA();
if(!isset($_SESSION['private'])){
	// private rsa key is not set yet
	extract($rsa->createKey());
	$_SESSION["private"] = $privatekey;
	$_SESSION["public"] = $publickey;
}

$rsa->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);
if(isset($_POST["crypt_usr"]) && isset($_POST["crypt_pwd"])){
	$crypt_usr = $_POST["crypt_usr"];
	$crypt_pwd = $_POST["crypt_pwd"];

	$rsa->loadkey($_SESSION["private"]);
	$plain_usr = $rsa->decrypt(base64_decode($crypt_usr));
	$plain_pwd = $rsa->decrypt(base64_decode($crypt_pwd));


	$db = new dbConn();
	$res = $db->query("SELECT hash FROM users WHERE username = ?", $plain_usr);
	if(count($res) == 1){
		$hash = $res[0]["hash"];
		if(password_verify($plain_pwd, $hash) == true){
			$_SESSION["access_granted"] = 1;
			redirect($target_address);
		}
	}
}
?>

<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">

	<title>Login</title>
	<script type="text/javascript" src="https://code.jquery.com/jquery-2.2.4.min.js"></script>
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jsencrypt/2.3.1/jsencrypt.min.js"></script>

	<link rel="stylesheet" type="text/css" href="base.css">
</head>
<body class="alignCenter">

<script type="text/javascript">
function onSend(){
	var encrypt = new JSEncrypt();
	encrypt.setPublicKey($('#public').val());

	var crypt_pwd = encrypt.encrypt($("#pwd").val());
	$("#pwd").val(crypt_pwd);

	var crypt_usr = encrypt.encrypt($("#usr").val());
	$("#usr").val(crypt_usr);

	return true;
}
</script>


<?php print("<textarea id='public' class='hidden'>".$_SESSION["public"]."</textarea>");?>

<form method="post" onsubmit="return onSend()">

	<label for="usr" class="f5">Username</label><br>
	<input class="textinput f5" id="usr" type="text" name="crypt_usr">

	<br>

	<label for="pwd" class="f5">Password</label><br>
	<input class="textinput f5" id="pwd" type="password" name="crypt_pwd">

	<br>
	<br>

	<input class="button f5" type="submit" value="OK">

</form>

</body>
