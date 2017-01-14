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

// PHP Catalog Web Interface: Browse Specifications

include 'include-head.php'; include 'db.php';
extract($_GET, EXTR_PREFIX_ALL, 'var'); extract($_POST, EXTR_PREFIX_ALL, 'var');
echo '<div style="font-size: small"><div><div>'."\n";
if (@$var_lang=='cs') { include "lang/browse-cs.php"; } else include "lang/browse-en.php";

$itemlang = 1;
$var_node+=0;

if ($var_node) echo '<p><a href="browse-item.php?item='.$var_node.'" target="main">Node: '.$var_node."</a></p>\n";

$query='SELECT * FROM XBITEM item, XBXNAME item_name WHERE item.id = item_name.item_id AND item_name.lang_id = '.$itemlang." AND dtype LIKE '%Spec'";
if (@$var_node) $query .= ' AND item.owner_id = '.$var_node;
$query .= ' ORDER BY item_name.text';
DB_Query($query);
while ($item=DB_Row()) {
  echo '<a href="browse-item.php?item='.$item['ITEM_ID'].'" target="main">'.$item['TEXT']."</a><br/>\n";
}
done(); ?>
