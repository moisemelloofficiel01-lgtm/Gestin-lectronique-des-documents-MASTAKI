import 'package:file_picker/file_picker.dart';

class PickedFileResult {
  final String name;
  final List<int> bytes;
  PickedFileResult({required this.name, required this.bytes});
}

Future<PickedFileResult?> pickFile({bool imageOnly = false}) async {
  try {
    final type = imageOnly ? FileType.image : FileType.any;
    final result = await FilePicker.platform.pickFiles(type: type, withData: true);
    if (result == null || result.files.isEmpty) return null;
    final f = result.files.first;
    if (f.bytes != null) {
      return PickedFileResult(name: f.name, bytes: f.bytes!);
    }
  } catch (_) {}
  return null;
}
