import org.jsoup.Jsoup;

import java.io.File;
import java.io.FileNotFoundException;
import java.io.FileWriter;
import java.io.IOException;
import java.util.HashMap;
import java.util.HashSet;
import java.util.Scanner;
import java.util.Set;
import org.jsoup.nodes.Document;
import org.jsoup.nodes.Element;
import org.jsoup.select.Elements;


public class EdgeProcessor {

    public static void fileWriter(String filename, Set<String> content) {
        try {
            FileWriter writer = new FileWriter(filename, true);

            for(String s: content)
                writer.write(s+"\n");
            writer.flush();
            writer.close();

        } catch (IOException e) {
            e.printStackTrace();
        }
    }

    public static void main(String[] args) throws IOException, FileNotFoundException {
        String maplocation = "D:\\EduIR-572\\HW4\\NYTIMES\\URLtoHTML_nytimes_news.csv";
        String newsDirectory = "D:\\EduIR-572\\HW4\\NYTIMES\\nytimes\\";


        HashMap<String, String> filetoUrlMap = new HashMap<>();
        HashMap<String, String> urltoFileMap = new HashMap<>();

        File filePtr = new File(maplocation);
        Scanner sc = new Scanner(filePtr);

        String temp = "";
        while (sc.hasNextLine()) {
            temp = sc.nextLine();
            String[] fileUrlString = temp.split(",", 2);
            filetoUrlMap.put(fileUrlString[0], fileUrlString[1]);
            urltoFileMap.put(fileUrlString[1], fileUrlString[0]);
        }

        File dirPtr = new File(newsDirectory);

        int counter = 0;
        for(File file: dirPtr.listFiles()){
            counter ++;
            if (counter%100 == 0)
                System.out.println(counter);

            Document doc = Jsoup.parse(file, "UTF-8", filetoUrlMap.get(file.getName()));
            Elements links = doc.select("a[href]");
//            Element pings = doc.select("src");
            Set<String> edges = new HashSet<>();
            for(Element link: links){
                String url = link.attr("abs:href").trim();
                if(urltoFileMap.containsKey(url))
                    edges.add(file.getName()+" "+ urltoFileMap.get(url));
            }
            fileWriter("D:\\EduIR-572\\HW4\\NYTIMES\\modern_edge_list_simple.txt", edges);
        }






    }

}

