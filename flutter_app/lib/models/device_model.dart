class DeviceModel {
  final String id;
  final String plantName;
  final SensorData sensorData;
  final ControlStatus controlStatus;
  final int lastSeen;
  final List<HistoryLog> history;
  final WifiConfig wifiConfig;

  DeviceModel({required this.id, required this.plantName, required this.sensorData, required this.controlStatus, required this.lastSeen, required this.history, required this.wifiConfig});

  factory DeviceModel.fromMap(String id, Map<dynamic, dynamic> map) {
    var historyMap = map['history'] as Map<dynamic, dynamic>? ?? {};
    List<HistoryLog> historyList = historyMap.entries.map((e) => HistoryLog.fromMap(e.key.toString(), e.value as Map<dynamic, dynamic>)).toList();
    historyList.sort((a, b) => b.timestamp.compareTo(a.timestamp));
    return DeviceModel(
      id: id,
      plantName: map['plant_name'] ?? 'Unknown Plant',
      sensorData: SensorData.fromMap(map['sensor_data'] ?? {}),
      controlStatus: ControlStatus.fromMap(map['control_status'] ?? {}),
      lastSeen: map['last_seen'] ?? 0,
      history: historyList,
      wifiConfig: WifiConfig.fromMap(map['wifi_config'] ?? {}),
    );
  }
}

class SensorData {
  final int soil;
  final double temp;
  SensorData({required this.soil, required this.temp});
  factory SensorData.fromMap(Map<dynamic, dynamic> map) {
    return SensorData(soil: map['soil'] ?? 0, temp: (map['temp'] ?? 0).toDouble());
  }
}

class ControlStatus {
  final bool pump;
  final String schedule;
  ControlStatus({required this.pump, required this.schedule});
  factory ControlStatus.fromMap(Map<dynamic, dynamic> map) {
    return ControlStatus(pump: map['pump'] ?? false, schedule: map['schedule'] ?? '00:00');
  }
}

class HistoryLog {
  final String id;
  final int timestamp;
  final String type;
  final String state;
  HistoryLog({required this.id, required this.timestamp, required this.type, required this.state});
  factory HistoryLog.fromMap(String id, Map<dynamic, dynamic> map) {
    return HistoryLog(id: id, timestamp: map['timestamp'] ?? 0, type: map['type'] ?? 'unknown', state: map['state'] ?? 'unknown');
  }
}

class WifiConfig {
  final String ssid;
  final String password;
  WifiConfig({required this.ssid, required this.password});
  factory WifiConfig.fromMap(Map<dynamic, dynamic> map) {
    return WifiConfig(ssid: map['ssid'] ?? '', password: map['password'] ?? '');
  }
}
