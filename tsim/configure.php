<?php
error_reporting (E_ALL ^ E_NOTICE);
include 'definitions.php';
date_default_timezone_set('Europe/Stockholm');
$heading="Configure CIP/IP Simulator";

$config_file=Array();

if (is_file("config/configuration")) {
  $lines = file("config/configuration");
  foreach($lines as $line) {
    if (preg_match('/^(\w*)[:=,](.*)/',$line,$matches)) {
      if (!(isset($_POST["s_".$matches[1]]) || isset($_POST["c_".$matches[1]]))) $config_file[$matches[1]]=$matches[2];
    }
  }
}

if (isset($_POST) && count($_POST)) {
  $fh = fopen("config/configuration", 'w') or die("can't open file: config/configuration");
  foreach ($_POST as $key => $value) {
    if (preg_match("/^s_(.*)$/", $key,$matches)) {
      fwrite($fh, $matches[1]."=$value\n");
    }
    if (preg_match("/^c_(.*)$/", $key,$matches)) {
      setcookie($matches[1], $value, 2145916800, '/', $_SERVER['SERVER_NAME']);
    }
  }
  #rewrite all parameters that are not configurable through the GUI
  foreach ($config_file as $key => $value) {
    fwrite($fh, $key."=$value\n");
  }
  
  fclose($fh);
  #reload the page so that the browser will send the new cookie values
  header( "Location: http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT'].$_SERVER['PHP_SELF'] ) ;
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
    <script type="text/javascript" src="jquery-1.7.1.min.js"></script>
    <title><?php if (isset($heading) && strlen($heading)) {echo "$heading";} ?></title>
    <script type="text/javascript">
      $(document).ready(function(){
        set_defaults();
        
        $('input[name^="c_"]').focus(function(){
          if ($(this).attr('useDefault')) {
            $(this).val("");
            $(this).css('color','black');
            $(this).attr('useDefault','');
          }
          return false;
        });
        $('input[name^="c_"]').blur(function(){
          var server_field=$(this).attr('name').replace('c_','s_');
          if ($(this).val() == "") {
            $(this).val($("input[name='"+server_field+"']").val());
            $(this).css('color','lightgrey');
            $(this).attr('useDefault','yes');
          } else {
            $(this).attr('useDefault','');
          }
          return false;
        });
        $('input[name^="s_"]').blur(function(){
          var client_field=$(this).attr('name').replace('s_','c_');
          if ($("input[name='"+client_field+"']").attr('useDefault') == 'yes') {
            $("input[name='"+client_field+"']").val($(this).val());
          }
        });
        $('input#save').click(function(){
          $('input[name^="c_"]').each(function(){
            var server_field=$(this).attr('name').replace('c_','s_');
            if ($(this).attr('useDefault')) {
              $(this).val("");
            }
          });
          return true;
        });
        $('input#reset').click(function(){
          setTimeout("set_defaults()",100);
          return true;
        });
        $('button#showServerConfig').click(function(){
          $('th#s_col').each(function(){
            $(this).toggleClass('hidden');
          });
          $('td#s_col').each(function(){
            $(this).toggleClass('hidden');
          });
          if ($('button#showServerConfig').val() == 'show') {
            $('button#showServerConfig').val("hide");
            $('button#showServerConfig').html("Hide Server Config");
          } else {
            $('button#showServerConfig').val("show");
            $('button#showServerConfig').html("Show Server Config");
          }
        });
      });
      function set_defaults() {
        $('input[name^="c_"]').each(function(){
          var server_field=$(this).attr('name').replace('c_','s_');
          if ($(this).val() == "" || $(this).attr('useDefault') == 'yes') {
            $(this).css('color','lightgrey');
            $(this).val($("input[name='"+server_field+"']").val());
            $(this).attr('useDefault','yes');
          }
        });
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
    <td class="mainBox">
      <div>
        <?php read_config(); ?>
        <form name="cipipsim_config_form" id="cipipsim_config_form" method="POST">
          <!--table><tr><td-->
          <div style="float:left;padding-bottom:10px;padding-right:10px;">
            <div class="rounded ReqDescBox"><div class="ReqDescHdr" >General Parameters</div><div>
              <table>
                <tr><th>Parameter</th><th id="s_col" class="hidden">Server</th><th>Client</th><th>Comment</th></tr>
                <tr><td>Simulator Server</td><td id="s_col" class="hidden"><input type="text" name="s_sim_ip"<?php echo " value=\"".$config['s_sim_ip']."\""; ?> /></td><td><input type="text" <?php if (!$config['c_sim_ip']) echo "useDefault='yes' "; ?>name="c_sim_ip"<?php echo " value=\"".$config['c_sim_ip']."\""; ?> /></td><td>Leave blank for same as webserver</td></tr>
                <tr><td>CDR Path</td><td id="s_col" class="hidden"><input type="text" name="s_cdrpath"<?php echo " value=\"".$config['s_cdrpath']."\""; ?> /></td><td><input type="text" <?php if (!$config['c_cdrpath']) echo "useDefault='yes' "; ?>name="c_cdrpath"<?php echo " value=\"".$config['c_cdrpath']."\""; ?> /></td><td>Specify path to write CDR files</td></tr>
              </table>
            </div></div>
          </div> <!-- floater -->
          <div style="float:left;padding-bottom:10px;padding-right:10px;">
            <div class="rounded ReqDescBox"><div class="ReqDescHdr" >Diameter Parameters</div><div>
              <table>
                <tr><th>Parameter</th><th id="s_col" class="hidden">Server</th><th>Client</th><th>Comment</th></tr>
                <tr><td>Simulator FQDN</td><td id="s_col" class="hidden"><input type="text" name="s_ownIP"<?php echo " value=\"".$config['s_ownIP']."\""; ?> /></td><td><input type="text" <?php if (!$config['c_ownIP']) echo "useDefault='yes' "; ?>name="c_ownIP"<?php echo " value=\"".$config['c_ownIP']."\""; ?> /></td><td><b>Mandatory</b>, Fully Qualified Domain Name of the simulator server, must resolve to correct IP on simulator server</td></tr>
                <tr><td>Simulator Realm</td><td id="s_col" class="hidden"><input type="text" name="s_ownRealm"<?php echo " value=\"".$config['s_ownRealm']."\""; ?> /></td><td><input type="text" <?php if (!$config['c_ownRealm']) echo "useDefault='yes' "; ?>name="c_ownRealm"<?php echo " value=\"".$config['c_ownRealm']."\""; ?> /></td><td><b>Mandatory</b></td></tr>
                <tr><td>Simulator SCTP port</td><td id="s_col" class="hidden"><input type="text" name="s_ownPort"<?php echo " value=\"".$config['s_ownPort']."\""; ?> /></td><td><input type="text" <?php if (!$config['c_ownPort']) echo "useDefault='yes' "; ?>name="c_ownPort"<?php echo " value=\"".$config['c_ownPort']."\""; ?> /></td><td>Leave blank to randomize ports every request, 8732-9000</td></tr>
                <tr><td>SDP Server</td><td id="s_col" class="hidden"><input type="text" name="s_destIP"<?php echo " value=\"".$config['s_destIP']."\""; ?> /></td><td><input type="text" <?php if (!$config['c_destIP']) echo "useDefault='yes' "; ?>name="c_destIP"<?php echo " value=\"".$config['c_destIP']."\""; ?> /></td><td><b>Mandatory</b>, IP, hostname or FQDN of SDP, must resolve on simulator server if IP is not used</td></tr>
                <tr><td>SDP Realm</td><td id="s_col" class="hidden"><input type="text" name="s_destRealm"<?php echo " value=\"".$config['s_destRealm']."\""; ?> /></td><td><input type="text" <?php if (!$config['c_destRealm']) echo "useDefault='yes' "; ?>name="c_destRealm"<?php echo " value=\"".$config['c_destRealm']."\""; ?> /></td><td><b>Mandatory</b></td></tr>
                <tr><td>SDP SCTP port</td><td id="s_col" class="hidden"><input type="text" name="s_destPort"<?php echo " value=\"".$config['s_destPort']."\""; ?> /></td><td><input type="text" <?php if (!$config['c_destPort']) echo "useDefault='yes' "; ?>name="c_destPort"<?php echo " value=\"".$config['c_destPort']."\""; ?> /></td><td>Default: 8732</td></tr>
              </table>
            </div></div>
          </div> <!-- floater -->
          <!--/td><td-->
          <!--/td></tr></table-->
          <div style="clear:both;"></div>
          <input id="save" type="submit" value="Save" />
          <input id="reset" type="reset" value="Cancel" />
          <button id="showServerConfig" type="button" value="show">Show Server Config</button>
        </form>
          <div style="margin-top:20px;">
            <div class="rounded ReqDescBox"><div class="ReqDescHdr" >Instructions</div><div>
              Configure client parameters to override the default for this client only.<br/>
              Client side parameters are stored in cookies so this must be enabled in the browser or this feature will not work.
            </div></div>
          </div> <!-- floater -->
      <?php
      ?>
      </div>
    </td>
    </tr>
</table>
  <?php if (is_file("../footer.php")) { include "../footer.php"; } else {include "footer.php";} ?>
  </body>
</html>
