class DocumentCategory {
  final int categoryId;
  final String code;
  final String name;
  final String? icon;
  final String? color;
  final String? description;
  final int documentCount;

  DocumentCategory({
    required this.categoryId,
    required this.code,
    required this.name,
    this.icon,
    this.color,
    this.description,
    this.documentCount = 0,
  });

  factory DocumentCategory.fromJson(Map<String, dynamic> json) {
    return DocumentCategory(
      categoryId: int.tryParse('${json['category_id']}') ?? 0,
      code: json['code'] as String? ?? '',
      name: json['name'] as String? ?? '',
      icon: json['icon'] as String?,
      color: json['color'] as String?,
      description: json['description'] as String?,
      documentCount: int.tryParse('${json['document_count'] ?? 0}') ?? 0,
    );
  }
}
