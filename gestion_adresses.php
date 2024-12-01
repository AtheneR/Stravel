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

    $gares = null;
    $adresses = null;
    $message = "";
    $adresse_details_suppr = null;
    $adresse_details_modif = null;

    $connexiongares = "SELECT gare.*, adresse.ville
        FROM gare
        LEFT JOIN adresse ON gare.id_adresse = adresse.id_adresse
        ORDER BY gare.nom ASC
        ";
    $requete_gares = $bdd->prepare($connexiongares);
    $requete_gares->execute();
    $gares = $requete_gares->fetchAll();

    $connexionadresses = "SELECT * FROM adresse";
    $requete_adresses = $bdd->prepare($connexionadresses);
    $requete_adresses->execute();
    $adresses = $requete_adresses->fetchAll();
    
    $afficher_formulaire_ajout = isset($_POST['ajouter_adresse']);
    $afficher_formulaire_modif = isset($_POST['modifier_adresse']);
    $afficher_formulaire_suppr = isset($_POST['supprimer_adresse']);

    // var_dump($_POST);
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['valider'])) {
        // print_r('\n nentree \n');
        $numero = $_POST['numero'];
        $rue = $_POST['rue'];
        $ville = $_POST['ville'];
        
        if($_POST['code_postal']>99999){
            $message = "Veuillez vérifier votre code postal."
        } else {
            $code_postal = $_POST['code_postal'];

            $sql = "INSERT INTO adresse(numero, rue, ville, code_postal) VALUES (:numero, :rue, :ville, :code_postal)";
            $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            try {
                $stmt = $bdd->prepare($sql);
                $stmt->execute([
                    ':numero' => $numero,
                    ':rue' => $rue,
                    ':ville' => $ville,
                    ':code_postal' => $code_postal
                ]);
                // var_dump($stmt);

                header("Location: gestion_adresses.php?reussite_ajout=1");
                exit();
            } catch (PDOException $e) {
                $message = "Erreur lors de l'ajout de l'adresse' : " . $e->getMessage();
            }
        }
    }

    if (isset($_POST['suppression']) && !empty($_POST['id_adresse'])) {
        $id_adresse = $_POST['id_adresse'];
        // var_dump($id_gare);
        $sql = "DELETE FROM adresse WHERE id_adresse = :id_adresse";
        try {
            $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $bdd->prepare($sql);
            $stmt->execute([
                ':id_adresse' => $id_adresse,
            ]);

            header("Location: gestion_adresses.php?reussite_suppr=1");
            exit();
        } catch (PDOException $e) {
            $message = "Erreur lors de la suppression de l'adresse : " . $e->getMessage();
        }
    }

    if ($afficher_formulaire_suppr){
        $sql = "SELECT * FROM adresse";
        $stmt = $bdd->prepare($sql);
        // var_dump($stmt);
        $stmt->execute();
        $adresses = $stmt->fetchAll();
        if (empty($adresses)) {
            $adresses = "Il n'y a aucune adresse enregistrée.";
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_adresse']) && isset($_POST['suppr']) ) {
        // var_dump($_POST['id_adresse']);
        $id_adresse = $_POST['id_adresse'];
        $sql_details = "SELECT * FROM adresse WHERE adresse.id_adresse = :id_adresse";

        $stmt_details = $bdd->prepare($sql_details);
        $stmt_details->execute([
            'id_adresse' => $id_adresse
        ]);
        $adresse_details_suppr = $stmt_details->fetch();
        var_dump($adresse_details_suppr);
    }

    if ($afficher_formulaire_modif){
        $sql = "SELECT gare.*, adresse.ville, adresse.code_postal, adresse.numero, adresse.rue
            FROM gare
            LEFT JOIN adresse ON gare.id_adresse = adresse.id_adresse
            ORDER BY gare.nom ASC
        ";
        $stmt = $bdd->prepare($sql);
        // var_dump($stmt);
        $stmt->execute();
        $gares = $stmt->fetchAll();
        // var_dump($gares);
        if (empty($gares)) {
            $message = "Il n'y a aucune gare existante.";
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modifier']) && isset($_POST['id_adresse'])) {
        // var_dump($_POST['id_adresse']);
        $id_adresse = $_POST['id_adresse'];
        $sql_details = "SELECT * FROM adresse WHERE adresse.id_adresse = :id_adresse";

        $stmt_details = $bdd->prepare($sql_details);
        $stmt_details->execute([
            'id_adresse' => $id_adresse
        ]);
        $adresse_details_modif = $stmt_details->fetch();
        
        // var_dump($adresse_details_modif);
        // var_dump($id_train);
    }
    
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['modification']) && isset($_POST['id_adresse'])) {
        // print_r("passage\n");
        $numero = !empty($_POST["numero"]) ? $_POST["numero"] : $adresse_details_modif['numero'];
        $rue = !empty($_POST["rue"]) ? $_POST["rue"] : $adresse_details_modif['rue'];
        $ville = !empty($_POST["ville"]) ? $_POST["ville"] : $adresse_details_modif['ville'];
        $code_postal = !empty($_POST["code_postal"]) ? $_POST["code_postal"] : $adresse_details_modif['code_postal'];
        $id_adresse = $_POST['id_adresse'];
        var_dump($code_postal);
        $sql = "UPDATE adresse 
                SET numero = :numero, 
                    rue = :rue, 
                    ville = :ville, 
                    code_postal = :code_postal
                WHERE id_adresse = :id_adresse";
    
        try {
            $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $requeteMiseAJour = $bdd->prepare($sql);
            $requeteMiseAJour->execute([
                'numero' => $numero,
                'rue' => $rue,
                'ville' => $ville,
                'code_postal' => $code_postal,
                'id_adresse' => $id_adresse,
            ]);
            // var_dump($requeteMiseAJour);
            header("Location: gestion_adresses.php?reussite_modif=1");
            exit();
        } catch (PDOException $e) {
            $message = "Erreur lors de la mise à jour de l'adresse : " . $e->getMessage();
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
        <h2>Gestion des adresses</h2>
        <?php if ($message): ?>
            <p class="centre"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
        <?php if (isset($_GET['reussite_ajout']) && $_GET['reussite_ajout'] == 1): ?>
            <h3>L'adresse a bien été ajoutée.</h3>
            <a href="gestion_adresses.php" class="action-button">Retour</a>
        <?php elseif (isset($_GET['reussite_modif']) && $_GET['reussite_modif'] == 1): ?>
            <h3>L'adresse a bien été modifiée.</h3>
            <a href="gestion_adresses.php" class="action-button">Retour</a>
        <?php elseif (isset($_GET['reussite_suppr']) && $_GET['reussite_suppr'] == 1): ?>
            <h3>L'adresse a bien été supprimée.</h3>
            <a href="gestion_adresses.php" class="action-button">Retour</a>
        <?php elseif (!$afficher_formulaire_ajout && !$afficher_formulaire_modif && !$afficher_formulaire_suppr && !$adresse_details_suppr && !$adresse_details_modif && !isset($_POST['modification'])): ?>
            <div class="actions">
                <form method="POST" style="display: inline;">
                    <button type="submit" name="ajouter_adresse" class="action-button">Ajouter une adresse</button>
                </form><br><br>
                <form method="POST" style="display: inline;">
                    <button type="submit" name="modifier_adresse" class="action-button">Modifier une adresse</button>
                </form><br><br>
                <form method="POST" style="display: inline;">
                    <button type="submit" name="supprimer_adresse" class="action-button">Supprimer une adresse</button>
                </form>
            </div>
        <?php elseif($afficher_formulaire_ajout): ?>
            <form method="POST" action="gestion_adresses.php">
                <div class="form-group">
                    <label for="numero">Numero :</label><br>
                    <input type="number" id="numero" name="numero" required>
                </div>
                <div class="form-group">
                    <label for="rue">Rue :</label><br>
                    <input type="text" id="rue" name="rue" required>
                </div>
                <div class="form-group">
                    <label for="ville">Ville :</label><br>
                    <input type="text" id="ville" name="ville" required>
                </div>
                <div class="form-group">
                    <label for="code_postal">Code postal :</label><br>
                    <input type="number" id="code_postal" name="code_postal" min="1000" required>
                </div>
                <div class="form-group">
                    <button type="submit" name="valider" class="action-button">Valider</button>
                    <a href="gestion_adresses.php" class="action-button">Retour</a>
                </div>
            </form>
        <?php elseif($afficher_formulaire_suppr): ?>
            <table>
                <thead>
                    <tr>
                        <th>Adresse</th>
                        <th>Ville</th>
                        <th>Voir les informations</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($adresses as $adresse): ?>
                        <tr>
                            <td><?= htmlspecialchars($adresse['numero']." ".$adresse['rue']); ?></td>
                            <td><?= htmlspecialchars($adresse['ville']); ?></td>
                            <td>
                                <form method="post" action="gestion_adresses.php">
                                    <input type="hidden" name="id_adresse" value="<?= htmlspecialchars($adresse['id_adresse']); //var_dump($train['id_train']); ?>">
                                    <button type="submit" name="suppr">Détails de l'adresse</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($adresse_details_suppr): ?>
            <h3>Détails de l'adresse</h3>
            <p>Numéro : <?= htmlspecialchars($adresse_details_suppr['numero']); ?></p>
            <p>Rue : <?= htmlspecialchars($adresse_details_suppr['rue']); ?></p>
            <p>Code postal : <?= htmlspecialchars($adresse_details_suppr['code_postal']); ?></p>
            <p>Ville : <?= htmlspecialchars($adresse_details_suppr['ville']); ?></p>
            <form method="post" action="gestion_adresses.php">
                <input type="hidden" name="id_adresse" value="<?= htmlspecialchars($id_adresse); ?>">
                <button type="submit" name="suppression" class="deconnexion">Supprimer l'adresse</button>
            </form>
            <a href="gestion_adresses.php">Retour</a>
        <?php elseif($afficher_formulaire_modif): ?>
            <table>
                <thead>
                    <tr>
                        <th>Adresse</th>
                        <th>Ville</th>
                        <th>Voir les informations</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($adresses as $adresse): ?>
                        <tr>
                            <td><?= htmlspecialchars($adresse['numero']." ".$adresse['rue']); ?></td>
                            <td><?= htmlspecialchars($adresse['ville']); ?></td>
                            <td>
                                <form method="post" action="gestion_adresses.php">
                                    <input type="hidden" name="id_adresse" value="<?= htmlspecialchars($adresse['id_adresse']); //var_dump($train['id_train']); ?>">
                                    <button type="submit" name="modifier">Détails de l'adresse</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($adresse_details_modif): ?>
            <form method="POST" action="gestion_adresses.php">
                <h3>Détails de l'adresse</h3>
                <div class="form-group">
                    <label for="numero">Numéro :</label><br>
                    <input type="number" id="numero" name="numero" value="<?= htmlspecialchars($adresse_details_modif['numero'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="rue">Rue :</label><br>
                    <input type="text" id="rue" name="rue" value="<?= htmlspecialchars($adresse_details_modif['rue'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="ville">Ville :</label><br>
                    <input type="text" id="ville" name="ville" value="<?= htmlspecialchars($adresse_details_modif['ville'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="code_postal">Code postal :</label><br>
                    <input type="number" id="code_postal" name="code_postal" min="1000" value="<?= htmlspecialchars($adresse_details_modif['code_postal'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <input type="hidden" name="id_adresse" value="<?= htmlspecialchars($id_adresse); ?>">
                    <button type="submit" name="modification" class="deconnexion">Modifier l'adresse</button>
                </div>
                <a href="gestion_adresses.php" class="action-button">Retour</a>
            </form>

        <?php endif; ?>
    </div>
</div>
