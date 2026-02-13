import 'package:firebase_database/firebase_database.dart';
import '../models/device_model.dart';

class FirebaseService {
  final FirebaseDatabase _db = FirebaseDatabase.instance;
  Future<bool> validateDeviceId(String deviceId) async {
    try {
      final snapshot = await _db.ref('devices/$deviceId').get();
      return snapshot.exists;
    } catch (e) { return false; }
  }
  Stream<DeviceModel?> getDeviceStream(String deviceId) {
    return _db.ref('devices/$deviceId').onValue.map((event) {
      final data = event.snapshot.value as Map<dynamic, dynamic>?;
      if (data == null) return null;
      return DeviceModel.fromMap(deviceId, data);
    });
  }
  Future<void> updatePlantName(String deviceId, String name) async {
    await _db.ref('devices/$deviceId').update({'plant_name': name});
  }
  Future<void> setPumpStatus(String deviceId, bool status) async {
    await _db.ref('devices/$deviceId/control_status').update({'pump': status});
  }
  Future<void> updateSchedule(String deviceId, String schedule) async {
    await _db.ref('devices/$deviceId/control_status').update({'schedule': schedule});
  }
  Future<void> updateWifiConfig(String deviceId, String ssid, String password) async {
    await _db.ref('devices/$deviceId/wifi_config').update({'ssid': ssid, 'password': password});
  }
}
