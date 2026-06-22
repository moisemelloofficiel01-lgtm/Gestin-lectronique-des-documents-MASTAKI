import 'package:flutter/material.dart';

void showToast(BuildContext context, String message, {bool isError = false}) {
  final entry = OverlayEntry(
    builder: (ctx) => Positioned(
      top: 60,
      left: 16,
      right: 16,
      child: Material(
        elevation: 6,
        borderRadius: BorderRadius.circular(12),
        color: isError ? Colors.red.shade700 : const Color(0xFF323232),
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(isError ? Icons.error_outline : Icons.check_circle_outline, color: Colors.white, size: 20),
              const SizedBox(width: 10),
              Flexible(child: Text(message, style: const TextStyle(color: Colors.white, fontSize: 14))),
            ],
          ),
        ),
      ),
    ),
  );

  final overlay = Overlay.of(context);
  overlay.insert(entry);
  Future.delayed(const Duration(seconds: 3), () {
    if (entry.mounted) entry.remove();
  });
}
