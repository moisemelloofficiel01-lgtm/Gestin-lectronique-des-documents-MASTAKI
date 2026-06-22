import 'dart:io' show Platform;

import 'package:flutter/foundation.dart';

class ApiConfig {
  static String get baseUrl {
    if (kIsWeb) {
      return '${Uri.base.scheme}://${Uri.base.host}/team37/src';
    }
    if (Platform.isAndroid) {
      return 'http://10.0.2.2/team37/src';
    }
    return 'http://localhost/team37/src';
  }

  static String get mobileApi => '$baseUrl/api/mobile';

  static String get loginUrl => '$mobileApi/auth/login';
  static String get dashboardStatsUrl => '$mobileApi/dashboard/stats';

  static String get documentsUrl => '$mobileApi/documents/read';
  static String get documentUrl => '$mobileApi/documents/get_one';
  static String get documentCreateUrl => '$mobileApi/documents/create';
  static String get documentUpdateUrl => '$mobileApi/documents/update';
  static String get documentDeleteUrl => '$mobileApi/documents/delete';
  static String get documentArchiveUrl => '$mobileApi/documents/archive';
  static String get documentUnarchiveUrl => '$mobileApi/documents/unarchive';

  static String get categoriesUrl => '$mobileApi/categories/read';
  static String get categoryCreateUrl => '$mobileApi/categories/create';
  static String get categoryDeleteUrl => '$mobileApi/categories/delete';

  static String get fournisseursUrl => '$mobileApi/fournisseurs/read';
  static String get fournisseurUrl => '$mobileApi/fournisseurs/get_one';
  static String get fournisseurCreateUrl => '$mobileApi/fournisseurs/create';
  static String get fournisseurUpdateUrl => '$mobileApi/fournisseurs/update';
  static String get fournisseurDeleteUrl => '$mobileApi/fournisseurs/delete';
}
