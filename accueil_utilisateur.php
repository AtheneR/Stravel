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
    <link rel="icon" type="image/png" href="logo_head.png">
</head>

<nav class="navbar">
    <a href="accueil_utilisateur.php" class="imglogo"><img src="logo_couleur.png" alt="Logo Starvel" class="logo" /></a>
    <a href="reservation.php">Réserver un trajet</a>
    <a href="modification_utilisateur.php">Modifier mon profil</a>
    <a href="annulation.php">Mes trajets à venir</a>
    <a href="historique.php">Historique de mes trajets</a>
    <a href="connexion.php?action=logout" class="deconnexion">Se déconnecter</a>
</nav>

<div class="container" class="bas">
    <h2>Bienvenue sur Stravel</h2>

    <div class="informations">
        <h3>Vos informations</h3>
        <p>Nom : <?= htmlspecialchars($user['nom']) ?></p>
        <p>Prénom : <?= htmlspecialchars($user['prenom']) ?></p>
        <p>Date de naissance : <?= htmlspecialchars($user['date_naissance']) ?></p>
        <p>Téléphone : <?= htmlspecialchars($user['telephone']) ?></p>
        <p>E-mail : <?= htmlspecialchars($user['email']) ?></p>
        <p>Préférence de communication : <?= htmlspecialchars($user['preference_communication']) ?></p>
        <!-- <p>Nombre de points : </p> -->
    </div>
</div>

<?php
    if (isset($_GET['action']) && $_GET['action'] == 'logout') {
        $_SESSION = [];
        session_destroy();
        header('Location: connexion.php');
        exit();
    }
?>
