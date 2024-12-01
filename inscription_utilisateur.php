<?php
	include_once('header.php');

    $message = '';
    if (isset($_POST['email']) && isset($_POST['mot_de_passe']) && isset($_POST['nom']) && isset($_POST['prenom']) && isset($_POST['date_naissance']) && isset($_POST['telephone']) && isset($_POST['preference_communication'])){
        $mot_de_passe = password_hash($_POST['mot_de_passe'], PASSWORD_DEFAULT);
        $nom = $_POST['nom'];
        $prenom = $_POST['prenom'];
        $date_naissance = $_POST['date_naissance'];
        $telephone = $_POST['telephone'];
        $email = $_POST['email'];
        $preference_communication = $_POST['preference_communication'];
        
        $req_inscrit = "SELECT * FROM utilisateur WHERE email = :email";
        $verif_inscrit = $bdd->prepare($req_inscrit);
        $verif_inscrit->execute(['email' => $email]);
        $user = $verif_inscrit->fetch();

        $req_admin = "SELECT * FROM administrateur WHERE email = :email";
        $verif_admin = $bdd->prepare($req_admin);
        $verif_admin->execute(['email' => $email]);
        $admin = $verif_admin->fetch();

        if($user || $admin){
            $message = 'Il y a déjà un compte lié à cet email.';
        } else {
            $sql = "INSERT INTO utilisateur (nom, prenom, date_naissance, telephone, email, mot_de_passe, preference_communication) VALUES (:nom, :prenom, :date_naissance, :telephone, :email, :mot_de_passe, :preference_communication)";
            $requete = $bdd->prepare($sql);
            $resultat = $requete->execute(['nom' => $nom, 'prenom' => $prenom, 'date_naissance' => $date_naissance, 'telephone' => $telephone, 'email' => $email, 'mot_de_passe' => $mot_de_passe, 'preference_communication' => $preference_communication]);
        
            if ($resultat) {
                $message = 'Inscription réussie';
                header('Location: accueil_utilisateur.php');
            } else {
                $message = 'Erreur lors de l\'inscription.';
            }
        }
    }
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
        <form method="POST" action="inscription_utilisateur.php">
            <h2>Inscription</h2>
            <?php if (!empty($message)): ?>
                <p style="color:red"><?= $message ?></p>
            <?php endif; ?>
            <div>
                <div>  
                    <label for="nom">Nom :</label><br>
                    <input type="text" id="nom" name="nom" placeholder="Rousseau" required>
                </div>
                <div>  
                    <label for="prenom">Prénom :</label><br>
                    <input type="text" id="prenom" name="prenom" placeholder="Jade" required>
                </div>
                <div>  
                    <label for="date_naissance">Date de naissance :</label><br>
                    <input type="date" id="date_naissance" name="date_naissance" required>
                </div>
                <div>  
                    <label for="telephone">Téléphone : </label><br>
                    <input type="tel" id="telephone" name="telephone" pattern="[0-9]{10}" placeholder="0122334455" required>
                </div>
                <div>  
                    <label for="email">Adresse mail :</label>
                    <input type="email" id="email" name="email" placeholder="nomprenom@gmail.com" required>
                </div>
                <div class="commentaire">
                    <label for="mot_de_passe">Mot de passe : </label>
                    <input type="password" id="mot_de_passe" name="mot_de_passe" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{5,}" required><br>
                    <p>Le mot de passe doit contenir au moins 1 chiffre, 1 lettre majuscule, 1 lettre minuscule, et doit avoir au moins 8 caractères en tout.</p>
                </div>
                <div>
                    <p class="centrecom">Préférence de communication :</p>
                    <input type="radio" id="pref_email" name="preference_communication" value="email" checked/>
                    <label for="pref_email">e-mail</label>

                    <input type="radio" id="pref_tel" name="preference_communication" value="telephone" />
                    <label for="pref_tel">téléphone</label>
                </div>
            </div>
            <br>
            <div class="seconnecter">
                <input type="submit" value="S'inscrire">
            </div>
        </form>
        <p class = "centre">Déjà inscrit ? <a href="connexion.php">Se connecter</a></p>
    </div>
</div>
