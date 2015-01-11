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

// PHP Catalog Web Interface: File Management

$GLOBALS['current']="search.php";
import_request_variables('gP','var_');

if (@$var_lang=='cs') { include "lang/search-cs.php"; } else include "lang/search-en.php";
$pagename=@$lang['pagename'];
$dateform='j.n.Y';
include "auth.php"; global $auth; include "include.php";
include 'include-menu.php';

echo '<form method="post" action="search.php'.pl('limi='.$var_limi).'" class="regForm">'."\n";
echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['search_byname']."</legend>\n";
echo '  <label>'.@$lang['name']."</label><br/>\n";
echo '  <input class="formText" type="text" name="name" value="'.$var_name."\"/><br/>\n";
echo '  <label>'.@$lang['desc']."</label><br/>\n";
echo '  <input class="formText" type="text" name="desc" value="'.$var_desc.'"/><br/>'."\n";
echo '  <input type="submit" name="search" value="'.@$lang['search'].'" class="formButton"/>'."\n";
echo "</fieldset>\n</form>\n";

if ($var_search) {
  $query = 'SELECT item.id, item.dtype, item_name.text AS iname, item_desc.text AS idesc FROM XBITEM item LEFT JOIN XBXNAME item_name ON item.id = item_name.item_id AND item_name.lang_id = 1';
  if ($var_name) $query .= " AND item_name.text LIKE '%".htmlentities($var_name)."%'";
  $query .= ' LEFT JOIN XBXDESC item_desc ON item.id = item_desc.item_id AND item_desc.lang_id = 1';
  if ($var_desc) $query .= " AND item_desc.text LIKE '%".htmlentities($var_desc)."%'";

  DB_Query($query);
  $count = 0;
  while ($row=DB_Row()) {
    if (($row['iname'])&&($row['idesc'])) {
      echo '<a href="item.php?item='.$row['id'].'">['.$row['id'].'] '.@$lang['type'.$row['dtype']].' ('.$row['dtype'].')</a> '.$row['iname'];
      if ($row['idesc']) echo ' - '.$row['idesc'];
      echo "<br/>\n";
      $count=1;
    }
  }
  if ($count==0) err_echo(@$lang['error_notfound']);
}
done(); ?>
