<?php
ini_set ( "memory_limit", "64M");
if (file_exists('/home/iand/web/lib/')) {
  define('LIB_DIR', '/home/iand/web/lib/');
}
else {
  define('LIB_DIR', '/var/www/lib/');
}
define('MORIARTY_DIR', LIB_DIR . 'moriarty' . DIRECTORY_SEPARATOR);
define('MORIARTY_ARC_DIR', LIB_DIR . 'arc_2008_11_18' . DIRECTORY_SEPARATOR);

$cache_dir = dirname(__FILE__).DIRECTORY_SEPARATOR.'cache';
if ( ! file_exists($cache_dir)) {
  if (mkdir($cache_dir) === FALSE) {
    echo 'Could not create cache directory ' . $cache_dir;
    exit; 
  }
}
define('MORIARTY_HTTP_CACHE_DIR', $cache_dir);



define('PAGET_DIR', LIB_DIR . 'paget2' . DIRECTORY_SEPARATOR);

require_once MORIARTY_DIR . 'moriarty.inc.php';
require_once MORIARTY_DIR . 'store.class.php';
require_once MORIARTY_DIR . 'simplegraph.class.php';
require_once MORIARTY_DIR . 'labeller.class.php';


$script_uri = $_SERVER['SCRIPT_URI'];
if (preg_match("~/lodgrid/([a-z][a-z0-9-]+)$~", $script_uri, $m)) {
  $store = $m[1];
}
else if (!preg_match("~/lodgrid/?$~", $script_uri, $m)) {
  header("404 Not Found");
  header("Content-type: text/html");
  print "<h1>Not Found</h1>";
  exit();
}

if (array_key_exists('query', $_GET)) {
  $query = stripslashes(trim($_GET['query']));  
}

if (array_key_exists('columns', $_GET)) {
  $columns = stripslashes(trim($_GET['columns']));  
}
else {
  $columns = 6;
}




function show_samples() {
  ?>
    <p>Try one of these sample searches:</p>
    <ul>
      <li><a href="./lodgrid/bbc-backstage?query=stephen+fry&columns=6">search for "stephen fry" in BBC Programmes and Music data</a></li>
      <li><a href="./lodgrid/airports?query=london&columns=6">search for "london" in airports data</a></li>
      <li><a href="./lodgrid/space?query=jupiter&columns=6">search for "jupiter" in NASA data</a></li>
      <li><a href="./lodgrid/discogs?query=prodigy&columns=6">search for "prodigy" in Discogs</a></li>
      <li><a href="./lodgrid/lcsh-info?query=medicine&columns=6">search for "medicine" in Library of Congress Subject Headings</a></li>
      <li><a href="./lodgrid/dbpedia?query=france&columns=3">search for "france" in DBPedia </a></li>
      <li><a href="./lodgrid/semlibsearch-dev1?query=discworld&columns=3">search for "discworld" in Semantic Library data</a></li>
      
      <li><a href="./lodgrid/guardian?query=alan&columns=6">search for "alan" in Guardian MP Expense data</a></li>
      <li><a href="./lodgrid/talis-irc?query=germany&columns=5">search for "germany" in Talis IRC data</a></li>
      <li><a href="./lodgrid/schema-cache?query=person&columns=5">search for "person" in schema cache data</a></li>
      <li><a href="./lodgrid/ordnance-survey?query=london&columns=5">search for "london" in Ordnance Survey data</a></li>
      <li><a href="./lodgrid/productdb?query=honda&columns=5">search for "honda" in ProductDB</a></li>
      <li><a href="./lodgrid/bbc-backstage?query=type%3A&quot;http%3A%2F%2Fpurl.org%2Fontology%2Fmo%2FMusicArtist&quot;+big+band&columns=6">search for "big bands" in BBC Programmes and Music data</a></li>
      <li><a href="./lodgrid/space?query=type%3A&quot;http%3A%2F%2Fpurl.org%2Fnet%2Fschemas%2Fspace%2FSpacecraft&quot;+agency%3Aindia&columns=6">search for spacecraft launched by india in NASA data</a></li>
      <li><a href="./lodgrid/kwijibo-dev2?query=Trossachs&columns=6">search for "trossachs" in Climbing data</a></li>
      <li><a href="./lodgrid/periodicals?query=chemical&columns=6">search for "chemical" in Academic Periodicals data</a></li>
      <li><a href="./lodgrid/datagovuk?query=anthony&columns=5">search for "anthony" in London Gazette data</a></li>
      <li><a href="./lodgrid/govuk-crime?query=fixed+penalty&columns=5">search for "fixed penalty" in UK Government Crime data</a></li>
    
    </ul>
  
  <?php
  // <li><a href="?store=govuk-education&query=teacher&columns=6">teacher in govuk-education</a></li>
}


?>
<html>
  <head>
    <title>lodgrid</title>
    <style type="text/css">
/* reset.css */
html, body, div, span, object, iframe, h1, h2, h3, h4, h5, h6, p, blockquote, pre, a, abbr, acronym, address, code, del, dfn, em, img, q, dl, dt, dd, ol, ul, li, fieldset, form, label, legend, table, caption, tbody, tfoot, thead, tr, th, td {margin:0;padding:0;border:0;font-weight:inherit;font-style:inherit;font-size:100%;font-family:inherit;vertical-align:baseline;}
body {line-height:1.5; padding: 1em;}
table {border-collapse:separate;border-spacing:0;}
caption, th, td {text-align:left;font-weight:normal;}
table, td, th {vertical-align:middle;}
blockquote:before, blockquote:after, q:before, q:after {content:"";}
blockquote, q {quotes:"" "";}
a img {border:none;}

/* typography.css */
html {font-size:100.01%;}
body {font-size:75%;color:#222;background:#fff;font-family:"Helvetica Neue", Arial, Helvetica, sans-serif;}
h1, h2, h3, h4, h5, h6 {font-weight:normal;color:#111;}
h1 {font-size:3em;line-height:1;margin-bottom:0.5em;}
h2 {font-size:2em;margin-bottom:0.75em;}
h3 {font-size:1.5em;line-height:1;margin-bottom:1em;}
h4 {font-size:1.2em;line-height:1.25;margin-bottom:1.25em;}
h5 {font-size:1em;font-weight:bold;margin-bottom:1.5em;}
h6 {font-size:1em;font-weight:bold;}
h1 img, h2 img, h3 img, h4 img, h5 img, h6 img {margin:0;}
p {margin:0 0 1.5em;}
p img.left {float:left;margin:1.5em 1.5em 1.5em 0;padding:0;}
p img.right {float:right;margin:1.5em 0 1.5em 1.5em;}
a:focus, a:hover {color:#000;}
a {color:#009;text-decoration:underline;}
blockquote {margin:1.5em;color:#666;font-style:italic;}
strong {font-weight:bold;}
em, dfn {font-style:italic;}
dfn {font-weight:bold;}
sup, sub {line-height:0;}
abbr, acronym {border-bottom:1px dotted #666;}
address {margin:0 0 1.5em;font-style:italic;}
del {color:#666;}
pre {margin:1.5em 0;white-space:pre;}
pre, code, tt {font:1em 'andale mono', 'lucida console', monospace;line-height:1.5;}
li ul, li ol {margin:0 1.5em;}
ul, ol {margin:0 1.5em 1.5em 1.5em;}
ul {list-style-type:disc;}
ol {list-style-type:decimal;}
dl {margin:0 0 1.5em 0;}
dl dt {font-weight:bold;}
dd {margin-left:1.5em;}
table {margin-bottom:1.4em;width:100%;}
th {font-weight:bold;}
thead th {background:#c3d9ff;}
th, td, caption {padding:4px 10px 4px 5px;}
tr.even td {background:#e5ecf9;}
tfoot {font-style:italic;}
caption {background:#eee;}
.small {font-size:.8em;margin-bottom:1.875em;line-height:1.875em;}
.large {font-size:1.2em;line-height:2.5em;margin-bottom:1.25em;}
.hide {display:none;}
.quiet {color:#666;}
.loud {color:#000;}
.highlight {background:#ff0;}
.added {background:#060;color:#fff;}
.removed {background:#900;color:#fff;}
.first {margin-left:0;padding-left:0;}
.last {margin-right:0;padding-right:0;}
.top {margin-top:0;padding-top:0;}
.bottom {margin-bottom:0;padding-bottom:0;}

/* forms.css */
label {font-weight:bold;}
fieldset {padding:1.4em;margin:0 0 1.5em 0;border:1px solid #ccc;}
legend {font-weight:bold;font-size:1.2em;}
input[type=text], input[type=password], input.text, input.title, textarea, select {background-color:#fff;border:1px solid #bbb;}
input[type=text]:focus, input[type=password]:focus, input.text:focus, input.title:focus, textarea:focus, select:focus {border-color:#666;}
input[type=text], input[type=password], input.text, input.title, textarea, select {margin:0.5em 0;}
input.text, input.title {width:300px;padding:5px;}
input.title {font-size:1.5em;}
textarea {width:390px;height:250px;padding:5px;}
input[type=checkbox], input[type=radio], input.checkbox, input.radio {position:relative;top:.25em;}
form.inline {line-height:3;}
form.inline p {margin-bottom:0;}
.error, .notice, .success {padding:.8em;margin-bottom:1em;border:2px solid #ddd;}
.error {background:#FBE3E4;color:#8a1f11;border-color:#FBC2C4;}
.notice {background:#FFF6BF;color:#514721;border-color:#FFD324;}
.success {background:#E6EFC2;color:#264409;border-color:#C6D880;}
.error a {color:#8a1f11;}
.notice a {color:#514721;}
.success a {color:#264409;}
    
table.results td, table.results th { border-left: 1px solid #c2c2c2; border-bottom: 1px solid #c2c2c2; }  
table.results  { border-right: 1px solid #c2c2c2; border-top: 1px solid #c2c2c2;}
table.results th { font-weight: bold; font-size: 1.2em; }  
    </style>

  </head>
  <body>
    <h3><a href="./">LODGRID</a> - Explore data in the Talis Platform</h3>
    <?php
      if (isset($store) ) {
    ?>
    <form action="" method="get">
      <table>
        <tr>
          <th><label for="query">Search <?php echo(htmlspecialchars($store)); ?> store: </label></th>
          <td><input type="text" value="<?php echo(htmlspecialchars($query));?>" name="query" id="query" size="40"/> <input type="submit" value="Search"/></td>
        </tr>
        <tr>
          <th><label for="columns">Number of columns: </label></th>
          <td><select name="columns" id="columns">
          <?php
            for ($i = 3; $i < 15; $i++) {
              echo '<option';
              if ($columns == $i) echo ' selected="selected"';
              echo '>' . htmlspecialchars($i) . '</option>';
            }
          ?>
        </tr>
      </table>
            
    </form>
    <?php
    }
    ?>

<?php
if ($query && $store) {
  $labeller = new Labeller();
  $thestore = new Store('http://api.talis.com/stores/' . $store);
  $cb = $thestore->get_contentbox();

  $response = $cb->search($query, 15);
  $uri = $cb->make_search_uri($query, 15);
  if ($response->is_success()) {
    echo '<table class="results">'. "\n";
    $property_counts = array();
    
    $g = new SimpleGraph();
    $g->from_rdfxml($response->body);  
    
    $items_resources = $g->get_subject_property_values($uri, RSS_ITEMS);
    $items = array();
    
    foreach ($items_resources as $items_resource) {
      if ($items_resource['type'] != 'literal') {
        $items_resource_props = $g->get_subject_properties($items_resource['value']);
        foreach ($items_resource_props as $items_resource_prop) {
          if (strpos( $items_resource_prop, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#_') === 0) {
            $items_resource_prop_values = $g->get_subject_property_values($items_resource['value'], $items_resource_prop);
            foreach ($items_resource_prop_values as $item_resource) {
              if ($item_resource['type'] != 'literal') {
                $items[] = $item_resource['value'];
                $item_props = $g->get_subject_properties($item_resource['value']);
                foreach ($item_props as $item_prop) {
                  if ($item_prop  != 'http://a9.com/-/opensearch/extensions/relevance/1.0/score'
                    && $item_prop != 'http://purl.org/rss/1.0/link'
                    && $item_prop != 'http://purl.org/rss/1.0/title'
                    && $item_prop != 'http://purl.org/rss/1.0/description'
                    && $item_prop != 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type'
                    && $item_prop != 'http://dbpedia.org/property/wikilink'
                  
                    ) {
                    if (array_key_exists($item_prop, $property_counts)) {
                      $property_counts[$item_prop] = $property_counts[$item_prop] + 1;
                    }
                    else {
                      $property_counts[$item_prop] = 1;
                    }
                    if ($g->get_first_literal($item_resource['value'], $item_prop) ) {
                      $property_counts[$item_prop] = $property_counts[$item_prop] + 1;
                    }
                    
                  }
                }
              }
            }
          }
        }
      }
    }


    if (count($property_counts) == 0 ) {
      echo '<p>No results matched your search.</p>';
      show_samples();
    }
    else {
      arsort($property_counts, SORT_NUMERIC );
      $ordered_properties = array_keys($property_counts);
    
      if ($columns > count($ordered_properties)) {
        $columns = count($ordered_properties);
      }
      echo '<tr>';
      echo '<th>&nbsp;</th>';
      for ($i = 0; $i < $columns; $i++) {
        echo '<th><a href="' . htmlspecialchars($ordered_properties[$i]) . '">' . htmlspecialchars($labeller->get_label($ordered_properties[$i], $g, true)) . '</a></th>' . "\n";
      }
      echo '</tr>';

      foreach ($items as $item_uri) {
        echo '<tr>';
        echo '<td><a href="' . htmlspecialchars($item_uri) . '">' . htmlspecialchars($labeller->get_label($item_uri, $g, true)) . '</a><br><br>&rarr;&nbsp;<a href="http://linksailor.com/nav?uri='. htmlspecialchars(urlencode($item_uri)) . '">View&nbsp;with&nbsp;LinkSailor</a></td>';
        for ($i = 0; $i < $columns; $i++) {
          echo '<td>';
          $values = $g->get_subject_property_values($item_uri, $ordered_properties[$i]);
          if (count($values) > 0) {
            for ($vi = 0; $vi < count($values) && $vi < 5; $vi++) {
              if ($vi > 0) echo '<br />';
              if ($values[$vi]['type'] == 'literal') {
                if ($values[$vi]['type']['datatype'] == 'http://www.w3.org/1999/02/22-rdf-syntax-ns#XMLLiteral') {
                  echo $values[$vi]['value'];
                }
                else {
                  echo htmlspecialchars($values[$vi]['value']);
                }
              }
              else {
                echo '<a href="' . htmlspecialchars($values[$vi]['value']) . '">' . htmlspecialchars($labeller->get_label($values[$vi]['value'], $g, true))  . '</a>';
              }
            }
            if (count($values) > 5) {
              echo '<br />' . (count($values) - 5) . ' not shown';
            }
          }
          else {
            echo "&nbsp;";
          }
          echo '</td>';
        }
        echo '</tr>';
      }


      echo '</table>'. "\n";
    }
  }
}
else {
  show_samples();
}


?>
  <hr />
  <p>This demo was created by <a href="http://iandavis.com/">Ian Davis</a> and is powered by the <a href="http://www.talis.com/platform">Talis Platform</a></p>
  </body>
</html>
