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

// PHP Catalog Web Interface: Catalog Browsing

if (!function_exists('pl')) {
  $GLOBALS['current']="browse.php";
  $target = '';
  import_request_variables('gP','var_');

  if (@$var_lang=='cs') { include "lang/browse-cs.php"; } else include "lang/browse-en.php";
  $pagename=@$lang['pagename'];
  include "auth.php"; global $auth; include "include.php";
  include 'include-menu.php';
} else {
  $target=' target="main"';
  echo '<p style="background-color: #DFDFFF; margin: 5px 5px 5px 5px; padding: 5px 5px 5px 5px; text-align: right;">'.$lang['split_title'].' '.$GLOBALS['catalog_version'].'</p>'."\n";
}

echo '</div></div></div><span id="split"><iframe id="split_iframe" src="browse-split.php'.pl('item='.$var_item).'" name="split"></iframe></span>';
echo '</body>'; ?>
