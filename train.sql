-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:3306
-- Généré le : lun. 02 déc. 2024 à 14:16
-- Version du serveur : 8.0.30
-- Version de PHP : 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `train`
--

-- --------------------------------------------------------

--
-- Structure de la table `administrateur`
--

CREATE TABLE `administrateur` (
  `id_administrateur` int NOT NULL,
  `nom` varchar(50) DEFAULT NULL,
  `prenom` varchar(50) DEFAULT NULL,
  `poste` enum('admin','super_admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `telephone` varchar(15) DEFAULT NULL,
  `mot_de_passe` varchar(60) DEFAULT NULL,
  `date_creation` date DEFAULT NULL,
  `date_derniere_connexion` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `administrateur`
--

INSERT INTO `administrateur` (`id_administrateur`, `nom`, `prenom`, `poste`, `email`, `telephone`, `mot_de_passe`, `date_creation`, `date_derniere_connexion`) VALUES
(1, 'Rousseau', 'Athene', 'admin', 'emailathene@truc.com', '0606060606', '$2y$10$swMsu9kf7YtZcWHdTqbfsedA84jBzb9tCQj1QAsaT0F/4hJfebq9m', '2024-11-27', '2024-12-02'),
(2, 'Bli', 'Blou', 'admin', 'blabla@gmail.com', '0609276576', '$2y$10$sZ8MUDl4vBngbBFNxXxZLOY8McVU9o5h2EsG14KMTcK2VuaG7rO7C', '2024-12-01', '2024-12-02'),
(4, 'Blu', 'Blou', 'super_admin', 'blublou@yahoo.fr', '0002000300', '$2y$10$SHgFmqpymCxWN.gu774s2uwcR8eeDSvqJLHPzeJpWNeOe/13Rat2q', '2024-12-02', '2024-12-02');

-- --------------------------------------------------------

--
-- Structure de la table `adresse`
--

CREATE TABLE `adresse` (
  `id_adresse` int NOT NULL,
  `numero` varchar(7) DEFAULT NULL,
  `rue` varchar(50) DEFAULT NULL,
  `ville` varchar(50) DEFAULT NULL,
  `code_postal` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `adresse`
--

INSERT INTO `adresse` (`id_adresse`, `numero`, `rue`, `ville`, `code_postal`) VALUES
(1, '112', 'rue de Maubeuge', 'Paris', 75010),
(2, '1', 'Rue de Lyon', 'Paris', 75012),
(4, '18', 'Rue de Dunkerque', 'Brest', 75010),
(7, '10', 'Place de la Part-Dieu', 'Lyon', 69003),
(8, '12', 'Rue Faidherbe', 'Lille', 59800);

-- --------------------------------------------------------

--
-- Structure de la table `gare`
--

CREATE TABLE `gare` (
  `id_gare` int NOT NULL,
  `nb_quai` int DEFAULT NULL,
  `nom` varchar(50) DEFAULT NULL,
  `capacite_accueil` int DEFAULT NULL,
  `horaire_ouverture` time DEFAULT NULL,
  `horaire_fermeture` time DEFAULT NULL,
  `acces_mobilite_reduite` tinyint(1) DEFAULT NULL,
  `id_adresse` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `gare`
--

INSERT INTO `gare` (`id_gare`, `nb_quai`, `nom`, `capacite_accueil`, `horaire_ouverture`, `horaire_fermeture`, `acces_mobilite_reduite`, `id_adresse`) VALUES
(1, 10, 'Gare du Nord', 1000, '05:00:00', '01:00:00', 1, 1),
(4, 5, 'Gare de Lyon', 2000, '05:30:00', '23:30:00', 1, 1),
(5, 4, 'Gare Montparnasse', 1500, '06:00:00', '23:45:00', 1, 2),
(8, 5, 'Gare d\'Austerlitz', 1600, '06:00:00', '23:30:00', 1, 4),
(10, 3, 'Gare de Lyon Part-Dieu', 1200, '05:00:00', '23:00:00', 1, 7),
(12, 5, 'Gare de Bordeaux Saint-Jean', 2000, '05:30:00', '23:30:00', 1, 8);

-- --------------------------------------------------------

--
-- Structure de la table `reservation`
--

CREATE TABLE `reservation` (
  `id_utilisateur` int NOT NULL,
  `id_train` int NOT NULL,
  `heure_achat` datetime DEFAULT NULL,
  `nom_voyageur` varchar(50) NOT NULL,
  `prenom_voyageur` varchar(50) NOT NULL,
  `date_naissance_voyageur` date DEFAULT NULL,
  `numero_billet` varchar(12) DEFAULT NULL,
  `jour_trajet` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `reservation`
--

INSERT INTO `reservation` (`id_utilisateur`, `id_train`, `heure_achat`, `nom_voyageur`, `prenom_voyageur`, `date_naissance_voyageur`, `numero_billet`, `jour_trajet`) VALUES
(10, 66, '2024-12-02 02:55:04', 'Rousseau', 'Jade', '2003-06-24', '1643920736', '2024-11-30'),
(10, 145, '2024-12-02 02:59:35', 'Fructose', 'Lila', '2024-12-01', '958812205815', '2024-12-03'),
(10, 145, '2024-12-02 02:52:48', 'Rambach', 'Jade', '2024-12-03', '111258916394', '2024-12-03');

-- --------------------------------------------------------

--
-- Structure de la table `train`
--

CREATE TABLE `train` (
  `id_train` int NOT NULL,
  `nb_places` int DEFAULT NULL,
  `id_gare_depart` int DEFAULT NULL,
  `id_gare_arrivee` int DEFAULT NULL,
  `heure_depart` time DEFAULT NULL,
  `heure_arrivee` time DEFAULT NULL,
  `jour_trajet` date DEFAULT NULL,
  `type` enum('TGV','TER','Intercite') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `train`
--

INSERT INTO `train` (`id_train`, `nb_places`, `id_gare_depart`, `id_gare_arrivee`, `heure_depart`, `heure_arrivee`, `jour_trajet`, `type`) VALUES
(41, 200, 1, 4, '08:30:00', '10:00:00', '2024-11-10', 'TGV'),
(42, 150, 4, 5, '09:00:00', '11:30:00', '2024-11-10', 'TER'),
(64, 220, 1, 4, '07:30:00', '09:00:00', '2024-11-10', 'TER'),
(65, 220, 1, 4, '17:30:00', '19:00:00', '2024-11-10', 'TER'),
(66, 220, 1, 4, '07:30:00', '09:00:00', '2024-11-11', 'TER'),
(67, 220, 1, 4, '17:30:00', '19:00:00', '2024-11-11', 'TER'),
(68, 220, 1, 4, '07:30:00', '09:00:00', '2024-11-12', 'TER'),
(69, 220, 1, 4, '17:30:00', '19:00:00', '2024-11-12', 'TER'),
(70, 220, 1, 4, '07:30:00', '09:00:00', '2024-11-13', 'TER'),
(79, 220, 1, 4, '17:30:00', '19:00:00', '2024-11-17', 'TER'),
(80, 220, 1, 4, '07:30:00', '09:00:00', '2024-11-18', 'TER'),
(81, 220, 1, 4, '17:30:00', '19:00:00', '2024-11-18', 'TER'),
(82, 220, 1, 4, '07:30:00', '09:00:00', '2024-11-19', 'TER'),
(83, 220, 1, 4, '17:30:00', '19:00:00', '2024-11-19', 'TER'),
(84, 220, 1, 4, '07:30:00', '09:00:00', '2024-11-20', 'TER'),
(85, 220, 1, 4, '17:30:00', '19:00:00', '2024-11-20', 'TER'),
(86, 201, 4, 1, '09:00:00', '10:30:00', '2024-12-01', 'Intercite'),
(87, 150, 1, 4, '14:00:00', '15:30:00', '2024-12-01', 'TER'),
(88, 180, 1, 5, '10:00:00', '11:45:00', '2024-12-01', 'Intercite'),
(89, 100, 1, 5, '16:00:00', '17:45:00', '2024-12-01', 'TER'),
(92, 200, 4, 5, '07:00:00', '08:30:00', '2024-12-01', 'TER'),
(99, 50, 1, 4, '13:57:00', '18:57:00', '2024-11-29', 'TGV'),
(135, 50, 10, 8, '22:19:00', '19:19:00', '2024-11-30', 'Intercite'),
(145, 3, 12, 10, '22:53:00', '23:30:00', '2024-12-03', 'TGV');

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `id_utilisateur` int NOT NULL,
  `nom` varchar(50) DEFAULT NULL,
  `prenom` varchar(50) DEFAULT NULL,
  `date_naissance` date DEFAULT NULL,
  `telephone` varchar(15) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `mot_de_passe` varchar(60) DEFAULT NULL,
  `preference_communication` enum('telephone','email') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`id_utilisateur`, `nom`, `prenom`, `date_naissance`, `telephone`, `email`, `mot_de_passe`, `preference_communication`) VALUES
(5, 'bla', 'olihkljh', '2024-10-05', '0609276577', 'athene.rousseau-rambach@lacatholille.fr', '$2y$10$5ut0ILIPlnA1F4G2eBG.Z.39AvmwoXJGgwSD8Ju8krfU7D4e4Rs1y', 'email'),
(6, 'Athène Rousseau Rambach', 'olihkljh', '2024-10-10', '0609276577', '2023685462@lacatholille.fr', '$2y$10$LrQxe3Wn/y635NU79pvadOitE7aI8JrXmxFGaDglnsOh/MoIdSrai', 'email'),
(7, 'Athène Rousseau Rambach', 'olihkljh', '2024-10-10', '0609276577', 'bla@gmail.com', '$2y$10$LN.YS9U.1i69XB0.DjhSSulSxjV4T/m7.ixTxxSRfTPPMEe/uTbMC', 'email'),
(8, 'Athène Rousseau Rambach', 'olihkljh', '2024-10-20', '0609276577', 'monnom@gmail.com', '$2y$10$QB/Xvg4.ogGtwrBROFMK4uVux.4MF4mK64wAPKhjVLoiLk3/EzE96', 'email'),
(9, 'Rousseau Rambach', 'Athène', '2003-06-24', '0609276577', 'athene.rousseaurambach@gmail.com', '$2y$10$zHY1xrc3lDRM.BGP8FWzhe9IGUdWQXVYvlF7oXYT4pVBNyByYJTay', 'email'),
(10, 'Blu', 'Bla', '2024-11-06', '0609276577', 'bli@gmail.com', '$2y$10$cta4JsydWu.jpJCHdMKaz.6kM9qPCU7oYjxj4X/8oVupE8d0OYq9C', 'email'),
(12, '', 'Bla', '2024-11-06', '', '', '$2y$10$pMPw1zlOgN56Dh2yuixibO9P.LB0apDISv7hHZKKVfY/ghW80tTyq', 'email');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `administrateur`
--
ALTER TABLE `administrateur`
  ADD PRIMARY KEY (`id_administrateur`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `adresse`
--
ALTER TABLE `adresse`
  ADD PRIMARY KEY (`id_adresse`);

--
-- Index pour la table `gare`
--
ALTER TABLE `gare`
  ADD PRIMARY KEY (`id_gare`),
  ADD KEY `id_adresse` (`id_adresse`);

--
-- Index pour la table `reservation`
--
ALTER TABLE `reservation`
  ADD PRIMARY KEY (`id_train`,`id_utilisateur`,`nom_voyageur`,`prenom_voyageur`),
  ADD KEY `id_utilisateur` (`id_utilisateur`);

--
-- Index pour la table `train`
--
ALTER TABLE `train`
  ADD PRIMARY KEY (`id_train`),
  ADD KEY `id_gare_depart` (`id_gare_depart`),
  ADD KEY `id_gare_arrivee` (`id_gare_arrivee`);

--
-- Index pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`id_utilisateur`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `administrateur`
--
ALTER TABLE `administrateur`
  MODIFY `id_administrateur` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `adresse`
--
ALTER TABLE `adresse`
  MODIFY `id_adresse` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT pour la table `gare`
--
ALTER TABLE `gare`
  MODIFY `id_gare` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT pour la table `train`
--
ALTER TABLE `train`
  MODIFY `id_train` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=146;

--
-- AUTO_INCREMENT pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  MODIFY `id_utilisateur` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `gare`
--
ALTER TABLE `gare`
  ADD CONSTRAINT `gare_ibfk_1` FOREIGN KEY (`id_adresse`) REFERENCES `adresse` (`id_adresse`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Contraintes pour la table `reservation`
--
ALTER TABLE `reservation`
  ADD CONSTRAINT `reservation_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `reservation_ibfk_2` FOREIGN KEY (`id_train`) REFERENCES `train` (`id_train`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Contraintes pour la table `train`
--
ALTER TABLE `train`
  ADD CONSTRAINT `train_ibfk_1` FOREIGN KEY (`id_gare_depart`) REFERENCES `gare` (`id_gare`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `train_ibfk_2` FOREIGN KEY (`id_gare_arrivee`) REFERENCES `gare` (`id_gare`) ON DELETE RESTRICT ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
