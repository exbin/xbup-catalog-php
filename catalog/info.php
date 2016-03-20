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

// PHP Catalog Web Interface: Info Extension

$GLOBALS['current']="info.php";
import_request_variables('gP','var_');
if (@$var_lang=='cs') { include "lang/info-cs.php"; } else include "lang/info-en.php";
// $GLOBALS['stylesheets']='<link rel="stylesheet" href="styles/news.css" type="text/css" media="screen,projection" />'."\n";
$pagename=@$lang['pagename'];
$dateform='j.n.Y';
include "auth.php"; global $auth; include "include.php";
echo '<div style="text-align: right;" align="right">';
if (isset($auth)) {
  echo @$lang['user'].': <a href="account.php'.$GLOBALS['pl'].'">'.$auth['login'].'</a> <a href="item.php'.pl('logout=1&amp;item='.$var_item).'">['.@$lang['logout'].']</a>';
} else echo '<a href="login.php'.$GLOBALS['pl'].'">'.@$lang['login'].'</a>';
echo '</div>'."\n";
echo '<div>'.@$lang['return_pre'].'<a href="item.php'.pl('item='.$var_item).'">'.@$lang['return'].'</a>'.@$lang['return_post']."</div>\n";

if (@$var_addinfo) {
// Add new info
  $var_item+=0;
  $created = date($var_created);
  if ($created == '') $created = 'NULL';
  $updated = date($var_updated);
  if ($updated == '') $updated = 'NULL';
  $filename = htmlspecialchars($var_filename);
  $item = DB_SimpleQuery('SELECT * FROM XBITEM item WHERE id='.$var_item);
  if ($item) {
    $parent = DB_SimpleQuery("SELECT * FROM XBITEMINFO item_info, XBITEM item WHERE item_info.item_id = item.id AND item.dtype = 'XBNode' AND item.id=".$item['parent']);
    if ($parent) {
      $path = $parent['path'];
      if ($path[0] == '/') $path = substr($path, 1);
      mkdir($path.'/'.$filename);
      chmod($path.'/'.$filename, 0777);
    }
    DB_Query("INSERT INTO XBITEMINFO (item_id, created, updated, filename, path) VALUES ({$var_item},{$created},{$updated},'{$filename}','".$parent['path'].'/'.$parent['name']."')");
    echo '<div class="message">'.@$lang['addinfo_ok'].".</div>\n";
    $var_info=mysql_insert_id();
    $var_op='edit';
  } else err_echo(@$lang['error_noitem']);
} if (@$var_updateinfo) {
// Update info Values
  $var_info+=0;
  $var_item+=0;
  $info = DB_SimpleQuery('SELECT * FROM XBITEMINFO WHERE id='.$var_info);
  if ($info) {
    $parent = DB_SimpleQuery("SELECT * FROM XBITEMINFO item_info, XBITEM item WHERE item_info.item_id = item.id AND item.dtype = 'XBNode' AND item.id=".$var_item);
    if ($parent) {
      $path = $info['path'];
      if ($path[0] == '/') $path = substr($path, 1);
      $npath = $parent['path'];
      if ($npath[0] == '/') $npath = substr($npath, 1);
        DB_Query("UPDATE XBITEMINFO SET item_id={$var_item}, filename='".htmlspecialchars($var_filename)."' WHERE id = ".$var_info);
        $path = $parent['path'].'/'.$parent['filename'];
        $idx = strlen($path);
        DB_Query("UPDATE item_info SET path = CONCAT('".$parent['path'].'/'.htmlspecialchars($var_filename)."',SUBSTRING(path,".($idx+1).",LENGTH(path)-".$idx.")) WHERE path LIKE '".$path."%'");
        echo '<div class="message">'.@$lang['updateinfo_ok'].".</div>\n";
    }
    $var_op='edit';
  } else err_echo(@$lang['error_noinfo']);
} if (@$var_deleteinfo) {
// Delete info Values
  if ($var_really) {
    $var_info+=0;
    DB_Query('DELETE FROM XBITEMINFO WHERE item_id='.$var_info);
    echo '<div class="message">'.@$lang['deleteinfo_ok'].".</div>\n";
  } else echo '<div class="error">'.@$lang['error_mustpermit'].".</div>\n";
}

if ($var_op=='add') {
// Add new info dialog
  if (@$var_item) {
    $var_item+=0;
    $item = DB_SimpleQuery('SELECT * FROM XBITEM item WHERE id='.$var_item);
    if ($item) {

      echo '<form method="post" action="info.php'.pl('item='.$var_item).'" class="regForm">'."\n";
            echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['addinfo_legend']."</legend>\n";
      echo '  <label>'.@$lang['owner']."</label><br/>\n";
      echo '  <input class="formText" type="text" name="item" readonly="readonly" value="'.$var_item."\"/><br/>\n";
      echo '  <label>'.@$lang['created']."</label><br/>\n";
      echo '  <input class="formText" type="text" name="created"/><br/>'."\n";
      echo '  <label>'.@$lang['updated']."</label><br/>\n";
      echo '  <input class="formText" type="text" name="updated"/><br/>'."\n";
      echo '  <input type="submit" name="addinfo" value="'.@$lang['addinfo'].'" class="formButton"/>'."\n";
      echo "</fieldset>\n</form>\n";
    } else err_echo(@$lang['error_noinfo']);
  } else err_echo(@$lang['error_noitem']);
} if ($var_op=='edit') {
  $var_info+=0;
  $item = DB_SimpleQuery('SELECT * FROM XBITEMINFO WHERE item_id='.$var_info);
  if ($item) {

    echo '<form method="post" action="info.php'.pl('info='.$var_info).'" class="regForm">'."\n";
    echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['updateinfo_legend']."</legend>\n";
    echo '  <label>'.@$lang['owner']."</label><br/>\n";
    echo '  <input class="formText" type="text" name="item" readonly="readonly" value="'.$item['OWNER_ID']."\"/><br/>\n";
    echo '  <label>'.@$lang['created']."</label><br/>\n";
    echo '  <input class="formText" type="text" name="created" value="'.$item['CREATIONDATE']."\"/><br/>\n";
    echo '  <label>'.@$lang['updated']."</label><br/>\n";
    echo '  <input class="formText" type="text" name="updated" value="'.$item['CREATEDBYUSER'].'"/><br/>'."\n";
    echo '  <input type="submit" name="updateinfo" value="'.@$lang['updateinfo'].'" class="formButton"/>'."\n";
    echo "</fieldset>\n</form>\n";

    echo '<form method="post" action="info.php'.pl('info='.$var_info).'" class="regForm">'."\n";
    echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['deleteinfo_legend']."</legend>\n";
    echo '  <input class="formText" type="checkbox" name="really"/>'.@$lang['deleteinfo_really']."<br/>\n";
    echo '  <input type="submit" name="deleteinfo" value="'.@$lang['deleteinfo'].'" class="formButton"/>'."\n";
    echo "</fieldset>\n</form>\n";
  } else err_echo(@$lang['error_noinfo']);
}
done(); ?>
