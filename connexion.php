<?php 
  // on inclut le fichier du header
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
                    <label for="email">adresse mail :</label><br>
                    <input type="email" id="email" name="email" required>
                </div>
                <div>
                    <label for="mot_de_passe">mot de passe :</label><br>
                    <input type="password" id="mot_de_passe" name="mot_de_passe" required>
                </div>
            </div>
            <br>
            <div>
                <input type="submit" value="se connecter">
            </div>
        </form>

        <?php
            $message = '';

            // on vérifie si le formulaire a été soumis avec les champs nécessaires
            if (isset($_POST['email']) && isset($_POST['mot_de_passe'])) {
                // on récupère et nettoie l'email envoyé par l'utilisateur
                $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
                $mot_de_passe = $_POST['mot_de_passe'];

                // on valide l'email pour s'assurer qu'il est correct
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $connexionadmin = "SELECT * FROM administrateur WHERE email = :email";
                    $requete_connexion = $bdd->prepare($connexionadmin);
                    $requete_connexion->execute(['email' => $email]);
                    $admin = $requete_connexion->fetch();

                    // si l'email correspond et le mot de passe est correct, on connecte l'utilisateur en tant qu'administrateur
                    if ($admin && password_verify($mot_de_passe, $admin['mot_de_passe'])) {
                        session_start();
                        $_SESSION['user_id'] = $admin['id_administrateur'];
                        $_SESSION['role'] = 'admin';
                        header('Location: accueil_administrateur.php');
                        exit();
                    }

                    $connexionutilisateur = "SELECT * FROM utilisateur WHERE email = :email";
                    $requete_connexion = $bdd->prepare($connexionutilisateur);
                    $requete_connexion->execute(['email' => $email]);
                    $user = $requete_connexion->fetch();

                    // si un utilisateur est trouvé et le mot de passe est correct, on le connecte
                    if ($user && password_verify($mot_de_passe, $user['mot_de_passe'])) {
                        session_start();
                        $_SESSION['user_id'] = $user['id_utilisateur'];
                        $_SESSION['role'] = 'utilisateur';
                        header('Location: accueil_utilisateur.php');
                        exit();
                    }

                    // on affiche un message si les identifiants sont incorrects ou le compte n'existe pas
                    $message = 'mauvais identifiants ou compte inexistant.';
                } else {
                    $message = 'Adresse email invalide.';
                }
            }
        ?>

        <?php if (!empty($message)) : ?>
            <div>
              <p class="message" style="color: red; display: flex; align-items: center; justify-content: center;">
                <?= htmlspecialchars($message) ?>
              </p>
            </div>
        <?php endif; ?>

        <p class="centre">
            Pas encore inscrit ? 
            <a href="inscription_utilisateur.php">s'inscrire</a>
        </p>
    </div>
</div>
