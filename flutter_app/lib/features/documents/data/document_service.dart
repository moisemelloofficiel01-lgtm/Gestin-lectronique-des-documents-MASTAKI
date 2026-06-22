import 'dart:convert';

import 'package:http/http.dart' as http;

import '../../../core/api_config.dart';
import '../../../models/document.dart';

class PaginatedResult<T> {
  final List<T> data;
  final int page;
  final int totalPages;
  final int totalRecords;

  PaginatedResult({
    required this.data,
    required this.page,
    required this.totalPages,
    required this.totalRecords,
  });
}

class DocumentService {
  Future<PaginatedResult<Document>> getDocuments({
    int page = 1,
    int limit = 20,
    String search = '',
    String? type,
    String? status,
  }) async {
    final params = <String, String>{
      'page': page.toString(),
      'limit': limit.toString(),
    };
    if (search.isNotEmpty) params['search'] = search;
    if (type != null && type.isNotEmpty) params['type'] = type;
    if (status != null && status.isNotEmpty) params['status'] = status;

    final uri = Uri.parse(ApiConfig.documentsUrl).replace(queryParameters: params);
    final response = await http.get(uri, headers: {'Accept': 'application/json'});

    if (response.body.isEmpty) throw Exception('Reponse vide.');
    final decoded = jsonDecode(response.body) as Map<String, dynamic>;
    if (decoded['status'] != 'success') {
      throw Exception(decoded['message']?.toString() ?? 'Erreur serveur.');
    }

    final pagination = decoded['pagination'] as Map<String, dynamic>? ?? {};
    final list = (decoded['data'] as List?)?.map((e) => Document.fromJson(e as Map<String, dynamic>)).toList() ?? [];

    return PaginatedResult(
      data: list,
      page: pagination['page'] as int? ?? page,
      totalPages: pagination['total_pages'] as int? ?? 1,
      totalRecords: pagination['total_records'] as int? ?? 0,
    );
  }

  Future<Document> getDocument(int id) async {
    final uri = Uri.parse(ApiConfig.documentUrl).replace(queryParameters: {'id': id.toString()});
    final response = await http.get(uri, headers: {'Accept': 'application/json'});

    if (response.body.isEmpty) throw Exception('Reponse vide.');
    final decoded = jsonDecode(response.body) as Map<String, dynamic>;
    if (decoded['status'] != 'success') {
      throw Exception(decoded['message']?.toString() ?? 'Erreur serveur.');
    }
    return Document.fromJson(decoded['data'] as Map<String, dynamic>);
  }

  Future<Map<String, dynamic>> createDocument({
    required String filePath,
    required String fileName,
    required List<int> fileBytes,
    required String typeDocument,
    String? numeroFacture,
    String? dateFacture,
    double? montantHt,
    double? montantTtc,
    String devise = 'USD',
    int? fournisseurId,
    String statut = 'NOUVEAU',
  }) async {
    final uri = Uri.parse(ApiConfig.documentCreateUrl);
    final request = http.MultipartRequest('POST', uri);

    request.fields['type_document'] = typeDocument;
    request.fields['devise'] = devise;
    request.fields['statut'] = statut;
    if (numeroFacture != null) request.fields['numero_facture'] = numeroFacture;
    if (dateFacture != null) request.fields['date_facture'] = dateFacture;
    if (montantHt != null) request.fields['montant_ht'] = montantHt.toString();
    if (montantTtc != null) request.fields['montant_ttc'] = montantTtc.toString();
    if (fournisseurId != null) request.fields['fournisseur_id'] = fournisseurId.toString();

    request.files.add(http.MultipartFile.fromBytes('document', fileBytes, filename: fileName));

    final streamedResponse = await request.send();
    final response = await http.Response.fromStream(streamedResponse);

    if (response.body.isEmpty) throw Exception('Reponse vide.');
    final decoded = jsonDecode(response.body) as Map<String, dynamic>;
    if (decoded['status'] != 'success') {
      throw Exception(decoded['message']?.toString() ?? 'Erreur creation.');
    }
    return decoded;
  }

  Future<void> updateDocument(int id, Map<String, dynamic> data) async {
    data['document_id'] = id;
    final response = await http.post(
      Uri.parse(ApiConfig.documentUpdateUrl),
      headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
      body: jsonEncode(data),
    );

    if (response.body.isEmpty) throw Exception('Reponse vide.');
    final decoded = jsonDecode(response.body) as Map<String, dynamic>;
    if (decoded['status'] != 'success') {
      throw Exception(decoded['message']?.toString() ?? 'Erreur mise a jour.');
    }
  }

  Future<void> deleteDocument(int id) async {
    final response = await http.post(
      Uri.parse(ApiConfig.documentDeleteUrl),
      headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
      body: jsonEncode({'document_id': id}),
    );

    if (response.body.isEmpty) throw Exception('Reponse vide.');
    final decoded = jsonDecode(response.body) as Map<String, dynamic>;
    if (decoded['status'] != 'success') {
      throw Exception(decoded['message']?.toString() ?? 'Erreur suppression.');
    }
  }

  Future<void> archiveDocument(int id) async {
    final response = await http.post(
      Uri.parse(ApiConfig.documentArchiveUrl),
      headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
      body: jsonEncode({'document_id': id}),
    );

    if (response.body.isEmpty) throw Exception('Reponse vide.');
    final decoded = jsonDecode(response.body) as Map<String, dynamic>;
    if (decoded['status'] != 'success') {
      throw Exception(decoded['message']?.toString() ?? 'Erreur archivage.');
    }
  }

  Future<void> unarchiveDocument(int id) async {
    final response = await http.post(
      Uri.parse(ApiConfig.documentUnarchiveUrl),
      headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
      body: jsonEncode({'document_id': id}),
    );

    if (response.body.isEmpty) throw Exception('Reponse vide.');
    final decoded = jsonDecode(response.body) as Map<String, dynamic>;
    if (decoded['status'] != 'success') {
      throw Exception(decoded['message']?.toString() ?? 'Erreur desarchivage.');
    }
  }
}
