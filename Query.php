<?php

header('Content-Type: text/html; charset=utf-8');
$limit = 10;
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
$da = false;
$rank = isset($_REQUEST['rank']) ? $_REQUEST['rank'] : 0;
if ($rank == 0) {
    $paramSet = array(
        'fl' => array('id', 'title', 'og_description', 'og_url'),
    );
} else {
    $paramSet = array(
        'fl' => array('id', 'title', 'og_description', 'og_url'),
        'sort' => 'pageRankFile desc'
    );
}
if ($query) {
 // The Apache Solr Client library should be on the include path
 // which is usually most easily accomplished by placing in the
 // same directory as this script ( . or current directory is a default
 // php include path entry in the php.ini)
    require_once('solrPhpClient/Apache/Solr/Service.php');
 // create a new solr service instance - host, port, and corename
 // path (all defaults in this example)
    $solr = new Apache_Solr_Service('localhost', 8983, '/solr/irindexer/');
 // if magic quotes is enabled then stripslashes will be needed
    if (get_magic_quotes_gpc() == 1) {
        $query = stripslashes($query);
    }
 // in production code you'll always want to use a try /catch for any
 // possible exceptions emitted by searching (i.e. connection
 // problems or a query parsing error)
    try {
        $data = $solr->search($query, 0, $limit, $paramSet);
    } catch (Exception $e) {
    // in production you'd probably log or email this error to an admin
    // and then show a special message to the user but for this example
    // we're going to show the full exception
        die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
    }
}
?>

<html>
    <head>
        <title>Solr Search Engine</title>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
        <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <style>
        .formf {
            margin: 0 auto;
            text-align: center;
            margin-top: 30px;
            font-size: 18px
        }
        a{
            color: #32CD32
        }
        a:visited{
            color: #FF4500
        }
        
    </style>
    </head>

    <body >
        <div class = "formf" >
            <h2> Solr Search Engine </h2>

        <form accept-charset="utf-8" method="get" class="form-group">
            
            <label for="q" ><b> Search for </b></label>
            <input id="q" name="q" type="text" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>"/>
            <input type="submit" class = "btn btn-dark"/>
            <br>
            <br>
            <b>Rank data using </b>
            <?php if ($rank < 1) {
                ?>
            <select name=rank size=1 class = "btn btn-dark dropdown-toggle">
                    <option selected value=0> Lucene</option>
                    <option value=1> PageRank</option>
                
            </select>

            <?php 
        } else { ?>
                <select name=rank size=1 class = "btn btn-dark dropdown-toggle">
                    <option value=0> Lucene</option>
                    <option selected value=1> PageRank</option>
                
            </select>
            <?php 
        } ?>

        </form>
        </div>
        <?php
        // display data
        if ($data) {
            $total = (int)$data
                ->response->numFound;
            $start = min(1, $total);
            $end = min($limit, $total);

            $fileName = "/home/harish/ir572/NYTIMES/URLtoHTML_nytimes_news.csv";
            $fh = fopen($fileName, 'r');
            $theData = fread($fh, filesize($fileName));
            $mapper = array();
            $my_array = explode("\n", $theData);
            foreach ($my_array as $line) {
                $tmp = explode(",", $line);
                $key = "/home/harish/ir572/NYTIMES/nytimes/" . $tmp[0];
                $mapper[$key] = $tmp[1];
            }
            fclose($fh);

            ?>
            <div class = "formf" >
            <h4> Search Results </h4>
            </div>
            <div style = "margin-left: 40px; font-size:18px"> <b>
                Results
                <?php echo $start; ?> - <?php echo $end; ?> of <?php echo $total; ?>:
            </b></div>
            <ol>
                <?php
                    // iterate result documents
                foreach ($data
                    ->response->docs as $doc) {
                    ?>
                <li>
                    <table class="table table-dark table-striped" style="border: 1px solid black; text-align: left">
                        <?php
                        // iterate document fields / values

                        $link = "#";
                        $markers = array('id', 'title', 'og_description', 'og_url');
                        $master_id = "";
                        foreach ($doc as $f => $val) {
                            $field = htmlspecialchars($f, ENT_NOQUOTES, 'utf-8');
                            $value = htmlspecialchars($val, ENT_NOQUOTES, 'utf-8');
                            if ($field == "og_url") $link = $value;
                            if ($field == "id") $master_id = $value;
                            if (in_array($field, $markers)) {
                                $index = array_search($field, $markers, true);
                                unset($markers[$index]);
                            }
                        }
                        if ($master_id != "" && ($link == "#" || $link == "")) {
                            $link = $mapper[$master_id];
                        }

                        foreach ($markers as $x => $d) {
                            // console_log($d);
                            $doc->setField($d, "N/A");
                        }

                        foreach ($doc as $f => $val) {
                            $field = htmlspecialchars($f, ENT_NOQUOTES, 'utf-8');
                            $value = htmlspecialchars($val, ENT_NOQUOTES, 'utf-8');
                            if ($field == "og_url") $field = "URL";
                            if ($field == "id") $field = "ID";
                            if ($field == "og_description") $field = "Description";
                            ?>
                        <tr>
                            <th width="5%" ><?php echo ucwords($field); ?></th>
                            <td>
                                <?php 
                                if ($value == "") $value = "N/A";
                                if (strval($field) != "URL" && strval($field) != "title") {
                                    echo $value;
                                } else {
                                    if (strval($field) == "URL")
                                        $value = $link;
                                    ?>
                                <a href = <?php echo $link ?> target = "_blank">
                                <?php echo $value; ?>
                                </a>
                                <?php 
                            } ?>
                            </td>
                        </tr>
                        <?php 

                    } ?>
                    </table>    
                </li>
                <?php

            }
            ?>
            </ol>
        
            <?php

        }
        ?>
    </body>
</html>