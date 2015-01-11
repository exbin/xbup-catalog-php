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

// PHP Catalog Web Interface: Page Functions Library

include 'include-head.php'; ?>
<div id="pagehead"><div id="pageheadtext">XBUP - Experimetal Catalog - <?php echo $pagename;
if (!@$GLOBALS['current']) $GLOBALS['current']=@$GLOBALS['menuitem']; ?>
</div>
<?php if ($var_lang=='cs') {
  echo '<div align="right"><a href="'.$GLOBALS['current'].'"><img src="imgs/gb.gif" style="margin: 10px 5px 10px 5px;" border="0" alt="[EN]" title="English Version"/></a></div>'; 
} else echo'<div align="right"><a href="'.$GLOBALS['current'].'?lang=cs"><img src="imgs/cz.gif" style="margin: 10px 5px 10px 5px;" border="0" alt="[CZ]" title="Česká verze"/></a></div>'; ?>

<div id="main"><div id="maintext">
<?php
function err_echo($text) {
  if (!$text) $text = 'Unknown error message';
  echo '<div class="error">'.$text."!</div>\n";
}

function pl($text) {
  if ($text) {
    if (@$GLOBALS['pl']) {
      return $GLOBALS['pl'].'&amp;'.$text;
    } else return '?'.$text; 
  } else return $GLOBALS['pl'];
} ?>
