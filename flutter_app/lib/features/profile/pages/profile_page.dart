import 'package:flutter/material.dart';

import '../../../models/user.dart';
import '../data/profile_service.dart';
import '../../../core/toast_util.dart';

class ProfilePage extends StatefulWidget {
  final User user;

  const ProfilePage({super.key, required this.user});

  @override
  State<ProfilePage> createState() => ProfilePageState();
}

class ProfilePageState extends State<ProfilePage> {
  final _service = ProfileService();
  late TextEditingController _prenomCtrl;
  late TextEditingController _nomCtrl;
  late TextEditingController _emailCtrl;
  final _currentPwdCtrl = TextEditingController();
  final _newPwdCtrl = TextEditingController();
  final _confirmPwdCtrl = TextEditingController();
  bool _saving = false;

  @override
  void initState() {
    super.initState();
    _prenomCtrl = TextEditingController(text: widget.user.prenom ?? '');
    _nomCtrl = TextEditingController(text: widget.user.nom ?? '');
    _emailCtrl = TextEditingController(text: widget.user.email);
  }

  @override
  void dispose() {
    _prenomCtrl.dispose();
    _nomCtrl.dispose();
    _emailCtrl.dispose();
    _currentPwdCtrl.dispose();
    _newPwdCtrl.dispose();
    _confirmPwdCtrl.dispose();
    super.dispose();
  }

  Future<void> _saveProfile() async {
    setState(() => _saving = true);
    try {
      await _service.updateProfile(
        userId: widget.user.id,
        prenom: _prenomCtrl.text,
        nom: _nomCtrl.text,
        email: _emailCtrl.text,
      );
      if (mounted) showToast(context, 'Profil mis a jour');
    } catch (e) {
      if (mounted) showToast(context, 'Erreur: $e', isError: true);
    }
    setState(() => _saving = false);
  }

  Future<void> _changePassword() async {
    if (_newPwdCtrl.text != _confirmPwdCtrl.text) {
      showToast(context, 'Les mots de passe ne correspondent pas', isError: true);
      return;
    }
    if (_newPwdCtrl.text.length < 6) {
      showToast(context, 'Minimum 6 caracteres', isError: true);
      return;
    }
    setState(() => _saving = true);
    try {
      await _service.changePassword(
        userId: widget.user.id,
        currentPassword: _currentPwdCtrl.text,
        newPassword: _newPwdCtrl.text,
      );
      _currentPwdCtrl.clear();
      _newPwdCtrl.clear();
      _confirmPwdCtrl.clear();
      if (mounted) showToast(context, 'Mot de passe modifie');
    } catch (e) {
      if (mounted) showToast(context, 'Erreur: $e', isError: true);
    }
    setState(() => _saving = false);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Mon profil')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Center(
            child: CircleAvatar(
              radius: 40,
              child: Text(
                '${(widget.user.prenom ?? widget.user.username)[0]}${(widget.user.nom ?? ' ')[0]}'.toUpperCase(),
                style: const TextStyle(fontSize: 28),
              ),
            ),
          ),
          const SizedBox(height: 8),
          Center(child: Text(widget.user.username, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold))),
          Center(child: Text(widget.user.email, style: const TextStyle(color: Colors.grey))),
          const SizedBox(height: 8),
          Center(
            child: Wrap(
              spacing: 4,
              children: widget.user.roles.map((r) => Chip(label: Text(r, style: const TextStyle(fontSize: 12)), materialTapTargetSize: MaterialTapTargetSize.shrinkWrap)).toList(),
            ),
          ),
          const SizedBox(height: 24),
          const Text('Modifier le profil', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
          const SizedBox(height: 12),
          TextField(controller: _prenomCtrl, decoration: const InputDecoration(labelText: 'Prenom', border: OutlineInputBorder())),
          const SizedBox(height: 12),
          TextField(controller: _nomCtrl, decoration: const InputDecoration(labelText: 'Nom', border: OutlineInputBorder())),
          const SizedBox(height: 12),
          TextField(controller: _emailCtrl, decoration: const InputDecoration(labelText: 'Email', border: OutlineInputBorder()), keyboardType: TextInputType.emailAddress),
          const SizedBox(height: 16),
          FilledButton(onPressed: _saving ? null : _saveProfile, child: const Text('Enregistrer le profil')),
          const SizedBox(height: 24),
          const Divider(),
          const SizedBox(height: 8),
          const Text('Changer le mot de passe', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
          const SizedBox(height: 12),
          TextField(controller: _currentPwdCtrl, decoration: const InputDecoration(labelText: 'Mot de passe actuel', border: OutlineInputBorder()), obscureText: true),
          const SizedBox(height: 12),
          TextField(controller: _newPwdCtrl, decoration: const InputDecoration(labelText: 'Nouveau mot de passe', border: OutlineInputBorder()), obscureText: true),
          const SizedBox(height: 12),
          TextField(controller: _confirmPwdCtrl, decoration: const InputDecoration(labelText: 'Confirmer', border: OutlineInputBorder()), obscureText: true),
          const SizedBox(height: 16),
          FilledButton(onPressed: _saving ? null : _changePassword, child: const Text('Changer le mot de passe')),
        ],
      ),
    );
  }
}
