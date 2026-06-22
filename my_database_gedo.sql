-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : jeu. 18 juin 2026 à 17:23
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `my_database_gedo`
--

-- --------------------------------------------------------

--
-- Structure de la table `archive_documents`
--

CREATE TABLE `archive_documents` (
  `archive_id` int(11) NOT NULL,
  `uuid_archive` varchar(36) NOT NULL,
  `document_id` int(11) NOT NULL,
  `type_archivage` varchar(20) NOT NULL,
  `categorie_archivage` varchar(50) DEFAULT NULL,
  `sous_categorie` varchar(50) DEFAULT NULL,
  `date_archivage` datetime NOT NULL DEFAULT current_timestamp(),
  `date_fin_vie_utile` date DEFAULT NULL,
  `date_fin_conservation` date DEFAULT NULL,
  `duree_conservation` int(11) NOT NULL,
  `emplacement_physique` varchar(500) DEFAULT NULL,
  `bucket_s3` varchar(255) DEFAULT NULL,
  `chemin_objet` varchar(1000) DEFAULT NULL,
  `format_fichier` varchar(10) NOT NULL,
  `version_format` varchar(20) DEFAULT NULL,
  `hash_sha256` varchar(64) NOT NULL,
  `hash_sha512` varchar(128) DEFAULT NULL,
  `taille_fichier` bigint(20) NOT NULL,
  `signature_electronique` text DEFAULT NULL,
  `certificat_signature` text DEFAULT NULL,
  `horodatage_certifie` text DEFAULT NULL,
  `titre` varchar(500) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `sujet` text DEFAULT NULL,
  `auteur` varchar(255) DEFAULT NULL,
  `createur` varchar(255) DEFAULT NULL,
  `editeur` varchar(255) DEFAULT NULL,
  `contributeurs` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`contributeurs`)),
  `date_creation` date DEFAULT NULL,
  `date_publication` date DEFAULT NULL,
  `type_mime` varchar(100) DEFAULT NULL,
  `format_mime` varchar(100) DEFAULT NULL,
  `identifiants` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`identifiants`)),
  `langue` varchar(10) DEFAULT 'fr',
  `relation` text DEFAULT NULL,
  `couverture` text DEFAULT NULL,
  `droits` text DEFAULT NULL,
  `niveau_confidentialite` varchar(20) DEFAULT 'INTERNE',
  `politiques_acces` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`politiques_acces`)),
  `date_declassification` date DEFAULT NULL,
  `statut_archivage` varchar(20) DEFAULT 'ACTIF',
  `date_dernier_acces` datetime DEFAULT NULL,
  `compteur_acces` int(11) DEFAULT 0,
  `dernier_acces_par` int(11) DEFAULT NULL,
  `archive_par` int(11) DEFAULT NULL,
  `autorise_par` int(11) DEFAULT NULL,
  `date_autorisation` date DEFAULT NULL,
  `motif_archivage` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `archive_documents`
--

INSERT INTO `archive_documents` (`archive_id`, `uuid_archive`, `document_id`, `type_archivage`, `categorie_archivage`, `sous_categorie`, `date_archivage`, `date_fin_vie_utile`, `date_fin_conservation`, `duree_conservation`, `emplacement_physique`, `bucket_s3`, `chemin_objet`, `format_fichier`, `version_format`, `hash_sha256`, `hash_sha512`, `taille_fichier`, `signature_electronique`, `certificat_signature`, `horodatage_certifie`, `titre`, `description`, `sujet`, `auteur`, `createur`, `editeur`, `contributeurs`, `date_creation`, `date_publication`, `type_mime`, `format_mime`, `identifiants`, `langue`, `relation`, `couverture`, `droits`, `niveau_confidentialite`, `politiques_acces`, `date_declassification`, `statut_archivage`, `date_dernier_acces`, `compteur_acces`, `dernier_acces_par`, `archive_par`, `autorise_par`, `date_autorisation`, `motif_archivage`) VALUES
(1, '9b4885a9-2d6a-4249-8c40-f185b3ede78d', 1, 'DEFINITIF', '', '', '2026-06-18 12:52:14', NULL, '2031-06-18', 5, 'C:\\xampp\\htdocs\\team37\\src\\api\\documents/../../uploads/documents/48d78467-5bf3-477a-a108-a5b18b7eb229.pdf', NULL, NULL, 'PDF', NULL, 'caf63b0003bcbeb56029fffc05d868afe3b16265a609059d369831fe2c1522a1', NULL, 314629, NULL, NULL, NULL, 'FICHE DE SCOLARITE 2026 LMD LICENCE REVUE.docx.pdf', 'Archivé depuis le module documents', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fr', NULL, NULL, NULL, 'INTERNE', NULL, NULL, 'ACTIF', NULL, 0, NULL, 1, NULL, NULL, '');

-- --------------------------------------------------------

--
-- Structure de la table `documents`
--

CREATE TABLE `documents` (
  `document_id` int(11) NOT NULL,
  `uuid_document` varchar(36) NOT NULL,
  `type_document` varchar(50) NOT NULL,
  `sous_type` varchar(50) DEFAULT NULL,
  `nom_fichier_original` varchar(500) NOT NULL,
  `extension_fichier` varchar(10) DEFAULT NULL,
  `chemin_stockage` varchar(1000) DEFAULT NULL,
  `taille_fichier` bigint(20) DEFAULT NULL,
  `checksum` varchar(64) DEFAULT NULL,
  `numero_facture` varchar(100) DEFAULT NULL,
  `numero_commande` varchar(100) DEFAULT NULL,
  `numero_bon_livraison` varchar(100) DEFAULT NULL,
  `date_facture` date DEFAULT NULL,
  `date_echeance` date DEFAULT NULL,
  `montant_ht` decimal(15,2) DEFAULT NULL,
  `montant_tva` decimal(15,2) DEFAULT NULL,
  `montant_ttc` decimal(15,2) DEFAULT NULL,
  `devise` varchar(3) DEFAULT 'USD',
  `fournisseur_id` int(11) DEFAULT NULL,
  `service_demandeur` varchar(100) DEFAULT NULL,
  `centre_cout` varchar(50) DEFAULT NULL,
  `statut` varchar(50) DEFAULT 'NOUVEAU',
  `date_reception` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_archivage` timestamp NULL DEFAULT NULL,
  `duree_conservation` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `documents`
--

INSERT INTO `documents` (`document_id`, `uuid_document`, `type_document`, `sous_type`, `nom_fichier_original`, `extension_fichier`, `chemin_stockage`, `taille_fichier`, `checksum`, `numero_facture`, `numero_commande`, `numero_bon_livraison`, `date_facture`, `date_echeance`, `montant_ht`, `montant_tva`, `montant_ttc`, `devise`, `fournisseur_id`, `service_demandeur`, `centre_cout`, `statut`, `date_reception`, `date_archivage`, `duree_conservation`, `created_by`, `created_at`) VALUES
(1, '48d78467-5bf3-477a-a108-a5b18b7eb229', 'CONTRAT', '', 'FICHE DE SCOLARITE 2026 LMD LICENCE REVUE.docx.pdf', 'pdf', '48d78467-5bf3-477a-a108-a5b18b7eb229.pdf', 314629, 'caf63b0003bcbeb56029fffc05d868afe3b16265a609059d369831fe2c1522a1', 'xxxxxxx', NULL, NULL, '2026-06-16', NULL, 3.00, NULL, 33.00, 'EUR', 1, NULL, NULL, 'VALIDE', '2026-06-18 10:51:52', NULL, NULL, 1, '2026-06-18 10:51:52');

-- --------------------------------------------------------

--
-- Structure de la table `documents_partages`
--

CREATE TABLE `documents_partages` (
  `partage_id` int(11) NOT NULL,
  `document_id` int(11) NOT NULL,
  `shared_by` int(11) NOT NULL,
  `shared_with` int(11) NOT NULL,
  `permission` varchar(20) DEFAULT 'view',
  `type_partage` varchar(20) DEFAULT 'document',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `documents_partages`
--

INSERT INTO `documents_partages` (`partage_id`, `document_id`, `shared_by`, `shared_with`, `permission`, `type_partage`, `created_at`) VALUES
(1, 1, 1, 2, 'view', 'document', '2026-06-18 10:52:46');

-- --------------------------------------------------------

--
-- Structure de la table `documents_personnels`
--

CREATE TABLE `documents_personnels` (
  `document_id` int(11) NOT NULL,
  `uuid` varchar(36) NOT NULL,
  `dossier_id` int(11) DEFAULT NULL,
  `nom_fichier` varchar(500) NOT NULL,
  `fichier_original` varchar(500) NOT NULL,
  `extension` varchar(10) DEFAULT NULL,
  `taille` bigint(20) DEFAULT NULL,
  `chemin_stockage` varchar(1000) DEFAULT NULL,
  `type_document` varchar(50) DEFAULT 'AUTRE',
  `created_by` int(11) NOT NULL,
  `prive` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `document_categories`
--

CREATE TABLE `document_categories` (
  `category_id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `icon` varchar(50) DEFAULT 'ti-file',
  `color` varchar(20) DEFAULT 'primary',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `document_categories`
--

INSERT INTO `document_categories` (`category_id`, `code`, `name`, `icon`, `color`, `description`, `created_at`) VALUES
(1, 'FACTURE', 'Factures', 'ti-file-invoice', 'primary', NULL, '2026-06-17 15:05:52'),
(2, 'BON_COMMANDE', 'Bons de Commande', 'ti-shopping-cart', 'success', NULL, '2026-06-17 15:05:52'),
(3, 'BON_LIVRAISON', 'Bons de Livraison', 'ti-truck-delivery', 'info', NULL, '2026-06-17 15:05:52'),
(4, 'DEVIS', 'Devis', 'ti-file-percent', 'warning', NULL, '2026-06-17 15:05:52'),
(5, 'CONTRAT', 'Contrats', 'ti-file-check', 'danger', NULL, '2026-06-17 15:05:52'),
(6, 'AUTRE', 'Autres', 'ti-file-text', 'secondary', NULL, '2026-06-17 15:05:52');

-- --------------------------------------------------------

--
-- Structure de la table `dossiers`
--

CREATE TABLE `dossiers` (
  `dossier_id` int(11) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `type` varchar(20) DEFAULT 'personnel',
  `partage` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `dossiers`
--

INSERT INTO `dossiers` (`dossier_id`, `nom`, `parent_id`, `created_by`, `type`, `partage`, `created_at`, `updated_at`) VALUES
(1, 'ddddddd', NULL, 1, 'personnel', 0, '2026-06-18 10:53:47', '2026-06-18 10:53:47'),
(2, 'Serge', 1, 1, 'personnel', 0, '2026-06-18 10:54:27', '2026-06-18 10:54:27');

-- --------------------------------------------------------

--
-- Structure de la table `fournisseurs`
--

CREATE TABLE `fournisseurs` (
  `fournisseur_id` int(11) NOT NULL,
  `nom_fournisseur` varchar(255) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `adresse` varchar(255) DEFAULT NULL,
  `complement_adresse` varchar(255) DEFAULT NULL,
  `code_postal` varchar(20) DEFAULT NULL,
  `ville` varchar(100) DEFAULT NULL,
  `pays` varchar(100) DEFAULT 'RDC',
  `contacts` text DEFAULT NULL,
  `telephone_principal` varchar(50) DEFAULT NULL,
  `email_general` varchar(100) DEFAULT NULL,
  `categorie_fournisseur` varchar(50) DEFAULT NULL,
  `secteur_activite` varchar(100) DEFAULT NULL,
  `commentaires_evaluation` text DEFAULT NULL,
  `statut` varchar(20) DEFAULT 'ACTIF',
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_modification` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `fournisseurs`
--

INSERT INTO `fournisseurs` (`fournisseur_id`, `nom_fournisseur`, `logo`, `adresse`, `complement_adresse`, `code_postal`, `ville`, `pays`, `contacts`, `telephone_principal`, `email_general`, `categorie_fournisseur`, `secteur_activite`, `commentaires_evaluation`, `statut`, `date_creation`, `date_modification`) VALUES
(1, 'ddddd', '', 'dddddddddddd', '', '', 'dd', 'RDC', '[]', 'ddd', 'dd@gmail.com', 'SERVICES', '', '', 'ACTIF', '2026-06-18 10:51:05', '2026-06-18 10:51:05');

-- --------------------------------------------------------

--
-- Structure de la table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) DEFAULT 'info',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `link` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `title`, `message`, `type`, `is_read`, `created_at`, `link`) VALUES
(1, 1, 'Document Archivé', 'Le document \'FICHE DE SCOLARITE 2026 LMD LICENCE REVUE.docx.pdf\' a été archivé avec succès pour une durée de 5 ans.', 'success', 0, '2026-06-18 12:52:14', NULL),
(2, 1, 'Document Désarchivé', 'Le document \'FICHE DE SCOLARITE 2026 LMD LICENCE REVUE.docx.pdf\' a été désarchivé.', 'info', 0, '2026-06-18 17:14:37', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `partage_categories`
--

CREATE TABLE `partage_categories` (
  `partage_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `shared_by` int(11) NOT NULL,
  `shared_with` int(11) NOT NULL,
  `permission` varchar(20) DEFAULT 'view',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `partage_categories`
--

INSERT INTO `partage_categories` (`partage_id`, `category_id`, `shared_by`, `shared_with`, `permission`, `created_at`) VALUES
(1, 4, 1, 2, 'view', '2026-06-18 12:19:18');

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `utilisateur_id` int(11) NOT NULL,
  `matricule` varchar(50) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `fonction` varchar(100) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `adresse` text DEFAULT NULL,
  `code_postal` varchar(10) DEFAULT NULL,
  `ville` varchar(100) DEFAULT NULL,
  `roles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`roles`)),
  `actif` tinyint(1) DEFAULT 1,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_modification` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `cree_par` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`utilisateur_id`, `matricule`, `nom`, `prenom`, `email`, `mot_de_passe`, `photo`, `fonction`, `telephone`, `adresse`, `code_postal`, `ville`, `roles`, `actif`, `date_creation`, `date_modification`, `cree_par`) VALUES
(1, 'SA-001', 'Admin', 'Super', 'admin@ged.com', '$2y$10$4qSR3h4r5JIfC4PLzAPYX.rn8COjxdPeFJG5Z4rd0f61bSctiUPUS', NULL, NULL, NULL, NULL, NULL, NULL, '[\"SUPERADMIN\"]', 1, '2026-06-17 15:05:52', NULL, NULL),
(2, 'USR-94049', 'mul', 'destin', 'mulendelwadestin2@gmail.com', '$2y$10$7YjIrF7TT2Ko2lHGplr.zuL4qzgBTP3YuRkvLmhFZImX6lO6/ivT6', NULL, 'sqqqq', '0983678804', NULL, NULL, NULL, '[\"USER\"]', 1, '2026-06-18 10:41:55', NULL, 1);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `archive_documents`
--
ALTER TABLE `archive_documents`
  ADD PRIMARY KEY (`archive_id`),
  ADD UNIQUE KEY `uuid_archive` (`uuid_archive`),
  ADD KEY `document_id` (`document_id`);

--
-- Index pour la table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`document_id`),
  ADD UNIQUE KEY `uuid_document` (`uuid_document`),
  ADD KEY `fournisseur_id` (`fournisseur_id`);

--
-- Index pour la table `documents_partages`
--
ALTER TABLE `documents_partages`
  ADD PRIMARY KEY (`partage_id`),
  ADD UNIQUE KEY `unique_share` (`document_id`,`shared_with`),
  ADD KEY `shared_by` (`shared_by`),
  ADD KEY `shared_with` (`shared_with`);

--
-- Index pour la table `documents_personnels`
--
ALTER TABLE `documents_personnels`
  ADD PRIMARY KEY (`document_id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD KEY `dossier_id` (`dossier_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Index pour la table `document_categories`
--
ALTER TABLE `document_categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Index pour la table `dossiers`
--
ALTER TABLE `dossiers`
  ADD PRIMARY KEY (`dossier_id`),
  ADD KEY `parent_id` (`parent_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Index pour la table `fournisseurs`
--
ALTER TABLE `fournisseurs`
  ADD PRIMARY KEY (`fournisseur_id`);

--
-- Index pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `partage_categories`
--
ALTER TABLE `partage_categories`
  ADD PRIMARY KEY (`partage_id`),
  ADD UNIQUE KEY `unique_cat_share` (`category_id`,`shared_with`),
  ADD KEY `shared_by` (`shared_by`),
  ADD KEY `shared_with` (`shared_with`);

--
-- Index pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`utilisateur_id`),
  ADD UNIQUE KEY `matricule` (`matricule`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `archive_documents`
--
ALTER TABLE `archive_documents`
  MODIFY `archive_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `documents`
--
ALTER TABLE `documents`
  MODIFY `document_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `documents_partages`
--
ALTER TABLE `documents_partages`
  MODIFY `partage_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `documents_personnels`
--
ALTER TABLE `documents_personnels`
  MODIFY `document_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `document_categories`
--
ALTER TABLE `document_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `dossiers`
--
ALTER TABLE `dossiers`
  MODIFY `dossier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `fournisseurs`
--
ALTER TABLE `fournisseurs`
  MODIFY `fournisseur_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `partage_categories`
--
ALTER TABLE `partage_categories`
  MODIFY `partage_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `utilisateur_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `archive_documents`
--
ALTER TABLE `archive_documents`
  ADD CONSTRAINT `archive_documents_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`document_id`);

--
-- Contraintes pour la table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `documents_ibfk_1` FOREIGN KEY (`fournisseur_id`) REFERENCES `fournisseurs` (`fournisseur_id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `documents_partages`
--
ALTER TABLE `documents_partages`
  ADD CONSTRAINT `documents_partages_ibfk_1` FOREIGN KEY (`shared_by`) REFERENCES `utilisateurs` (`utilisateur_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `documents_partages_ibfk_2` FOREIGN KEY (`shared_with`) REFERENCES `utilisateurs` (`utilisateur_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `documents_partages_ibfk_3` FOREIGN KEY (`document_id`) REFERENCES `documents` (`document_id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `documents_personnels`
--
ALTER TABLE `documents_personnels`
  ADD CONSTRAINT `documents_personnels_ibfk_1` FOREIGN KEY (`dossier_id`) REFERENCES `dossiers` (`dossier_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `documents_personnels_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `utilisateurs` (`utilisateur_id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `dossiers`
--
ALTER TABLE `dossiers`
  ADD CONSTRAINT `dossiers_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `dossiers` (`dossier_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `dossiers_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `utilisateurs` (`utilisateur_id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `utilisateurs` (`utilisateur_id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `partage_categories`
--
ALTER TABLE `partage_categories`
  ADD CONSTRAINT `partage_categories_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `document_categories` (`category_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `partage_categories_ibfk_2` FOREIGN KEY (`shared_by`) REFERENCES `utilisateurs` (`utilisateur_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `partage_categories_ibfk_3` FOREIGN KEY (`shared_with`) REFERENCES `utilisateurs` (`utilisateur_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
