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

// PHP Catalog Web Interface: Folder Management

$GLOBALS['current']="folder.php";
import_request_variables('gP','var_');
if (@$var_lang=='cs') { include "lang/folder-cs.php"; } else include "lang/folder-en.php";
$pagename=@$lang['pagename'];
$dateform='j.n.Y';
include "auth.php"; global $auth; include "include.php";  
echo '<div style="text-align: right;" align="right">';
if (isset($auth)) {
  echo @$lang['user'].': <a href="account.php'.$GLOBALS['pl'].'">'.$auth['login'].'</a> <a href="item.php'.pl('logout=1&amp;item='.$var_item).'">['.@$lang['logout'].']</a>';
} else echo '<a href="login.php'.$GLOBALS['pl'].'">'.@$lang['login'].'</a>';
echo '</div>'."\n";
echo '<div>'.@$lang['return_pre'].'<a href="data.php'.pl('folder='.($var_op=='add' ? $var_parent : $var_folder)).'">'.@$lang['return'].'</a>'.@$lang['return_post']."</div>\n";

if (@$var_addfolder) {
// Add new folder
  $var_parent+=0;
  $var_name = htmlspecialchars(str_replace('..','_',$var_name));
  if (@$var_parent) {
    $item = DB_SimpleQuery("SELECT * FROM XBXSTRI stri, XBITEM item WHERE stri.item_id = item.id AND item.dtype = 'XBNode' AND item.id=".$var_parent);
    if ($item) {
      $path = $item['NODEPATH'];
      if ($path[0] == '/') $path = substr($path, 1);
      mkdir($path.'/'.$var_name);
      chmod($path.'/'.$var_name, 0777);
      DB_Query("INSERT INTO XBITEM (owner_id, dtype, xbindex) SELECT {$var_parent},0,MAX(xbindex)+1 FROM item WHERE dtype = 'XBNode' AND owner_id = {$var_parent}");
      $var_folder=mysql_insert_id();
      DB_Query("INSERT INTO XBXSTRI (item_id, text, nodepath) VALUES ({$var_folder},'{$var_name}','".$item['NODEPATH'].'/'.$item['TEXT']."')"); // 
      echo '<div class="message">'.@$lang['addfolder_ok'].".</div>\n";
      $var_op='edit';
    } else err_echo(@$lang['error_nofolder']);
  } else err_echo(@$lang['error_noparent']);
} if (@$var_updatefolder) {
// Update Folder Values
  $var_folder+=0;
  $var_name = htmlspecialchars(str_replace('..','_',$var_name));
  $item = DB_SimpleQuery("SELECT * FROM XBITEM item WHERE item.dtype = 'XBNode' AND id=".$var_folder);
  if ($item) {
    // TODO: Subfolders nodepath update
    DB_Query("UPDATE XBXSTRI SET text={$var_name} WHERE item_id=".$var_folder);
    rename($item['NODEPATH'].'/'.$item['TEXT'], $item['NODEPATH'].'/'.$var_name);
    echo '<div class="message">'.@$lang['updatefolder_ok'].".</div>\n";
    $var_op='edit';
  } else err_echo(@$lang['error_nofolder']);
} if (@$var_deletefolder) {
// Delete Folder Values
  if ($var_really) {
    $var_folder+=0;
    $item = DB_SimpleQuery("SELECT * FROM XBITEM item, XBXSTRI stri WHERE item.dtype = 'XBNode' AND stri.item_id = item.id AND id=".$var_folder);
    if ($item) {
      rmdir($item['NODEPATH'].'/'.$item['TEXT']);
      // TODO: Delete also subfolders from database
      DB_Query('DELETE FROM XBXSTRI WHERE item_id='.$var_folder);
      DB_Query("DELETE FROM XBITEM item WHERE dtype = 'XBNode' AND id=".$var_folder);
      echo '<div class="message">'.@$lang['deletefolder_ok'].".</div>\n";
    } else echo '<div class="error">'.@$lang['deletefolder_failed'].".</div>\n";
  } else echo '<div class="error">'.@$lang['error_mustpermit'].".</div>\n";
}

if ($var_op=='add') {
// Add new folder dialog
  if (@$var_parent) {
    $item = DB_SimpleQuery("SELECT * FROM XBXSTRI stri, XBITEM item WHERE stri.item_id = item.id AND item.dtype = 'XBNode' AND item.owner_id=".$var_parent);
    if ($item) {
//      $prev = DB_SimpleQuery('SELECT MAX(xbindex) FROM item WHERE parent='.$var_parent);

      echo '<form method="post" action="folder.php'.pl('parent='.$var_parent).'" class="regForm">'."\n";
            echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['addfolder_legend']."</legend>\n";
      echo '  <div>'.@$lang['parentid'].': '.$var_parent."</div>\n";
      echo '  <label>'.@$lang['name']."</label><br/>\n";
      echo '  <input class="formText" type="text" name="name" value="'.html_entity_decode($var_name)."\"/><br/>\n";
      echo '  <input type="submit" name="addfolder" value="'.@$lang['addfolder'].'" class="formButton"/>'."\n";
      echo "</fieldset>\n</form>\n";
    } else err_echo(@$lang['error_nofolder']);
  } else err_echo(@$lang['error_noparent']);
} if ($var_op=='edit') {
  $var_folder+=0;
  $item = DB_SimpleQuery("SELECT * FROM XBXSTRI stri, XBITEM item WHERE stri.item_id = item.id AND item.dtype = 'XBNode' AND item.owner_id=".$var_folder);
  if ($item) {

    echo '<form method="post" action="folder.php'.pl('folder='.$var_folder).'" class="regForm">'."\n";
    echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['updatefolder_legend']."</legend>\n";
    echo '  <label>'.@$lang['parentid']."</label><br/>\n";
    echo '  <input class="formText" type="text" name="parent" readonly="readonly" value="'.$item['OWNER_ID']."\"/><br/>\n";
    echo '  <label>'.@$lang['name']."</label><br/>\n";
    echo '  <input class="formText" type="text" name="name" value="'.html_entity_decode($item['TEXT'])."\"/><br/>\n";
//    echo '  <label>'.@$lang['xblimit']."</label><br/>\n";
//    echo '  <input class="formText" type="text" name="xblimit" value="'.$item['xbindex'].'"/><br/>'."\n";
    echo '  <input type="submit" name="updatefolder" value="'.@$lang['updatefolder'].'" class="formButton"/>'."\n";
    echo "</fieldset>\n</form>\n";

    echo '<form method="post" action="folder.php'.pl('folder='.$var_folder).'" class="regForm">'."\n";
    echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['deletefolder_legend']."</legend>\n";
    echo '  <input class="formText" type="checkbox" name="really"/>'.@$lang['deletefolder_really']."<br/>\n";
    echo '  <input type="submit" name="deletefolder" value="'.@$lang['deletefolder'].'" class="formButton"/>'."\n";
    echo "</fieldset>\n</form>\n";
  } else err_echo(@$lang['error_nofolder']);
}
done(); ?>
