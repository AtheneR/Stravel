<?php
    include_once('header.php');

    session_start();

    if (!isset($_SESSION['user_id'])) {
        header('Location: connexion.php');
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $connexionutilisateur = "SELECT * FROM utilisateur WHERE id_utilisateur = :id";
    $requete_connexion = $bdd->prepare($connexionutilisateur);
    $requete_connexion->execute(['id' => $user_id]);
    $user = $requete_connexion->fetch();
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Starvel</title>
    <link rel="stylesheet" href="visuel.css">
    <link rel="icon" type="image/png" href="logo_couleur.png">
</head>

<nav class="navbar">
    <a href="accueil_utilisateur.php"><img src="logo_couleur.png" alt="Logo Starvel" class="logo" /></a>
    <a href="reservation.php">Réserver un trajet</a>
    <a href="modification_utilisateur.php">Modifier mon profil</a>
    <a href="annulation.php">Mes trajets à venir</a>
    <a href="historique.php">Historique de mes trajets</a>
    <a href="connexion.php?action=logout" class="deconnexion">Se déconnecter</a>
</nav>

<div class="container">
        <div class="informations">
        <form method="POST" action="inscription_utilisateur.php">
        <h2>Modification</h2>

        <div>
            <div>  
            <label for="nom">Nom : <!--<p class='rouge'>*</p>--></label><br>
            <input type="text" id="nom" name="nom" placeholder=<?= htmlspecialchars($user['nom']) ?> >
            </div>
            <div>  
            <label for="prenom">Prénom :</label><br>
            <input type="text" id="prenom" name="prenom" placeholder=<?= htmlspecialchars($user['prenom']) ?> >
            </div>
            
            <div>  
                <label for="date_naissance">Date de naissance :</label><br>
                <input type="date" id="date_naissance" name="date_naissance" 
                    value="<?= htmlspecialchars($user['date_naissance'] ?? '') ?>">
            </div>

            <div>  
            <label for="telephone">Téléphone : </label><br>
            <input type="tel" id="telephone" name="telephone" pattern="[0-9]{10}" placeholder=<?= htmlspecialchars($user['telephone']) ?> >
            </div>
            <div>  
            <label for="email">Adresse mail :</label>
            <input type="email" id="email" name="email" placeholder=<?= htmlspecialchars($user['email']) ?> >
            </div>
            <div class="commentaire">
                <label for="mot_de_passe">Mot de passe : </label>
                <input type="password" id="mot_de_passe" name="mot_de_passe" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{5,}" ><br>
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
            <input type="submit" value="Enregistrer les modifications">
        </div>
        </form>
    </div>
</div>

<?php
    $message = '';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (!empty($_POST["nom"])) {
            $nom = $_POST["nom"];
        } else {
            $nom = $user['nom'];
        }
        
        if (!empty($_POST["prenom"])) {
            $prenom = $_POST["prenom"];
        } else {
            $prenom = $user['prenom'];
        }
        
        if (!empty($_POST["date_naissance"])) {
            $date_naissance = $_POST["date_naissance"];
        } else {
            $date_naissance = $user['date_naissance'];
        }
        
        if (!empty($_POST["telephone"])) {
            $telephone = $_POST["telephone"];
        } else {
            $telephone = $user['telephone'];
        }
        
        if (!empty($_POST["email"])) {
            $email = $_POST["email"];
        } else {
            $email = $user['email'];
        }
        
        if (!empty($_POST["mot_de_passe"])) {
            $mot_de_passe = password_hash($_POST['mot_de_passe'], PASSWORD_DEFAULT);
        } else {
            $mot_de_passe = $user['mot_de_passe'];
        }
        
        if (!empty($_POST["preference_communication"])) {
            $preference_communication = $_POST["preference_communication"];
        } else {
            $preference_communication = $user['preference_communication'];
        }

       
        $sql = "UPDATE utilisateur SET nom=:nom, prenom=:prenom,date_naissance=:date_naissance,telephone=:telephone, email=:email, mot_de_passe=:mot_de_passe,preference_communication=:preference_communication WHERE id_utilisateur=:user_id";
        
        $rqUpdate = $bdd->prepare($sql);
        $rqUpdate->execute(
            ['nom' => $nom,
            'prenom' => $prenom,
            'date_naissance' => $date_naissance,
            'telephone' => $telephone,
            'email' => $email,
            'mot_de_passe' => $mot_de_passe,
            'preference_communication' => $preference_communication,
            'user_id' => $user_id]
        );
        if ($rqUpdate){
            $message = 'Modification réussie';
            header('Location: accueil_utilisateur.php');
            exit();
        } else {
            $message = 'Erreur lors de la modification';
        }
    }
?>
