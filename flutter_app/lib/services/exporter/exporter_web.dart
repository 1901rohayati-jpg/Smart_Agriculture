import 'dart:html' as html;
import 'exporter_base.dart';
class WebExporter implements Exporter {
  @override
  void download(String data, String filename) {
    final bytes = Uri.encodeComponent(data);
    final anchor = html.AnchorElement(href: "data:text/csv;charset=utf-8,$bytes")..setAttribute("download", filename)..click();
  }
}
Exporter getExporter() => WebExporter();
