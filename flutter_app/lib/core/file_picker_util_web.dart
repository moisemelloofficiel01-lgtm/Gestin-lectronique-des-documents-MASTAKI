import 'dart:async';
import 'dart:convert';
import 'dart:html' as html;

class PickedFileResult {
  final String name;
  final List<int> bytes;
  PickedFileResult({required this.name, required this.bytes});
}

Future<PickedFileResult?> pickFile({bool imageOnly = false}) async {
  final completer = Completer<PickedFileResult?>();
  final input = html.InputElement(type: 'file')..accept = imageOnly ? 'image/*' : '*/*';

  html.document.body?.append(input);
  input.style.display = 'none';

  input.onChange.listen((_) {
    final files = input.files;
    if (files != null && files.isNotEmpty) {
      final file = files[0];
      final reader = html.FileReader();
      reader.onLoad.listen((_) {
        final result = reader.result;
        if (result is String && result.startsWith('data:')) {
          final comma = result.indexOf(',');
          if (comma > 0) {
            final base64 = result.substring(comma + 1);
            try {
              final bytes = base64Decode(base64);
              if (!completer.isCompleted) {
                completer.complete(PickedFileResult(name: file.name, bytes: bytes));
              }
            } catch (_) {
              if (!completer.isCompleted) completer.complete(null);
            }
          } else {
            if (!completer.isCompleted) completer.complete(null);
          }
        } else {
          if (!completer.isCompleted) completer.complete(null);
        }
        input.remove();
      });
      reader.onError.listen((_) {
        if (!completer.isCompleted) completer.complete(null);
        input.remove();
      });
      reader.readAsDataUrl(file);
    } else {
      completer.complete(null);
      input.remove();
    }
  });

  input.click();

  final result = await completer.future.timeout(const Duration(seconds: 30), onTimeout: () {
    input.remove();
    return null;
  });
  return result;
}
