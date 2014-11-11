<?php
error_reporting (E_ALL ^ E_NOTICE);
include 'definitions.php';
date_default_timezone_set('Europe/Stockholm');
$requestPath="";
$request="";
$readytosend=0; #always doing ajax, never on page load
$debug=false;
$requestName="";
$parametermap=array();
$reqDesc="";
$heading="Traffic Simulator";

read_config();

if(isset($_GET['request']))
{
  $pattern = '/^[a-zA-Z0-9.\-_ !\/\+=]*$/';
  if (!preg_match($pattern, rawurldecode($_GET['request'])) || $_GET['request'] == "") {
    //do something
    echo "Wrong request name!!!";
  }
  else {
    $requestPath = rawurldecode($_GET['request']);
    $request = preg_replace('/.*\//','',$requestPath);
    $methodName = $request;
    $requestName = $request;
    $heading=$request;
  }
}

if(isset($_GET['debug'])) { $debug=true; }
if(isset($_POST['debug'])) { $debug=true; }

function special_parameter($name,$value) {
  if (strlen($value)) {
    return $value;
  }
  if (isset($_COOKIE[$name])) {
    $value=$_COOKIE[$name];
  }
  if (isset($_GET[$name])) {
    $value=$_GET[$name];
  }
  if (isset($_POST[$name])) {
    $value=$_POST[$name];
  }
  return $value;
}

function print_parameters(&$parameters) {
  global $purestruct;
  global $parametermap;
  global $readytosend;
  global $datefield;
  global $help;
  if (gettype($parameters) =="array") {
    foreach ($parameters as $param => $value) {
      if (gettype($value) =="array") {
        print_parameters($parameters[$param]);
      } else {
        if ((gettype($value) != "object" && strlen($value) <=0) || $readytosend == 0) {
          if ( ($parametermap[$param]['M'] === 'M' || $readytosend == 0) && ($parametermap[$param]['M'] != 'H') ) {
            echo "<tr>";
            if (isset($help[$param]) || preg_match("/^.*DateTime$/", $param) || preg_match("/^.*Date$/", $param) || (isset($datefield[$param]) && $datefield[$param])) {
              echo "<td><a href=\"#\" title=\"Click to display help text for this item\" onclick=\"return hs.htmlExpand(this, { headingText: '$param' })\">$param</a><div class=\"highslide-maincontent\">";
              if (preg_match("/^.*DateTime$/", $param)) {
                echo $help['DateTime'];
              } elseif (preg_match("/^.*Date$/", $param) || (isset($datefield[$param]) && $datefield[$param])) {
                echo $help['Date'];
              } else {
                echo $help[$param];
              }
              echo "</div></td>";
            } else {
              echo "<td>$param</td>";
            }
            echo "<td><input type=\"text\" name=\"$param\" value=\"$value\"></input></td><td>".$parametermap[$param]['M']."</td>";
            echo "</tr>\n";
            $readytosend=0;
          } elseif ($parametermap[$param]['M'] === 'O') {
            unset($parameters[$param]);
          }
        }
      }
    }
  }
}

if (is_file($_SERVER["DOCUMENT_ROOT"]."/phpincludes/autoVersion.php")) {
  include($_SERVER["DOCUMENT_ROOT"]."/phpincludes/autoVersion.php");
} else {
  function autoVersion($filename) {
    $realfile=$filename;
    if (preg_match('/^\//',$filename)) $realfile=$_SERVER["DOCUMENT_ROOT"].$filename;
    if (is_file($realfile)) return $filename."?autoVer=".filemtime("$realfile");
    return $filename."?autoVer=".date("YmdH");
  }
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <link rel="stylesheet" media="screen,print" href="<?php echo autoVersion('/ericsson.css');?>" type="text/css">
    <link rel="stylesheet" media="screen,print" href="<?php echo autoVersion("trafficsim.css"); ?>" type="text/css">
    <script type="text/javascript" src="highslide/highslide-with-html.packed.js"></script>
    <script type="text/javascript" src="<?php echo autoVersion("trafficsim.js"); ?>"></script>
    <script type="text/javascript" src="jquery-1.7.1.min.js"></script>
    <script type="text/javascript" src="jquery.cookie.js"></script>
    <link rel="stylesheet" type="text/css" href="highslide/highslide.css" />
    <title><?php if (strlen($request)) {echo "$request";} else {echo "Traffic Simulator";} ?></title>

<script type="text/javascript">
  hs.graphicsDir = 'highslide/graphics/';
  hs.outlineType = 'rounded-white';
  hs.showCredits = false;
  hs.wrapperClassName = 'draggable-header';


    function toggle_visibility(id) {
       var e = document.getElementById(id);
       if(e.style.display == 'block')
          e.style.display = 'none';
       else
          e.style.display = 'block';
    }
</script>
  </head>
  <body>
  <?php if (is_file("../header.php")) { include "../header.php"; } else {include "header.php";} ?>
<table>
  <tr>
    <td class="navBox">
      <div>
  <?php if (is_file("../menu.php")) {include "../menu.php";} ?>
  <?php if (is_file("menu.php")) {include "menu.php";} ?>
      </div>
    </td>
    <td class="mainBox" style="width:100%;">
      <div>
        
        <?php
if (strlen($requestPath)) {
        if (is_file("requests/$requestPath")) {
          $lines = file("requests/$requestPath");
          foreach($lines as $line)
          {
#            echo "$line<br/>";
            if (preg_match('/methodName[:=,](\w*)/',$line,$reqfile)) {
              $methodName=$reqfile[1];
            }
            if (preg_match('/([+-]{0,1})([+-]{0,1})([MOH])[:=,]([-_\w]*)([:=,]{0,1})(.*)/',$line,$reqfile)) {
              $struct1=$reqfile[1];
              $struct2=$reqfile[2];
              $mandatory=$reqfile[3];
              $param=$reqfile[4];
              $value=$reqfile[6];
              $value = preg_replace('/\s*$/','', $value); #remove any trailing stuff like spaces and DOS newlines
              $parametermap[$param]['M']=$mandatory;
              if ($struct1 == '+') {
                $structname1=$param;
              } elseif ($struct2 == '+') {
                $structname2=$param;
              } else {
                $target=&$parameters;
                if ($struct1 == '-') {
                  if (isset($purestruct[$structname1]) && $purestruct[$structname1]) {
                    $target=&$parameters[$structname1];
                  } else {
                    $target=&$parameters[$structname1][0];
                  }
                } else {
                  $structname1="";
                }
                if ($struct2 == '-') {
                  if (isset($purestruct[$structname2]) && $purestruct[$structname2]) {
                    $target=&$target[$structname2];
                  } else {
                    $target=&$target[$structname2][0];
                  }
                } else {
                  $structname2="";
                }
                $target[$param]=special_parameter($param,$value);
                if (gettype($target[$param]) != "object" && strlen($target[$param]) <=0 && $mandatory === 'M') {
                  $readytosend=0;
                }
                
              }
            }
            if (preg_match('/description[:=,](.*)/',$line,$reqfile)) {
              $reqDesc.=((strlen($reqDesc))?"<br/>":"").$reqfile[1];
            }
          }
        } else {
          echo "Could not find the specified file: \"$requestPath\"";
        }

      if ($debug) {
        echo "<pre>";
        var_dump($parameters);
        echo "</pre>\n";
      }

        echo "<table>\n";
        echo "<tr><td>";
        echo "<form method=\"POST\" action=\"?request=$requestPath\">\n";
        echo "<input type=\"hidden\" name=\"trafficcase\" value=\"".$parameters['trafficcase']."\">";
        echo "<table>\n";
        print_parameters($parameters);
        echo "</table>\n";
        
        echo "<input type=\"submit\" name=\"submit\" onclick=\"return startSimulation()\" value=\"Start\"></input>";
        echo "</form></td><td>".((!$readytosend && strlen($reqDesc))? "<div class=\"rounded ReqDescBox\"><div class=\"ReqDescHdr\" >Description</div><div>$reqDesc</div></div>":"")."</td></tr></table>\n";

} else { #no request selected
  $readytosend=0;
  echo "Select a traffic case from the menu<br/>";
}
?>
        <div id="ongoing_call" style="float:left;"><img src="ongoing_call.gif" alt="" /></div>
        <!--textarea id="result" style="width:100%;" rows="30" readonly="readonly"></textarea-->
        <div id="resultsummarySent"></div>
        <div id="resultsummaryOK"></div>
        <div id="resultsummaryFailed"></div>
        <div style="clear:both;margin-bottom:20px;"></div>
        <div id="result"></div>
        <!--textarea id="result" style="width:100%;"></textarea-->
      </div>
    </td>
    </tr>
</table>
  <?php if (is_file("../footer.php")) { include "../footer.php"; } else {include "footer.php";} ?>
  </body>
</html>
