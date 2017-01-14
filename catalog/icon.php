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

// PHP Catalog Web Interface: Icon Extension

$GLOBALS['current']="icon.php";
extract($_GET, EXTR_PREFIX_ALL, 'var'); extract($_POST, EXTR_PREFIX_ALL, 'var');
if (@$var_lang=='cs') { include "lang/icon-cs.php"; } else include "lang/icon-en.php";
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

if (@$var_addicon) {
// Add new icon
  $var_item+=0;
  $var_file+=0;
  $var_mode+=0;
  $var_xbindex+=0;
  if ($var_item) {
    $item = DB_SimpleQuery('SELECT * FROM XBITEM item WHERE id='.$var_item);
    DB_Query("INSERT INTO XBXICON (owner_id, xbindex, mode_id, iconfile_id) VALUES ({$var_item},{$var_xbindex},{$var_mode},{$var_file})");
    echo '<div class="message">'.@$lang['addicon_ok'].".</div>\n";
    $var_op='add';
  } else err_echo(@$lang['error_noitem']);

} if (@$var_updateicon) {
// Update icon Values
  $var_item+=0;
  $var_icon+=0;
  $var_file+=0;
  $var_mode+=0;
  $var_xbindex+=0;
  $item = DB_SimpleQuery('SELECT * FROM XBXICON WHERE id='.$var_icon);
  if ($item) {
    DB_Query("UPDATE XBXICON SET iconfile_id=".$var_file.", mode_id = ".$var_mode.", xbindex = ".$var_xbindex." WHERE id={$var_icon}");
    echo '<div class="message">'.@$lang['updateicon_ok'].".</div>\n";
    $var_op='edit';
  } else err_echo(@$lang['error_noitem']);
  
} if (@$var_deleteicon) {
// Delete Node Values
  if ($var_really) {
    $var_icon+=0;
    DB_Query('DELETE FROM XBXICON WHERE id='.$var_icon); // .' AND lang='.$var_langid
    echo '<div class="message">'.@$lang['deleteicon_ok'].".</div>\n";
  } else echo '<div class="error">'.@$lang['error_mustpermit'].".</div>\n";
}

if ($var_op=='select') {
  echo '<div>'.@$lang['cancelselect_pre'].'<a href="icon.php'.pl('op='.$var_for.'&item='.$var_item.'&icon='.$var_icon).'">'.@$lang['cancelselect'].'</a>'.@$lang['cancelselect_post']."</div><br/>\n";
  $var_folder+=0;
  $var_item+=0;
  $var_icon+=0;
  $var_file+=0;
  if ($var_folder) {
    $folder = DB_SimpleQuery('SELECT * FROM XBITEM item, XBXSTRI stri WHERE stri.item_id = item.id AND item.id = '.$var_folder);
  } else if (@$var_icon>0) {
    $icon = DB_SimpleQuery('SELECT * FROM XBXICON WHERE id='.$var_icon);
    if ($icon) {
      $folder = DB_SimpleQuery('SELECT * FROM XBITEM item, XBXSTRI stri WHERE stri.item_id = item.id AND item.id = '.$icon['OWNER_ID']);
      if (!@$folder) $folder = DB_SimpleQuery('SELECT * FROM item, item_info WHERE item_info.owner = item.id AND EXISTS(SELECT 1 FROM item it WHERE item.id = it.parent AND it.id = '.$icon['item_id'].')');
    }
  } else if ($var_item) {
    $folder = DB_SimpleQuery('SELECT * FROM XBITEM item, XBXSTRI stri WHERE stri.item_id = item.id AND item.id = '.$var_item);
  }
  if (!@$folder) $folder = DB_SimpleQuery('SELECT * FROM XBITEM item, XBXSTRI stri WHERE stri.item_id = item.id AND item.node_id = 0');
  if (@$folder) {
    echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['selectparent_legend']."</legend>\n";

//    echo '<a href="icon.php'.pl('op='.$var_for.'&amp;item='.$var_item.'&amp;icon='.$var_icon).'">[.]</a><br/>'."\n";
    if ($folder['parent']>0) echo '<a href="icon.php'.pl('op=select&amp;for='.$var_for.'&amp;item='.$var_item.'&amp;icon='.$var_icon.'&amp;folder='.$folder['parent']).'">[..]</a><br/>'."\n";
    // Print folders
    DB_Query("SELECT * FROM XBITEM item, XBXSTRI stri WHERE item.dtype = 'XBNode' AND stri.item_id = item.id AND item.node_id = ".$folder['NODE_ID'].' ORDER BY text');
    $lastid = 0;
    while ($row=DB_Row()) if ($row['id']!=$lastid) {
      echo '<a href="icon.php'.pl('op=select&amp;for='.$var_for.'&amp;item='.$var_item.'&amp;icon='.$var_icon.'&amp;folder='.$row['owner']).'">';
      echo '['.@$lang['folder'].' '.$row['NODE_ID'].'] '.$row['TEXT'];
      $lastid = $row['owner'];
      echo '</a><br/>'."\n";
    }
    echo '</fieldset>'."\n";
    echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['selectfile_legend']."</legend>\n";

    // Print files
    DB_Query('SELECT * FROM XBXFILE item_file WHERE node_id = '.$folder['owner'].' ORDER BY filename');
    $lastid = 0;
    while ($row=DB_Row()) if ($row['id']!=$lastid) {
      echo '<a href="icon.php'.pl('op='.$var_for.'&amp;item='.$var_item.'&amp;icon='.$var_icon.'&amp;file='.$row['id']).'">';
      echo '['.@$lang['file'].' '.$row['id'].'] '.$row['filename'];
      $lastid = $row['id'];
      echo '</a><br/>'."\n";
    }
    echo '</fieldset>'."\n";
  } else err_echo(@$lang['error_noitem']);
} else if ($var_op=='add') {
// Add new icon dialog
  $var_item+=0;
  $item = DB_SimpleQuery('SELECT * FROM XBITEM item WHERE id='.$var_item);
  if ($item) {
    DB_Query('SELECT * FROM XBXICONMODE');
    while ($row=DB_Row()) $icon[$row['ID']]=$row;

    echo '<form method="post" action="icon.php'.pl('item='.$var_item).'">'."\n";
    echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['addicon_legend']."</legend>\n";
    echo '  <label>'.@$lang['xbindex']."</label><br/>\n";
    echo '  <input class="formText" type="text" name="xbindex" value="'.$row['xbindex']."\"/><br/>\n";
    echo '  <label>'.@$lang['mode']."</label><br/>\n";
    echo "  <select name=\"mode\">\n";
    foreach($icon as $row) {
      echo '    <option value="'.$row['ID'].'">'.$row['CAPTION'].' ('.$row['MIME'].")</option>\n";
    }
    echo "  </select><br/>\n";
    echo '  <label>'.@$lang['file']."</label><br/>\n";
    echo '  <input class="formText" type="text" name="file" value="'.$var_file."\"/>".' <a href="icon.php'.pl('op=select&amp;for=add&amp;item='.$var_item).'">'.@$lang['select_file'].'</a><br/>'."\n";
    echo '  <input type="submit" name="addicon" value="'.@$lang['addicon'].'" class="formButton"/>'."\n";
    echo "</fieldset>\n</form>\n";
  } else err_echo(@$lang['error_noitem']);

} if ($var_op=='edit') {
  // Edit icon dialog
  $var_icon+=0;
  $item = DB_SimpleQuery('SELECT * FROM XBXICON item_icon WHERE id='.$var_icon);
  if ($item) {
    if (!@$var_file) $var_file = $item['ICONFILE_ID'];
    if (!@$var_xbindex) $var_xbindex = $item['XBINDEX'];
    DB_Query('SELECT * FROM XBXICONMODE');
    while ($row=DB_Row()) $icon[$row['ID']]=$row;
    // TODO Preview
    echo '<form method="post" action="icon.php'.pl('icon='.$var_icon.'&amp;item='.$item['item_id']).'">'."\n";
    echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['updateicon_legend']."</legend>\n";
    echo '  <label>'.@$lang['xbindex']."</label><br/>\n";
    echo '  <input class="formText" type="text" name="xbindex" value="'.$var_xbindex."\"/><br/>\n";
    echo '  <label>'.@$lang['mode']."</label><br/>\n";
    echo "  <select name=\"mode\">\n";
    foreach($icon as $row) {
      echo '    <option value="'.$row['ID'].'"';
      if ($row['ID']==$item['MODE_ID']) echo ' selected="selected"';
      echo '>'.$row['CAPTION'].' ('.$row['MIME'].")</option>\n";
    }
    echo "  </select><br/>\n";
    echo '  <label>'.@$lang['file']."</label><br/>\n";
    echo '  <input class="formText" type="text" name="file" value="'.$var_file."\"/>".' <a href="icon.php'.pl('op=select&amp;for=edit&amp;icon='.$var_icon.'&amp;item='.$item['item_id']).'">'.@$lang['select_file'].'</a><br/>'."\n";
    echo '  <input type="submit" name="updateicon" value="'.@$lang['updateicon'].'" class="formButton"/>'."\n";
    echo "</fieldset>\n</form>\n";

    echo '<form method="post" action="icon.php'.pl('icon='.$var_icon).'" class="regForm">'."\n";
    echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['deleteicon_legend']."</legend>\n";
    echo '  <input class="formText" type="checkbox" name="really"/>'.@$lang['deleteicon_really']."<br/>\n";
    echo '  <input type="submit" name="deleteicon" value="'.@$lang['deleteicon'].'" class="formButton"/>'."\n";
    echo "</fieldset>\n</form>\n";
  } else err_echo(@$lang['error_noitem']);
} else if ($var_op=='view') {
  // Edit icon dialog
  $var_item+=0;
  $var_langid+=0;
  $item = DB_SimpleQuery('SELECT * FROM XBXICON item_icon, XBXLANGUAGE language WHERE item_icon.id='.$var_item.' AND lang='.$var_langid.' AND language.id = item_icon.lang');
  if ($item) {
    echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['viewicon_legend']."</legend>\n";
    echo '<div>'.@$lang['lang'].': '.$item['code']."</div>\n";
    echo '<div>'.@$lang['text']."</div>\n";
    echo '<div>'.html_entity_decode($item['text'])."</div>\n";
    echo "</fieldset>\n";
  } else err_echo(@$lang['error_noitem']);
}
done(); ?>
