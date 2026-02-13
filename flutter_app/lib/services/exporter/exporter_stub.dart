import 'exporter_base.dart';
class StubExporter implements Exporter {
  @override
  void download(String data, String filename) { print('Stub download: $filename'); }
}
Exporter getExporter() => StubExporter();
