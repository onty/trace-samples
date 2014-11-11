<div>
  <div style="float:left">
    <a style="outline:none;margin-left:15px;" HREF="<?php echo "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']; ?>"><img src="trafficsim_banner.png" style="border:none;padding-top:5px;"/></a>
  </div>
<?php  if (isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false)) {  ?>
<div class="iehate">
<h3>WARNING! Internet Explorer detected!</h3>
Many elements of this site will not work properly.<br/>
Why not to switch to a standards-compliant browser, like 
<a href="http://www.mozilla.com/firefox/">Firefox</a>?
</div>
<?php }  ?>
  <?php
    echo "<div id=\"hdrHeading\">" . ((isset($heading) && strlen($heading))?$heading:"") . "</div>\n";
  ?>
</div>
<div style="clear:both;"></div>
<hr />
