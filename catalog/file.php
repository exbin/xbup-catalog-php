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

// PHP Catalog Web Interface: Files Management

$GLOBALS['current']="file.php";
extract($_GET, EXTR_PREFIX_ALL, 'var'); extract($_POST, EXTR_PREFIX_ALL, 'var');
if (@$var_lang=='cs') { include "lang/file-cs.php"; } else include "lang/file-en.php";
$pagename=@$lang['pagename'];
$dateform='j.n.Y';
include "auth.php"; global $auth; include "include.php";
echo '<div style="text-align: right;" align="right">';
if (isset($auth)) {
  echo @$lang['user'].': <a href="account.php'.$GLOBALS['pl'].'">'.$auth['login'].'</a> <a href="item.php'.pl('logout=1&amp;item='.$var_item).'">['.@$lang['logout'].']</a>';
} else echo '<a href="login.php'.$GLOBALS['pl'].'">'.@$lang['login'].'</a>';
echo '</div>'."\n";
echo '<div>'.@$lang['return_pre'].'<a href="data.php'.pl('folder='.$var_folder).'">'.@$lang['return'].'</a>'.@$lang['return_post']."</div>\n";

if (@$var_addfile) {
// Add new folder
  $var_folder+=0;
  $var_name = htmlspecialchars(str_replace('..','_',$_FILES['userfile']['name']));
  if (@$var_folder) {
    $item = DB_SimpleQuery("SELECT * FROM XBITEM item, XBXSTRI stri WHERE stri.item_id = item.id AND item.dtype = 'XBNode' AND item.id=".$var_folder);
    if ($item) {
      $path = $item['path'].'/'.$item['filename'];
      if ($path[0] == '/') $path = substr($path, 1);
      if (is_uploaded_file($_FILES['userfile']['tmp_name'])) {
//        echo 'Filename '.$_FILES['userfile']['name'].': '.$_FILES['userfile']['tmp_name'].': '.$path.'/'.$var_name;
        if (move_uploaded_file($_FILES['userfile']['tmp_name'],$path.'/'.$var_name)) {
//      mkdir($item['path'].'/'.$item['name'].'/'.$var_name);
          chmod($path.'/'.$var_name, 0666);
          DB_Query("INSERT INTO item_file (item_id, filename) VALUES ({$var_folder},'{$var_name}')");
          $var_file=mysql_insert_id();
          echo '<div class="message">'.@$lang['addfile_ok'].".</div>\n";
          $var_op='edit';
	} else err_echo(@$lang['error_movefailed']);
      } else err_echo(@$lang['error_noupload']);
    } else err_echo(@$lang['error_nofile']);
  } else err_echo(@$lang['error_noparent']);
} if (@$var_updatefile) {
// Update & Replace File
  $var_file+=0;
  $var_parent+=0;
  $var_name = htmlspecialchars(str_replace('..','_',$var_name));
  $item = DB_SimpleQuery('SELECT *, item_file.filename AS name, item_info.filename AS foldername FROM item_info, item_file WHERE item_info.owner = item_file.item_id AND item_file.id='.$var_file);
  if ($item) {
    $path = $item['path'].'/'.$item['foldername'];
    if ($path[0] == '/') $path = substr($path, 1);
    $parent = DB_SimpleQuery("SELECT * FROM item, item_info WHERE item_info.owner = item.id AND item.dtype = 'XBNode' AND item.id=".$var_parent);
    $npath = $parent['path'].'/'.$parent['filename'];
    if ($npath[0] == '/') $npath = substr($npath, 1);
    if (rename($path.'/'.$item['name'],$npath.'/'.$var_name)) {
      DB_Query("UPDATE item_file SET item_id=".$var_parent.", filename='".$var_name."' WHERE id = ".$var_file);
      echo '<div class="message">'.@$lang['updatefile_ok'].".</div>\n";
    } else err_echo(@$lang['error_renamefailed']);
    $var_op='edit';
  } else err_echo(@$lang['error_nofile']);
} if (@$var_deletefile) {
// Delete Folder Values
  if ($var_really) {
    $var_file+=0;
    $item = DB_SimpleQuery('SELECT item_file.*, item_info.path, item_info.filename AS fname FROM item_file, item_info WHERE item_info.owner = item_file.item_id AND item_file.id='.$var_file);
    if ($item) {
      $path = $item['path'].'/'.$item['fname'].'/'.$item['filename'];
      if ($path[0] == '/') $path = substr($path, 1);
      unlink($path);
//      rmdir($item['path'].'/'.$item['name']);
      DB_Query('DELETE FROM item_file WHERE id='.$var_file);
      echo '<div class="message">'.@$lang['deletefile_ok'].".</div>\n";
    } else echo '<div class="error">'.@$lang['deletefile_failed'].".</div>\n";
  } else echo '<div class="error">'.@$lang['error_mustpermit'].".</div>\n";
}

if ($var_op=='add') {
// Add new file dialog
  if (@$var_folder) {
    $item = DB_SimpleQuery("SELECT * FROM XBITEM item WHERE dtype = 'XBNode' AND id=".$var_folder);
    if ($item) {
//      $prev = DB_SimpleQuery('SELECT MAX(xbindex) FROM item WHERE parent='.$var_parent);

      echo '<form method="post" action="file.php'.pl('folder='.$var_folder).'" class="regForm" enctype="multipart/form-data">'."\n";
            echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['addfile_legend']."</legend>\n";
      echo '  <div>'.@$lang['parentid'].': '.$var_folder."</div>\n";
      echo '  <label>'.@$lang['file']."</label><br/>\n";
      echo '  <input class="formText" type="file" name="userfile"/><br/>'."\n";
      echo '  <input type="submit" name="addfile" value="'.@$lang['addfile'].'" class="formButton"/>'."\n";
      echo "</fieldset>\n</form>\n";
    } else err_echo(@$lang['error_nofile']);
  } else err_echo(@$lang['error_noparent']);
} if ($var_op=='edit') {
  $var_file+=0;
  $item = DB_SimpleQuery('SELECT *, item_file.filename AS fname FROM XBXFILE item_file, XBXSTRI stri WHERE stri.item_id = item_file.node_id AND item_file.id='.$var_file);
  if ($item) {

    echo '<form method="post" action="file.php'.pl('folder='.$var_folder.'&amp;file='.$var_file).'" class="regForm" enctype="multipart/form-data">'."\n";
    echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['updatefile_legend']."</legend>\n";
    echo '  <label>'.@$lang['parentid']."</label><br/>\n";
    echo '  <input class="formText" type="text" name="parent" readonly="readonly" value="'.$item['item_id']."\"/><br/>\n";
    echo '  <label>'.@$lang['name']."</label><br/>\n";
    echo '  <input class="formText" type="text" name="name" value="'.$item['fname']."\"/><br/>\n";
    echo '  <label>'.@$lang['file']."</label><br/>\n";
    echo '  <input class="formText" type="file" name="userfile" disabled="disabled"/><br/>'."\n";
//    echo '  <label>'.@$lang['xblimit']."</label><br/>\n";
//    echo '  <input class="formText" type="text" name="xblimit" value="'.$item['xbindex'].'"/><br/>'."\n";
    echo '  <input type="submit" name="updatefile" value="'.@$lang['updatefile'].'" class="formButton"/>'."\n";
    echo "</fieldset>\n</form>\n";

    echo '<form method="post" action="file.php'.pl('file='.$var_file.'&amp;folder='.$item['item_id']).'" class="regForm">'."\n";
    echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['deletefile_legend']."</legend>\n";
    echo '  <input class="formText" type="checkbox" name="really"/>'.@$lang['deletefile_really']."<br/>\n";
    echo '  <input type="submit" name="deletefile" value="'.@$lang['deletefile'].'" class="formButton"/>'."\n";
    echo "</fieldset>\n</form>\n";
  } else err_echo(@$lang['error_nofile']);
}
done(); ?>
