import 'package:flutter/material.dart';

import '../data/category_service.dart';
import '../../../models/category.dart';
import '../../../core/toast_util.dart';
import '../../documents/pages/documents_page.dart';

class CategoriesPage extends StatefulWidget {
  const CategoriesPage({super.key});

  @override
  State<CategoriesPage> createState() => CategoriesPageState();
}

class CategoriesPageState extends State<CategoriesPage> {
  final _service = CategoryService();
  List<DocumentCategory> _categories = [];
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  void load() => _load();

  Future<void> _load() async {
    setState(() { _loading = true; _error = null; });
    try {
      final cats = await _service.getCategories();
      setState(() { _categories = cats; _loading = false; });
    } catch (e) {
      setState(() { _error = e.toString(); _loading = false; });
    }
  }

  void _showAddDialog() {
    final nameCtrl = TextEditingController();
    final codeCtrl = TextEditingController();
    String icon = 'ti-file';
    String color = 'primary';

    showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(builder: (ctx, setDialogState) {
        return AlertDialog(
          title: const Text('Nouvelle categorie'),
          content: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                TextField(controller: nameCtrl, decoration: const InputDecoration(labelText: 'Nom', border: OutlineInputBorder())),
                const SizedBox(height: 12),
                TextField(controller: codeCtrl, decoration: const InputDecoration(labelText: 'Code', border: OutlineInputBorder())),
                const SizedBox(height: 12),
                DropdownButtonFormField<String>(
                  decoration: const InputDecoration(labelText: 'Couleur', border: OutlineInputBorder()),
                  initialValue: color,
                  items: ['primary', 'success', 'info', 'warning', 'danger', 'secondary'].map((c) => DropdownMenuItem(value: c, child: Row(children: [
                    Container(width: 16, height: 16, decoration: BoxDecoration(color: _colorFromName(c), shape: BoxShape.circle)),
                    const SizedBox(width: 8), Text(c),
                  ]))).toList(),
                  onChanged: (v) => setDialogState(() { color = v!; }),
                ),
              ],
            ),
          ),
          actions: [
            TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Annuler')),
            FilledButton(
              onPressed: () async {
                if (nameCtrl.text.isEmpty || codeCtrl.text.isEmpty) return;
                try {
                  await _service.createCategory(name: nameCtrl.text, code: codeCtrl.text, icon: icon, color: color);
                  Navigator.pop(ctx);
                  _load();
                } catch (e) {
                  if (mounted) showToast(context, 'Erreur: $e', isError: true);
                }
              },
              child: const Text('Creer'),
            ),
          ],
        );
      }),
    );
  }

  Future<void> _deleteCategory(DocumentCategory cat) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Supprimer'),
        content: Text('Supprimer la categorie "${cat.name}" ?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Annuler')),
          TextButton(onPressed: () => Navigator.pop(ctx, true), child: const Text('Supprimer', style: TextStyle(color: Colors.red))),
        ],
      ),
    );
    if (confirm == true) {
      try {
        await _service.deleteCategory(cat.categoryId);
        _load();
        if (mounted) showToast(context, 'Categorie supprimee');
      } catch (e) {
        if (mounted) showToast(context, 'Erreur: $e', isError: true);
      }
    }
  }

  Color _colorFromName(String name) {
    switch (name) {
      case 'primary': return Colors.indigo;
      case 'success': return Colors.green;
      case 'info': return Colors.lightBlue;
      case 'warning': return Colors.orange;
      case 'danger': return Colors.red;
      case 'secondary': return Colors.grey;
      default: return Colors.indigo;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Categories')),
      floatingActionButton: FloatingActionButton(
        onPressed: _showAddDialog,
        child: const Icon(Icons.add),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(child: Text(_error!, style: const TextStyle(color: Colors.red)))
              : RefreshIndicator(
                  onRefresh: _load,
                  child: ListView.builder(
                    padding: const EdgeInsets.all(12),
                    itemCount: _categories.length,
                    itemBuilder: (ctx, i) {
                      final cat = _categories[i];
                      return Dismissible(
                        key: ValueKey(cat.categoryId),
                        direction: DismissDirection.endToStart,
                        background: Container(
                          alignment: Alignment.centerRight,
                          padding: const EdgeInsets.only(right: 20),
                          color: Colors.red,
                          child: const Icon(Icons.delete, color: Colors.white),
                        ),
                        confirmDismiss: (d) async {
                          await _deleteCategory(cat);
                          return false;
                        },
                        child: Card(
                          child: ListTile(
                            leading: CircleAvatar(
                              backgroundColor: _colorFromName(cat.color ?? 'primary').withValues(alpha: 0.2),
                              child: Text(cat.code.substring(0, 1), style: TextStyle(color: _colorFromName(cat.color ?? 'primary'))),
                            ),
                            title: Text(cat.name),
                            subtitle: Text('${cat.documentCount} document(s) - Code: ${cat.code}'),
                            trailing: IconButton(
                              icon: const Icon(Icons.delete_outline, color: Colors.red),
                              onPressed: () => _deleteCategory(cat),
                            ),
                            onTap: () => Navigator.push(
                              context,
                              MaterialPageRoute(builder: (_) => DocumentsPage(initialType: cat.code)),
                            ).then((_) => _load()),
                          ),
                        ),
                      );
                    },
                  ),
                ),
    );
  }
}
