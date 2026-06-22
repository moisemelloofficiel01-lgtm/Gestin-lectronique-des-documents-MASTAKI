import 'package:flutter_test/flutter_test.dart';

import 'package:flutter_app/main.dart';

void main() {
  testWidgets('App loads login page', (WidgetTester tester) async {
    await tester.pumpWidget(const GedMobileApp());
    expect(find.text('GED Mobile'), findsWidgets);
  });
}
