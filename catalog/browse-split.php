<!DOCTYPE html public "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">
<?php
/*
 * Copyright (C) XBUP Project (http://xbup.org)
 *
 * This application or library is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the License,
 * or (at your option) any later version.
 *
 * This application or library is distributed in the hope that it will be
 * useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along this application.  If not, see <http://www.gnu.org/licenses/>.
 */

// PHP Catalog Web Interface: Browse as Split Pages

global $lang;
if (@$var_lang=='cs') { include "lang/browse-cs.php"; } else include "lang/browse-en.php"; 
import_request_variables('gP','var_');
function pl($text) {
  if ($text) {
    if (@$GLOBALS['pl']) {
      return $GLOBALS['pl'].'&amp;'.$text;
    } else return '?'.$text; 
  } else return @$GLOBALS['pl'];
};
?><html><head>
<title><?php echo $lang['split_title']; ?></title>
</head>
<frameset cols="20%,80%" title="">
<frameset rows="30%,70%" title="">
<frame src="browse-nodes.php<?php echo pl(''); ?>" name="nodes" title="X Y<?php $lang['split_nodes']; ?>">
<frame src="browse-specs.php<?php echo pl(''); ?>" name="specs" title="Y X<?php $lang['split_specs']; ?>">
</frameset>
<frame src="browse-item.php<?php echo pl('item='.$var_item); ?>" name="main" title="Current item page" scrolling="yes">
<noframes>
<h2>Frame Alert</h2>
<p>This document is designed to be viewed using the frames feature. If you see this message, you are using a non-frame-capable web client.
<br>Link to <a href="browse-item.php<?php echo pl('item='.$var_item); ?>">Non-frame version.</a></p>
</noframes>
</frameset>
</html>
