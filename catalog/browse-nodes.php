<?php
/*
 * Copyright (C) ExBin Project (http://exbin.org)
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

// PHP Catalog Web Interface: Browse Nodes

include 'include-head.php'; include 'db.php';
import_request_variables('gP','var_');
echo "<p>XBUP Catalog - Pre Release Version</p>\n";
echo '<div style="font-size: small"><div><div>'."\n";
if (@$var_lang=='cs') { include "lang/browse-cs.php"; } else include "lang/browse-en.php";

$itemlang = 1;
$level = 0;
$nodepath = '';

$parents = array(0);
$parent = 0;
$skips = array(0);
$skip = 0;
$counts = array(0);
$count = 1;
$captions = array();
while ($level>=0) {
  $item = DB_SimpleQuery('SELECT * FROM XBITEM item, XBXNAME item_name WHERE item.id = item_name.item_id AND item_name.lang_id = '.$itemlang." AND dtype='XBNode' AND owner_id ".($parent==0?'IS NULL':'= '.$parent).' ORDER BY item_name.text LIMIT '.$skip.',1');
  if (!$item) { echo 'ERROR!'; die(); };
  array_push($captions,$item['TEXT']);
  echo '<a href="browse-specs.php?node='.$item['ITEM_ID'].'" target="specs">'.implode($captions,'.')."</a><br/>\n";
  $subcount = DB_SimpleQuery('SELECT COUNT(*) FROM XBITEM item, XBXNAME item_name WHERE item.id = item_name.item_id AND item_name.lang_id = '.$itemlang." AND dtype='XBNode' AND owner_id = ".$item['ITEM_ID']);
  if ($subcount[0]>0) {
      if (!$item['OWNER_ID']) array_pop($captions);
      $level++;
      array_push($skips,$skip);
      $skip = 0;
      array_push($parents,$parent);
      $parent = $item['ITEM_ID'];
      array_push($counts,$count);
      $count = $subcount[0];
  } else {
      array_pop($captions);
      $skip++;
      while ($skip==$count) {
          $skip = array_pop($skips);
          $parent = array_pop($parents);
          $count = array_pop($counts);
          array_pop($captions);
          $skip++;
          $level--;
      }
  }
}
done(); ?>
