    import org.apache.tika.exception.TikaException;
    import org.apache.tika.metadata.Metadata;
    import org.apache.tika.parser.AutoDetectParser;
    import org.apache.tika.sax.BodyContentHandler;
    import org.xml.sax.SAXException;

    import java.io.*;
    import java.util.ArrayList;

    public class tikaExtraction {
        public static void fileWriter(String filename, String content) {
            try {
                FileWriter writer = new FileWriter(filename, true);

                writer.write(content);
                writer.flush();
                writer.close();

            } catch (IOException e) {
                e.printStackTrace();
            }
        }

        public static String getTrimmedData(String[] data){
            String result = "";

            for(String datum: data){
                String content = datum.trim();
                if (!content.equals("")) result += content + "\n";
            }

            return result;
        }
        public static void main(String[] args) throws IOException, TikaException, SAXException {
            String newsDirectory = "D:\\EduIR-572\\HW4\\NYTIMES\\nytimes\\";

            File dirPtr = new File(newsDirectory);

            int counter = 0;
            for(File file: dirPtr.listFiles()) {
                counter++;
                if (counter % 100 == 0)
                    System.out.println(counter);

                BodyContentHandler handler = new BodyContentHandler(-1);
                AutoDetectParser parser = new AutoDetectParser();
                Metadata metadata = new Metadata();

               try (InputStream stream = new FileInputStream(file)) {
                    parser.parse(stream, handler, metadata);
                   String[] data =  handler.toString().split("\n");
                   String result = getTrimmedData(data);
                   fileWriter("D:\\EduIR-572\\HW5\\big.txt", result );
                }

            }
        }
    }
