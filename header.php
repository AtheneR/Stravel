<?php
    try{
        $bdd = new PDO('mysql:host=localhost;dbname=train;charset=utf8', 'root', '');
        // on configure pour afficher les erreurs de connexion si elles surviennent
        $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(Exception $e) {
        die('Erreur : '.$e->getMessage());
    }
?>
