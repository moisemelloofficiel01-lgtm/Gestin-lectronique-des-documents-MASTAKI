import 'dart:typed_data';

import 'package:flutter/material.dart';

import '../data/fournisseur_service.dart';
import '../../../models/fournisseur.dart';
import '../../../core/file_picker_util.dart';
import '../../../core/toast_util.dart';

class FournisseurDetailPage extends StatefulWidget {
  final int fournisseurId;

  const FournisseurDetailPage({super.key, required this.fournisseurId});

  @override
  State<FournisseurDetailPage> createState() => _FournisseurDetailPageState();
}

class _FournisseurDetailPageState extends State<FournisseurDetailPage> {
  final _service = FournisseurService();
  Fournisseur? _f;
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
      final f = await _service.getFournisseur(widget.fournisseurId);
      setState(() { _f = f; _loading = false; });
    } catch (e) {
      setState(() { _error = e.toString(); _loading = false; });
    }
  }

  void _showEditDialog() {
    if (_f == null) return;
    final f = _f!;
    final nomCtrl = TextEditingController(text: f.nomFournisseur);
    final adresseCtrl = TextEditingController(text: f.adresse ?? '');
    final villeCtrl = TextEditingController(text: f.ville ?? '');
    final paysCtrl = TextEditingController(text: f.pays ?? 'RDC');
    final telCtrl = TextEditingController(text: f.telephonePrincipal ?? '');
    final emailCtrl = TextEditingController(text: f.emailGeneral ?? '');
    String categorie = f.categorieFournisseur ?? '';
    String statut = f.statut;
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
                const Text('Modifier le fournisseur', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                const SizedBox(height: 16),
                TextField(controller: nomCtrl, decoration: const InputDecoration(labelText: 'Nom', border: OutlineInputBorder())),
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
                  label: Text(pickedLogo?.name ?? 'Changer logo'),
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
                  initialValue: categorie.isEmpty ? null : categorie,
                  items: ['MATIERES_PREMIERES', 'SERVICES', 'SOUS_TRAITANCE', 'AUTRE'].map((c) =>
                    DropdownMenuItem(value: c, child: Text(c))).toList(),
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
                TextField(controller: telCtrl, decoration: const InputDecoration(labelText: 'Telephone', border: OutlineInputBorder())),
                const SizedBox(height: 12),
                TextField(controller: emailCtrl, decoration: const InputDecoration(labelText: 'Email', border: OutlineInputBorder())),
                const SizedBox(height: 12),
                DropdownButtonFormField<String>(
                  decoration: const InputDecoration(labelText: 'Statut', border: OutlineInputBorder()),
                  initialValue: statut,
                  items: ['ACTIF', 'INACTIF'].map((s) => DropdownMenuItem(value: s, child: Text(s))).toList(),
                  onChanged: (v) => statut = v!,
                ),
                const SizedBox(height: 20),
                FilledButton(
                  onPressed: () async {
                    try {
                      await _service.updateFournisseur(
                        fournisseurId: widget.fournisseurId,
                        nomFournisseur: nomCtrl.text,
                        adresse: adresseCtrl.text.isNotEmpty ? adresseCtrl.text : null,
                        ville: villeCtrl.text.isNotEmpty ? villeCtrl.text : null,
                        pays: paysCtrl.text.isNotEmpty ? paysCtrl.text : null,
                        telephone: telCtrl.text.isNotEmpty ? telCtrl.text : null,
                        email: emailCtrl.text.isNotEmpty ? emailCtrl.text : null,
                        categorie: categorie.isNotEmpty ? categorie : null,
                        statut: statut,
                        logoBytes: pickedLogo?.bytes,
                        logoFileName: pickedLogo?.name,
                      );
                      Navigator.pop(ctx);
                      _load();
                      if (mounted) showToast(context, 'Fournisseur modifie');
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

  Future<void> _deleteFournisseur() async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Supprimer'),
        content: const Text('Supprimer ce fournisseur ? Les documents associes perdront la reference.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Annuler')),
          TextButton(onPressed: () => Navigator.pop(ctx, true), child: const Text('Supprimer', style: TextStyle(color: Colors.red))),
        ],
      ),
    );
    if (confirm == true) {
      try {
        await _service.deleteFournisseur(widget.fournisseurId);
        if (mounted) {
          showToast(context, 'Fournisseur supprime');
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
        title: Text(_f?.nomFournisseur ?? 'Fournisseur'),
        actions: [
          IconButton(icon: const Icon(Icons.edit), onPressed: _showEditDialog),
          IconButton(icon: const Icon(Icons.delete, color: Colors.red), onPressed: _deleteFournisseur),
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
    final f = _f!;
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        Center(
          child: CircleAvatar(
            radius: 36,
            child: Text(f.nomFournisseur.substring(0, 1).toUpperCase(), style: const TextStyle(fontSize: 28)),
          ),
        ),
        const SizedBox(height: 8),
        Center(
          child: Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
            decoration: BoxDecoration(
              color: f.statut == 'ACTIF' ? Colors.green.withValues(alpha: 0.15) : Colors.red.withValues(alpha: 0.15),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Text(f.statut, style: TextStyle(color: f.statut == 'ACTIF' ? Colors.green : Colors.red, fontWeight: FontWeight.bold)),
          ),
        ),
        const SizedBox(height: 16),
        const Divider(),
        _header('Contact'),
        if (f.telephonePrincipal != null) _field('Telephone', f.telephonePrincipal!),
        if (f.emailGeneral != null) _field('Email', f.emailGeneral!),
        if (f.adresse != null) _field('Adresse', f.adresse!),
        if (f.ville != null) _field('Ville', f.ville!),
        if (f.pays != null) _field('Pays', f.pays!),
        const Divider(),
        _header('Classification'),
        if (f.categorieFournisseur != null) _field('Categorie', f.categorieFournisseur!),
        if (f.secteurActivite != null) _field('Secteur', f.secteurActivite!),
        if (f.commentairesEvaluation != null) _field('Evaluation', f.commentairesEvaluation!),
        const Divider(),
        if (f.documents != null && f.documents!.isNotEmpty) ...[
          _header('Documents (${f.documents!.length})'),
          ...f.documents!.map((doc) => Card(
            child: ListTile(
              dense: true,
              title: Text(doc.nomFichierOriginal, maxLines: 1, overflow: TextOverflow.ellipsis),
              subtitle: Text('${doc.typeDocument} - ${doc.statut}'),
              trailing: doc.montantTtc != null ? Text('${doc.montantTtc!.toStringAsFixed(0)} ${doc.devise}') : null,
            ),
          )),
        ],
      ],
    );
  }

  Widget _header(String text) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6),
      child: Text(text, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
    );
  }

  Widget _field(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(width: 100, child: Text(label, style: const TextStyle(fontWeight: FontWeight.w600, color: Colors.grey))),
          Expanded(child: Text(value)),
        ],
      ),
    );
  }
}
