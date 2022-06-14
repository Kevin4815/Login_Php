<?php

	session_start();

	require('src/log.php');

	//if the informations is well filled
	if(!empty($_POST['email']) && !empty($_POST['password'])){

		require('src/connect.php');

		// VARIABLES
		$email = htmlspecialchars($_POST['email']);
		$password = htmlspecialchars($_POST['password']);

		// E-MAIL ADDRESS SYNTAX (if the e-mail address is not valid)
		if(!filter_var($email, FILTER_VALIDATE_EMAIL)){

			header('location: index.php?error=1&message=Votre adresse email est invalide.');
			exit();
		}

		//PASSWORD ENCRYPTION
		$password = sha1($password);
		$password = "zq2".sha1($password."123")."92";

		//E-MAIL ALREADY USED
		$req = $db->prepare("SELECT count(*) as numberEmail FROM user WHERE email = ?");
		$req->execute(array($email));

		while($email_verification = $req->fetch()){
			
			//If e-mail address is different from 1 (only one account)
			if($email_verification['numberEmail'] != 1){

				header('location: index.php?error=1&message=Impossible de vous authentifier correctement.');
				exit();
			}
		}

		//CONNECTION
		$req = $db->prepare("SELECT * FROM user WHERE email = ?");
		$req->execute(array($email));

		//Loop on all e-mail addresses of the user table to see if it exists
		while($user = $req->fetch()){

			if($password == $user['password'] && $user['blocked'] == 0){

				$_SESSION['connect'] = 1;
				$_SESSION['email'] =  $user['email'];

				//if the checkbox was checked
				if(isset($_POST['auto'])){

					//Create cookie
					setcookie('auth', $user['secret'], time() + 364*24*3600, '/', null, false, true);
				}

				header('location: index.php?success=1');
				exit();
			}
			else{

				header('location: index.php?error=1&message=Impossible de vous authentifier correctement.');
			}
		}
	}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Voyage</title>
	<link rel="stylesheet" type="text/css" href="design/default.css">
	<link rel="icon" type="image/pngn" href="img/favicon.png">
</head>
<body>

	<?php include('src/header.php'); ?>
	<section>
		<div id="login-body">

			<!--If i'm connected, the connection page will be not visible-->
			<?php if(isset($_SESSION['connect'])) { ?>

				<h1>Bonjour !</h1>

				<?php //If the succes variable exists
				if(isset($_GET['success'])){

					header('location: inside.php');
				} ?>

			<!--Otherwise the connection page will be visible-->
			<?php } else { ?>

				<h1>S'identifier</h1>

				<!--If the error variable exists-->
				<?php if(isset($_GET['error'])) {

					//If the message variable exists
					if(isset($_GET['message'])) {
						echo'<div class="alert error">'.htmlspecialchars($_GET['message']).'</div>';
					}

				} ?>

				<form method="post" action="index.php">
					<input type="email" name="email" placeholder="Votre adresse email" required />
					<input type="password" name="password" placeholder="Mot de passe" required />
					<button type="submit">S'identifier</button>


					<label id="option"><input type="checkbox" name="auto" checked />Se souvenir de moi</label>


				</form>


				<p class="grey">Première visite sur notre site ? <a href="inscription.php">Inscrivez-vous</a>.</p>
			<?php } ?>
		</div>
	</section>
	<?php include('src/footer.php'); ?>
</body>
</html>