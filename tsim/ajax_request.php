<?php
if (is_file("config.inc.php")) {
  include "config.inc.php"; 
} else {
  $ffsdir="/opt/ffs";
  $ffscfgdir="/var/opt/ffs";
  $ffslogdir="/var/opt/ffs/logs";
}
include 'definitions.php';
$lines_done=0;
$session="";
$sessionID="";

if (isset($_POST['lines_done'])) {
  $lines_done=$_POST['lines_done'];
}
if (isset($_GET['lines_done'])) {
  $lines_done=$_GET['lines_done'];
}
if (isset($_POST['session'])) {
  $session=$_POST['session'];
}
if (isset($_GET['session'])) {
  $session=$_GET['session'];
}
if (strlen($lines_done) == 0) {
  $lines_done=0;
}
read_config();
foreach($config as $name => $value) {
  if (preg_match('/^s_*/', $name) || preg_match('/^c_*/', $name) || $name === "sim_ip") {
    continue;
  }
  if (strlen($value) > 0) {
    if (!isset($_GET[$name])) {
      $_GET[$name]=$value;
    }
  }
}

if (isset($config["sim_ip"]) && strlen($config["sim_ip"]) > 0 ) {
  $param_string="";
  if (isset($_GET)) {
    foreach($_GET as $name => $value) {
      if ($value != "\"\"") {
        if (strlen($param_string)) {
          $param_string.="&";
        } else {
          $param_string.="?";
        }
        $param_string.="$name=$value";
      }
    }
  }
  $output= file_get_contents("http://".$config["sim_ip"].$_SERVER['SCRIPT_NAME'].$param_string);
  echo $output;
  exit;
}

if (isset($_POST['sessionID'])) {
  $sessionID=$_POST['sessionID'];
  unset($_POST['sessionID']);
}
if (isset($_GET['sessionID'])) {
  $sessionID=$_GET['sessionID'];
  unset($_GET['sessionID']);
}

$search = array ('@&(quot|#34);@i',                // Replace HTML entities
                 '@&(amp|#38);@i',
                 '@&(lt|#60);@i',
                 '@&(gt|#62);@i',
                 '@&(nbsp|#160);@i',
                 '@&(iexcl|#161);@i',
                 '@&(cent|#162);@i',
                 '@&(pound|#163);@i',
                 '@&(copy|#169);@i',
                 '@&#(\d+);@e');                    // evaluate as php

$replace = array ('"',
                 '&',
                 '<',
                 '>',
                 ' ',
                 chr(161),
                 chr(162),
                 chr(163),
                 chr(169),
                 'chr(\1)');
function add_desc($key,$value) {
  global $valuemap;
  if (isset($valuemap[$key]) && isset($valuemap[$key][$value])) {
    return "$value (".$valuemap[$key][$value].")";
  }
  return $value;
}

if ($session != "") { #get log from session
  if (is_file("$ffslogdir/$session")) {
    $lines = file("$ffslogdir/$session");
    $current_line=0;
    echo "SessionID: $sessionID;\n";
    echo "Session: $session\n";
    foreach($lines as $line) {
      $current_line++;
      if ($current_line > $lines_done || preg_match('/Session completed/',$line)) {
        if (preg_match('/(<AVP.*?code="1064".*?value=")(.*?)(".*)/', $line,$line_matches)) {
          $rawCDRdata = html_entity_decode($line_matches[2], ENT_QUOTES);
          $CDRdata="";
          $CDRdata=bin2hex($rawCDRdata);
          print "$line_matches[1]$CDRdata$line_matches[3]\n";
        } elseif (preg_match('/(<AVP.*?code="23".*?value=")(.*?)(".*)/', $line,$line_matches)) {
          $rawTZdata = html_entity_decode($line_matches[2], ENT_QUOTES);
          $hexTZdata = bin2hex($rawTZdata);
          $decTZdata = hexdec($hexTZdata);
          $TZdata=(($decTZdata >> 8) & 8)?"-":"+";
          $reverse=(($decTZdata >> 12) | ((($decTZdata >> 8) & 7 ) << 4));
          $hours=$reverse / 4;
          $min=($reverse % 4) * 15;
          $min=($min === 0)?"":":$min";
          if ($decTZdata & 01) { $DSTdata=" (DST +1)"; }
          if ($decTZdata & 10) { $DSTdata=" (DST +2)"; }
          print "$line_matches[1]UTC$TZdata$hours$min$DSTdata (0x$hexTZdata)$line_matches[3]\n";
        } elseif (preg_match('/(<AVP.*?code-name="(.*?)".*?value=")(.*?)(".*)/', $line,$line_matches)) {
          $modified="$line_matches[1]".add_desc($line_matches[2],$line_matches[3])."$line_matches[4]";
          print "$modified\n";
        } else {
          print "$line";
        }
      }
    }
  } else {
    echo "No matching session found for (\"$session\")\n";
  }
} else { #new session
  $cmd="perl cipipsim.pl --ucpath $ffscfgdir/usecases --logpath $ffslogdir --ffspath $ffsdir --cdrpath \"".$config["cdrpath"]."\"";
  if (isset($_GET)) {
    foreach($_GET as $name => $value) {
      if ($value != "\"\"") {
        $cmd.=" --$name \"$value\"";
      }
    }
  }
  $cmd = escapeshellcmd($cmd);
  $output;
  exec($cmd,$output);

  echo "SessionID: $sessionID;\n";
  echo implode("\n",$output)."\n\n";
} #end new session
?>
