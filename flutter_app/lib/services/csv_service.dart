import '../models/device_model.dart';
import 'package:intl/intl.dart';
import 'exporter/exporter_base.dart'
    if (dart.library.html) 'exporter/exporter_web.dart'
    if (dart.library.io) 'exporter/exporter_stub.dart';

class CsvService {
  static void exportHistoryToCsv(List<HistoryLog> history) {
    String csvData = "Timestamp,Date,Type,State\n";
    for (var log in history) {
      DateTime date = DateTime.fromMillisecondsSinceEpoch(log.timestamp * 1000);
      csvData += "${log.timestamp},${DateFormat('yyyy-MM-dd HH:mm:ss').format(date)},${log.type},${log.state}\n";
    }
    getExporter().download(csvData, "irrigation_history.csv");
  }
}
