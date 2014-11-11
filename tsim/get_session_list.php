<?php
if (is_file("config.inc.php")) {
  include "config.inc.php"; 
} else {
  $ffslogdir="/var/opt/ffs/logs";
}
$session="";
$session_time=0;
$firsttmptimestamp=99999999999999999999;

if (isset($_POST['session'])) {
  $session=$_POST['session'];
}
if (isset($_GET['session'])) {
  $session=$_GET['session'];
}
include_once 'definitions.php';
read_config();
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
} else {
  
  if (preg_match('/.*_(\d{4}\d{2}\d{2})_(\d{2}\d{2}\d{2})_(\d{6}).*$/', $session,$session_matches)) {
    $session_time="$session_matches[1]$session_matches[2]$session_matches[3]";
  }
  # Check if we have any running sessions and get the lowest timestamp
  if (is_dir("$ffslogdir/")) {
    $filelisttmp=glob("$ffslogdir/tmp*");
    sort($filelisttmp);
    foreach (($filelisttmp) as $path) {
      if (preg_match('/.*tmp(\d*)$/', $path,$tmpmatches)) {
        $firsttmptimestamp=$tmpmatches[1];
        break;
      }
    }
  }
  if (is_dir("$ffslogdir/")) {
    $filelist=glob("$ffslogdir/cipipsim_*");
    rsort($filelist);
    foreach (($filelist) as $path) {
      $file=preg_replace('/.*\//','',$path);
      if (preg_match('/(.*_(\d{4})(\d{2})(\d{2})_(\d{2})(\d{2})(\d{2})_(\d{6}).*)data.log$/', $file,$matches)) {
        # if there is another session still running that was started before this we have to wait until it is finished
        # otherwise it will not be picked up on the next run
#        if ("$matches[2]$matches[3]$matches[4]$matches[5]$matches[6]$matches[7]$matches[8]" > $firsttmptimestamp) break;
        # when we find the one or lower that we have already in the browser we skip the rest
        if ("$matches[2]$matches[3]$matches[4]$matches[5]$matches[6]$matches[7]$matches[8]" <= $session_time) break;
        $datafile=file($path);
        $datafile=implode($datafile);
        $anum="";
        $service="";
        if (preg_match('/AVP code="444" code-name="Subscription-Id-Data" mandatory="true" protected="false" value="(\d*)"/', $datafile,$matches2)) {
          $anum=$matches2[1];
        }
        if (preg_match('/AVP code="439".*value="(\d*)"/', $datafile,$matches2)) {
          $service=$matches2[1];
          if ($service == 0) {$service="Voice";}
          if ($service == 1) {$service="Fax";}
          if ($service == 2) {$service="Data";}
          if ($service == 3) {$service="Unknown";}
          if ($service == 4) {$service="SMS";}
          if ($service == 7) {$service="Video Telephony";}
          if ($service == 8) {$service="Video Conference";}
        }
        if (preg_match('/Ericsson_OCS_V1_0.0.0.7.32251@3gpp.org/', $datafile)) {
          $service="Data";
        }
        echo "<div class='prefiltered' msisdn=\"$anum\" session=\"$file\"><a href=\"#\" onclick=\"getSessionData(this,'$file')\">$matches[2]-$matches[3]-$matches[4] $matches[5]:$matches[6]:$matches[7] $anum $service</a> <span>|</span></div>\n";
      }
    }
  }
}
?>
