package parser;
import java.io.File;
import java.io.FileInputStream;
import java.io.IOException;
import java.util.*;
import java.io.*;

import org.apache.tika.exception.TikaException;
import org.apache.tika.metadata.Metadata;
import org.apache.tika.parser.ParseContext;
import org.apache.tika.parser.html.HtmlParser;
import org.apache.tika.sax.BodyContentHandler;

import org.xml.sax.SAXException;

public class Parse {

   public static void main(final String[] args) throws IOException,SAXException, TikaException {
	   
	   // creates the file
	   File outputFile = new File("big.txt");
	   outputFile.createNewFile();
	   PrintWriter writer = new PrintWriter(new OutputStreamWriter(new FileOutputStream(outputFile), "UTF-8"));
      
	   // returns pathnames for files and directory
	   File dir = new File("/Users/KuanHanChen/Documents/CSCI-572/HW/HW4/solr-6.4.2/LATimesData/LATimesDownloadData");

	   
	   for(File file: dir.listFiles()){
			if(!file.getName().equals(".DS_Store")){
				
				 //detecting the file type
			      BodyContentHandler handler = new BodyContentHandler();
			      Metadata metadata = new Metadata();
			      FileInputStream inputstream = new FileInputStream(file);
			      ParseContext pcontext = new ParseContext();
			      
			      //Html parser 
			      HtmlParser htmlparser = new HtmlParser();
			      htmlparser.parse(inputstream, handler, metadata, pcontext);
			      
			      //body content
			      String body_str = handler.toString();
			      body_str = body_str.replaceAll("[0-9]", "");
			      String[] body_arr = body_str.split("[\\p{Punct}\\s]+");
			      for (int i = 0; i < body_arr.length; i++) {
			          
//			          System.out.println(body_arr[i]);
			          writer.println(body_arr[i]);
			          
			      }
			      
			      
			      //metadata
			      // System.out.println("Metadata of the document:");
			      String[] metadataNames_arr = metadata.names();
			      for(String name : metadataNames_arr) {
			    	  String metatada_str = metadata.get(name).replaceAll("[0-9]", "");
			    	  String[] metatada_arr = metatada_str.split("[\\p{Punct}\\s]+");
			    	  for(int i = 0; i<metatada_arr.length; i++){
//			    		  System.out.println(name + ":   " + metatada_arr[i]);
			    		  writer.println(metatada_arr[i]);
			    	  }
			           
			      }			
			}
		}

		writer.flush();
		writer.close();
      
   }
   
}