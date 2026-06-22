import 'dart:typed_data';

import 'package:flutter/material.dart';

import '../data/fournisseur_service.dart';
import '../../../models/fournisseur.dart';
import '../../../core/file_picker_util.dart';
import '../../../core/toast_util.dart';
import 'fournisseur_detail_page.dart';

class FournisseursPage extends StatefulWidget {
  const FournisseursPage({super.key});

  @override
  State<FournisseursPage> createState() => FournisseursPageState();
}

class FournisseursPageState extends State<FournisseursPage> {
  final _service = FournisseurService();
  final _searchCtrl = TextEditingController();

  List<Fournisseur> _fournisseurs = [];
  bool _loading = true;
  String? _error;
  int _page = 1;
  int _totalPages = 1;
  String _searchQuery = '';

  @override
  void initState() {
    super.initState();
    _load();
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
      final result = await _service.getFournisseurs(page: _page, search: _searchQuery);
      setState(() { _fournisseurs = result.data; _totalPages = result.totalPages; _loading = false; });
    } catch (e) {
      setState(() { _error = e.toString(); _loading = false; });
    }
  }

  void _doSearch() {
    _page = 1;
    _searchQuery = _searchCtrl.text.trim();
    _load();
  }

  void _showAddDialog() {
    final nomCtrl = TextEditingController();
    final adresseCtrl = TextEditingController();
    final villeCtrl = TextEditingController();
    final paysCtrl = TextEditingController(text: 'RDC');
    final telCtrl = TextEditingController();
    final emailCtrl = TextEditingController();
    String categorie = '';
    PickedFileResult? pickedLogo;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      builder: (ctx) {
        return StatefulBuilder(builder: (ctx, setDialogState) {
          return Padding(
            padding: EdgeInsets.only(bottom: MediaQuery.of(ctx).viewInsets.bottom),
            child: ListView(
              shrinkWrap: true,
              padding: const EdgeInsets.all(20),
              children: [
                const Text('Nouveau fournisseur', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                const SizedBox(height: 16),
                TextField(controller: nomCtrl, decoration: const InputDecoration(labelText: 'Nom du fournisseur', border: OutlineInputBorder())),
                if (pickedLogo != null)
                  ClipRRect(
                    borderRadius: BorderRadius.circular(8),
                    child: Image.memory(
                      Uint8List.fromList(pickedLogo!.bytes),
                      height: 100, width: 100, fit: BoxFit.cover,
                    ),
                  ),
                const SizedBox(height: 12),
                ElevatedButton.icon(
                  icon: const Icon(Icons.image),
                  label: Text(pickedLogo?.name ?? 'Logo (optionnel)'),
                  onPressed: () async {
                    final result = await pickFile(imageOnly: true);
                    if (result != null) {
                      setDialogState(() { pickedLogo = result; });
                    }
                  },
                ),
                const SizedBox(height: 12),
                DropdownButtonFormField<String>(
                  decoration: const InputDecoration(labelText: 'Categorie', border: OutlineInputBorder()),
                  items: ['', 'MATIERES_PREMIERES', 'SERVICES', 'SOUS_TRAITANCE', 'AUTRE'].map((c) =>
                    DropdownMenuItem(value: c, child: Text(c.isEmpty ? '-- Aucune --' : c))).toList(),
                  onChanged: (v) => categorie = v ?? '',
                ),
                const SizedBox(height: 12),
                TextField(controller: adresseCtrl, decoration: const InputDecoration(labelText: 'Adresse', border: OutlineInputBorder())),
                const SizedBox(height: 12),
                Row(children: [
                  Expanded(child: TextField(controller: villeCtrl, decoration: const InputDecoration(labelText: 'Ville', border: OutlineInputBorder()))),
                  const SizedBox(width: 12),
                  Expanded(child: TextField(controller: paysCtrl, decoration: const InputDecoration(labelText: 'Pays', border: OutlineInputBorder()))),
                ]),
                const SizedBox(height: 12),
                TextField(controller: telCtrl, decoration: const InputDecoration(labelText: 'Telephone', border: OutlineInputBorder()), keyboardType: TextInputType.phone),
                const SizedBox(height: 12),
                TextField(controller: emailCtrl, decoration: const InputDecoration(labelText: 'Email', border: OutlineInputBorder()), keyboardType: TextInputType.emailAddress),
                const SizedBox(height: 20),
                FilledButton(
                  onPressed: nomCtrl.text.isEmpty ? null : () async {
                    try {
                      await _service.createFournisseur(
                        nomFournisseur: nomCtrl.text,
                        adresse: adresseCtrl.text.isNotEmpty ? adresseCtrl.text : null,
                        ville: villeCtrl.text.isNotEmpty ? villeCtrl.text : null,
                        pays: paysCtrl.text.isNotEmpty ? paysCtrl.text : null,
                        telephone: telCtrl.text.isNotEmpty ? telCtrl.text : null,
                        email: emailCtrl.text.isNotEmpty ? emailCtrl.text : null,
                        categorie: categorie.isNotEmpty ? categorie : null,
                        logoBytes: pickedLogo?.bytes,
                        logoFileName: pickedLogo?.name,
                      );
                      Navigator.pop(ctx);
                      _load();
                      if (mounted) showToast(context, 'Fournisseur ajoute');
                    } catch (e) {
                      if (mounted) showToast(context, 'Erreur: $e', isError: true);
                    }
                  },
                  child: const Text('Ajouter'),
                ),
              ],
            ),
          );
        });
      },
    );
  }

  Future<void> _deleteFournisseur(Fournisseur f) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Supprimer'),
        content: Text('Supprimer "${f.nomFournisseur}" ? Les documents resteront mais perdront la reference.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Annuler')),
          TextButton(onPressed: () => Navigator.pop(ctx, true), child: const Text('Supprimer', style: TextStyle(color: Colors.red))),
        ],
      ),
    );
    if (confirm == true) {
      try {
        await _service.deleteFournisseur(f.fournisseurId);
        _load();
        if (mounted) showToast(context, 'Fournisseur supprime');
      } catch (e) {
        if (mounted) showToast(context, 'Erreur: $e', isError: true);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Fournisseurs')),
      floatingActionButton: FloatingActionButton(
        onPressed: _showAddDialog,
        child: const Icon(Icons.add),
      ),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.all(12),
            child: TextField(
              controller: _searchCtrl,
              decoration: InputDecoration(
                hintText: 'Rechercher...',
                prefixIcon: const Icon(Icons.search),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                contentPadding: const EdgeInsets.symmetric(horizontal: 16),
                suffixIcon: _searchCtrl.text.isNotEmpty
                    ? IconButton(icon: const Icon(Icons.clear), onPressed: () { _searchCtrl.clear(); _doSearch(); })
                    : null,
              ),
              onSubmitted: (_) => _doSearch(),
            ),
          ),
          Expanded(
            child: _loading
                ? const Center(child: CircularProgressIndicator())
                : _error != null
                    ? Center(child: Text(_error!, style: const TextStyle(color: Colors.red)))
                    : _fournisseurs.isEmpty
                        ? const Center(child: Text('Aucun fournisseur'))
                        : RefreshIndicator(
                            onRefresh: _load,
                            child: ListView.builder(
                              itemCount: _fournisseurs.length + 1,
                              itemBuilder: (ctx, i) {
                                if (i == _fournisseurs.length) return _buildPagination();
                                final f = _fournisseurs[i];
                                return Dismissible(
                                  key: ValueKey(f.fournisseurId),
                                  direction: DismissDirection.endToStart,
                                  background: Container(
                                    alignment: Alignment.centerRight,
                                    padding: const EdgeInsets.only(right: 20),
                                    color: Colors.red,
                                    child: const Icon(Icons.delete, color: Colors.white),
                                  ),
                                  confirmDismiss: (d) async {
                                    await _deleteFournisseur(f);
                                    return false;
                                  },
                                  child: Card(
                                    margin: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                                    child: ListTile(
                                      leading: CircleAvatar(child: Text(f.nomFournisseur.substring(0, 1).toUpperCase())),
                                      title: Text(f.nomFournisseur),
                                      subtitle: Text('${f.ville ?? ""} ${f.pays ?? ""}'.trim()),
                                      trailing: Row(
                                        mainAxisSize: MainAxisSize.min,
                                        children: [
                                          Container(
                                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                            decoration: BoxDecoration(
                                              color: f.statut == 'ACTIF' ? Colors.green.withValues(alpha: 0.15) : Colors.red.withValues(alpha: 0.15),
                                              borderRadius: BorderRadius.circular(12),
                                            ),
                                            child: Text(f.statut, style: TextStyle(
                                              fontSize: 11, color: f.statut == 'ACTIF' ? Colors.green : Colors.red, fontWeight: FontWeight.w600)),
                                          ),
                                          IconButton(
                                            icon: const Icon(Icons.delete_outline, size: 20, color: Colors.red),
                                            onPressed: () => _deleteFournisseur(f),
                                          ),
                                        ],
                                      ),
                                      onTap: () => Navigator.push(
                                        context,
                                        MaterialPageRoute(builder: (_) => FournisseurDetailPage(fournisseurId: f.fournisseurId)),
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
