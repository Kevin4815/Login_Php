<?php

	//Notify php that we will use the sessions
	session_start();

	require('src/log.php');

	//Check if the informations are well filled
	if(!empty($_POST['email']) && !empty($_POST['password']) && !empty($_POST['password_two'])){

		//Call the database
		require('src/connect.php');

		//VARIABLES   (htmlspecialchars, to avoid intrusive characters)
		$email = htmlspecialchars($_POST['email']);
		$password = htmlspecialchars($_POST['password']);
		$password_two = htmlspecialchars($_POST['password_two']);

		//PASSWORD == PASSWORD_TWO
		//If the passwords are different, the error message is displayed
		if($password != $password_two){

			header('location: inscription.php?error=1&message=Vos mots de passe ne sont pas identiques.');
			exit();
		}

		//If the e-mail address is not valid
		if(!filter_var($email, FILTER_VALIDATE_EMAIL)){

			header('location: inscription.php?error=1&message=Votre adresse email est invalide.');
			exit();
		}

		//E-MAIL ALREADY USED
		//Get the number of users who have this e-mail address
		$req = $db->prepare("SELECT count(*) as numberEmail FROM user WHERE email = ?");
		$req->execute(array($email));

		while($email_verification = $req->fetch()){
			
			// If the e-mail address is different from 0 (already exist)
			if($email_verification['numberEmail'] != 0){

				header('location: inscription.php?error=1&message=Cette adresse email est déjà utilisée par un autre utilisateur.');
				exit();
			}

		}

		//HASH
		$secret = sha1($email).time();
		//Crypt again the character chain for more security
		$secret = sha1($secret).time();

		//PASSWORD ENCRYPTION
		$password = sha1($password);
		$password = "zq2".sha1($password."123")."92";

		//Send informations
		$req = $db->prepare("INSERT INTO user(email, password, secret) VALUES (?,?,?)");
		$req->execute(array($email, $password, $secret));

		header('location: inscription.php?success=1');
		exit();

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
			<h1>S'inscrire</h1>

			<?php 
			// Vérifie si la variable "error" existe
			if(isset($_GET['error'])){
				// Vérifie si la variable "message" existe
				if(isset($_GET['message'])){

					echo'<div class="alert error">'.htmlspecialchars($_GET['message']).'</div>';

				}
			} 
			// Si l'inscription a marché
			else if(isset($_GET['success'])){

				echo'<div class="alert success">Vous êtes désormais inscrit. <a href="index.php">Connectez-vous</a>.</div>';
			}
			?>

			<form method="post" action="inscription.php">
				<input type="email" name="email" placeholder="Votre adresse email" required />
				<input type="password" name="password" placeholder="Mot de passe" required />
				<input type="password" name="password_two" placeholder="Retapez votre mot de passe" required />
				<button type="submit">S'inscrire</button>
			</form>

			<p class="grey">Déjà inscrit ? <a href="index.php">Connectez-vous</a>.</p>
		</div>
	</section>

	<?php include('src/footer.php'); ?>
</body>
</html>