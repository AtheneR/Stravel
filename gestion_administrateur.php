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
    $requete_connexion->execute(['id_administrateur' => $user_id]);
    $user = $requete_connexion->fetch();

    $est_super_admin = $user['poste'] === 'super_admin';

    $message = "";
    $admin_details_suppr = null;
    $admin_details_suppr = null;

    $afficher_formulaire_ajout = isset($_POST['ajouter_admin']);
    $afficher_formulaire_suppr = isset($_POST['supprimer_admin']);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($afficher_formulaire_suppr)) {
        if (!$est_super_admin) {
            $message = "Les administrateurs non super-administrateurs ne peuvent pas supprimer d'administrateur.";
        } else {
            $sql = "SELECT * FROM administrateur";
            $stmt = $bdd->prepare($sql);
            // var_dump($stmt);
            $stmt->execute();
            $admins = $stmt->fetchAll();
            if (empty($admins)) {
                $message = "Il n'y a aucun administrateur enregistré.";
            }
        }
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['valider'])) {
        $nom = $_POST['nom'];
        $prenom = $_POST['prenom'];
        $poste = $_POST['poste'];
        $email = $_POST['email'];
        $telephone = $_POST['telephone'];
        $mot_de_passe = password_hash($_POST['mot_de_passe'], PASSWORD_DEFAULT);

        $sql = "INSERT INTO administrateur(nom, prenom, poste, email, telephone, mot_de_passe, date_creation, date_derniere_connexion) VALUES (:nom,:prenom,:poste,:email,:telephone,:mot_de_passe,CURRENT_DATE(),CURRENT_DATE())";
        $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $req_admin = "SELECT * FROM administrateur WHERE email = :email";
        $verif_admin = $bdd->prepare($req_admin);
        $verif_admin->execute(['email' => $email]);
        $a_verif = $verif_admin->fetch();

        $req_inscrit = "SELECT * FROM utilisateur WHERE email = :email";
        $verif_inscrit = $bdd->prepare($req_inscrit);
        $verif_inscrit->execute(['email' => $email]);
        $u_verif = $verif_inscrit->fetch();

        if($u_verif || $a_verif){
            $message = 'Il y a déjà un compte lié à cet email.';
        } else {
            try {
                $stmt = $bdd->prepare($sql);
                $stmt->execute([
                    ':nom' => $nom,
                    ':prenom' => $prenom,
                    ':poste' => $poste,
                    ':email' => $email,
                    ':telephone' => $telephone,
                    ':mot_de_passe' => $mot_de_passe
                ]);
                // var_dump($stmt);
                header("Location: gestion_administrateur.php?reussite_ajout=1");
                exit();
            } catch (PDOException $e) {
                $message = "Erreur lors de l'ajout de l'administrateur' : " . $e->getMessage();
            }
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_administrateur']) && isset($_POST['suppr']) ) {
        // var_dump($_POST['id_administrateur']);
        $id_administrateur = $_POST['id_administrateur'];
        $sql_details = "SELECT * FROM administrateur WHERE id_administrateur = :id_administrateur";

        $stmt_details = $bdd->prepare($sql_details);
        $stmt_details->execute([
            'id_administrateur' => $id_administrateur
        ]);
        $admin_details_suppr = $stmt_details->fetch();
        //var_dump($admin_details_suppr);
    }

    if (isset($_POST['suppression']) && !empty($_POST['id_administrateur'])) {
        $id_administrateur = $_POST['id_administrateur'];
        // var_dump($id_gare);
        $sql = "DELETE FROM administrateur WHERE id_administrateur = :id_administrateur";
        try {
            $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $bdd->prepare($sql);
            $stmt->execute([
                ':id_administrateur' => $id_administrateur,
            ]);

            header("Location: gestion_administrateur.php?reussite_suppr=1");
            exit();
        } catch (PDOException $e) {
            $message = "Erreur lors de la suppression de l'administrateur' : " . $e->getMessage();
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
        <h2>Gestion des administrateurs</h2>
        <?php if ($message): ?>
            <p class="centre"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
        <?php if (isset($_GET['reussite_ajout']) && $_GET['reussite_ajout'] == 1): ?>
            <h3>L'administrateur a bien été ajouté.</h3>
            <a href="gestion_administrateur.php" class="action-button">Retour</a>
        <?php elseif (isset($_GET['reussite_suppr']) && $_GET['reussite_suppr'] == 1): ?>
            <h3>L'administrateur a bien été supprimé.</h3>
            <a href="gestion_administrateur.php" class="action-button">Retour</a>
        <?php elseif (!$afficher_formulaire_ajout && !$afficher_formulaire_suppr && !$admin_details_suppr): ?>
            <div class="actions">
                <form method="POST" style="display: inline;">
                    <button type="submit" name="ajouter_admin" class="action-button">Ajouter un administrateur</button>
                </form><br><br>
                <form method="POST" style="display: inline;">
                    <button type="submit" name="supprimer_admin" class="action-button" <?= !$est_super_admin ? 'disabled' : ''; ?>>
                        Supprimer un administrateur
                    </button>
                </form>
                <div class="commentaire">
                    <br><p>Seul un super-administrateur peut supprimer un autre administrateur.</p>
                </div>
            </div>
        <?php elseif($afficher_formulaire_ajout): ?>
            <form method="POST" action="gestion_administrateur.php">
                <div class="form-group">
                    <label for="nom">Nom :</label><br>
                    <input type="text" id="nom" name="nom" required>
                </div>
                <div class="form-group">
                    <label for="prenom">Prénom :</label><br>
                    <input type="text" id="prenom" name="prenom" required>
                </div>
                <div class="form-group">
                    <label for="poste">Poste :</label><br>
                    <select id="poste" name="poste" required>
                        <option value="admin">Administrateur</option>
                        <option value="super_admin" <?= $est_super_admin ? '' : 'disabled'; ?>>Super-administrateur</option>
                    </select>
                    <div class="commentaire">
                        <p>Seul un super-administrateur peut créer un autre super-administrateur.</p>
                    </div>
                </div>
                <div class="form-group">
                    <label for="email">Adresse mail :</label><br>
                    <input type="email" id="email" name="email" placeholder="nomprenom@gmail.com" required>
                </div>
                <div class="form-group">
                    <label for="telephone">Téléphone :</label><br>
                    <input type="tel" id="telephone" name="telephone" pattern="[0-9]{10}" placeholder="0122334455" required>
                </div>
                <div class="form-group commentaire">
                    <label for="mot_de_passe">Mot de passe : </label><br>
                    <input type="password" id="mot_de_passe" name="mot_de_passe" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{5,}" required><br>
                    <p>Le mot de passe doit contenir au moins 1 chiffre, 1 lettre majuscule, 1 lettre minuscule, et doit avoir au moins 8 caractères en tout.</p>
                </div>
                <div class="form-group">
                    <button type="submit" name="valider" class="action-button">Valider</button>
                    <a href="gestion_administrateur.php" class="action-button">Retour</a>
                </div>
            </form>
        <?php elseif($afficher_formulaire_suppr && $est_super_admin): ?>
            <table>
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Poste</th>
                        <th>Voir les informations</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($admins as $admin): ?>
                        <tr>
                            <td><?= htmlspecialchars($admin['nom']." ".$admin['prenom']); ?></td>
                            <td><?=$admin['poste'] == 'admin' ? htmlspecialchars('Administrateur') : htmlspecialchars('Super-administrateur');?></td>
                            <td>
                                <form method="post" action="gestion_administrateur.php">
                                    <input type="hidden" name="id_administrateur" value="<?= htmlspecialchars($admin['id_administrateur']); //var_dump($train['id_train']); ?>">
                                    <button type="submit" name="suppr">Détails de l'administrateur</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($admin_details_suppr): ?>
            <h3>Détails de l'administrateur</h3>
            <p>Nom : <?= htmlspecialchars($admin_details_suppr['nom']); ?></p>
            <p>Prénom : <?= htmlspecialchars($admin_details_suppr['prenom']); ?></p>
            <p>Poste : <?=$admin_details_suppr['poste'] == 'admin' ? htmlspecialchars('Super-administrateur') : htmlspecialchars('Administrateur');?></p>
            <p>Adresse mail : <?= htmlspecialchars($admin_details_suppr['email']); ?></p>
            <p>Téléphone : <?= htmlspecialchars($admin_details_suppr['telephone']); ?></p>
            <p>Date de création : <?= htmlspecialchars($admin_details_suppr['date_creation']); ?></p>
            <p>Date de dernière connexion : <?= htmlspecialchars($admin_details_suppr['date_derniere_connexion']); ?></p>
            <form method="post" action="gestion_administrateur.php">
                <input type="hidden" name="id_administrateur" value="<?= htmlspecialchars($id_administrateur); ?>">
                <button type="submit" name="suppression" class="deconnexion">Supprimer l'administrateur</button>
            </form>
            <a href="gestion_administrateur.php">Retour</a>
        <?php endif; ?>
    </div>
</div>
