abstract class Exporter { void download(String data, String filename); }
Exporter getExporter() => throw UnsupportedError('Cannot create an exporter');
