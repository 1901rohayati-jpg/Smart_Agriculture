import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../services/firebase_service.dart';
import 'dashboard_screen.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});
  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final TextEditingController _deviceIdController = TextEditingController();
  bool _isLoading = false;
  void _handleLogin() async {
    final deviceId = _deviceIdController.text.trim();
    if (deviceId.isEmpty) return;
    setState(() => _isLoading = true);
    final isValid = await context.read<FirebaseService>().validateDeviceId(deviceId);
    setState(() => _isLoading = false);
    if (isValid && mounted) {
      Navigator.pushReplacement(context, MaterialPageRoute(builder: (context) => DashboardScreen(deviceId: deviceId)));
    }
  }
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.green.shade50,
      body: Center(
        child: Container(
          constraints: const BoxConstraints(maxWidth: 400),
          padding: const EdgeInsets.all(32),
          decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16)),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(Icons.eco, size: 80, color: Colors.green),
              const Text('Smart Irrigation', style: TextStyle(fontSize: 28, fontWeight: FontWeight.bold)),
              const SizedBox(height: 32),
              TextField(controller: _deviceIdController, decoration: const InputDecoration(labelText: 'Device ID', border: OutlineInputBorder())),
              const SizedBox(height: 24),
              SizedBox(width: double.infinity, height: 50, child: ElevatedButton(onPressed: _isLoading ? null : _handleLogin, child: const Text('LOGIN'))),
            ],
          ),
        ),
      ),
    );
  }
}
