import 'package:flutter/material.dart';

import '../data/document_service.dart';
import '../../../models/document.dart';
import '../../../models/category.dart';
import '../../../models/fournisseur.dart';
import '../../../core/file_picker_util.dart';
import '../../../core/toast_util.dart';
import '../../categories/data/category_service.dart';
import '../../fournisseurs/data/fournisseur_service.dart';
import 'document_detail_page.dart';

class DocumentsPage extends StatefulWidget {
  final String? initialType;
  final String? initialStatus;

  const DocumentsPage({super.key, this.initialType, this.initialStatus});

  @override
  State<DocumentsPage> createState() => DocumentsPageState();
}

class DocumentsPageState extends State<DocumentsPage> {
  final _service = DocumentService();
  final _catService = CategoryService();
  final _fournService = FournisseurService();
  final _searchCtrl = TextEditingController();

  List<Document> _docs = [];
  bool _loading = true;
  String? _error;
  int _page = 1;
  int _totalPages = 1;
  String _search = '';
  String? _filterType;
  String? _filterStatus;

  @override
  void initState() {
    super.initState();
    _filterType = widget.initialType;
    _filterStatus = widget.initialStatus;
    _load();
  }

  @override
  void didUpdateWidget(DocumentsPage oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (widget.initialType != oldWidget.initialType || widget.initialStatus != oldWidget.initialStatus) {
      _filterType = widget.initialType;
      _filterStatus = widget.initialStatus;
      _page = 1;
      _load();
    }
  }

  @override
  void dispose() {
    _searchCtrl.dispose();
    super.dispose();
  }

  void load() => _load();

  Future<void> _load() async {
    setState(() { _loading = true; _error = null; });
    try {
      final result = await _service.getDocuments(
        page: _page, search: _search, type: _filterType, status: _filterStatus,
      );
      setState(() { _docs = result.data; _totalPages = result.totalPages; _loading = false; });
    } catch (e) {
      setState(() { _error = e.toString(); _loading = false; });
    }
  }

  void _searchDocs() {
    _page = 1;
    _search = _searchCtrl.text.trim();
    _load();
  }

  Future<void> _deleteDoc(Document doc) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Supprimer'),
        content: Text('Supprimer "${doc.nomFichierOriginal}" ?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Annuler')),
          TextButton(onPressed: () => Navigator.pop(ctx, true), child: const Text('Supprimer', style: TextStyle(color: Colors.red))),
        ],
      ),
    );
    if (confirm == true) {
      try {
        await _service.deleteDocument(doc.documentId);
        _load();
        if (mounted) showToast(context, 'Document supprime');
      } catch (e) {
        if (mounted) showToast(context, 'Erreur: $e', isError: true);
      }
    }
  }

  Future<void> _archiveDoc(Document doc) async {
    try {
      if (doc.statut == 'ARCHIVE' || doc.statut == 'ARCHIVAL') {
        await _service.unarchiveDocument(doc.documentId);
      } else {
        await _service.archiveDocument(doc.documentId);
      }
      _load();
      if (mounted) showToast(context, doc.statut == 'ARCHIVE' || doc.statut == 'ARCHIVAL' ? 'Document desarchive' : 'Document archive');
    } catch (e) {
      if (mounted) showToast(context, 'Erreur: $e', isError: true);
    }
  }

  void _showAddDialog() {
    List<DocumentCategory> categories = [];
    List<Fournisseur> fournisseurs = [];
    bool loadingCats = true;

    final typeCtrl = TextEditingController();
    final factureCtrl = TextEditingController();
    final dateCtrl = TextEditingController();
    final htCtrl = TextEditingController();
    final ttcCtrl = TextEditingController();
    String devise = 'USD';
    String statut = 'NOUVEAU';
    int? fournisseurId;
    PickedFileResult? pickedFile;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      builder: (ctx) {
        return StatefulBuilder(builder: (ctx, setDialogState) {
          if (loadingCats) {
            (() async {
              try {
                categories = await _catService.getCategories();
                fournisseurs = (await _fournService.getFournisseurs(limit: 100)).data;
                if (ctx.mounted) setDialogState(() { loadingCats = false; });
              } catch (_) { if (ctx.mounted) setDialogState(() { loadingCats = false; }); }
            })();
          }

          return Padding(
            padding: EdgeInsets.only(bottom: MediaQuery.of(ctx).viewInsets.bottom),
            child: ListView(
              shrinkWrap: true,
              padding: const EdgeInsets.all(20),
              children: [
                const Text('Ajouter un document', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                const SizedBox(height: 16),
                if (pickedFile != null)
                  Card(
                    child: ListTile(
                      leading: const Icon(Icons.insert_drive_file),
                      title: Text(pickedFile!.name, maxLines: 1, overflow: TextOverflow.ellipsis),
                      subtitle: Text('${pickedFile!.bytes.length ~/ 1024} KB'),
                      trailing: IconButton(
                        icon: const Icon(Icons.close, size: 18),
                        onPressed: () => setDialogState(() { pickedFile = null; }),
                      ),
                    ),
                  ),
                const SizedBox(height: 12),
                ElevatedButton.icon(
                  icon: const Icon(Icons.upload_file),
                  label: Text(pickedFile?.name != null ? 'Changer le fichier' : 'Choisir un fichier'),
                  onPressed: () async {
                    final result = await pickFile(imageOnly: false);
                    if (result != null) {
                      setDialogState(() { pickedFile = result; });
                    }
                  },
                ),
                const SizedBox(height: 12),
                if (loadingCats) const LinearProgressIndicator(),
                if (!loadingCats) ...[
                  DropdownButtonFormField<String>(
                    decoration: const InputDecoration(labelText: 'Type de document'),
                    items: categories.map((c) => DropdownMenuItem(value: c.code, child: Text(c.name))).toList(),
                    onChanged: (v) => typeCtrl.text = v ?? '',
                  ),
                  const SizedBox(height: 12),
                  DropdownButtonFormField<int>(
                    decoration: const InputDecoration(labelText: 'Fournisseur'),
                    items: [const DropdownMenuItem(value: null, child: Text('-- Aucun --')),
                      ...fournisseurs.map((f) => DropdownMenuItem(value: f.fournisseurId, child: Text(f.nomFournisseur)))],
                    onChanged: (v) => fournisseurId = v,
                  ),
                  const SizedBox(height: 12),
                  TextField(controller: factureCtrl, decoration: const InputDecoration(labelText: 'N Facture / Ref')),
                  const SizedBox(height: 12),
                  TextField(controller: dateCtrl, decoration: const InputDecoration(labelText: 'Date (YYYY-MM-DD)')),
                  const SizedBox(height: 12),
                  TextField(controller: htCtrl, decoration: const InputDecoration(labelText: 'Montant HT'), keyboardType: TextInputType.number),
                  const SizedBox(height: 12),
                  TextField(controller: ttcCtrl, decoration: const InputDecoration(labelText: 'Montant TTC'), keyboardType: TextInputType.number),
                  const SizedBox(height: 12),
                  DropdownButtonFormField<String>(
                    decoration: const InputDecoration(labelText: 'Devise'),
                    initialValue: devise,
                    items: ['USD', 'EUR', 'CDF'].map((d) => DropdownMenuItem(value: d, child: Text(d))).toList(),
                    onChanged: (v) => devise = v!,
                  ),
                  const SizedBox(height: 12),
                  DropdownButtonFormField<String>(
                    decoration: const InputDecoration(labelText: 'Statut'),
                    initialValue: statut,
                    items: ['NOUVEAU', 'EN_COURS', 'VALIDE'].map((s) => DropdownMenuItem(value: s, child: Text(s))).toList(),
                    onChanged: (v) => statut = v!,
                  ),
                ],
                const SizedBox(height: 20),
                FilledButton(
                  onPressed: () async {
                    if (pickedFile == null) {
                      if (mounted) showToast(context, 'Veuillez selectionner un fichier d\'abord');
                      return;
                    }
                    if (typeCtrl.text.isEmpty) {
                      if (mounted) showToast(context, 'Veuillez choisir un type de document');
                      return;
                    }
                    try {
                      await _service.createDocument(
                        filePath: '',
                        fileName: pickedFile!.name,
                        fileBytes: pickedFile!.bytes,
                        typeDocument: typeCtrl.text,
                        numeroFacture: factureCtrl.text.isNotEmpty ? factureCtrl.text : null,
                        dateFacture: dateCtrl.text.isNotEmpty ? dateCtrl.text : null,
                        montantHt: double.tryParse(htCtrl.text),
                        montantTtc: double.tryParse(ttcCtrl.text),
                        devise: devise,
                        fournisseurId: fournisseurId,
                        statut: statut,
                      );
                      Navigator.pop(ctx);
                      _load();
                      if (mounted) showToast(context, 'Document ajoute');
                    } catch (e) {
                      if (mounted) showToast(context, 'Erreur: $e', isError: true);
                    }
                  },
                  child: const Text('Ajouter le document'),
                ),
              ],
            ),
          );
        });
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Documents')),
      floatingActionButton: FloatingActionButton(
        onPressed: _showAddDialog,
        child: const Icon(Icons.add),
      ),
      body: Column(
        children: [
          _buildFilterBar(),
          _buildCategoryFilter(),
          Padding(
            padding: const EdgeInsets.fromLTRB(12, 0, 12, 0),
            child: TextField(
              controller: _searchCtrl,
              decoration: InputDecoration(
                hintText: 'Rechercher...',
                prefixIcon: const Icon(Icons.search),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                contentPadding: const EdgeInsets.symmetric(horizontal: 16),
                suffixIcon: _searchCtrl.text.isNotEmpty
                    ? IconButton(icon: const Icon(Icons.clear), onPressed: () { _searchCtrl.clear(); _searchDocs(); })
                    : null,
              ),
              onSubmitted: (_) => _searchDocs(),
            ),
          ),
          const SizedBox(height: 8),
          Expanded(
            child: _loading
                ? const Center(child: CircularProgressIndicator())
                : _error != null
                    ? Center(child: Text(_error!, style: const TextStyle(color: Colors.red)))
                    : _docs.isEmpty
                        ? const Center(child: Text('Aucun document trouve'))
                        : RefreshIndicator(
                            onRefresh: _load,
                            child: ListView.builder(
                              itemCount: _docs.length + 1,
                              itemBuilder: (ctx, i) {
                                if (i == _docs.length) return _buildPagination();
                                final doc = _docs[i];
                                return Dismissible(
                                  key: ValueKey(doc.documentId),
                                  direction: DismissDirection.endToStart,
                                  background: Container(
                                    alignment: Alignment.centerRight,
                                    padding: const EdgeInsets.only(right: 20),
                                    color: Colors.red,
                                    child: const Icon(Icons.delete, color: Colors.white),
                                  ),
                                  confirmDismiss: (d) async {
                                    await _deleteDoc(doc);
                                    return false;
                                  },
                                  child: Card(
                                    margin: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                                    child: ListTile(
                                      title: Text(doc.nomFichierOriginal, maxLines: 1, overflow: TextOverflow.ellipsis),
                                      subtitle: Text('${doc.typeDocument} - ${doc.nomFournisseur ?? "N/A"}'),
                                      trailing: Row(
                                        mainAxisSize: MainAxisSize.min,
                                        children: [
                                          _statusChip(doc.statut),
                                          IconButton(
                                            icon: Icon(doc.statut == 'ARCHIVE' || doc.statut == 'ARCHIVAL' ? Icons.unarchive : Icons.archive, size: 20),
                                            onPressed: () => _archiveDoc(doc),
                                          ),
                                          IconButton(
                                            icon: const Icon(Icons.delete_outline, size: 20, color: Colors.red),
                                            onPressed: () => _deleteDoc(doc),
                                          ),
                                        ],
                                      ),
                                      onTap: () => Navigator.push(
                                        context,
                                        MaterialPageRoute(builder: (_) => DocumentDetailPage(documentId: doc.documentId)),
                                      ).then((_) => _load()),
                                    ),
                                  ),
                                );
                              },
                            ),
                          ),
          ),
        ],
      ),
    );
  }

  Widget _buildFilterBar() {
    final statuses = [
      ('Tous', null as String?),
      ('Actifs', 'ACTIF'),
      ('Archives', 'ARCHIVE'),
    ];
    return Padding(
      padding: const EdgeInsets.fromLTRB(12, 8, 12, 0),
      child: Row(
        children: statuses.map((s) {
          final active = _filterStatus == s.$2;
          return Padding(
            padding: const EdgeInsets.only(right: 8),
            child: FilterChip(
              label: Text(s.$1, style: TextStyle(fontSize: 12, color: active ? Colors.white : null)),
              selected: active,
              onSelected: (_) {
                setState(() { _filterStatus = s.$2; _page = 1; });
                _load();
              },
              selectedColor: Theme.of(context).colorScheme.primary,
              checkmarkColor: Colors.white,
              visualDensity: VisualDensity.compact,
            ),
          );
        }).toList(),
      ),
    );
  }

  Widget _buildCategoryFilter() {
    if (_filterType == null) return const SizedBox.shrink();
    return Padding(
      padding: const EdgeInsets.fromLTRB(12, 4, 12, 0),
      child: Chip(
        label: Text('Type: $_filterType', style: const TextStyle(fontSize: 12)),
        deleteIcon: const Icon(Icons.close, size: 16),
        onDeleted: () {
          setState(() { _filterType = null; _page = 1; });
          _load();
        },
        visualDensity: VisualDensity.compact,
      ),
    );
  }

  Widget _statusChip(String statut) {
    Color c;
    switch (statut) {
      case 'VALIDE': c = Colors.green; break;
      case 'PAYE': c = Colors.teal; break;
      case 'ARCHIVE': case 'ARCHIVAL': c = Colors.orange; break;
      case 'REJETE': c = Colors.red; break;
      default: c = Colors.blue;
    }
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
      margin: const EdgeInsets.only(right: 4),
      decoration: BoxDecoration(color: c.withValues(alpha: 0.15), borderRadius: BorderRadius.circular(12)),
      child: Text(statut, style: TextStyle(fontSize: 11, color: c, fontWeight: FontWeight.w600)),
    );
  }

  Widget _buildPagination() {
    if (_totalPages <= 1) return const SizedBox.shrink();
    return Padding(
      padding: const EdgeInsets.all(12),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          IconButton(icon: const Icon(Icons.chevron_left), onPressed: _page > 1 ? () { _page--; _load(); } : null),
          Text('$_page / $_totalPages'),
          IconButton(icon: const Icon(Icons.chevron_right), onPressed: _page < _totalPages ? () { _page++; _load(); } : null),
        ],
      ),
    );
  }
}
