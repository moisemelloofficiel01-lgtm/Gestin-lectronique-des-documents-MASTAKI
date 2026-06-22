import 'dart:convert';

import 'package:http/http.dart' as http;

import '../../../core/api_config.dart';

class ProfileService {
  Future<void> updateProfile({
    required int userId,
    required String prenom,
    required String nom,
    required String email,
  }) async {
    final response = await http.post(
      Uri.parse('${ApiConfig.mobileApi}/users/update_profile'),
      headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
      body: jsonEncode({'user_id': userId, 'prenom': prenom, 'nom': nom, 'email': email}),
    );

    if (response.body.isEmpty) throw Exception('Reponse vide.');
    final decoded = jsonDecode(response.body) as Map<String, dynamic>;
    if (decoded['status'] != 'success') {
      throw Exception(decoded['message']?.toString() ?? 'Erreur.');
    }
  }

  Future<void> changePassword({
    required int userId,
    required String currentPassword,
    required String newPassword,
  }) async {
    final response = await http.post(
      Uri.parse('${ApiConfig.mobileApi}/users/update_password'),
      headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
      body: jsonEncode({
        'user_id': userId,
        'current_password': currentPassword,
        'new_password': newPassword,
      }),
    );

    if (response.body.isEmpty) throw Exception('Reponse vide.');
    final decoded = jsonDecode(response.body) as Map<String, dynamic>;
    if (decoded['status'] != 'success') {
      throw Exception(decoded['message']?.toString() ?? 'Erreur.');
    }
  }
}
