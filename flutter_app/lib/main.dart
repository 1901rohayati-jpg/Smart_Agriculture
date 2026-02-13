import 'package:flutter/material.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:provider/provider.dart';
import 'services/firebase_service.dart';
import 'screens/login_screen.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  try {
    await Firebase.initializeApp(
      options: const FirebaseOptions(apiKey: "demo", appId: "demo", messagingSenderId: "demo", projectId: "demo", databaseURL: "https://demo.firebaseio.com"),
    );
  } catch (e) {}
  runApp(MultiProvider(
    providers: [Provider<FirebaseService>(create: (_) => FirebaseService())],
    child: const SmartIrrigationApp(),
  ));
}

class SmartIrrigationApp extends StatelessWidget {
  const SmartIrrigationApp({super.key});
  @override
  Widget build(BuildContext context) {
    return MaterialApp(debugShowCheckedModeBanner: false, theme: ThemeData(primarySwatch: Colors.green), home: const LoginScreen());
  }
}
