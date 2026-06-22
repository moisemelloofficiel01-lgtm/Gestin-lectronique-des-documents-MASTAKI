import 'dart:convert';

import 'package:http/http.dart' as http;

import '../../../core/api_config.dart';
import '../../../models/user.dart';

class AuthService {
  Future<Map<String, dynamic>> login({
    required String email,
    required String password,
  }) async {
    final uri = Uri.parse(ApiConfig.loginUrl);

    final response = await http.post(
      uri,
      headers: const {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: jsonEncode({
        'email': email.trim(),
        'password': password,
      }),
    );

    if (response.body.isEmpty) {
      throw Exception('Le serveur a retourne une reponse vide.');
    }

    final dynamic decoded = jsonDecode(response.body);
    if (decoded is! Map<String, dynamic>) {
      throw Exception('Format de reponse invalide.');
    }

    if (response.statusCode >= 400) {
      throw Exception(decoded['message']?.toString() ?? 'Erreur serveur.');
    }

    return decoded;
  }

  User parseUser(Map<String, dynamic> response) {
    return User.fromJson(response['user'] as Map<String, dynamic>);
  }
}
