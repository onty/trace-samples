<?php
error_reporting (E_ALL ^ E_NOTICE);
date_default_timezone_set('Europe/Stockholm');
$heading="About CIP/IP Simulator";

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
    <title>About CIP/IP Simulator</title>
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
        <div style="margin:0px 50px;">
          <h1>About</h1>
          The traffic simulator is using FFS to send CIP/IP requests directly towards the SDP to simulate real traffic cases.<br/><br/>
          Currently using FFS v3.0.356 see <a target="_blank" href="https://ericoll.internal.ericsson.com/sites/Online_mediation/Wikis/Tools.aspx" >Online mediation - Tools</a> for more information.<br/>
          A web interface and backend FFS wrapper programmed in php and perl respectively was added to make FFS more userfriendly.<br/>
          Use cases are hardcoded with a set number of variable parameters the user can change.<br/>
          <br/>
          Author: <a href="mailto:mikael.h.persson@ericsson.com">Mikael Persson</a><br/>
          Version: 0.3<br/>
          <br/>
          This application may be downloaded on Ericssons internal <a target="_blank" href="https://ericoll.internal.ericsson.com/sites/BSS-TS/default.aspx">knowledgebase ericoll</a>.
        </div>
      </div>
    </td>
    </tr>
</table>
  <?php if (is_file("../footer.php")) { include "../footer.php"; } else {include "footer.php";} ?>
  </body>
</html>
