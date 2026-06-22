class Document {
  final int documentId;
  final String uuidDocument;
  final String typeDocument;
  final String? sousType;
  final String nomFichierOriginal;
  final String? extensionFichier;
  final String? cheminStockage;
  final int? tailleFichier;
  final String? checksum;
  final String? numeroFacture;
  final String? numeroCommande;
  final String? numeroBonLivraison;
  final String? dateFacture;
  final String? dateEcheance;
  final double? montantHt;
  final double? montantTva;
  final double? montantTtc;
  final String devise;
  final int? fournisseurId;
  final String? nomFournisseur;
  final String? fournisseurVille;
  final String? serviceDemandeur;
  final String? centreCout;
  final String statut;
  final String? dateReception;
  final String? dateArchivage;
  final String createdAt;

  Document({
    required this.documentId,
    required this.uuidDocument,
    required this.typeDocument,
    this.sousType,
    required this.nomFichierOriginal,
    this.extensionFichier,
    this.cheminStockage,
    this.tailleFichier,
    this.checksum,
    this.numeroFacture,
    this.numeroCommande,
    this.numeroBonLivraison,
    this.dateFacture,
    this.dateEcheance,
    this.montantHt,
    this.montantTva,
    this.montantTtc,
    this.devise = 'USD',
    this.fournisseurId,
    this.nomFournisseur,
    this.fournisseurVille,
    this.serviceDemandeur,
    this.centreCout,
    this.statut = 'NOUVEAU',
    this.dateReception,
    this.dateArchivage,
    required this.createdAt,
  });

  factory Document.fromJson(Map<String, dynamic> json) {
    return Document(
      documentId: int.tryParse('${json['document_id']}') ?? 0,
      uuidDocument: json['uuid_document'] as String? ?? '',
      typeDocument: json['type_document'] as String? ?? '',
      sousType: json['sous_type'] as String?,
      nomFichierOriginal: json['nom_fichier_original'] as String? ?? '',
      extensionFichier: json['extension_fichier'] as String?,
      cheminStockage: json['chemin_stockage'] as String?,
      tailleFichier: int.tryParse('${json['taille_fichier'] ?? 0}'),
      checksum: json['checksum'] as String?,
      numeroFacture: json['numero_facture'] as String?,
      numeroCommande: json['numero_commande'] as String?,
      numeroBonLivraison: json['numero_bon_livraison'] as String?,
      dateFacture: json['date_facture'] as String?,
      dateEcheance: json['date_echeance'] as String?,
      montantHt: double.tryParse('${json['montant_ht'] ?? 0}'),
      montantTva: double.tryParse('${json['montant_tva'] ?? 0}'),
      montantTtc: double.tryParse('${json['montant_ttc'] ?? 0}'),
      devise: json['devise'] as String? ?? 'USD',
      fournisseurId: int.tryParse('${json['fournisseur_id'] ?? 0}'),
      nomFournisseur: json['nom_fournisseur'] as String?,
      fournisseurVille: json['fournisseur_ville'] as String?,
      serviceDemandeur: json['service_demandeur'] as String?,
      centreCout: json['centre_cout'] as String?,
      statut: json['statut'] as String? ?? 'NOUVEAU',
      dateReception: json['date_reception'] as String?,
      dateArchivage: json['date_archivage'] as String?,
      createdAt: json['created_at'] as String? ?? '',
    );
  }
}
