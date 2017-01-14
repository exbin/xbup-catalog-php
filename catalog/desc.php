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

// PHP Catalog Web Interface: Description Extension

$GLOBALS['current']="desc.php";
extract($_GET, EXTR_PREFIX_ALL, 'var'); extract($_POST, EXTR_PREFIX_ALL, 'var');
if (@$var_lang=='cs') { include "lang/desc-cs.php"; } else include "lang/desc-en.php";
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

if (@$var_adddesc) {
// Add new desc
  $var_item+=0;
  $var_langid+=0;
  if ($var_item) {
    $item = DB_SimpleQuery('SELECT * FROM XBXDESC item_desc WHERE item_id='.$var_item.' AND lang_id = '.$var_langid);
    if (!$item) {
      DB_Query("INSERT INTO XBXDESC (item_id, lang_id, text) VALUES ({$var_item},{$var_langid},'".htmlspecialchars($var_text)."')");
      echo '<div class="message">'.@$lang['adddesc_ok'].".</div>\n";
      $var_op='add';
    } else err_echo(@$lang['error_alreadypresent']);
  } else err_echo(@$lang['error_noitem']);

} if (@$var_updatedesc) {
// Update desc Values
  $var_item+=0;
  $var_langid+=0;
  $item = DB_SimpleQuery('SELECT * FROM XBXDESC item_desc WHERE item_id='.$var_item.' AND lang_id='.$var_langid);
  if ($item) {
    DB_Query("UPDATE XBXDESC SET text='".htmlspecialchars($var_text)."' WHERE item_id={$var_item} AND lang_id={$var_langid}");
    echo '<div class="message">'.@$lang['updatedesc_ok'].".</div>\n";
    $var_op='edit';
  } else err_echo(@$lang['error_noitem']);
  
} if (@$var_deletedesc) {
// Delete Node Values
  if ($var_really) {
    $var_node+=0;
    $var_langid+=0;
    DB_Query('DELETE FROM XBXDESC WHERE item_id='.$var_item.' AND lang_id='.$var_langid);
    echo '<div class="message">'.@$lang['deletedesc_ok'].".</div>\n";
  } else echo '<div class="error">'.@$lang['error_mustpermit'].".</div>\n";
}

if ($var_op=='add') {
// Add new desc dialog
  $var_item+=0;
  $item = DB_SimpleQuery('SELECT * FROM XBITEM item WHERE id='.$var_item);
  if ($item) {

    DB_Query('SELECT * FROM XBXLANGUAGE language');
    while ($row=DB_Row()) $language[$row['ID']]=$row;

    echo '<form method="post" action="desc.php'.pl('item='.$var_item).'">'."\n";
    echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['adddesc_legend']."</legend>\n";
    echo '  <label>'.@$lang['lang']."</label><br/>\n";
    echo "  <select name=\"langid\">\n";
    foreach($language as $row) {
      echo '    <option value="'.$row['ID'].'">'.$row['NAME'].' ('.$row['LANGCODE'].")</option>\n";
    }
    echo "  </select><br/>\n";
    echo '  <label>'.@$lang['text']."</label><br/>\n";
    echo '  <input class="formText" type="text" name="text"/><br/>'."\n";
    echo '  <input type="submit" name="adddesc" value="'.@$lang['adddesc'].'" class="formButton"/>'."\n";
    echo "</fieldset>\n</form>\n";
  } else err_echo(@$lang['error_noitem']);

} if ($var_op=='edit') {
  // Edit desc dialog
  $var_item+=0;
  $var_langid+=0;
  $item = DB_SimpleQuery('SELECT * FROM XBXDESC item_desc WHERE item_id='.$var_item.' AND lang_id='.$var_langid);
  if ($item) {

    echo '<form method="post" action="desc.php'.pl('item='.$var_item.'&amp;langid='.$var_langid).'">'."\n";
    echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['updatedesc_legend']."</legend>\n";
    echo '  <label>'.@$lang['text']."</label><br/>\n";
    echo '  <input class="formText" type="text" name="text" value="'.$item['TEXT']."\"/><br/>\n";
    echo '  <input type="submit" name="updatedesc" value="'.@$lang['updatedesc'].'" class="formButton"/>'."\n";
    echo "</fieldset>\n</form>\n";

    echo '<form method="post" action="desc.php'.pl('item='.$var_item.'&amp;langid='.$var_langid).'" class="regForm">'."\n";
    echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['deletedesc_legend']."</legend>\n";
    echo '  <input class="formText" type="checkbox" name="really"/>'.@$lang['deletedesc_really']."<br/>\n";
    echo '  <input type="submit" name="deletedesc" value="'.@$lang['deletedesc'].'" class="formButton"/>'."\n";
    echo "</fieldset>\n</form>\n";
  } else err_echo(@$lang['error_noitem']);
}
done(); ?>
