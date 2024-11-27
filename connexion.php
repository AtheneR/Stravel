<?php
	include_once('header.php');
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Starvel</title>
    <link rel="stylesheet" href="visuel.css">
    <link rel="icon" type="image/png" href="logo_couleur.png">
</head>

<div class="container">
  <div class="informations">
    <form method="POST">
      <h2>Connexion</h2>
      <div>
        <div>  
          <label for="email">Adresse mail :</label>
          <input type="email" id="email" name="email" required>
        </div>
        <div>
          <label for="mot_de_passe">Mot de passe :</label>
          <input type="password" id="mot_de_passe" name="mot_de_passe" required>
        </div>
      </div>
      <br>
      <div>
        <input type="radio" id="utilisateur" name="connexion" value="utilisateur" checked/>
        <label for="utilisateur">utilisateur</label>
        <input type="radio" id="admin" name="connexion" value="admin" />
        <label for="admin">administrateur</label>
      </div>
      <br>
      <div>
        <input type="submit" value="Se connecter">
      </div>
    </form>

    <?php
      $message = '';

      if (isset($_POST['email']) && isset($_POST['mot_de_passe'])) {
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $mot_de_passe = $_POST['mot_de_passe'];

        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
          if(isset($_POST['connexion']) && $_POST['connexion'] == 'utilisateur') {
            $connexionutilisateur = "SELECT * FROM utilisateur WHERE email = :email";
            $requete_connexion = $bdd->prepare($connexionutilisateur);
            $requete_connexion->execute(['email' => $email]);
            $user = $requete_connexion->fetch();

            if ($user && password_verify($mot_de_passe, $user['mot_de_passe'])) {
              session_start();
              $_SESSION['user_id'] = $user['id_utilisateur'];
              header('Location: accueil_utilisateur.php');
              exit();
            } else {
              $message = 'Mauvais identifiants';
            }
          } elseif (isset($_POST['connexion']) && $_POST['connexion'] == 'admin') {
            $connexionadmin = "SELECT * FROM administrateur WHERE email = :email";
            $requete_connexion = $bdd->prepare($connexionadmin);
            $requete_connexion->execute(['email' => $email]);
            $user = $requete_connexion->fetch();

            if ($user && password_verify($mot_de_passe, $user['mot_de_passe'])) {
              session_start();
              $_SESSION['user_id'] = $user['id_administrateur'];
              header('Location: accueil_administrateur.php');
              exit();
            } else {
              $message = 'Mauvais identifiants';
            }
          }
        } else {
          $message = 'Adresse email invalide';
        }
      }
    ?>

    <?php if (!empty($message)): ?>
      <div><p class="message" style="color: red; 
    display: flex;
    align-items: center; 
    justify-content: center; "><?= htmlspecialchars($message) ?></p></div>
    <?php endif; ?>

    <p class="centre">Pas encore inscrit ? <a href="inscription_utilisateur.php">S'inscrire</a></p>
  </div>
</div>
