import 'dart:convert';

import 'package:http/http.dart' as http;

import '../../../core/api_config.dart';
import '../../../models/category.dart';

class CategoryService {
  Future<List<DocumentCategory>> getCategories() async {
    final uri = Uri.parse(ApiConfig.categoriesUrl);
    final response = await http.get(uri, headers: {'Accept': 'application/json'});

    if (response.body.isEmpty) throw Exception('Reponse vide.');
    final decoded = jsonDecode(response.body) as Map<String, dynamic>;
    if (decoded['status'] != 'success') {
      throw Exception(decoded['message']?.toString() ?? 'Erreur serveur.');
    }

    return (decoded['data'] as List?)?.map((e) => DocumentCategory.fromJson(e as Map<String, dynamic>)).toList() ?? [];
  }

  Future<Map<String, dynamic>> createCategory({
    required String name,
    required String code,
    String icon = 'ti-file',
    String color = 'primary',
  }) async {
    final response = await http.post(
      Uri.parse(ApiConfig.categoryCreateUrl),
      headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
      body: jsonEncode({'name': name, 'code': code, 'icon': icon, 'color': color}),
    );

    if (response.body.isEmpty) throw Exception('Reponse vide.');
    final decoded = jsonDecode(response.body) as Map<String, dynamic>;
    if (decoded['status'] != 'success') {
      throw Exception(decoded['message']?.toString() ?? 'Erreur creation.');
    }
    return decoded;
  }

  Future<void> deleteCategory(int id) async {
    final response = await http.post(
      Uri.parse(ApiConfig.categoryDeleteUrl),
      headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
      body: jsonEncode({'category_id': id}),
    );

    if (response.body.isEmpty) throw Exception('Reponse vide.');
    final decoded = jsonDecode(response.body) as Map<String, dynamic>;
    if (decoded['status'] != 'success') {
      throw Exception(decoded['message']?.toString() ?? 'Erreur suppression.');
    }
  }
}
