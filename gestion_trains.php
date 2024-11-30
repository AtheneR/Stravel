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
    $message = "";

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
        $type_train = $_POST['type_train'];
        $capacite = $_POST['capacite'];

        // print_r([
        //     ':capacite' => $capacite,
        //     ':gare_depart' => $gare_depart,
        //     ':gare_arrivee' => $gare_arrivee,
        //     ':horaire_depart' => $horaire_depart,
        //     ':horaire_arrivee' => $horaire_arrivee,
        //     ':jour_trajet' => $jour_trajet,
        //     ':type_train' => $type_train,
        // ]);
        if (!empty($gare_depart) && !empty($gare_arrivee) && !empty($jour_trajet) &&
            !empty($horaire_depart) && !empty($horaire_arrivee) && !empty($type_train) && !empty($capacite)) {
            
            $sql = "INSERT INTO train (nb_places, id_gare_depart, id_gare_arrivee, heure_depart, heure_arrivee, jour_trajet, type) 
                    VALUES (:nb_places, :gare_depart, :gare_arrivee, :horaire_depart, :horaire_arrivee, :jour_trajet, :type_train)";
            $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            try {
                $stmt = $bdd->prepare($sql);

                // echo "Requête exécutée : " . $sql . "\n";

                $stmt->execute([
                    ':nb_places' => $capacite,
                    ':gare_depart' => $gare_depart,
                    ':gare_arrivee' => $gare_arrivee,
                    ':horaire_depart' => $horaire_depart,
                    ':horaire_arrivee' => $horaire_arrivee,
                    ':jour_trajet' => $jour_trajet,
                    ':type_train' => $type_train,
                ]);
        
                // print_r([
                //     ':capacite' => $capacite,
                //     ':gare_depart' => $gare_depart,
                //     ':gare_arrivee' => $gare_arrivee,
                //     ':horaire_depart' => $horaire_depart,
                //     ':horaire_arrivee' => $horaire_arrivee,
                //     ':jour_trajet' => $jour_trajet,
                //     ':type_train' => $type_train,
                // ]);

                header("Location: gestion_trains.php?reussite_ajout=1");
                exit();
            } catch (PDOException $e) {
                $message = "Erreur lors de l'ajout du train : " . $e->getMessage();
            }
        } else {
            $message = "Veuillez remplir tous les champs du formulaire.";
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
        <?php elseif (!$afficher_formulaire_ajout): ?>
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
        <?php else: ?>
            <form method="POST" action="gestion_trains.php">
                <div class="form-group">
                    <label for="gare_depart">Gare de départ :</label><br>
                    <select id="gare_depart" name="gare_depart" required>
                        <option value="">Sélectionnez une gare</option>
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
                        <option value="">Sélectionnez une gare</option>
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
                    <label for="type_train">Type de train :</label>
                    <select id="type_train" name="type_train" required>
                        <option value="TGV">TGV</option>
                        <option value="Intercite">Intercite</option>
                        <option value="TER">TER</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="capacite">Capacité :</label><br>
                    <input type="number" id="capacite" name="capacite" min="1" required>
                </div>
                <div class="form-group">
                    <button type="submit" class="action-button">Valider</button>
                    <a href="gestion_trains.php" class="action-button">Retour</a>
                </div>
            </form>
        <?php endif; ?>
        <?php if ($message): ?>
            <p class="centre"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
    </div>
</div>
