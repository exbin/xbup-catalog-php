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

// PHP Catalog Web Interface: String Id Extension

$GLOBALS['current']="stri.php";
extract($_GET, EXTR_PREFIX_ALL, 'var'); extract($_POST, EXTR_PREFIX_ALL, 'var');
if (@$var_lang=='cs') { include "lang/stri-cs.php"; } else include "lang/stri-en.php";
// $GLOBALS['stylesheets']='<link rel="stylesheet" href="styles/news.css" type="text/css" media="screen,projection" />'."\n";
$pagename=@$lang['pagename'];
$dateform='j.n.Y';
include "auth.php"; global $auth; include "include.php";  
echo '<div style="text-align: right;" align="right">';
if (isset($auth)) {
  echo @$lang['user'].': <a href="account.php'.$GLOBALS['pl'].'">'.$auth['login'].'</a> <a href="item.php'.pl('logout=1&amp;item='.$var_item).'">['.@$lang['logout'].']</a>';
} else echo '<a href="login.php'.$GLOBALS['pl'].'">'.@$lang['login'].'</a>';
echo '</div>'."\n";
echo '<div>'.@$lang['return_pre'].'<a href="item.php'.pl('item=').$var_item.'">'.@$lang['return'].'</a>'.@$lang['return_post']."</div>\n";

if (@$var_addstri) {
// Add new stri
  $var_item+=0;
  if ($var_item) {
    $item = DB_SimpleQuery('SELECT * FROM XBXSTRI WHERE item_id='.$var_item);
    if (!$item) {
      $path = $item['NODEPATH'].'/'.$item['TEXT'];
      DB_Query("INSERT INTO XBXSTRI (item_id, text, nodepath) VALUES ({$var_item},'".htmlspecialchars($var_text)."','".$path."')");
      echo '<div class="message">'.@$lang['addstri_ok'].".</div>\n";
      $var_op='add';
    } else err_echo(@$lang['error_alreadypresent']);
  } else err_echo(@$lang['error_noitem']);

} if (@$var_updatestri) {
// Update stri Values
  $var_item+=0;
  $item = DB_SimpleQuery('SELECT * FROM XBXSTRI WHERE item_id='.$var_item);
  if ($item) {
    $path = 'root';
    if ($item['NODEPATH']) {
      if ($item['NODEPATH'] != '/') $path .= $item['NODEPATH'];
      rename($path.'/'.$item['TEXT'],$path.'/'.htmlspecialchars($var_text));
    }

    DB_Query("UPDATE XBXSTRI SET text='".htmlspecialchars($var_text)."' WHERE item_id={$var_item}");
    echo '<div class="message">'.@$lang['updatestri_ok'].".</div>\n";
    $var_op='edit';
  } else err_echo(@$lang['error_noitem']);
  
} if (@$var_deletestri) {
// Delete Node Values
  if ($var_really) {
    $var_node+=0;
    DB_Query('DELETE FROM XBXSTRI WHERE item_id='.$var_item);
    echo '<div class="message">'.@$lang['deletestri_ok'].".</div>\n";
  } else echo '<div class="error">'.@$lang['error_mustpermit'].".</div>\n";
}

if ($var_op=='add') {
// Add new stri dialog
  $var_item+=0;
  $item = DB_SimpleQuery('SELECT * FROM XBITEM item WHERE id='.$var_item);
  if ($item) {
    echo '<form method="post" action="stri.php'.pl('item='.$var_item).'">'."\n";
    echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['addstri_legend']."</legend>\n";
    echo '  <label>'.@$lang['text']."</label><br/>\n";
    echo '  <input class="formText" type="text" name="text" ><br/>'."\n";
    echo '  <input type="submit" name="addstri" value="'.@$lang['addstri'].'" class="formButton"/>'."\n";
    echo "</fieldset>\n</form>\n";
  } else err_echo(@$lang['error_noitem']);

} if ($var_op=='edit') {
  // Edit stri dialog
  $var_item+=0;
  $item = DB_SimpleQuery('SELECT * FROM XBXSTRI WHERE item_id='.$var_item);
  if ($item) {

    echo '<form method="post" action="stri.php'.pl('item='.$var_item).'">'."\n";
    echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['updatestri_legend']."</legend>\n";
    echo '  <label>'.@$lang['text']."</label><br/>\n";
    echo '  <input class="formText" type="text" name="text" value="'.$item['TEXT']."\"/><br/>\n";
    echo '  <input type="submit" name="updatestri" value="'.@$lang['updatestri'].'" class="formButton"/>'."\n";
    echo "</fieldset>\n</form>\n";

    echo '<form method="post" action="stri.php'.pl('item='.$var_item).'" class="regForm">'."\n";
    echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['deletestri_legend']."</legend>\n";
    echo '  <input class="formText" type="checkbox" name="really"/>'.@$lang['deletestri_really']."<br/>\n";
    echo '  <input type="submit" name="deletestri" value="'.@$lang['deletestri'].'" class="formButton"/>'."\n";
    echo "</fieldset>\n</form>\n";
  } else err_echo(@$lang['error_noitem']);
}
done(); ?>
