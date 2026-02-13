import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../models/device_model.dart';
import '../services/firebase_service.dart';
import '../services/csv_service.dart';

class DashboardScreen extends StatelessWidget {
  final String deviceId;
  const DashboardScreen({super.key, required this.deviceId});

  @override
  Widget build(BuildContext context) {
    return StreamBuilder<DeviceModel?>(
      stream: context.read<FirebaseService>().getDeviceStream(deviceId),
      builder: (context, snapshot) {
        if (!snapshot.hasData || snapshot.data == null) return const Scaffold(body: Center(child: CircularProgressIndicator()));
        final device = snapshot.data!;
        return Scaffold(
          appBar: AppBar(title: const Text('Smart Irrigation Dashboard'), backgroundColor: Colors.green, foregroundColor: Colors.white),
          body: SingleChildScrollView(
            padding: const EdgeInsets.all(16),
            child: Column(children: [
              _buildOverview(context, device),
              const SizedBox(height: 16),
              _buildSensor('Soil Moisture', '${device.sensorData.soil}%', Icons.water_drop, Colors.blue),
              _buildSensor('Temperature', '${device.sensorData.temp}°C', Icons.thermostat, Colors.orange),
              const SizedBox(height: 16),
              _buildControls(context, device),
              const SizedBox(height: 16),
              _buildHistory(device),
            ]),
          ),
        );
      },
    );
  }

  Widget _buildOverview(BuildContext context, DeviceModel device) {
    final nameController = TextEditingController(text: device.plantName);
    return Card(child: ListTile(
      leading: const Icon(Icons.eco, color: Colors.green),
      title: TextField(
        controller: nameController,
        decoration: const InputDecoration(labelText: 'Plant Name'),
        onSubmitted: (v) => context.read<FirebaseService>().updatePlantName(deviceId, v),
      ),
      subtitle: Text('Device ID: ${device.id}'),
    ));
  }

  Widget _buildSensor(String label, String val, IconData icon, Color color) {
    return Card(child: ListTile(leading: Icon(icon, color: color), title: Text(label), trailing: Text(val, style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold))));
  }

  Widget _buildControls(BuildContext context, DeviceModel device) {
    return Card(child: Column(children: [
      const ListTile(title: Text('Controls', style: TextStyle(fontWeight: FontWeight.bold))),
      SwitchListTile(title: const Text('Manual Pump'), value: device.controlStatus.pump, onChanged: (v) => context.read<FirebaseService>().setPumpStatus(deviceId, v)),
      ListTile(
        title: const Text('Schedule'),
        subtitle: Text(device.controlStatus.schedule),
        trailing: const Icon(Icons.access_time),
        onTap: () async {
          final time = await showTimePicker(context: context, initialTime: const TimeOfDay(hour: 7, minute: 0));
          if (time != null) {
            context.read<FirebaseService>().updateSchedule(deviceId, "${time.hour.toString().padLeft(2, '0')}:${time.minute.toString().padLeft(2, '0')}");
          }
        },
      ),
      ListTile(
        title: const Text('WiFi Config'),
        subtitle: Text('SSID: ${device.wifiConfig.ssid}'),
        trailing: const Icon(Icons.wifi),
        onTap: () {
          // Simplified for brevity in this recreation
        },
      ),
    ]));
  }

  Widget _buildHistory(DeviceModel device) {
    return Card(child: Column(children: [
      ListTile(title: const Text('History'), trailing: IconButton(icon: const Icon(Icons.download), onPressed: () => CsvService.exportHistoryToCsv(device.history))),
      ...device.history.take(5).map((l) => ListTile(title: Text('Pump ${l.state}'), subtitle: Text(l.type))),
    ]));
  }
}
