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

// PHP Catalog Web Interface: File Management

$GLOBALS['current']="data.php";
extract($_GET, EXTR_PREFIX_ALL, 'var'); extract($_POST, EXTR_PREFIX_ALL, 'var');

if (@$var_lang=='cs') { include "lang/data-cs.php"; } else include "lang/data-en.php";
$pagename=@$lang['pagename'];
$dateform='j.n.Y';
include "auth.php"; global $auth; include "include.php";
include 'include-menu.php';

// Acquiring item
$var_folder+=0;
$folder='';
if ($var_folder==0) {
  $folder = DB_SimpleQuery("SELECT *, item.owner_id AS owner_id, item.ID AS ID FROM XBITEM item LEFT JOIN XBXSTRI stri ON stri.item_id = item.id LEFT JOIN XBITEMINFO item_info ON item_info.owner_id = item.id WHERE item.dtype = 'XBNode' AND item.owner_id IS NULL");
  if (!$folder) err_echo(@$lang['error_missingroot']);
} else {
  $folder = DB_SimpleQuery("SELECT *, item.owner_id AS owner_id, item.ID AS ID FROM XBITEM item LEFT JOIN XBXSTRI stri ON stri.item_id = item.id LEFT JOIN XBITEMINFO item_info ON item_info.owner_id = item.id WHERE item.dtype = 'XBNode' AND item.id=".$var_folder);
  if (!$folder) err_echo(@$lang['error_nofolder']);
}

if (@$folder['ID']>0) {
  // Print list of subfolders
  echo '<div id="sidebar">'."\n";
  echo '  <fieldset><legend>'.@$lang['folder_sub']."</legend>\n";
/*  if ($item['dtype']>0) {
    $node = DB_SimpleQuery('SELECT * FROM item WHERE id = '.$item['parent']);
    echo '<a href="'.pl('item='.$node['id']).'">[.]</a><br/>'."\n";
  } else $node = $item; // $node &= $item; */
  // Link to parent node
  if ($folder['owner_id']>0) echo '<a href="'.pl('folder='.$folder['owner_id']).'">[..]</a><br/>'."\n";
  // Print nodes
  DB_Query("SELECT *, item.ID AS ID FROM XBITEM item LEFT JOIN XBXSTRI stri ON stri.item_id = item.id LEFT JOIN XBITEMINFO item_info ON item_info.owner_id = item.id WHERE item.dtype = 'XBNode' AND item.owner_id = ".$folder['ID']);
  $lastid = 0;
  while ($row=DB_Row()) if ($row['ID']!=$lastid) {
    if ($row['ID']==$var_folder) echo '<span style="background-color: yellow;">';
    echo '<a href="'.pl('folder='.$row['ID']).'">';
    echo '['.@$lang['folder'].']';
    echo '</a> '.$row['TEXT'];
    if ($row['ID']==$var_folder) echo '</span>';
    echo "<br/>\n";
    $lastid = $row['ID'];
  }
  echo "  </fieldset>\n";
  if (@$auth) {
    echo '<p><a href="folder.php'.pl('op=add&amp;parent='.$folder['ID']).'">'.@$lang['add_folder']."</a><br/>\n"; 
  }

  echo '</div>'."\n";

  // Print information about current item
  // Print basic informations
  echo '<div id="mainbar">'."\n";
  echo '  <fieldset><legend>'.@$lang['folder_info']."</legend>\n";
  echo '    '.@$lang['folder_name'].': '.@$folder['TEXT']."<br/>\n";
/*  echo '    '.@$lang['item_basic_index'].': '.$item['id']."<br/>\n";
  echo '    '.@$lang['item_basic_parentid'].': '.$item['parent']."<br/>\n";
  echo '    '.@$lang['item_basic_xbindex'].': '.$item['xbindex']."<br/>\n";
  echo '    '.@$lang['item_basic_xblimit'].': '.$item['xblimit']."<br/>\n"; */
  if ((@$auth)&&($folder['OWNER_ID']>0)) {
    echo '    <a href="folder.php'.pl('op=edit&amp;folder='.$folder['ID']).'">'.@$lang['edit_item']."</a>\n";
  }
  echo "  </fieldset>\n";

  // Print files
  DB_Query('SELECT * FROM XBXFILE item_file WHERE node_id = '.$folder['ID']);
  echo '  <fieldset><legend>'.@$lang['files']."</legend>\n";
  if (DB_NumRows()==0) echo '    '.@$lang['files_notfound']."\n"; 
  while ($row = DB_Row()) {
    echo '    '.@$lang['item_file_list'].' ['.$row['ID'].'] : '.$row['FILENAME'];
    if (@$auth) echo ' <a href="file.php'.pl('op=edit&amp;file='.$row['ID'].'&amp;folder='.$folder['ID']).'">'.@$lang['edit_file'].'</a>';
    $path = 'root';
    if ($folder['NODEPATH']) {
      if ($folder['owner_id']) {
        $path .= $folder['NODEPATH'];
        if ($folder['NODEPATH'] != '/') $path .= '/';
        $path .= $folder['TEXT'];
      }
    }
    echo ' <a href="'.$path.'/'.$row['FILENAME'].'">'.@$lang['view_file'].'</a>';
    echo "<br/>\n";
  }
  if (@$auth) {
    echo '    <a href="file.php'.pl('op=add&amp;folder='.$folder['ID']).'">'.@$lang['add_file']."</a>\n";
  }
  echo "  </fieldset>\n";

  // Print plugins
  DB_Query('SELECT * FROM XBXPLUGIN plug WHERE owner_id = '.$folder['ID']);
  echo '  <fieldset><legend>'.@$lang['plugins']."</legend>\n";
  if (DB_NumRows()==0) echo '    '.@$lang['plugins_notfound']."\n"; 
  while ($row = DB_Row()) {
    echo '    '.@$lang['item_plugin_list'].' ['.$row['ID'].'] : '.$row['PLUGININDEX'];
    if (@$auth) echo ' <a href="plugin.php'.pl('op=edit&amp;plug='.$row['ID'].'&amp;folder='.$folder['ID']).'">'.@$lang['edit_plugin'].'</a>';
//    echo ' <a href="plugin.php'.pl('op=view&amp;plug='.$row['id'].'&amp;folder='.$folder['id']).'">'.@$lang['view_plugin'].'</a>';
/*    $path = $folder['path'].'/'.$folder['filename'];
    if ($path[0] == '/') $path = substr($path, 1);
    echo ' <a href="'.$path.'/'.$row['filename'].'">'.@$lang['view_plugin'].'</a>'; */
    echo "<br/>\n";
  }
  if (@$auth) {
    echo '    <a href="plugin.php'.pl('op=add&amp;folder='.$folder['ID']).'">'.@$lang['add_plugin']."</a>\n";
  }
  echo "  </fieldset>\n";

  echo '</div>'."\n"; }
done(); ?>
