<?php
    include_once('header.php');

    session_start();

    if (!isset($_SESSION['user_id'])) {
        header('Location: connexion.php');
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $connexionadmin = "SELECT * FROM administrateur WHERE id_administrateur = :id_administrateur";
    
    $requete_connexion = $bdd->prepare($connexionadmin);
    if (!$requete_connexion->execute(['id_administrateur' => $user_id])) {
        die('Erreur lors de l\'exécution de la requête SQL.');
    }

    $user = $requete_connexion->fetch();
    if (!$user) {
        die('Aucun administrateur trouvé avec cet ID.');
    }

    $updateConnexion = "UPDATE administrateur SET date_derniere_connexion = NOW() WHERE id_administrateur = :id_administrateur";
    $requete_update = $bdd->prepare($updateConnexion);
    if (!$requete_update->execute(['id_administrateur' => $user_id])) {
        die('Erreur lors de la mise à jour de la date de dernière connexion.');
    }

    if (isset($_GET['action']) && $_GET['action'] == 'logout') {
        $_SESSION = [];
        session_destroy();
        header('Location: connexion.php');
        exit();
    }
    
    $afficher_formulaire_modif = isset($_POST['modifier']);
    
    $est_super_admin = $user['poste'] === 'super_admin';
    $message = "";

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['modification'])) {
        print_r("passage\n");
        $nom = !empty($_POST["nom"]) ? $_POST["nom"] : $user['nom'];
        $prenom = !empty($_POST["prenom"]) ? $_POST["prenom"] : $user['prenom'];
        $poste = !empty($_POST["poste"]) ? $_POST["poste"] : $user['poste'];
        $telephone = !empty($_POST["telephone"]) ? $_POST["telephone"] : $user['telephone'];
        $mot_de_passe = !empty($_POST["mot_de_passe"]) ? password_hash($_POST['mot_de_passe'], PASSWORD_DEFAULT) : $user['mot_de_passe'];
        $email = !empty($_POST["email"]) ? $_POST["email"] : $user['email'];

        $sql = "UPDATE administrateur 
                SET nom = :nom, 
                    prenom = :prenom, 
                    poste = :poste, 
                    telephone = :telephone, 
                    mot_de_passe = :mot_de_passe, 
                    email = :email
                WHERE id_administrateur = :id_administrateur";
        
        $req_admin = "SELECT * FROM administrateur WHERE email = :email AND id_administrateur != :id_administrateur";
        $verif_admin = $bdd->prepare($req_admin);
        $verif_admin->execute(['email' => $email, 'id_administrateur' => $user_id]);
        $a_verif = $verif_admin->fetch();

        $req_inscrit = "SELECT * FROM utilisateur WHERE email = :email AND id_utilisateur != :id_utilisateur";
        $verif_inscrit = $bdd->prepare($req_inscrit);
        $verif_inscrit->execute(['email' => $email, 'id_utilisateur' => $user_id]);
        $u_verif = $verif_inscrit->fetch();

        if($u_verif || $a_verif){
            $message = 'Il y a déjà un compte lié à cet email.';
        } else {
            try {
                $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $requeteMiseAJour = $bdd->prepare($sql);
                $requeteMiseAJour->execute([
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'poste' => $poste,
                    'telephone' => $telephone,
                    'mot_de_passe' => $mot_de_passe,
                    'email' => $email,
                    'id_administrateur' => $user_id
                ]);
                var_dump($requeteMiseAJour);
                header("Location: accueil_administrateur.php");
                exit();
            } catch (PDOException $e) {
                $message = "Erreur lors de la mise à jour : " . $e->getMessage();
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

<nav class="navbar">
    <a href="accueil_administrateur.php"><img src="logo_couleur.png" alt="Logo Starvel" class="logo" /></a>
    <a href="gestion_trains.php">Gestion des trains</a>
    <a href="gestion_gares.php">Gestion des gares</a>
    <a href="gestion_administrateur.php">Gestion des administrateurs</a>
    <a href="gestion_adresses.php">Gestion des adresses</a>
    <a href="connexion.php?action=logout" class="deconnexion">Déconnexion</a>
</nav>

<div class="container">
    <div class="informations">
        <h3>Vos informations</h3>
        <?php if ($message): ?>
            <p class="centre"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
        <?php if ($afficher_formulaire_modif): ?>
            <form method="POST" action="accueil_administrateur.php">
                <div class="form-group">
                    <label for="nom">Nom :</label><br>
                    <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($user['nom'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="prenom">Prénom :</label><br>
                    <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($user['prenom'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="poste">Poste :</label><br>
                    <select id="poste" name="poste" required>
                        <option value="admin">Administrateur</option>
                        <option value="super_admin" <?= $est_super_admin ? '' : 'disabled'; ?>>Super-administrateur</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="telephone">Téléphone :</label><br>
                    <input type="number" id="telephone" name="telephone"min="0100000000" max="9999999999" value="<?= htmlspecialchars($user['telephone'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="mot_de_passe">Mot de passe :</label><br>
                    <input type="text" id="mot_de_passe" name="mot_de_passe" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{5,}">
                </div>
                <div class="form-group">
                    <label for="email">Adresse mail :</label><br>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <button type="submit" name="modification" class="deconnexion">Modifier</button>
                </div>
            </form>
        <?php else: ?>
            <p>Nom : <?= htmlspecialchars($user['nom']) ?></p>
            <p>Prénom : <?= htmlspecialchars($user['prenom']) ?></p>
            <p>Poste : <?=$user['poste'] == 'admin' ? htmlspecialchars('Administrateur') : htmlspecialchars('Super-administrateur');?></p>
            <p>Téléphone : <?= htmlspecialchars($user['telephone']) ?></p>
            <p>E-mail : <?= htmlspecialchars($user['email']) ?></p>
            <p>Date de création : <?= htmlspecialchars($user['date_creation']) ?></p>
            <p>Date de dernière connexion : <?= htmlspecialchars($user['date_derniere_connexion']) ?></p><br>
            <div class="actions">
                <form method="POST" style="display: inline;">
                    <button type="submit" name="modifier" class="action-button">Modifier mes informations</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>
