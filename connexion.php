<?php
	include_once('header.php');
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Starvel</title>
    <link rel="stylesheet" href="visuel.css">
    <link rel="icon" type="image/png" href="logo_starvel.webp">
</head>

<div class="container">
  <div class="informations">
    <form method="POST">
      <h2>Connexion</h2>
        <!-- champs pour l'adresse mail et le mot de passe, on met les deux en required par sécurité-->
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
        <!-- sélection de la connexion, je choisis de ne pas faire deux pages séparées pour la connexion d'un administrateur et d'un utilisateur -->
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
    <!-- <p>Pas encore inscris ? Inscris toi <a href="inscription_utilisateur.php">ici</a> !</p> -->

    <p class = "centre">Pas encore inscrit ?   <a href="inscription_utilisateur.php">S'inscrire</a></p>

    <?php
      $message = '';

        // connexion
      if (isset($_POST['email']) && isset($_POST['mot_de_passe'])) {
        $email = $_POST['email'];
        $mot_de_passe = $_POST['mot_de_passe'];

          // connexion d'un utilisateur
        if(isset($_POST['connexion']) && $_POST['connexion']=='utilisateur'){
          $connexionutilisateur = "SELECT * FROM utilisateur WHERE email = :email";
          $requete_connexion = $bdd->prepare($connexionutilisateur);

          $requete_connexion->execute(['email' => $email]);
          $user = $requete_connexion->fetch();

          if ($user && password_verify($mot_de_passe, $user['mot_de_passe'])) {
            session_start();
            $_SESSION['user_id'] = $user['id_utilisateur'];
            header('Location: accueil_utilisateur.php');
          } else {
            $message = 'Mauvais identifiants';
          }
        } elseif (isset($_POST['connexion']) && $_POST['connexion']=='admin'){
          $connexionadmin = "SELECT * FROM administrateur WHERE email = :email";
          $requete_connexion = $bdd->prepare($connexionadmin);

          $requete_connexion->execute(['email' => $email]);
          $user = $requete_connexion->fetch();

          if ($user && password_verify($mot_de_passe, $user['mot_de_passe'])) {
            session_start();
            $_SESSION['user_id'] = $user['id_administrateur'];
            header('Location: accueil_administrateur.php');
          } else {
            $message = 'Mauvais identifiants';
          }
        }
      }
    ?>
  </div>
</div>
