<?php
error_reporting (E_ALL ^ E_NOTICE);
include 'definitions.php';
read_config();
date_default_timezone_set('Europe/Dublin');
$heading="Session Viewer";

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
    <script type="text/javascript" src="<?php echo autoVersion("trafficsim.js"); ?>"></script>
    <script type="text/javascript">
      var msisdn_filter="all";
      $(document).ready(function(){

        $("#filter_area").html("<select name='msisdn_filter' id='msisdn_filter'><option value='all'>All MSISDN's</option></select>");
        populate_msisdn_filter_list();

        $("#msisdn_filter").change(function () {
          msisdn_filter=$("#msisdn_filter option:selected").val();
          $("#sessList div").each(function () {
            if (msisdn_filter == 'all' || $(this).attr('msisdn')==msisdn_filter) {
              $(this).removeClass("filtered");
            } else {
              $(this).addClass("filtered");
            }
          });              
          if ($(".selected").parent().hasClass('filtered')) {
            $(".selected").removeClass('selected');
            $("div#result").empty();
          }
        }).change();
        add_sessions();
      });
      function add_sessions() {
        $.ajax({url: "get_session_list.php", async:true, data: 'session='+$("#sessList div:first").attr('session'),
          success: function(data) {
            $("#sessList").prepend(data);
            populate_msisdn_filter_list();
            update_filtered();
            setTimeout("add_sessions()",5000);
          }
        });
      }
      function populate_msisdn_filter_list() {
        $("#sessList div.prefiltered").each(function () {
          
          var msisdn=$(this).attr('msisdn');
          if ($("#msisdn_filter option[value='"+msisdn+"']").length == 0) {
            $('#msisdn_filter').append(
              $('<option></option>').val(msisdn).html(msisdn)
            );
          }
        }); 
      }
      function update_filtered() {
              $("#sessList div.prefiltered").each(function () {
                if (msisdn_filter == 'all' || $(this).attr('msisdn')==msisdn_filter) {
                  $(this).removeClass("prefiltered");
                  $(this).removeClass("filtered");
                } else {
                  $(this).removeClass("prefiltered");
                  $(this).addClass("filtered");
                }
              });
          if ($(".selected").parent().hasClass('filtered')) {
            $(".selected").removeClass('selected');
            $("div#result").empty();
          }
      }
    </script>

    <title>Session Viewer</title>
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
      <table class="vmtbl">
        <tr><th>Session <span id="filter_area"></span></th><th style="width:100%">Log</th></tr>
        <tr><td><div id="sessList">
          <?php
#if (!isset($config["sim_ip"]) || strlen($config["sim_ip"]) == 0 ) {
#  include "get_session_list.php";
#}
          ?>
        </div></td><td>
          <div id="ongoing_call"><img src="ongoing_call.gif" alt="" /></div>
          <div id="result"></div>
        </td></tr>
      </table>
    </td>
    </tr>
</table>
  <?php if (is_file("../footer.php")) { include "../footer.php"; } else {include "footer.php";} ?>
  </body>
</html>
