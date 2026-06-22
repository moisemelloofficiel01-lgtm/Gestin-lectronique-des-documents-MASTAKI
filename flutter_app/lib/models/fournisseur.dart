import 'document.dart';

class Fournisseur {
  final int fournisseurId;
  final String nomFournisseur;
  final String? logo;
  final String? adresse;
  final String? complementAdresse;
  final String? codePostal;
  final String? ville;
  final String? pays;
  final String? contacts;
  final String? telephonePrincipal;
  final String? emailGeneral;
  final String? categorieFournisseur;
  final String? secteurActivite;
  final String? commentairesEvaluation;
  final String statut;
  final String? dateCreation;
  final List<Document>? documents;

  Fournisseur({
    required this.fournisseurId,
    required this.nomFournisseur,
    this.logo,
    this.adresse,
    this.complementAdresse,
    this.codePostal,
    this.ville,
    this.pays,
    this.contacts,
    this.telephonePrincipal,
    this.emailGeneral,
    this.categorieFournisseur,
    this.secteurActivite,
    this.commentairesEvaluation,
    this.statut = 'ACTIF',
    this.dateCreation,
    this.documents,
  });

  factory Fournisseur.fromJson(Map<String, dynamic> json) {
    return Fournisseur(
      fournisseurId: int.tryParse('${json['fournisseur_id']}') ?? 0,
      nomFournisseur: json['nom_fournisseur'] as String? ?? '',
      logo: json['logo'] as String?,
      adresse: json['adresse'] as String?,
      complementAdresse: json['complement_adresse'] as String?,
      codePostal: json['code_postal'] as String?,
      ville: json['ville'] as String?,
      pays: json['pays'] as String?,
      contacts: json['contacts'] as String?,
      telephonePrincipal: json['telephone_principal'] as String?,
      emailGeneral: json['email_general'] as String?,
      categorieFournisseur: json['categorie_fournisseur'] as String?,
      secteurActivite: json['secteur_activite'] as String?,
      commentairesEvaluation: json['commentaires_evaluation'] as String?,
      statut: json['statut'] as String? ?? 'ACTIF',
      dateCreation: json['date_creation'] as String?,
      documents: json['documents'] != null
          ? (json['documents'] as List).map((d) => Document.fromJson(d)).toList()
          : null,
    );
  }
}
