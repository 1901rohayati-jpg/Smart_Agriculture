import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_app/main.dart';
import 'package:provider/provider.dart';
import 'package:flutter_app/services/firebase_service.dart';

void main() {
  testWidgets('App starts and shows login screen', (WidgetTester tester) async {
    await tester.pumpWidget(
      MultiProvider(
        providers: [Provider<FirebaseService>(create: (_) => FirebaseService())],
        child: const SmartIrrigationApp(),
      ),
    );
    expect(find.text('Smart Irrigation'), findsOneWidget);
    expect(find.text('LOGIN'), findsOneWidget);
  });
}
