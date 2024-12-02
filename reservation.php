<?php
    include_once('header.php');

    session_start();

    // on vérifie si l'utilisateur est connecté
    if (!isset($_SESSION['user_id'])) {
        // si l'utilisateur n'est pas connecté, on le redirige vers la page de connexion
        header('Location: connexion.php');
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $connexionutilisateur = "SELECT * FROM utilisateur WHERE id_utilisateur = :id";
    $requete_connexion = $bdd->prepare($connexionutilisateur);
    $requete_connexion->execute(['id' => $user_id]);
    $user = $requete_connexion->fetch();

    // on récupère les informations nécessaires plus tard
    $connexiongares = "SELECT id_gare, nom FROM gare";
    $requete_gares = $bdd->prepare($connexiongares);
    $requete_gares->execute();
    $gares = $requete_gares->fetchAll();

    $connexionreservations = "SELECT * FROM reservation";
    $requete_reservations = $bdd->prepare($connexionreservations);
    $requete_reservations->execute();
    $reservations = $requete_reservations->fetchAll();

    $trains = null;
    $message = "";
    $reussite = null;
    $train_selectionne = isset($_POST['id_train']) && !empty($_POST['id_train']);

    // on traite le cas où l'on appuie sur le bouton pour chercher des trains aux conditions entrées
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
        if ($_POST['action'] == "rechercher_train" && !$train_selectionne) {
            $gare_depart = $_POST['gare_depart'];
            $gare_arrivee = $_POST['gare_arrivee'];
            $jour_trajet = $_POST['jour_trajet'];

            // on vérifie que la gare de départ et la gare d'arrivée sont différentes
            if ($gare_depart !== $gare_arrivee) {
                $sql = "
                    SELECT t.*,
                        TIMEDIFF(t.heure_arrivee, t.heure_depart) AS duree,
                        t.nb_places - IFNULL(r.nb_reservations, 0) AS places_disponibles
                    FROM train t
                    LEFT JOIN (
                        SELECT id_train, COUNT(*) AS nb_reservations
                        FROM reservation
                        GROUP BY id_train
                    ) r ON t.id_train = r.id_train
                    WHERE t.id_gare_depart = :gare_depart
                    AND t.id_gare_arrivee = :gare_arrivee
                    AND (t.nb_places - IFNULL(r.nb_reservations, 0)) > 0
                    AND t.jour_trajet = :jour_trajet
                    ORDER BY t.heure_depart ASC
                ";

                $stmt = $bdd->prepare($sql);
                $stmt->execute([
                    'gare_depart' => $gare_depart,
                    'gare_arrivee' => $gare_arrivee,
                    'jour_trajet' => $jour_trajet,
                ]);

                // on regarde s'il y a un train disponible ce jour-ci entre ces deux gares

                $trains = $stmt->fetchAll();

                if (empty($trains)) {
                    $message = "Aucun trajet disponible pour la date sélectionnée.";
                }
            } else {
                $message = "Les gares de départ et d'arrivée doivent être différentes.";
            }
        } elseif ($_POST['action'] == "reserver_train") {
            $train_selectionne = true;
            $id_train = $_POST['id_train'];
            $jour_trajet = $_POST['jour_trajet'];
        }
    }

    // on effectue la réservation du voyageur pour le trajet sélectionné
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_train'], $_POST['jour_trajet'], $_POST['nom_voyageur'], $_POST['prenom_voyageur'], $_POST['date_naissance_voyageur'])) {
        // on récupère les données entrées
        $id_train = $_POST['id_train'];
        $jour_trajet = $_POST['jour_trajet'];
        $nom_voyageur = $_POST['nom_voyageur'];
        $prenom_voyageur = $_POST['prenom_voyageur'];
        $date_naissance_voyageur = $_POST['date_naissance_voyageur'];
        // print_r($id_train);

        $id_utilisateur = $_SESSION['user_id'];
        // on génère un numéro de biller aléatoire, en vérifiant qu'il n'existe pas déjà dans la base de données, si c'est le cas on regénère jusqu'à en trouver un libre
        do {
            $numero_billet = mt_rand(100000000000, 999999999999);
            
            $requete = "SELECT COUNT(*) FROM reservation WHERE numero_billet = :numero_billet";
            $stmt = $bdd->prepare($requete);
            $stmt->execute(['numero_billet' => $numero_billet]);
            $existe = $stmt->fetchColumn();
        } while ($existe > 0);

        // on effectue l'insertion dans la table
        $sql = "INSERT INTO reservation (id_utilisateur, id_train, heure_achat, nom_voyageur, prenom_voyageur, date_naissance_voyageur, numero_billet, jour_trajet)
                VALUES (:id_utilisateur, :id_train, NOW(), :nom_voyageur, :prenom_voyageur, :date_naissance_voyageur, :numero_billet, :jour_trajet)";

        $stmt = $bdd->prepare($sql);
        $stmt->execute([
            'id_utilisateur' => $id_utilisateur,
            'id_train' => $id_train,
            'nom_voyageur' => $nom_voyageur,
            'prenom_voyageur' => $prenom_voyageur,
            'date_naissance_voyageur' => $date_naissance_voyageur,
            'numero_billet' => $numero_billet,
            'jour_trajet' => $jour_trajet,
        ]);

        if ($stmt->rowCount() > 0) {
            header("Location: reservation.php?reussite=1");
            exit();
        } else {
            echo "Erreur lors de la réservation.";
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
    <a href="accueil_utilisateur.php"><img src="logo_couleur.png" alt="Logo Starvel" class="logo" /></a>
    <a href="reservation.php">Réserver un trajet</a>
    <a href="modification_utilisateur.php">Modifier mon profil</a>
    <a href="annulation.php">Mes trajets à venir</a>
    <a href="historique.php">Historique de mes trajets</a>
    <a href="connexion.php?action=logout" class="deconnexion">Déconnexion</a>
</nav>

<div class="container">
    <div class="informations">
        <!-- on affiche le message de réussite de réservation -->
        <?php if (isset($_GET['reussite']) && $_GET['reussite'] == 1): ?>
            <h3>Votre trajet a bien été réservé</h3>
        <?php else: ?>
            <!-- on affiche le premier formulaire pour entrer ce qu'on recherche -->
            <?php if (!$trains && !$train_selectionne):?>
                <form method="POST" action="">
                    <h2>Réservation</h2>
                    <div>
                        <label for="gare_depart">Gare de départ :</label><br>
                        <select id="gare_depart" name="gare_depart" required>
                            <option value="">Sélectionnez une gare</option>
                            <?php foreach ($gares as $gare): ?>
                                <option value="<?= $gare['id_gare']; ?>"><?= htmlspecialchars($gare['nom']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <br>
                    <div>  
                        <label for="gare_arrivee">Gare d'arrivée :</label><br>
                        <select id="gare_arrivee" name="gare_arrivee" required>
                            <option value="">Sélectionnez une gare</option>
                            <?php foreach ($gares as $gare): ?>
                                <option value="<?= $gare['id_gare']; ?>"><?= htmlspecialchars($gare['nom']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <br>
                    <div>  
                        <label for="jour_trajet">Date de votre trajet :</label><br>
                        <input type="date" id="jour_trajet" name="jour_trajet" required>
                    </div>
                    <br>
                    <div class="rechercher_gare">
                        <input type="submit" value="Rechercher">
                    </div>
                    <input type="hidden" name="action" value="rechercher_train">

                </form>
            <?php endif; ?>
            <!-- une fois qu'on a entré ses critères on a un taleau avec la liste des trains disponibles -->
            <?php if ($trains && !$train_selectionne): ?>
                <h2>Réservation</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Horaire de départ</th>
                                <th>Horaire d'arrivée</th>
                                <th>Type</th>
                                <th>Sélection</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($trains as $train) : ?>
                                <tr>
                                    <td><?= htmlspecialchars($train['heure_depart']); ?></td>
                                    <td><?= htmlspecialchars($train['heure_arrivee']); ?></td>
                                    <td><?= htmlspecialchars($train['type']); ?></td>
                                    <td>
                                        <form method="post" action="reservation.php">
                                            <input type="hidden" name="id_train" value="<?= htmlspecialchars($train['id_train']); ?>">
                                            <button type="submit" name="reserver">Réserver</button>
                                            <input type="hidden" name="action" value="reserver_train">
                                            <input type="hidden" value="<?= $_POST['jour_trajet'] ?>" name="jour_trajet">
                                    
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
            <?php endif; ?>
            <!-- une fois un train choisi on entre les informations du voyageur -->
            <?php if ($train_selectionne && !$reussite): ?>
                <div id="formulaire-reservation" class="reservation-form">
                    <h3>Informations du voyageur</h3>
                    <form method="post" action="reservation.php">
                        <label for="nom_voyageur">Nom :</label><br>
                        <input type="text" name="nom_voyageur" required><br>

                        <label for="prenom_voyageur">Prénom :</label><br>
                        <input type="text" name="prenom_voyageur" required><br>

                        <label for="date_naissance_voyageur">Date de naissance :</label><br>
                        <input type="date" name="date_naissance_voyageur" required><br>

                        <input type="hidden" value="<?= $_POST['id_train'] ?>" name="id_train">
                        <input type="hidden" value="<?= $_POST['jour_trajet']?>" name="jour_trajet">
                        
                        <button type="submit">Confirmer la réservation</button>
                    </form>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        <?php if ($message): ?>
            <p class="centre"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
    </div>
</div>
