<?php
$activeMenu="_unset";
if (isset($requestPath)) {
  $activeMenu=$requestPath;
  if (preg_match('/\//', $activeMenu)) {
    $activeMenu=preg_replace('/\/.*/','',$activeMenu);
  } else {
    $activeMenu="_unset";
  }
}
?>
<script language="JavaScript" type="text/JavaScript">
menu_status = new Array();

function showHide(menuid,submenuid){
    if (document.getElementById) {
    var submenu = document.getElementById(submenuid);
    var menu = document.getElementById(menuid);
    
        if(submenu.className != 'show') {
           submenu.className = 'show';
           menu.className = 'menu_expanded';
           menu_status[submenuid] = 'show';
        }else{
           submenu.className = 'hide';
           menu.className = 'menu_collapsed';
           menu_status[submenuid] = 'hide';
        }
    }
}

</script>

<div style="margin-top:10px;"></div>
<div class="navWrapper">
<div class="navheader">CIP/IP Simulator</div>
<div id="myMenu">
  <a href="configure.php">&raquo;Configure</a> <span>|</span>
  <a href="session_viewer.php">&raquo;Session Viewer</a> <span>|</span>
<?php
  if (is_dir("requests")) {
    foreach (glob("requests/*") as $path) {
      $dir=preg_replace('/.*\//','',$path);
      if (is_dir($path)) {
        echo "<a id=\"menu$dir\" href=\"#\" class=\"".(($activeMenu === $dir)?"menu_expanded":"menu_collapsed")."\" onclick=\"showHide('menu$dir','menu".$dir."items')\">$dir</a> <span>|</span>\n";
        echo "<div id=\"menu".$dir."items\" class=\"".(($activeMenu === $dir)?"show":"hide")."\">\n";
        foreach (glob("requests/$dir/*") as $subpath) {
          $subRequest=preg_replace('/.*\//','',$subpath);
          echo "<a href=\"index.php?request=$dir/$subRequest\" class=\"submenu\">-$subRequest</a> <span>|</span>\n";
        }
        echo "</div>\n";
      } else {
        echo "<a href=\"index.php?request=",rawurlencode($dir),"\">$dir</a> <span>|</span>\n";
        $dir;
      }
    }
  }
?>
  <a class="rounded-bottom" href="about.php">&raquo;About</a> <span>|</span>
</div>
</div>

