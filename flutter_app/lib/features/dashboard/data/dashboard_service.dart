import 'dart:convert';

import 'package:http/http.dart' as http;

import '../../../core/api_config.dart';
import '../../../models/document.dart';

class DashboardStats {
  final int totalDocuments;
  final int activeDocuments;
  final int archivedDocuments;
  final int totalSuppliers;
  final int totalCategories;
  final double totalValueUsd;
  final double totalValueCdf;
  final List<String> statusLabels;
  final List<int> statusCounts;
  final List<Document> recentDocuments;

  DashboardStats({
    required this.totalDocuments,
    required this.activeDocuments,
    required this.archivedDocuments,
    required this.totalSuppliers,
    required this.totalCategories,
    required this.totalValueUsd,
    required this.totalValueCdf,
    required this.statusLabels,
    required this.statusCounts,
    required this.recentDocuments,
  });

  factory DashboardStats.fromJson(Map<String, dynamic> json) {
    final stats = json['stats'] as Map<String, dynamic>? ?? {};
    final dist = json['status_distribution'] as Map<String, dynamic>? ?? {};
    final recent = json['recent_documents'] as List? ?? [];

    return DashboardStats(
      totalDocuments: stats['total_documents'] as int? ?? 0,
      activeDocuments: stats['active_documents'] as int? ?? 0,
      archivedDocuments: stats['archived_documents'] as int? ?? 0,
      totalSuppliers: stats['total_suppliers'] as int? ?? 0,
      totalCategories: stats['total_categories'] as int? ?? 0,
      totalValueUsd: (stats['total_value_usd'] as num?)?.toDouble() ?? 0,
      totalValueCdf: (stats['total_value_cdf'] as num?)?.toDouble() ?? 0,
      statusLabels: (dist['labels'] as List?)?.map((e) => e.toString()).toList() ?? [],
      statusCounts: (dist['counts'] as List?)?.map((e) => (e as num).toInt()).toList() ?? [],
      recentDocuments: recent.map((e) => Document.fromJson(e as Map<String, dynamic>)).toList(),
    );
  }
}

class DashboardService {
  Future<DashboardStats> getStats() async {
    final uri = Uri.parse(ApiConfig.dashboardStatsUrl);
    final response = await http.get(uri, headers: {
      'Accept': 'application/json',
    });

    if (response.body.isEmpty) {
      throw Exception('Reponse vide du serveur.');
    }

    final decoded = jsonDecode(response.body) as Map<String, dynamic>;
    if (decoded['status'] != 'success') {
      throw Exception(decoded['message']?.toString() ?? 'Erreur serveur.');
    }

    return DashboardStats.fromJson(decoded);
  }
}
