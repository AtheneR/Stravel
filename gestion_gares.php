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
    $gare_details_suppr = null;
    $gare_details_modif = null;

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
    
    $afficher_formulaire_ajout = isset($_POST['ajouter_gare']);
    $afficher_formulaire_modif = isset($_POST['modifier_gare']);
    $afficher_formulaire_suppr = isset($_POST['supprimer_gare']);

    // var_dump($_POST);
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['valider'])) {
        // print_r('\n nentree \n');
        $nb_quai = $_POST['nb_quai'];
        $nom = $_POST['nom'];
        $capacite_accueil = $_POST['capacite_accueil'];
        $horaire_ouverture = $_POST['horaire_ouverture'];
        $horaire_fermeture = $_POST['horaire_fermeture'];
        if($_POST['acces_mobilite_reduite']=="Oui"){
            $acces_mobilite_reduite = 1;
        } else {
            $acces_mobilite_reduite =0;
        }
        // $acces_mobilite_reduite = $_POST['acces_mobilite_reduite'];
        $id_adresse = $_POST['id_adresse'];

        $sql = "INSERT INTO gare (nb_quai, nom, capacite_accueil, horaire_ouverture, horaire_fermeture, acces_mobilite_reduite, id_adresse) 
                    VALUES (:nb_quai, :nom, :capacite_accueil, :horaire_ouverture, :horaire_fermeture, :acces_mobilite_reduite, :id_adresse)";
        $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        try {
            $stmt = $bdd->prepare($sql);
            $stmt->execute([
                ':nb_quai' => $nb_quai,
                ':nom' => $nom,
                ':capacite_accueil' => $capacite_accueil,
                ':horaire_ouverture' => $horaire_ouverture,
                ':horaire_fermeture' => $horaire_fermeture,
                ':acces_mobilite_reduite' => $acces_mobilite_reduite,
                ':id_adresse' => $id_adresse,
            ]);
            // var_dump($stmt);

            header("Location: gestion_gares.php?reussite_ajout=1");
            exit();
        } catch (PDOException $e) {
            $message = "Erreur lors de l'ajout de la gare : " . $e->getMessage();
        }
    }

    if (isset($_POST['suppression']) && !empty($_POST['id_gare'])) {
        $id_gare = $_POST['id_gare'];
        var_dump($id_gare);
        $sql = "DELETE FROM gare WHERE id_gare = :id_gare";
        try {
            $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $bdd->prepare($sql);
            $stmt->execute([
                ':id_gare' => $id_gare,
            ]);

            header("Location: gestion_gares.php?reussite_suppr=1");
            exit();
        } catch (PDOException $e) {
            $message = "Erreur lors de la suppression de la gare : " . $e->getMessage();
        }
    }

    if ($afficher_formulaire_suppr){
        $sql = "SELECT gare.*, adresse.ville, adresse.code_postal, adresse.numero, adresse.rue
            FROM gare
            LEFT JOIN adresse ON gare.id_adresse = adresse.id_adresse
            ORDER BY gare.nom ASC
        ";
        $stmt = $bdd->prepare($sql);
        // var_dump($stmt);
        $stmt->execute();
        $gares = $stmt->fetchAll();
        if (empty($gares)) {
            $message = "Il n'y a aucune gare enregistrée.";
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_gare']) && isset($_POST['suppr']) ) {
        // var_dump($_POST['id_train']);
        $id_gare = $_POST['id_gare'];
        $sql_details = "SELECT gare.*, adresse.ville, adresse.code_postal, adresse.numero, adresse.rue
            FROM gare
            LEFT JOIN adresse ON gare.id_adresse = adresse.id_adresse
            WHERE gare.id_gare = :id_gare
            ORDER BY gare.nom ASC
        ";

        $stmt_details = $bdd->prepare($sql_details);
        $stmt_details->execute([
            'id_gare' => $id_gare
        ]);
        $gare_details_suppr = $stmt_details->fetch();
        //var_dump($gare_details_suppr);
    }

    if ($afficher_formulaire_modif){
        $sql = "
            SELECT 
                t.id_train, 
                t.jour_trajet, 
                g1.nom AS gare_depart, 
                g2.nom AS gare_arrivee
            FROM train t
            LEFT JOIN gare g1 ON t.id_gare_depart = g1.id_gare
            LEFT JOIN gare g2 ON t.id_gare_arrivee = g2.id_gare
            WHERE t.jour_trajet >= CURRENT_DATE()
            ORDER BY t.jour_trajet ASC
        ";
        $stmt = $bdd->prepare($sql);
        // var_dump($stmt);
        $stmt->execute();
        $gares = $stmt->fetchAll();
        if (empty($gares)) {
            $message = "Il n'y a aucun train à venir.";
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modifier']) && isset($_POST['id_train'])) {
        $id_train = $_POST['id_train'];
    
        $sql_details = "
            SELECT 
                t.jour_trajet, 
                t.nb_places,
                t.heure_depart, 
                t.heure_arrivee, 
                t.type, 
                t.id_gare_depart, 
                t.id_gare_arrivee
            FROM train t
            WHERE t.id_train = :id_train
        ";
    
        $stmt_details = $bdd->prepare($sql_details);
        $stmt_details->execute([
            'id_train' => $id_train
        ]);
    
        $gare_details_modif = $stmt_details->fetch();
        // var_dump($gare_details_modif);
        // var_dump($id_train);
    }
    
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['modification']) && isset($_POST['id_train'])) {
        print_r("passage\n");
        $gare_depart = !empty($_POST["gare_depart"]) ? $_POST["gare_depart"] : $gare_details_modif['gare_depart'];
        $gare_arrivee = !empty($_POST["gare_arrivee"]) ? $_POST["gare_arrivee"] : $gare_details_modif['gare_arrivee'];
        $jour_trajet = !empty($_POST["jour_trajet"]) ? $_POST["jour_trajet"] : $gare_details_modif['jour_trajet'];
        $horaire_depart = !empty($_POST["horaire_depart"]) ? $_POST["horaire_depart"] : $gare_details_modif['heure_depart'];
        $horaire_arrivee = !empty($_POST["horaire_arrivee"]) ? $_POST["horaire_arrivee"] : $gare_details_modif['heure_arrivee'];
        $type = !empty($_POST["type"]) ? $_POST["type"] : $gare_details_modif['type'];
        $nb_places = !empty($_POST["nb_places"]) ? $_POST["nb_places"] : $gare_details_modif['nb_places'];
        $id_train = $_POST['id_train'];
    
        $sql = "UPDATE train 
                SET id_gare_depart = :gare_depart, 
                    id_gare_arrivee = :gare_arrivee, 
                    jour_trajet = :jour_trajet, 
                    heure_depart = :horaire_depart, 
                    heure_arrivee = :horaire_arrivee, 
                    type = :type, 
                    nb_places = :nb_places 
                WHERE id_train = :id_train";
    
        try {
            $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $requeteMiseAJour = $bdd->prepare($sql);
            $requeteMiseAJour->execute([
                'gare_depart' => $gare_depart,
                'gare_arrivee' => $gare_arrivee,
                'jour_trajet' => $jour_trajet,
                'horaire_depart' => $horaire_depart,
                'horaire_arrivee' => $horaire_arrivee,
                'type' => $type,
                'nb_places' => $nb_places,
                'id_train' => $id_train,
            ]);
            var_dump($requeteMiseAJour);
            header("Location: gestion_gares.php?reussite_modif=1");
            exit();
        } catch (PDOException $e) {
            $message = "Erreur lors de la mise à jour du train : " . $e->getMessage();
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

<!-- <nav class="navbar">
    <a href="accueil_administrateur.php"><img src="logo_couleur.png" alt="Logo Starvel" class="logo" /></a>
    <a href="gestion_trains.php">Gestion des gares</a>
    <a href="gestion_gares.php">Gestion des gares</a>
    <a href="gestion_administrateur.php">Gestion des administrateurs</a>
    <a href="gestion_adresses.php">Gestion des adresses</a>
    <a href="connexion.php?action=logout" class="deconnexion">Déconnexion</a>
</nav> -->

<div class="container">
    <div class="informations">
        <h2>Gestion des gares</h2>
        <?php if (isset($_GET['reussite_ajout']) && $_GET['reussite_ajout'] == 1): ?>
            <h3>La gare a bien été ajoutée.</h3>
            <a href="gestion_gares.php" class="action-button">Retour</a>
        <?php elseif (isset($_GET['reussite_modif']) && $_GET['reussite_modif'] == 1): ?>
            <h3>La gare a bien été modifiée.</h3>
            <a href="gestion_gares.php" class="action-button">Retour</a>
        <?php elseif (isset($_GET['reussite_suppr']) && $_GET['reussite_suppr'] == 1): ?>
            <h3>La gare a bien été supprimée.</h3>
            <a href="gestion_gares.php" class="action-button">Retour</a>
        <?php elseif (!$afficher_formulaire_ajout && !$afficher_formulaire_modif && !$afficher_formulaire_suppr && !$gare_details_suppr && !$gare_details_modif && !isset($_POST['modification'])): ?>
            <div class="actions">
                <form method="POST" style="display: inline;">
                    <button type="submit" name="ajouter_gare" class="action-button">Ajouter une gare</button>
                </form><br><br>
                <form method="POST" style="display: inline;">
                    <button type="submit" name="modifier_gare" class="action-button">Modifier une gare</button>
                </form><br><br>
                <form method="POST" style="display: inline;">
                    <button type="submit" name="supprimer_gare" class="action-button">Supprimer une gare</button>
                </form>
            </div>
        <?php elseif($afficher_formulaire_ajout): ?>
            <form method="POST" action="gestion_gares.php">
                <div class="form-group">
                    <label for="nom">Nom :</label><br>
                    <input type="text" id="nom" name="nom" required>
                </div>
                <div class="form-group">
                    <label for="id_adresse">Adresse :</label><br>
                    <select id="id_adresse" name="id_adresse" required>
                        <option value="" disabled>Sélectionnez une adresse</option>
                        <?php if (!empty($adresses)): ?>
                            <?php foreach ($adresses as $adresse): ?>
                                <option value="<?= $adresse['id_adresse']; ?>"><?= htmlspecialchars($adresse['numero']." ".$adresse['rue'].", ".$adresse['code_postal']." ".$adresse['ville']); ?></option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="">Aucune adresse disponible</option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="nb_quai">Nombre de quais :</label><br>
                    <input type="number" id="nb_quai" name="nb_quai" min="1" required>
                </div>
                <div class="form-group">
                    <label for="capacite_accueil">Capacité d'accueil:</label><br>
                    <input type="number" id="capacite_accueil" name="capacite_accueil" min="1" required>
                </div>
                <div class="form-group">
                    <label for="horaire_ouverture">Horaire d'ouverture :</label><br>
                    <input type="time" id="horaire_ouverture" name="horaire_ouverture" required>
                </div>
                <div class="form-group">
                    <label for="horaire_fermeture">Horaire de fermeture :</label><br>
                    <input type="time" id="horaire_fermeture" name="horaire_fermeture" required>
                </div>
                <div class="form-group">
                    <label for="acces_mobilite_reduite">Accès pour les personnes à mobilité réduite :</label>
                    <select id="acces_mobilite_reduite" name="acces_mobilite_reduite" required>
                        <option value="oui">Oui</option>
                        <option value="non">Non</option>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" name="valider" class="action-button">Valider</button>
                    <a href="gestion_gares.php" class="action-button">Retour</a>
                </div>
            </form>
        <?php elseif($afficher_formulaire_suppr): ?>
            <table>
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Ville</th>
                        <th>Voir les informations</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($gares as $gare): ?>
                        <tr>
                            <td><?= htmlspecialchars($gare['nom']); ?></td>
                            <td><?= htmlspecialchars($gare['ville']); ?></td>
                            <td>
                                <form method="post" action="gestion_gares.php">
                                    <input type="hidden" name="id_gare" value="<?= htmlspecialchars($gare['id_gare']); //var_dump($train['id_train']); ?>">
                                    <button type="submit" name="suppr">Détails de la gare</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($gare_details_suppr): ?>
            <h3>Détails de la gare</h3>
            <p>Nom : <?= htmlspecialchars($gare_details_suppr['nom']); ?></p>
            <p>Adresse : <?= htmlspecialchars($gare_details_suppr['numero']." ".$gare_details_suppr['rue'].", ".$gare_details_suppr['code_postal']." ".$gare_details_suppr['ville']); ?></p>
            <p>Nombre de quais : <?= htmlspecialchars($gare_details_suppr['nb_quai']); ?></p>
            <p>Capacité d'accueil : <?= htmlspecialchars($gare_details_suppr['capacite_accueil']); ?></p>
            <p>Horaire d'ouverture : <?= htmlspecialchars($gare_details_suppr['horaire_ouverture']); ?></p>
            <p>Horaire de fermeture : <?= htmlspecialchars($gare_details_suppr['horaire_fermeture']); ?></p>
            <p>Accès aux personnes à mobilité réduite : 
                <?= htmlspecialchars($gare_details_suppr['acces_mobilite_reduite'] == 1 ? "Oui" : "Non"); ?>
            </p>
            <form method="post" action="gestion_gares.php">
                <input type="hidden" name="id_gare" value="<?= htmlspecialchars($id_gare); ?>">
                <button type="submit" name="suppression" class="deconnexion">Supprimer la gare</button>
            </form>
            <a href="gestion_gares.php">Retour</a>
        <?php elseif($afficher_formulaire_modif): ?>
            <table>
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Ville</th>
                        <th>Voir les informations</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($gares as $train): ?>
                        <tr>
                            <td><?= htmlspecialchars($train['jour_trajet']); // var_dump($train['id_train']);?></td>
                            <td><?= htmlspecialchars($train['gare_depart']); ?></td>
                            <td>
                                <form method="post" action="gestion_gares.php">
                                    <input type="hidden" name="id_train" value="<?= htmlspecialchars($train['id_train']); ?>">
                                    <button type="submit" name="modifier">Détails du trajet</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($gare_details_modif): ?>
            <form method="POST" action="gestion_gares.php">
                <h3>Détails du trajet</h3>
                <div class="form-group"> 
                    <label for="gare_depart">Gare de départ :</label><br>
                    <select id="gare_depart" name="gare_depart">
                        <option value="" disabled <?= empty($gare_details_modif['id_gare_depart']) ? 'selected' : ''; ?>>
                            Sélectionnez une gare
                        </option>
                        <?php if (!empty($gares)): ?>
                            <?php foreach ($gares as $gare): ?>
                                <option value="<?= htmlspecialchars($gare['id_gare']); ?>"
                                    <?= $gare['id_gare'] == $gare_details_modif['id_gare_depart'] ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($gare['nom']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="">Aucune gare disponible</option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="form-group"> 
                    <label for="gare_arrivee">Gare d'arrivée :</label><br>
                    <select id="gare_arrivee" name="gare_arrivee">
                        <option value="" disabled <?= empty($gare_details_modif['id_gare_arrivee']) ? 'selected' : ''; ?>>
                            Sélectionnez une gare
                        </option>
                        <?php if (!empty($gares)): ?>
                            <?php foreach ($gares as $gare): ?>
                                <option value="<?= htmlspecialchars($gare['id_gare']); ?>"
                                    <?= $gare['id_gare'] == $gare_details_modif['id_gare_arrivee'] ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($gare['nom']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="">Aucune gare disponible</option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="jour_trajet">Jour :</label><br>
                    <input type="date" id="jour_trajet" name="jour_trajet" value="<?= htmlspecialchars($gare_details_modif['jour_trajet'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="horaire_depart">Horaire de départ :</label><br>
                    <input type="time" id="horaire_depart" name="horaire_depart" 
                        value="<?= htmlspecialchars(substr($gare_details_modif['heure_depart'] ?? '', 0, 5)) ?>">
                </div>
                <div class="form-group">
                    <label for="horaire_arrivee">Horaire d'arrivée :</label><br>
                    <input type="time" id="horaire_arrivee" name="horaire_arrivee" 
                        value="<?= htmlspecialchars(substr($gare_details_modif['heure_arrivee'] ?? '', 0, 5)) ?>">
                </div>
                <div class="form-group">
                    <label for="type_train">Type de train :</label>
                    <select id="type_train" name="type_train">
                        <option value="TGV" <?= $gare_details_modif['type'] === 'TGV' ? 'selected' : ''; ?>>TGV</option>
                        <option value="Intercite" <?= $gare_details_modif['type'] === 'Intercite' ? 'selected' : ''; ?>>Intercite</option>
                        <option value="TER" <?= $gare_details_modif['type'] === 'TER' ? 'selected' : ''; ?>>TER</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="capacite">Capacité :</label><br>
                    <input type="number" id="capacite" name="capacite" min="1" 
                        value="<?= htmlspecialchars($gare_details_modif['nb_places'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <input type="hidden" name="id_train" value="<?= htmlspecialchars($id_train); ?>">
                    <button type="submit" name="modification" class="deconnexion">Modifier le train</button>
                </div>
                <a href="gestion_gares.php" class="action-button">Retour</a>
            </form>

        <?php endif; ?>
        <?php if ($message): ?>
            <p class="centre"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
    </div>
</div>
