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

// PHP Catalog Web Interface: HTML Documentation Extension

$GLOBALS['current']="supp.php";
import_request_variables('gP','var_');
if (@$var_lang=='cs') { include "lang/supp-cs.php"; } else include "lang/supp-en.php";
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

if (@$var_addsupp) {
// Add new supp
  $var_item+=0;
  $var_file+=0;
  $var_mode+=0;
  $var_xbindex+=0;
  if ($var_item) {
    $item = DB_SimpleQuery('SELECT * FROM item WHERE id='.$var_item);
    DB_Query("INSERT INTO item_supp (item_id, mode, xbindex, file) VALUES ({$var_item},{$var_mode},{$var_xbindex},{$var_file})");
    echo '<div class="message">'.@$lang['addsupp_ok'].".</div>\n";
    $var_op='add';
  } else err_echo(@$lang['error_noitem']);

} if (@$var_updatesupp) {
// Update supp Values
  $var_item+=0;
  $var_supp+=0;
  $var_file+=0;
  $var_mode+=0;
  $var_xbindex+=0;
  $item = DB_SimpleQuery('SELECT * FROM item_supp WHERE id='.$var_supp);
  if ($item) {
    DB_Query("UPDATE item_supp SET file=".$var_file.", mode = ".$var_mode.", xbindex = ".$var_xbindex." WHERE id={$var_supp}");
    echo '<div class="message">'.@$lang['updatesupp_ok'].".</div>\n";
    $var_op='edit';
  } else err_echo(@$lang['error_noitem']);
  
} if (@$var_deletesupp) {
// Delete Node Values
  if ($var_really) {
    $var_supp+=0;
    DB_Query('DELETE FROM item_supp WHERE id='.$var_supp); // .' AND lang='.$var_langid
    echo '<div class="message">'.@$lang['deletesupp_ok'].".</div>\n";
  } else echo '<div class="error">'.@$lang['error_mustpermit'].".</div>\n";
}

if ($var_op=='select') {
  echo '<div>'.@$lang['cancelselect_pre'].'<a href="supp.php'.pl('op='.$var_for.'&item='.$var_item.'&supp='.$var_supp).'">'.@$lang['cancelselect'].'</a>'.@$lang['cancelselect_post']."</div><br/>\n";
  $var_folder+=0;
  $var_item+=0;
  $var_supp+=0;
  $var_file+=0;
  if ($var_folder) {
    $folder = DB_SimpleQuery('SELECT * FROM item, item_info WHERE item_info.owner = item.id AND item.id = '.$var_folder);
  } else if (@$var_supp>0) {
    $supp = DB_SimpleQuery('SELECT * FROM item_supp WHERE id='.$var_supp);
    if ($supp) {
      $folder = DB_SimpleQuery('SELECT * FROM item, item_info WHERE item_info.owner = item.id AND item.id = '.$supp['item_id']);
      if (!@$folder) $folder = DB_SimpleQuery('SELECT * FROM item, item_info WHERE item_info.owner = item.id AND EXISTS(SELECT 1 FROM item it WHERE item.id = it.parent AND it.id = '.$supp['item_id'].')');
    }
  } else if ($var_item) {
    $folder = DB_SimpleQuery('SELECT * FROM item, item_info WHERE item_info.owner = item.id AND owner = '.$var_item);
  }
  if (!@$folder) $folder = DB_SimpleQuery('SELECT * FROM item, item_info WHERE item_info.owner = item.id AND parent = 0');
  if (@$folder) {
    echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['selectparent_legend']."</legend>\n";

//    echo '<a href="supp.php'.pl('op='.$var_for.'&amp;item='.$var_item.'&amp;supp='.$var_supp).'">[.]</a><br/>'."\n";
    if ($folder['parent']>0) echo '<a href="supp.php'.pl('op=select&amp;for='.$var_for.'&amp;item='.$var_item.'&amp;supp='.$var_supp.'&amp;folder='.$folder['parent']).'">[..]</a><br/>'."\n";
    // Print folders
    DB_Query('SELECT * FROM item, item_info WHERE item.dtype = 0 AND item_info.owner = item.id AND parent = '.$folder['owner'].' ORDER BY filename');
    $lastid = 0;
    while ($row=DB_Row()) if ($row['id']!=$lastid) {
      echo '<a href="supp.php'.pl('op=select&amp;for='.$var_for.'&amp;item='.$var_item.'&amp;supp='.$var_supp.'&amp;folder='.$row['owner']).'">';
      echo '['.@$lang['folder'].' '.$row['owner'].'] '.$row['filename'];
      $lastid = $row['owner'];
      echo '</a><br/>'."\n";
    }
    echo '</fieldset>'."\n";
    echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['selectfile_legend']."</legend>\n";

    // Print files
    DB_Query('SELECT * FROM item_file WHERE item_id = '.$folder['owner'].' ORDER BY filename');
    $lastid = 0;
    while ($row=DB_Row()) if ($row['id']!=$lastid) {
      echo '<a href="supp.php'.pl('op='.$var_for.'&amp;item='.$var_item.'&amp;supp='.$var_supp.'&amp;file='.$row['id']).'">';
      echo '['.@$lang['file'].' '.$row['id'].'] '.$row['filename'];
      $lastid = $row['id'];
      echo '</a><br/>'."\n";
    }
    echo '</fieldset>'."\n";
  } else err_echo(@$lang['error_noitem']);
} else if ($var_op=='add') {
// Add new supp dialog
  $var_item+=0;
  $item = DB_SimpleQuery('SELECT * FROM item WHERE id='.$var_item);
  if ($item) {
    DB_Query('SELECT * FROM supp');
    while ($row=DB_Row()) $supp[$row['id']]=$row;

    echo '<form method="post" action="supp.php'.pl('item='.$var_item).'">'."\n";
    echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['addsupp_legend']."</legend>\n";
    echo '  <label>'.@$lang['mode']."</label><br/>\n";
    echo "  <select name=\"mode\">\n";
    foreach($supp as $row) {
      echo '    <option value="'.$row['id'].'">'.$row['caption'].' ('.$row['mime'].")</option>\n";
    }
    echo "  </select><br/>\n";
    echo '  <label>'.@$lang['xbindex']."</label><br/>\n";
    echo '  <input class="formText" type="text" name="xbindex" value="'.$var_xbindex."\"/><br/>\n";
    echo '  <label>'.@$lang['file']."</label><br/>\n";
    echo '  <input class="formText" type="text" name="file" value="'.$var_file."\"/>".' <a href="supp.php'.pl('op=select&amp;for=add&amp;item='.$var_item).'">'.@$lang['select_file'].'</a><br/>'."\n";
    echo '  <input type="submit" name="addsupp" value="'.@$lang['addsupp'].'" class="formButton"/>'."\n";
    echo "</fieldset>\n</form>\n";
  } else err_echo(@$lang['error_noitem']);

} if ($var_op=='edit') {
  // Edit supp dialog
  $var_supp+=0;
  $item = DB_SimpleQuery('SELECT * FROM item_supp WHERE id='.$var_supp);
  if ($item) {
    if (!@$var_file) $var_file = $item['supp_id'];
    if (!@$var_xbindex) $var_xbindex = $item['xbindex'];
    DB_Query('SELECT * FROM supp');
    while ($row=DB_Row()) $supp[$row['id']]=$row;
    echo '<form method="post" action="supp.php'.pl('supp='.$var_supp.'&amp;item='.$item['item_id']).'">'."\n";
    echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['updatesupp_legend']."</legend>\n";
    echo '  <label>'.@$lang['mode']."</label><br/>\n";
    echo "  <select name=\"mode\">\n";
    foreach($supp as $row) {
      echo '    <option value="'.$row['id'].'"';
      if ($row['id']==$item['mode']) echo ' selected="selected"';
      echo '>'.$row['caption'].' ('.$row['mime'].")</option>\n";
    }
    echo "  </select><br/>\n";
    echo '  <label>'.@$lang['xbindex']."</label><br/>\n";
    echo '  <input class="formText" type="text" name="xbindex" value="'.$var_xbindex."\"/><br/>\n";
    echo '  <label>'.@$lang['file']."</label><br/>\n";
    echo '  <input class="formText" type="text" name="file" value="'.$var_file."\"/>".' <a href="supp.php'.pl('op=select&amp;for=edit&amp;supp='.$var_supp.'&amp;item='.$item['item_id']).'">'.@$lang['select_file'].'</a><br/>'."\n";
    echo '  <input type="submit" name="updatesupp" value="'.@$lang['updatesupp'].'" class="formButton"/>'."\n";
    echo "</fieldset>\n</form>\n";

    echo '<form method="post" action="supp.php'.pl('supp='.$var_supp).'" class="regForm">'."\n";
    echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['deletesupp_legend']."</legend>\n";
    echo '  <input class="formText" type="checkbox" name="really"/>'.@$lang['deletesupp_really']."<br/>\n";
    echo '  <input type="submit" name="deletesupp" value="'.@$lang['deletesupp'].'" class="formButton"/>'."\n";
    echo "</fieldset>\n</form>\n";
  } else err_echo(@$lang['error_noitem']);
} else if ($var_op=='view') {
  // TODO: Preview
  $var_item+=0;
  $var_langid+=0;
  $item = DB_SimpleQuery('SELECT * FROM item_supp,language WHERE item_supp.id='.$var_item.' AND lang='.$var_langid.' AND language.id = item_supp.lang');
  if ($item) {
    echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['viewsupp_legend']."</legend>\n";
    echo '<div>'.@$lang['lang'].': '.$item['code']."</div>\n";
    echo '<div>'.@$lang['text']."</div>\n";
    echo '<div>'.html_entity_decode($item['text'])."</div>\n";
    echo "</fieldset>\n";
  } else err_echo(@$lang['error_noitem']);
}
done(); ?>
