import 'package:flutter/material.dart';

import '../data/document_service.dart';
import '../../../models/document.dart';
import '../../../models/category.dart';
import '../../../core/toast_util.dart';
import '../../categories/data/category_service.dart';

class DocumentDetailPage extends StatefulWidget {
  final int documentId;

  const DocumentDetailPage({super.key, required this.documentId});

  @override
  State<DocumentDetailPage> createState() => _DocumentDetailPageState();
}

class _DocumentDetailPageState extends State<DocumentDetailPage> {
  final _service = DocumentService();
  Document? _doc;
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() { _loading = true; _error = null; });
    try {
      final doc = await _service.getDocument(widget.documentId);
      setState(() { _doc = doc; _loading = false; });
    } catch (e) {
      setState(() { _error = e.toString(); _loading = false; });
    }
  }

  void _showEditDialog() {
    if (_doc == null) return;
    final d = _doc!;

    final typeCtrl = TextEditingController(text: d.typeDocument);
    final factureCtrl = TextEditingController(text: d.numeroFacture ?? '');
    final dateCtrl = TextEditingController(text: d.dateFacture ?? '');
    final echeanceCtrl = TextEditingController(text: d.dateEcheance ?? '');
    final htCtrl = TextEditingController(text: d.montantHt?.toStringAsFixed(2) ?? '');
    final tvaCtrl = TextEditingController(text: d.montantTva?.toStringAsFixed(2) ?? '');
    final ttcCtrl = TextEditingController(text: d.montantTtc?.toStringAsFixed(2) ?? '');
    String devise = d.devise;
    String statut = d.statut;
    List<DocumentCategory> categories = [];
    bool loadingCats = true;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      builder: (ctx) {
        return StatefulBuilder(builder: (ctx, setDialogState) {
          if (loadingCats) {
            (() async {
              try {
                categories = await CategoryService().getCategories();
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
                const Text('Modifier le document', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                const SizedBox(height: 16),
                if (loadingCats) const LinearProgressIndicator(),
                if (!loadingCats) ...[
                  DropdownButtonFormField<String>(
                    decoration: const InputDecoration(labelText: 'Type de document'),
                    initialValue: categories.any((c) => c.code == typeCtrl.text) ? typeCtrl.text : null,
                    items: categories.map((c) => DropdownMenuItem(value: c.code, child: Text(c.name))).toList(),
                    onChanged: (v) => typeCtrl.text = v ?? '',
                  ),
                  const SizedBox(height: 12),
                  TextField(controller: factureCtrl, decoration: const InputDecoration(labelText: 'N Facture')),
                  const SizedBox(height: 12),
                  TextField(controller: dateCtrl, decoration: const InputDecoration(labelText: 'Date facture (YYYY-MM-DD)')),
                  const SizedBox(height: 12),
                  TextField(controller: echeanceCtrl, decoration: const InputDecoration(labelText: 'Echeance (YYYY-MM-DD)')),
                  const SizedBox(height: 12),
                  TextField(controller: htCtrl, decoration: const InputDecoration(labelText: 'Montant HT'), keyboardType: TextInputType.number),
                  const SizedBox(height: 12),
                  TextField(controller: tvaCtrl, decoration: const InputDecoration(labelText: 'TVA'), keyboardType: TextInputType.number),
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
                    items: ['NOUVEAU', 'EN_COURS', 'VALIDE', 'PAYE', 'ARCHIVE'].map((s) => DropdownMenuItem(value: s, child: Text(s))).toList(),
                    onChanged: (v) => statut = v!,
                  ),
                ],
                const SizedBox(height: 20),
                FilledButton(
                  onPressed: () async {
                    final data = <String, dynamic>{
                      'type_document': typeCtrl.text,
                      'numero_facture': factureCtrl.text.isNotEmpty ? factureCtrl.text : null,
                      'date_facture': dateCtrl.text.isNotEmpty ? dateCtrl.text : null,
                      'date_echeance': echeanceCtrl.text.isNotEmpty ? echeanceCtrl.text : null,
                      'montant_ht': double.tryParse(htCtrl.text),
                      'montant_tva': double.tryParse(tvaCtrl.text),
                      'montant_ttc': double.tryParse(ttcCtrl.text),
                      'devise': devise,
                      'statut': statut,
                    };
                    try {
                      await _service.updateDocument(widget.documentId, data);
                      Navigator.pop(ctx);
                      _load();
                      if (mounted) showToast(context, 'Document modifie');
                    } catch (e) {
                      if (mounted) showToast(context, 'Erreur: $e', isError: true);
                    }
                  },
                  child: const Text('Enregistrer'),
                ),
              ],
            ),
          );
        });
      },
    );
  }

  Future<void> _deleteDoc() async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Supprimer'),
        content: const Text('Supprimer ce document ?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Annuler')),
          TextButton(onPressed: () => Navigator.pop(ctx, true), child: const Text('Supprimer', style: TextStyle(color: Colors.red))),
        ],
      ),
    );
    if (confirm == true) {
      try {
        await _service.deleteDocument(widget.documentId);
        if (mounted) {
          showToast(context, 'Document supprime');
          Navigator.pop(context);
        }
      } catch (e) {
        if (mounted) showToast(context, 'Erreur: $e', isError: true);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Detail document'),
        actions: [
          IconButton(icon: const Icon(Icons.edit), onPressed: _showEditDialog),
          IconButton(icon: const Icon(Icons.delete, color: Colors.red), onPressed: _deleteDoc),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(child: Text(_error!, style: const TextStyle(color: Colors.red)))
              : _buildContent(),
    );
  }

  Widget _buildContent() {
    final d = _doc!;
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        _field('Fichier', d.nomFichierOriginal),
        _field('Type', d.typeDocument),
        if (d.sousType != null) _field('Sous-type', d.sousType!),
        _header('Statut'),
        Center(child: _bigStatusChip(d.statut)),
        const Divider(),
        _header('Informations'),
        _field('Fournisseur', d.nomFournisseur ?? 'N/A'),
        if (d.numeroFacture != null) _field('N Facture', d.numeroFacture!),
        if (d.numeroCommande != null) _field('N Commande', d.numeroCommande!),
        if (d.numeroBonLivraison != null) _field('N BL', d.numeroBonLivraison!),
        const Divider(),
        _header('Montants'),
        if (d.montantTtc != null) _field('TTC', '${d.montantTtc!.toStringAsFixed(2)} ${d.devise}'),
        if (d.montantHt != null) _field('HT', '${d.montantHt!.toStringAsFixed(2)} ${d.devise}'),
        if (d.montantTva != null) _field('TVA', '${d.montantTva!.toStringAsFixed(2)} ${d.devise}'),
        const Divider(),
        _header('Dates'),
        _field('Date facture', d.dateFacture ?? 'N/A'),
        _field('Echeance', d.dateEcheance ?? 'N/A'),
        _field('Cree le', d.createdAt),
        if (d.dateArchivage != null) _field('Archive le', d.dateArchivage!),
        const Divider(),
        _header('Classification'),
        if (d.serviceDemandeur != null) _field('Service', d.serviceDemandeur!),
        if (d.centreCout != null) _field('Centre cout', d.centreCout!),
        if (d.uuidDocument.isNotEmpty) _field('UUID', d.uuidDocument),
      ],
    );
  }

  Widget _header(String text) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: Text(text, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
    );
  }

  Widget _field(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(width: 120, child: Text(label, style: const TextStyle(fontWeight: FontWeight.w600, color: Colors.grey))),
          Expanded(child: Text(value)),
        ],
      ),
    );
  }

  Widget _bigStatusChip(String statut) {
    Color c;
    switch (statut) {
      case 'VALIDE': c = Colors.green; break;
      case 'PAYE': c = Colors.teal; break;
      case 'ARCHIVE': case 'ARCHIVAL': c = Colors.orange; break;
      case 'REJETE': c = Colors.red; break;
      default: c = Colors.blue;
    }
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
      decoration: BoxDecoration(color: c.withValues(alpha: 0.15), borderRadius: BorderRadius.circular(20)),
      child: Text(statut, style: TextStyle(fontSize: 14, color: c, fontWeight: FontWeight.bold)),
    );
  }
}
