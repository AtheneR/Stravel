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

    $connexiongares = "SELECT id_gare, nom FROM gare";
    $requete_gares = $bdd->prepare($connexiongares);
    $requete_gares->execute();
    $gares = $requete_gares->fetchAll();
    
    $trains = null;
    $message = "";
    $train_details_suppr = null;
    $train_details_modif = null;

    $afficher_formulaire_ajout = isset($_POST['ajouter_train']);
    $afficher_formulaire_modif = isset($_POST['modifier_train']);
    $afficher_formulaire_suppr = isset($_POST['supprimer_train']);

    // var_dump($_POST);
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['gare_depart'])) {
        // print_r('entree');
        $gare_depart = $_POST['gare_depart'];
        $gare_arrivee = $_POST['gare_arrivee'];
        $jour_trajet = $_POST['jour_trajet'];
        $horaire_depart = $_POST['horaire_depart'];
        $horaire_arrivee = $_POST['horaire_arrivee'];
        $type = $_POST['type'];
        $nb_places = $_POST['nb_places'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['valider'])) {    
            $sql = "INSERT INTO train (nb_places, id_gare_depart, id_gare_arrivee, heure_depart, heure_arrivee, jour_trajet, type) 
                    VALUES (:nb_places, :gare_depart, :gare_arrivee, :horaire_depart, :horaire_arrivee, :jour_trajet, :type)";
            $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            try {
                $stmt = $bdd->prepare($sql);
                // echo "Requête exécutée : " . $sql . "\n";

                $stmt->execute([
                    ':nb_places' => $nb_places,
                    ':gare_depart' => $gare_depart,
                    ':gare_arrivee' => $gare_arrivee,
                    ':horaire_depart' => $horaire_depart,
                    ':horaire_arrivee' => $horaire_arrivee,
                    ':jour_trajet' => $jour_trajet,
                    ':type' => $type,
                ]);

                header("Location: gestion_trains.php?reussite_ajout=1");
                exit();
            } catch (PDOException $e) {
                $message = "Erreur lors de l'ajout du train : " . $e->getMessage();
            }
        } else {
            $message = "Veuillez remplir tous les champs du formulaire.";
        }
    }

    if (isset($_POST['suppression']) && !empty($_POST['id_train'])) {
        $id_train = $_POST['id_train'];
        // var_dump($id_train);
        $sql = "DELETE FROM train WHERE id_train = :id_train";
        try {
            $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $bdd->prepare($sql);
            $stmt->execute([
                ':id_train' => $id_train,
            ]);

            header("Location: gestion_trains.php?reussite_suppr=1");
            exit();
        } catch (PDOException $e) {
            $message = "Erreur lors de la suppression du train : " . $e->getMessage();
        }
    }

    if ($afficher_formulaire_suppr){
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
        $trains = $stmt->fetchAll();
        if (empty($trains)) {
            $message = "Il n'y a aucun train à venir.";
        }
        
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_train']) && isset($_POST['suppr']) ) {
        // var_dump($_POST['id_train']);
        $id_train = $_POST['id_train'];
        $sql_details = "
            SELECT 
                t.jour_trajet, 
                t.nb_places,
                t.heure_depart, 
                t.heure_arrivee, 
                t.type, 
                g1.nom AS gare_depart, 
                g2.nom AS gare_arrivee,
                (SELECT COUNT(*) FROM reservation r WHERE r.id_train = t.id_train) AS nb_reservations_actuelles
            FROM train t
            LEFT JOIN gare g1 ON t.id_gare_depart = g1.id_gare
            LEFT JOIN gare g2 ON t.id_gare_arrivee = g2.id_gare
            WHERE t.id_train = :id_train
        ";

        $stmt_details = $bdd->prepare($sql_details);
        $stmt_details->execute([
            'id_train' => $id_train
        ]);
        $train_details_suppr = $stmt_details->fetch();
        //var_dump($train_details_suppr);
        $nb_places_libres = $train_details_suppr['nb_places'] - $train_details_suppr['nb_reservations_actuelles'];
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
        // print_r("\n");
        // var_dump($stmt);
        // print_r("\n");
        $stmt->execute();
        $trains = $stmt->fetchAll();
        if (empty($trains)) {
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
    
        $train_details_modif = $stmt_details->fetch();
        // print_r("\n");
        // var_dump($train_details_modif);
        // var_dump($id_train);
    }
    
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['modification']) && isset($_POST['id_train'])) {
        print_r("passage\n");
        $gare_depart = !empty($_POST["gare_depart"]) ? $_POST["gare_depart"] : $train_details_modif['gare_depart'];
        $gare_arrivee = !empty($_POST["gare_arrivee"]) ? $_POST["gare_arrivee"] : $train_details_modif['gare_arrivee'];
        $jour_trajet = !empty($_POST["jour_trajet"]) ? $_POST["jour_trajet"] : $train_details_modif['jour_trajet'];
        $horaire_depart = !empty($_POST["horaire_depart"]) ? $_POST["horaire_depart"] : $train_details_modif['heure_depart'];
        $horaire_arrivee = !empty($_POST["horaire_arrivee"]) ? $_POST["horaire_arrivee"] : $train_details_modif['heure_arrivee'];
        $type = !empty($_POST["type"]) ? $_POST["type"] : $train_details_modif['type'];
        $nb_places = !empty($_POST["nb_places"]) ? $_POST["nb_places"] : $train_details_modif['nb_places'];
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
            // var_dump($requeteMiseAJour);
            header("Location: gestion_trains.php?reussite_modif=1");
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
    <a href="gestion_trains.php">Gestion des trains</a>
    <a href="gestion_gares.php">Gestion des gares</a>
    <a href="gestion_administrateur.php">Gestion des administrateurs</a>
    <a href="gestion_adresses.php">Gestion des adresses</a>
    <a href="connexion.php?action=logout" class="deconnexion">Déconnexion</a>
</nav> -->

<div class="container">
    <div class="informations">
        <h2>Gestion des trains</h2>
        <?php if (isset($_GET['reussite_ajout']) && $_GET['reussite_ajout'] == 1): ?>
            <h3>Le train a bien été ajouté.</h3>
            <a href="gestion_trains.php" class="action-button">Retour</a>
        <?php elseif (isset($_GET['reussite_modif']) && $_GET['reussite_modif'] == 1): ?>
            <h3>Le train a bien été modifié.</h3>
            <a href="gestion_trains.php" class="action-button">Retour</a>
        <?php elseif (isset($_GET['reussite_suppr']) && $_GET['reussite_suppr'] == 1): ?>
            <h3>Le train a bien été supprimé.</h3>
            <a href="gestion_trains.php" class="action-button">Retour</a>
        <?php elseif (!$afficher_formulaire_ajout && !$afficher_formulaire_modif && !$afficher_formulaire_suppr && !$train_details_suppr && !$train_details_modif && !isset($_POST['modification'])): ?>
            <div class="actions">
                <form method="POST" style="display: inline;">
                    <button type="submit" name="ajouter_train" class="action-button">Ajouter un train</button>
                </form><br><br>
                <form method="POST" style="display: inline;">
                    <button type="submit" name="modifier_train" class="action-button">Modifier un train</button>
                </form><br><br>
                <form method="POST" style="display: inline;">
                    <button type="submit" name="supprimer_train" class="action-button">Supprimer un train</button>
                </form>
            </div>
        <?php elseif($afficher_formulaire_ajout): ?>
            <form method="POST" action="gestion_trains.php">
                <div class="form-group">
                    <label for="gare_depart">Gare de départ :</label><br>
                    <select id="gare_depart" name="gare_depart" required>
                        <option value="" disabled>Sélectionnez une gare</option>
                        <?php if (!empty($gares)): ?>
                            <?php foreach ($gares as $gare): ?>
                                <option value="<?= $gare['id_gare']; ?>"><?= htmlspecialchars($gare['nom']); ?></option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="">Aucune gare disponible</option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="gare_arrivee">Gare d'arrivée :</label><br>
                    <select id="gare_arrivee" name="gare_arrivee" required>
                        <option value="" disabled>Sélectionnez une gare</option>
                        <?php if (!empty($gares)): ?>
                            <?php foreach ($gares as $gare): ?>
                                <option value="<?= $gare['id_gare']; ?>"><?= htmlspecialchars($gare['nom']); ?></option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="">Aucune gare disponible</option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="jour_trajet">Jour :</label><br>
                    <input type="date" id="jour_trajet" name="jour_trajet" required>
                </div>
                <div class="form-group">
                    <label for="horaire_depart">Horaire de départ :</label><br>
                    <input type="time" id="horaire_depart" name="horaire_depart" required>
                </div>
                <div class="form-group">
                    <label for="horaire_arrivee">Horaire d'arrivée :</label><br>
                    <input type="time" id="horaire_arrivee" name="horaire_arrivee" required>
                </div>
                <div class="form-group">
                    <label for="type">Type de train :</label><br>
                    <select id="type" name="type" required>
                        <option value="TGV">TGV</option>
                        <option value="Intercite">Intercite</option>
                        <option value="TER">TER</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="nb_places">Capacité :</label><br>
                    <input type="number" id="nb_places" name="nb_places" min="1" required>
                </div>
                <div class="form-group">
                    <button type="submit" name="valider" class="action-button">Valider</button>
                    <a href="gestion_trains.php" class="action-button">Retour</a>
                </div>

            </form>
        <?php elseif($afficher_formulaire_suppr): ?>
            <table>
                <thead>
                    <tr>
                        <th>Jour du trajet</th>
                        <th>Gare de départ</th>
                        <th>Gare d'arrivée</th>
                        <th>Voir les informations</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($trains as $train): ?>
                        <tr>
                            <td><?= htmlspecialchars($train['jour_trajet']); ?></td>
                            <td><?= htmlspecialchars($train['gare_depart']); ?></td>
                            <td><?= htmlspecialchars($train['gare_arrivee']);?></td>
                            <td>
                                <form method="post" action="gestion_trains.php">
                                    <input type="hidden" name="id_train" value="<?= htmlspecialchars($train['id_train']); //var_dump($train['id_train']); ?>">
                                    <button type="submit" name="suppr">Détails du trajet</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($train_details_suppr): ?>
            <h3>Détails du trajet</h3>
            <p>Jour du trajet : <?= htmlspecialchars($train_details_suppr['jour_trajet']); ?></p>
            <p>Gare de départ : <?= htmlspecialchars($train_details_suppr['gare_depart']); ?></p>
            <p>Gare d'arrivée : <?= htmlspecialchars($train_details_suppr['gare_arrivee']); ?></p>
            <p>Heure de départ : <?= htmlspecialchars($train_details_suppr['heure_depart']); ?></p>
            <p>Heure d'arrivée : <?= htmlspecialchars($train_details_suppr['heure_arrivee']); ?></p>
            <p>Type : <?= htmlspecialchars($train_details_suppr['type']); ?></p>
            <p>Nombre de places total : <?= htmlspecialchars($train_details_suppr['nb_places']); ?></p>
            <p>Nombre de places réservées : <?= htmlspecialchars($train_details_suppr['nb_reservations_actuelles']); ?></p>
            <p>Nombre de places libres : <?= htmlspecialchars($nb_places_libres); ?></p>
            <form method="post" action="gestion_trains.php">
                <input type="hidden" name="id_train" value="<?= htmlspecialchars($id_train); ?>">
                <button type="submit" name="suppression" class="deconnexion">Supprimer le train</button>
            </form>
            <a href="gestion_trains.php">Retour</a>
        <?php elseif($afficher_formulaire_modif): ?>
            <table>
                <thead>
                    <tr>
                        <th>Jour du trajet</th>
                        <th>Gare de départ</th>
                        <th>Gare d'arrivée</th>
                        <th>Voir les informations</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($trains as $train): ?>
                        <tr>
                            <td><?= htmlspecialchars($train['jour_trajet']); // var_dump($train['id_train']);?></td>
                            <td><?= htmlspecialchars($train['gare_depart']); ?></td>
                            <td><?= htmlspecialchars($train['gare_arrivee']);?></td>
                            <td>
                                <form method="post" action="gestion_trains.php">
                                    <input type="hidden" name="id_train" value="<?= htmlspecialchars($train['id_train']); ?>">
                                    <button type="submit" name="modifier">Détails du trajet</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($train_details_modif): ?>
            <form method="POST" action="gestion_trains.php">
                <h3>Détails du trajet</h3>
                <div class="form-group"> 
                    <label for="gare_depart">Gare de départ :</label><br>
                    <select id="gare_depart" name="gare_depart">
                        <option value="" disabled <?= empty($train_details_modif['id_gare_depart']) ? 'selected' : ''; ?>>
                            Sélectionnez une gare
                        </option>
                        <?php if (!empty($gares)): ?>
                            <?php foreach ($gares as $gare): ?>
                                <option value="<?= htmlspecialchars($gare['id_gare']); ?>"
                                    <?= $gare['id_gare'] == $train_details_modif['id_gare_depart'] ? 'selected' : ''; ?>>
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
                        <option value="" disabled <?= empty($train_details_modif['id_gare_arrivee']) ? 'selected' : ''; ?>>
                            Sélectionnez une gare
                        </option>
                        <?php if (!empty($gares)): ?>
                            <?php foreach ($gares as $gare): ?>
                                <option value="<?= htmlspecialchars($gare['id_gare']); ?>"
                                    <?= $gare['id_gare'] == $train_details_modif['id_gare_arrivee'] ? 'selected' : ''; ?>>
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
                    <input type="date" id="jour_trajet" name="jour_trajet" value="<?= htmlspecialchars($train_details_modif['jour_trajet'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="horaire_depart">Horaire de départ :</label><br>
                    <input type="time" id="horaire_depart" name="horaire_depart" 
                        value="<?= htmlspecialchars(substr($train_details_modif['heure_depart'] ?? '', 0, 5)) ?>">
                </div>
                <div class="form-group">
                    <label for="horaire_arrivee">Horaire d'arrivée :</label><br>
                    <input type="time" id="horaire_arrivee" name="horaire_arrivee" 
                        value="<?= htmlspecialchars(substr($train_details_modif['heure_arrivee'] ?? '', 0, 5)) ?>">
                </div>
                <div class="form-group">
                    <label for="type">Type de train :</label><br>
                    <select id="type" name="type">
                        <option value="TGV" <?= $train_details_modif['type'] === 'TGV' ? 'selected' : ''; ?>>TGV</option>
                        <option value="Intercite" <?= $train_details_modif['type'] === 'Intercite' ? 'selected' : ''; ?>>Intercite</option>
                        <option value="TER" <?= $train_details_modif['type'] === 'TER' ? 'selected' : ''; ?>>TER</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="nb_places">Capacité :</label><br>
                    <input type="number" id="nb_places" name="nb_places" min="1" 
                        value="<?= htmlspecialchars($train_details_modif['nb_places'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <input type="hidden" name="id_train" value="<?= htmlspecialchars($id_train); ?>">
                    <button type="submit" name="modification" class="deconnexion">Modifier le train</button>
                </div>
                <a href="gestion_trains.php" class="action-button">Retour</a>
            </form>

        <?php endif; ?>
        <?php if ($message): ?>
            <p class="centre"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
    </div>
</div>
