<?php

// make sure browsers see this page as utf-8 encoded HTML
header('Content-Type: text/html; charset=utf-8');
ini_set('memory_limit', -1);
$limit = 10;
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
$results = false;

if ($query)
{
  // The Apache Solr Client library should be on the include path
  // which is usually most easily accomplished by placing in the
  // same directory as this script ( . or current directory is a default
  // php include path entry in the php.ini)
  require_once('solr-php-client-master/Apache/Solr/Service.php');

  // create a new solr service instance - host, port, and webapp
  // path (all defaults in this example)
  $solr = new Apache_Solr_Service('localhost', 8983, '/solr/myexample/');

  // if magic quotes is enabled then stripslashes will be needed
  if (get_magic_quotes_gpc() == 1)
  {
    $query = stripslashes($query);
  }

  if($_GET['method']=="pagerank")
  {
    $additionalParameters = array(
      'fq' => '',
      'facet' => 'true',
      'sort' => 'pageRankFile desc'
    );
  }

    
  // in production code you'll always want to use a try /catch for any
  // possible exceptions emitted  by searching (i.e. connection
  // problems or a query parsing error)
  try
  {
    $results = $solr->search($query, 0, $limit, $additionalParameters);
  }
  catch (Exception $e)
  {
    // in production you'd probably log or email this error to an admin
    // and then show a special message to the user but for this example
    // we're going to show the full exception
    die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
  }

}

?>

<html>
  <head>

    <title>PHP Solr Client Example</title>
    
    <style>

      #form_div{
        text-align: center;
      }

      input[type=text] {
        width: 300px;
        border: 2px solid #ccc;
        border-radius: 4px;
        font-size: 16px;
        padding: 5px;
      }

      input[type=submit]{
        font-size: 20px;
        border: 2px solid #ccc;
        border-radius: 4px;
        background: white;
      }

      .radioText {
        font-size: 20px;
        margin-top: 5px;
        margin-right: 10px;
      }

      #title{
        font-size: 20px;
      }

      #url{
        color: green;
      }
    </style>

    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

  </head>
  
  <body>
  <!-- You can show each result as: Title(clickable), Url(clickable), ID, Description -->
    <div id="form_div">

      <h1>PHP Solr Client Search</h1>

      <form accept-charset="utf-8" method="GET" id="searchForm">
          
          <input id="q" name="q" type="text" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>" placeholder="Search.." required/>
          
          <input type="submit" value="Search"/><br>

          <input type="radio" name="method" value="solr" <?php if(!isset($_GET['method']) || $_GET['method']== 'solr')  echo ' checked="checked"';?> ><inline class="radioText">Solr</inline>
          <input type="radio" name="method" value="pagerank" <?php if(isset($_GET['method']) && $_GET['method']== 'pagerank')  echo ' checked="checked"';?>><inline class="radioText">Page Rank</inline><br>
          
      </form>

    </div>
    <?php
    //display results
    if ($results){
        
      $total = (int)$results->response->numFound;
      $start = min(1, $total);
      $end = min($limit, $total);
    
    ?>
    

    <!-- SpellCheck if no result -->
    <?php
    if ($total == 0) {

      require_once('SpellCorrector.php');

      echo "<h3>Did you mean "; 
      
      // split search query into individual words stored in an array
      $query_arr =  explode(" ", $query);
      
      $correct_query = "";

      foreach($query_arr as $element){
          
          // correct each search word
          $correct_element = SpellCorrector::correct($element);
          $correct_query = $correct_query . $correct_element . " ";
         
      }

    }

    ?>
    <a href="http://localhost/~kuanhanchen/csci572/index.php?q=<?php echo htmlentities($correct_query); ?> "><?php echo $correct_query; ?></a></h3>
  
    
    <div>Results <?php echo $start; ?> - <?php echo $end;?> of <?php echo $total; ?>:</div>

    <br>

    <?php foreach ($results->response->docs as $doc): ?>
        <?php 
          
          // get real URL
          // example id:"/Users/KuanHanChen/Documents/CSCI-572/HW/HW4/solr-6.4.2/LATimesData/LATimesDownloadData/7a17fbd8-cc6c-4be0-9b2f-945d9a157121.html"
          $fileName_arr = explode('/', rtrim($doc->id, '/')); // get a fileName, LATimesDownloadData/7a17fbd8-cc6c-4be0-9b2f-945d9a157121.html in an array
          $fileName_str = array_pop($fileName_arr); // get fileName

          
          // get original url from fileName
          $file = fopen("mapLATimesDataFile.csv", "r");
          
          while ($row = fgetcsv($file)) {

              if ($row[0] == $fileName_str) {
                  $url = $row[1];
                  break;
              }
          }

          fclose($file);
        
       
          /*************************************************************/ 


          // Snippet, output in description
          $file_url = "./LATimesDownloadData/".$fileName_str;
          // echo $fileName_str;
          libxml_use_internal_errors(true);
          $html = file_get_contents($file_url);

          $dom = new DOMDocument();
          $dom->loadHTML($html);

          $content = "";

          foreach($dom->getElementsByTagName("body")->item(0)->childNodes as $child) {
              $content .= $dom->saveHTML($child);
          }

          $content = preg_replace('/<[^>]*>/', '.', $content);

          $searchword = $results->responseHeader->params->q;

          $matches = array();
          $content_arr = explode(".",$content);

          // print_r($homepage_lines_arr);
          foreach ($content_arr as $key => $value) {
            
            if(preg_match("/\b$searchword\b/i", $value)) {
                  
                  $value = preg_replace("/\w*?".preg_quote($searchword)."\w*/i", '<b>$0</b>', $value);

                  break;

              }
          }
        
        ?>

        <table>
        
          <tr>
            
            <td><strong>Title: </strong></td>
            <td><a href="<?php echo $url; ?>" id="title"><?php echo $doc->title ? $doc->title : "None"; ?></a></td>

          </tr>
        
          <tr>
            
            <td><strong>Url: </strong></td>
            <td><a href="<?php echo $url; ?>" id="url"><?php echo $url; ?></a></td>
          
          </tr>

          <tr>

            <td><strong>ID: </strong></td>
            <td><?php echo $doc->id ? $doc->id : "None"; ?></td>

          </tr>

          <tr>

            <td><strong>Description: </strong></td>
            <td><?php echo $value ?></td>

          </tr>
        </table>

        <br>

    <?php endforeach; ?>
  
  <?php 
  }
  ?>

  <script>

    $(function() {
      // example query for searching "ca" with a responseHeader, suggest: http://localhost:8983/solr/myexample/suggest?indent=on&q=ca&wt=json 


      $("#q").autocomplete({

          source : function(request, response) {
              
              //get the last/current word
              var searching_word = $("#q").val().toLowerCase();
              var searching_word_arr = searching_word.split(" ");
              var lastword = searching_word_arr[searching_word_arr.length-1];
              
              var URL = "http://localhost:8983/solr/myexample/suggest?indent=on&q=" + lastword + "&wt=json";
        
              $.ajax({
                  url : URL,
                  success : function(data) {

                      // data is a result for searching "ca" in a json format
                      // e.g. 
                      //{
                      // "responseHeader":{
                      //   "status":0,
                      //   "QTime":0},
                      // "suggest":{"suggest":{
                      //   "ca":{
                      //     "numFound":20,
                      //     "suggestions":[
                      //       {
                      //         "term":"ca",
                      //         "weight":2430,
                      //         "payload":""},
                      //       {
                      //         "term":"canonical",
                      //         "weight":18392,
                      //         "payload":""},
                      //       {
                      //         "term":"carousel",
                      //         "weight":17138,
                      //         "payload":""},
                      //       {
                      //         "term":"calc",
                      //         "weight":12258,
                      //         "payload":""}
                      //]}}}}
                      // so the result array is data.suggest.suggest.ca.suggestions

                      var suggestions_arr = data.suggest.suggest[lastword].suggestions;

                      suggestions_arr = $.map(suggestions_arr, function (value, index) {
                          
                          completed_words = "";

                          // if multiple words, keep the completed words
                          if(searching_word_arr.length > 1) {

                              var lastIndex = $("#q").val().lastIndexOf(" ");
                              completed_words = $("#q").val().substring(0, lastIndex);
                          }
                        
                          return completed_words + " " + value.term;
                      
                      });

                      response(suggestions_arr.slice(0, 5));
                  },
                  dataType : 'jsonp',
                  jsonp : 'json.wrf'
              });
          },
          minLength : 1
      });
    });

  </script>
</body>
</html>