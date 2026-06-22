import 'dart:convert';

import 'package:http/http.dart' as http;

import '../../../core/api_config.dart';
import '../../../models/fournisseur.dart';
import '../../documents/data/document_service.dart';

class FournisseurService {
  Future<PaginatedResult<Fournisseur>> getFournisseurs({
    int page = 1,
    int limit = 20,
    String search = '',
  }) async {
    final params = <String, String>{
      'page': page.toString(),
      'limit': limit.toString(),
    };
    if (search.isNotEmpty) params['search'] = search;

    final uri = Uri.parse(ApiConfig.fournisseursUrl).replace(queryParameters: params);
    final response = await http.get(uri, headers: {'Accept': 'application/json'});

    if (response.body.isEmpty) throw Exception('Reponse vide.');
    final decoded = jsonDecode(response.body) as Map<String, dynamic>;
    if (decoded['status'] != 'success') {
      throw Exception(decoded['message']?.toString() ?? 'Erreur serveur.');
    }

    final pagination = decoded['pagination'] as Map<String, dynamic>? ?? {};
    final list = (decoded['data'] as List?)?.map((e) => Fournisseur.fromJson(e as Map<String, dynamic>)).toList() ?? [];

    return PaginatedResult(
      data: list,
      page: pagination['page'] as int? ?? page,
      totalPages: pagination['total_pages'] as int? ?? 1,
      totalRecords: pagination['total_records'] as int? ?? 0,
    );
  }

  Future<Fournisseur> getFournisseur(int id) async {
    final uri = Uri.parse(ApiConfig.fournisseurUrl).replace(queryParameters: {'id': id.toString()});
    final response = await http.get(uri, headers: {'Accept': 'application/json'});

    if (response.body.isEmpty) throw Exception('Reponse vide.');
    final decoded = jsonDecode(response.body) as Map<String, dynamic>;
    if (decoded['status'] != 'success') {
      throw Exception(decoded['message']?.toString() ?? 'Erreur serveur.');
    }
    return Fournisseur.fromJson(decoded['data'] as Map<String, dynamic>);
  }

  Future<Map<String, dynamic>> createFournisseur({
    required String nomFournisseur,
    String? adresse,
    String? ville,
    String? pays,
    String? telephone,
    String? email,
    String? categorie,
    List<int>? logoBytes,
    String? logoFileName,
  }) async {
    final uri = Uri.parse(ApiConfig.fournisseurCreateUrl);
    final request = http.MultipartRequest('POST', uri);

    request.fields['nom_fournisseur'] = nomFournisseur;
    if (adresse != null) request.fields['adresse'] = adresse;
    if (ville != null) request.fields['ville'] = ville;
    if (pays != null) request.fields['pays'] = pays;
    if (telephone != null) request.fields['telephone_principal'] = telephone;
    if (email != null) request.fields['email_general'] = email;
    if (categorie != null) request.fields['categorie_fournisseur'] = categorie;

    if (logoBytes != null && logoFileName != null) {
      request.files.add(http.MultipartFile.fromBytes('logo', logoBytes, filename: logoFileName));
    }

    final streamedResponse = await request.send();
    final response = await http.Response.fromStream(streamedResponse);

    if (response.body.isEmpty) throw Exception('Reponse vide.');
    final decoded = jsonDecode(response.body) as Map<String, dynamic>;
    if (decoded['status'] != 'success') {
      throw Exception(decoded['message']?.toString() ?? 'Erreur creation.');
    }
    return decoded;
  }

  Future<void> updateFournisseur({
    required int fournisseurId,
    required String nomFournisseur,
    String? adresse,
    String? ville,
    String? pays,
    String? telephone,
    String? email,
    String? categorie,
    String statut = 'ACTIF',
    List<int>? logoBytes,
    String? logoFileName,
  }) async {
    final uri = Uri.parse(ApiConfig.fournisseurUpdateUrl);
    final request = http.MultipartRequest('POST', uri);

    request.fields['fournisseur_id'] = fournisseurId.toString();
    request.fields['nom_fournisseur'] = nomFournisseur;
    request.fields['statut'] = statut;
    if (adresse != null) request.fields['adresse'] = adresse;
    if (ville != null) request.fields['ville'] = ville;
    if (pays != null) request.fields['pays'] = pays;
    if (telephone != null) request.fields['telephone_principal'] = telephone;
    if (email != null) request.fields['email_general'] = email;
    if (categorie != null) request.fields['categorie_fournisseur'] = categorie;

    if (logoBytes != null && logoFileName != null) {
      request.files.add(http.MultipartFile.fromBytes('logo', logoBytes, filename: logoFileName));
    }

    final streamedResponse = await request.send();
    final response = await http.Response.fromStream(streamedResponse);

    if (response.body.isEmpty) throw Exception('Reponse vide.');
    final decoded = jsonDecode(response.body) as Map<String, dynamic>;
    if (decoded['status'] != 'success') {
      throw Exception(decoded['message']?.toString() ?? 'Erreur mise a jour.');
    }
  }

  Future<void> deleteFournisseur(int id) async {
    final response = await http.post(
      Uri.parse(ApiConfig.fournisseurDeleteUrl),
      headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
      body: jsonEncode({'fournisseur_id': id}),
    );

    if (response.body.isEmpty) throw Exception('Reponse vide.');
    final decoded = jsonDecode(response.body) as Map<String, dynamic>;
    if (decoded['status'] != 'success') {
      throw Exception(decoded['message']?.toString() ?? 'Erreur suppression.');
    }
  }
}
