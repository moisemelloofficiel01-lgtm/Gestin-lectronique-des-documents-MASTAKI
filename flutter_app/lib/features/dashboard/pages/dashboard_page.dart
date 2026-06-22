import 'package:flutter/material.dart';

import '../../../models/user.dart';
import '../data/dashboard_service.dart';

class DashboardPage extends StatefulWidget {
  final User user;

  const DashboardPage({super.key, required this.user});

  @override
  State<DashboardPage> createState() => DashboardPageState();
}

class DashboardPageState extends State<DashboardPage> {
  final _service = DashboardService();
  DashboardStats? _stats;
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadStats();
  }

  void load() => _loadStats();

  Future<void> _loadStats() async {
    setState(() { _loading = true; _error = null; });
    try {
      final stats = await _service.getStats();
      setState(() { _stats = stats; _loading = false; });
    } catch (e) {
      setState(() { _error = e.toString(); _loading = false; });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Tableau de bord'), actions: [
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 12),
          child: Center(child: Text(widget.user.username, style: const TextStyle(fontSize: 14))),
        ),
      ]),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(child: Text(_error!, style: const TextStyle(color: Colors.red)))
              : RefreshIndicator(
                  onRefresh: _loadStats,
                  child: ListView(
                    padding: const EdgeInsets.all(16),
                    children: [
                      _buildStatCards(),
                      const SizedBox(height: 16),
                      _buildStatusChart(),
                      const SizedBox(height: 16),
                      _buildRecentDocs(),
                    ],
                  ),
                ),
    );
  }

  Widget _buildStatCards() {
    final s = _stats!;
    return GridView.count(
      crossAxisCount: 2,
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      mainAxisSpacing: 8,
      crossAxisSpacing: 8,
      childAspectRatio: 1.6,
      children: [
        _card('Documents', s.totalDocuments.toString(), Colors.indigo, Icons.description),
        _card('Actifs', s.activeDocuments.toString(), Colors.green, Icons.check_circle),
        _card('Archives', s.archivedDocuments.toString(), Colors.orange, Icons.archive),
        _card('Fournisseurs', s.totalSuppliers.toString(), Colors.teal, Icons.business),
        _card('Categories', s.totalCategories.toString(), Colors.purple, Icons.category),
        _card('Valeur USD', '\$${s.totalValueUsd.toStringAsFixed(0)}', Colors.blue, Icons.attach_money),
      ],
    );
  }

  Widget _card(String label, String value, Color color, IconData icon) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Row(children: [
              Icon(icon, size: 18, color: color),
              const SizedBox(width: 4),
              Expanded(child: Text(label, style: const TextStyle(fontSize: 12), overflow: TextOverflow.ellipsis)),
            ]),
            const SizedBox(height: 4),
            Text(value, style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: color)),
          ],
        ),
      ),
    );
  }

  Widget _buildStatusChart() {
    final s = _stats!;
    if (s.statusLabels.isEmpty) return const SizedBox.shrink();

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('Statut des documents', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600)),
            const SizedBox(height: 12),
            ...List.generate(s.statusLabels.length, (i) {
              final total = s.statusCounts.fold(0, (a, b) => a + b);
              final pct = total > 0 ? s.statusCounts[i] / total * 100 : 0.0;
              return Padding(
                padding: const EdgeInsets.symmetric(vertical: 2),
                child: Row(
                  children: [
                    SizedBox(width: 80, child: Text(s.statusLabels[i], style: const TextStyle(fontSize: 12))),
                    Expanded(
                      child: ClipRRect(
                        borderRadius: BorderRadius.circular(4),
                        child: LinearProgressIndicator(
                          value: pct / 100,
                          backgroundColor: Colors.grey.shade200,
                          minHeight: 12,
                        ),
                      ),
                    ),
                    const SizedBox(width: 8),
                    SizedBox(width: 40, child: Text('${s.statusCounts[i]}', textAlign: TextAlign.right)),
                  ],
                ),
              );
            }),
          ],
        ),
      ),
    );
  }

  Widget _buildRecentDocs() {
    final docs = _stats!.recentDocuments;
    if (docs.isEmpty) return const SizedBox.shrink();

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('Documents recents', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600)),
            const SizedBox(height: 8),
            ...docs.map((doc) => ListTile(
              dense: true,
              title: Text(doc.nomFichierOriginal, maxLines: 1, overflow: TextOverflow.ellipsis),
              subtitle: Text('${doc.typeDocument} - ${doc.statut}'),
              trailing: Text(doc.devise, style: const TextStyle(fontWeight: FontWeight.w600)),
            )),
          ],
        ),
      ),
    );
  }
}
