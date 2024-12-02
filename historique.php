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

    // on initialise les variables pour les trains et les messages
    $trains = null;
    $message = "";

    $train_details = null;

    // on vérifie si le formulaire a été soumis avec l'id du train, c'est le cas où on a cliqué dans la liste des trains passés pour voir ses informations
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_train'])) {
        $id_train = $_POST['id_train'];
        $sql_details = "
            SELECT 
                r.heure_achat, 
                r.nom_voyageur, 
                r.prenom_voyageur, 
                r.numero_billet, 
                t.jour_trajet, 
                t.heure_depart, 
                t.heure_arrivee, 
                t.type, 
                g1.nom AS gare_depart, 
                g2.nom AS gare_arrivee
            FROM reservation r
            LEFT JOIN train t ON t.id_train = r.id_train
            LEFT JOIN gare g1 ON t.id_gare_depart = g1.id_gare
            LEFT JOIN gare g2 ON t.id_gare_arrivee = g2.id_gare
            WHERE r.id_utilisateur = :user_id AND r.id_train = :id_train
        ";
        $stmt_details = $bdd->prepare($sql_details);
        $stmt_details->execute([
            'user_id' => $user_id,
            'id_train' => $id_train
        ]);
        $train_details = $stmt_details->fetch();
    } else {
        // on récupère la liste des trains passés de l'utilisateur
        $sql = "
            SELECT 
                r.id_train, 
                t.jour_trajet, 
                g1.nom AS gare_depart, 
                g2.nom AS gare_arrivee
            FROM reservation r
            LEFT JOIN train t ON t.id_train = r.id_train
            LEFT JOIN gare g1 ON t.id_gare_depart = g1.id_gare
            LEFT JOIN gare g2 ON t.id_gare_arrivee = g2.id_gare
            WHERE r.id_utilisateur = :user_id
            AND t.jour_trajet < CURRENT_DATE()
            ORDER BY t.jour_trajet ASC
        ";
        $stmt = $bdd->prepare($sql);
        $stmt->execute(['user_id' => $user_id]);
        $trains = $stmt->fetchAll();

        // on affiche un message s'il n'y a pas de réservation passée
        if (empty($trains)) {
            $message = "Vous n'avez pas effectué de réservation pour l'instant.";
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
        <h2>Votre historique</h2>
        <!-- on affiche les détails du train qui a été sélectionné dans le tableau -->
        <?php if ($train_details): ?>
            <h3>Détails du trajet</h3>
            <p>Jour du trajet : <?= htmlspecialchars($train_details['jour_trajet']); ?></p>
            <p>Gare de départ : <?= htmlspecialchars($train_details['gare_depart']); ?></p>
            <p>Gare d'arrivée : <?= htmlspecialchars($train_details['gare_arrivee']); ?></p>
            <p>Heure de départ : <?= htmlspecialchars($train_details['heure_depart']); ?></p>
            <p>Heure d'arrivée : <?= htmlspecialchars($train_details['heure_arrivee']); ?></p>
            <p>Numéro de billet : <?= htmlspecialchars($train_details['numero_billet']); ?></p>
            <p>Nom du voyageur : <?= htmlspecialchars($train_details['nom_voyageur']); ?></p>
            <p>Prénom du voyageur : <?= htmlspecialchars($train_details['prenom_voyageur']); ?></p>
            <p>Heure d'achat : <?= htmlspecialchars($train_details['heure_achat']); ?></p>
            <a href="historique.php">Retour à l'historique</a>
        <!-- on affiche les trains passés qui ont été réservés par l'utilisateur connecté -->
        <?php elseif ($trains): ?>
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
                            <td><?= htmlspecialchars($train['gare_arrivee']); ?></td>
                            <td>
                                <form method="post" action="historique.php">
                                    <input type="hidden" name="id_train" value="<?= htmlspecialchars($train['id_train']); ?>">
                                    <button type="submit" name="details">Détails du trajet</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        <!-- on affiche les messages d'erreur s'il y en a -->
        <?php if ($message): ?>
            <p class="centre">
                <?= htmlspecialchars($message); ?>
            </p>
            <p class="centre">
                <a href="reservation.php">Effectuer une réservation</a>
            </p>
        <?php endif; ?>
    </div>
</div>
