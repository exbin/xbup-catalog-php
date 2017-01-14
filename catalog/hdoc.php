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

$GLOBALS['current']="hdoc.php";
extract($_GET, EXTR_PREFIX_ALL, 'var'); extract($_POST, EXTR_PREFIX_ALL, 'var');
if (@$var_lang=='cs') { include "lang/hdoc-cs.php"; } else include "lang/hdoc-en.php";
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

if (@$var_addhdoc) {
// Add new hdoc
  $var_item+=0;
  $var_file+=0;
  $var_langid+=0;
  if ($var_item) {
    $item = DB_SimpleQuery('SELECT * FROM XBITEM item WHERE id='.$var_item);
    DB_Query("INSERT INTO XBXHDOC (item_id, lang_id, docfile_id) VALUES ({$var_item},{$var_langid},{$var_file})");
    echo '<div class="message">'.@$lang['addhdoc_ok'].".</div>\n";
    $var_op='add';
  } else err_echo(@$lang['error_noitem']);
} if (@$var_updatehdoc) {
// Update hdoc Values
  $var_item+=0;
  $var_hdoc+=0;
  $var_file+=0;
  $var_langid+=0;
  $item = DB_SimpleQuery('SELECT * FROM XBXHDOC item_hdoc WHERE id='.$var_hdoc);
  if ($item) {
    DB_Query("UPDATE XBXDOC SET docfile_id=".$var_file.", lang_id = ".$var_langid." WHERE id={$var_hdoc}");
    echo '<div class="message">'.@$lang['updatehdoc_ok'].".</div>\n";
    $var_op='edit';
  } else err_echo(@$lang['error_noitem']);
} if (@$var_contenthdoc) {
// Update hdoc Values
  $var_item+=0;
  $var_hdoc+=0;
//  $var_text = htmlspecialchars($var_text);
  $item = DB_SimpleQuery('SELECT *, item_file.filename AS fname, item_info.filename AS dname FROM XBXFILE item_file, XBXSTRI stri, XBXHDOC item_hdoc WHERE item_file.item_id = item_info.owner AND item_file.id = item_hdoc.file AND item_hdoc.id='.$var_hdoc);
  if ($item) {
    $path = $item['path'].'/'.$item['dname'];
    if ($path[0] == '/') $path = substr($path, 1);
    $fl = fopen($path.'/'.$item['fname'],'w+');
    fwrite($fl, $var_text);
    fclose($fl);
    echo '<div class="message">'.@$lang['updatehdoc_ok'].".</div>\n";
    $var_op='view';
  } else err_echo(@$lang['error_noitem']);
} if (@$var_deletehdoc) {
// Delete Node Values
  if ($var_really) {
    $var_hdoc+=0;
    DB_Query('DELETE FROM XBXHDOC WHERE id='.$var_hdoc); // .' AND lang='.$var_langid
    echo '<div class="message">'.@$lang['deletehdoc_ok'].".</div>\n";
  } else echo '<div class="error">'.@$lang['error_mustpermit'].".</div>\n";
} if (@$var_createhdoc) {
  $var_item+=0;
  $var_langid+=0;
  $folder = DB_SimpleQuery('SELECT * FROM XBITEM item, XBXSTRI stri, XBXNAME item_name, XBXLANGUAGE language WHERE item_name.id = item.id AND language.id = item_name.lang AND item_name.lang = '.$var_langid." AND ((item.dtype = 'XBNode' AND item_info.owner = item.id) OR ((NOT item.dtype = 'XBNode') AND item_info.owner = item.parent)) AND item.id = ".$var_item);
  if (@$folder) {
    $path = $folder['path'].'/'.$folder['filename'];
    if ($path[0] == '/') $path = substr($path, 1);
    $var_name = 'hdoc_'.$folder['text'].'-'.$folder['code'].'.html';
    touch($path.'/'.$var_name);
    chmod($path.'/'.$var_name, 0666);
    DB_Query("INSERT INTO XBXFILE (item_id, filename) VALUES (".$folder['owner'].",'{$var_name}')");
    $var_file=mysql_insert_id();
    DB_Query("INSERT INTO XBXHDOC (item_id, lang, file) VALUES ({$var_item},{$var_langid},{$var_file})");
    $var_hdoc=mysql_insert_id();
    echo '<div class="message">'.@$lang['addhdoc_ok'].".</div>\n";
    $var_op='edit';
  } else err_echo(@$lang['error_noitem']);
}

if ($var_op=='select') {
  echo '<div>'.@$lang['cancelselect_pre'].'<a href="hdoc.php'.pl('op='.$var_for.'&item='.$var_item.'&hdoc='.$var_hdoc).'">'.@$lang['cancelselect'].'</a>'.@$lang['cancelselect_post']."</div><br/>\n";
  $var_folder+=0;
  $var_item+=0;
  $var_hdoc+=0;
  $var_file+=0;
  if ($var_folder) {
    $folder = DB_SimpleQuery('SELECT * FROM XBITEM item, item_info WHERE item_info.owner = item.id AND item.id = '.$var_folder);
  } else if (@$var_hdoc>0) {
    $hdoc = DB_SimpleQuery('SELECT * FROM item_hdoc WHERE id='.$var_hdoc);
    if ($hdoc) {
      $folder = DB_SimpleQuery('SELECT * FROM item, item_info WHERE item_info.owner = item.id AND item.id = '.$hdoc['item_id']);
      if (!@$folder) $folder = DB_SimpleQuery('SELECT * FROM item, item_info WHERE item_info.owner = item.id AND EXISTS(SELECT 1 FROM item it WHERE item.id = it.parent AND it.id = '.$hdoc['item_id'].')');
    }
  } else if ($var_item) {
    $folder = DB_SimpleQuery('SELECT * FROM item, item_info WHERE item_info.owner = item.id AND owner = '.$var_item);
  }
  if (!@$folder) $folder = DB_SimpleQuery('SELECT * FROM item, item_info WHERE item_info.owner = item.id AND parent = 0');
  if (@$folder) {
    echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['selectparent_legend']."</legend>\n";

//    echo '<a href="hdoc.php'.pl('op='.$var_for.'&amp;item='.$var_item.'&amp;hdoc='.$var_hdoc).'">[.]</a><br/>'."\n";
    if ($folder['parent']>0) echo '<a href="hdoc.php'.pl('op=select&amp;for='.$var_for.'&amp;item='.$var_item.'&amp;hdoc='.$var_hdoc.'&amp;folder='.$folder['parent']).'">[..]</a><br/>'."\n";
    // Print folders
    DB_Query("SELECT * FROM item, item_info WHERE item.dtype = 'XBNode' AND item_info.owner = item.id AND parent = ".$folder['owner'].' ORDER BY filename');
    $lastid = 0;
    while ($row=DB_Row()) if ($row['id']!=$lastid) {
      echo '<a href="hdoc.php'.pl('op=select&amp;for='.$var_for.'&amp;item='.$var_item.'&amp;hdoc='.$var_hdoc.'&amp;folder='.$row['owner']).'">';
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
      echo '<a href="hdoc.php'.pl('op='.$var_for.'&amp;item='.$var_item.'&amp;hdoc='.$var_hdoc.'&amp;file='.$row['id']).'">';
      echo '['.@$lang['file'].' '.$row['id'].'] '.$row['filename'];
      $lastid = $row['id'];
      echo '</a><br/>'."\n";
    }
    echo '</fieldset>'."\n";
  } else err_echo(@$lang['error_noitem']);
} else if ($var_op=='add') {
// Add new hdoc dialog
  $var_item+=0;
  $item = DB_SimpleQuery('SELECT * FROM item WHERE id='.$var_item);
  if ($item) {
    DB_Query('SELECT * FROM language');
    while ($row=DB_Row()) $language[$row['id']]=$row;

    echo '<form method="post" action="hdoc.php'.pl('item='.$var_item).'">'."\n";
    echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['addhdoc_legend']."</legend>\n";
    echo '  <label>'.@$lang['lang']."</label><br/>\n";
    echo "  <select name=\"langid\">\n";
    foreach($language as $row) {
      echo '    <option value="'.$row['id'].'">'.$row['caption'].' ('.$row['code'].")</option>\n";
    }
    echo "  </select><br/>\n";
    echo '  <label>'.@$lang['file']."</label><br/>\n";
    echo '  <input class="formText" type="text" name="file" value="'.$var_file."\"/>".' <a href="hdoc.php'.pl('op=select&amp;for=add&amp;item='.$var_item).'">'.@$lang['select_file'].'</a><br/>'."\n";
    echo '  <input type="submit" name="addhdoc" value="'.@$lang['addhdoc'].'" class="formButton"/>'."\n";
    echo "</fieldset>\n</form>\n";

    echo '<form method="post" action="hdoc.php'.pl('item='.$var_item).'">'."\n";
    echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['createhdoc_legend']."</legend>\n";
    echo '  <label>'.@$lang['lang']."</label><br/>\n";
    echo "  <select name=\"langid\">\n";
    foreach($language as $row) {
      echo '    <option value="'.$row['id'].'">'.$row['caption'].' ('.$row['code'].")</option>\n";
    }
    echo "  </select><br/>\n";
    echo '  <input type="submit" name="createhdoc" value="'.@$lang['createhdoc'].'" class="formButton"/>'."\n";
    echo "</fieldset>\n</form>\n";
  } else err_echo(@$lang['error_noitem']);

} if ($var_op=='edit') {
  // Edit hdoc dialog
  $var_hdoc+=0;
  $item = DB_SimpleQuery('SELECT * FROM item_hdoc WHERE id='.$var_hdoc);
  if ($item) {
    if (!@$var_file) $var_file = $item['hdoc_id'];
    DB_Query('SELECT * FROM language');
    while ($row=DB_Row()) $language[$row['id']]=$row;
    echo '<form method="post" action="hdoc.php'.pl('hdoc='.$var_hdoc.'&amp;item='.$item['item_id']).'">'."\n";
    echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['updatehdoc_legend']."</legend>\n";
    echo '  <label>'.@$lang['lang']."</label><br/>\n";
    echo "  <select name=\"langid\">\n";
    foreach($language as $row) {
      echo '    <option value="'.$row['id'].'">'.$row['caption'].' ('.$row['code'].")</option>\n";
    }
    echo "  </select><br/>\n";
    echo '  <label>'.@$lang['file']."</label><br/>\n";
    echo '  <input class="formText" type="text" name="file" value="'.$var_file."\"/>".' <a href="hdoc.php'.pl('op=select&amp;for=edit&amp;hdoc='.$var_hdoc.'&amp;item='.$item['item_id']).'">'.@$lang['select_file'].'</a><br/>'."\n";
    echo '  <input type="submit" name="updatehdoc" value="'.@$lang['updatehdoc'].'" class="formButton"/>'."\n";
    echo "</fieldset>\n</form>\n";

    echo '<form method="post" action="hdoc.php'.pl('hdoc='.$var_hdoc.'&amp;item='.$item['item_id']).'" class="regForm">'."\n";
    echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['deletehdoc_legend']."</legend>\n";
    echo '  <input class="formText" type="checkbox" name="really"/>'.@$lang['deletehdoc_really']."<br/>\n";
    echo '  <input type="submit" name="deletehdoc" value="'.@$lang['deletehdoc'].'" class="formButton"/>'."\n";
    echo "</fieldset>\n</form>\n";

    echo '<form method="post" action="hdoc.php'.pl('item='.$var_item).'">'."\n";
    echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['createhdoc_legend']."</legend>\n";
    echo '  <label>'.@$lang['lang']."</label><br/>\n";
    echo "  <select name=\"langid\">\n";
    foreach($language as $row) {
      echo '    <option value="'.$row['id'].'">'.$row['caption'].' ('.$row['code'].")</option>\n";
    }
    echo "  </select><br/>\n";
    echo '  <input type="submit" name="createhdoc" value="'.@$lang['createhdoc'].'" class="formButton"/>'."\n";
    echo "</fieldset>\n</form>\n";
  } else err_echo(@$lang['error_noitem']);
} else if ($var_op=='content') {
  $var_hdoc+=0;
  $item = DB_SimpleQuery('SELECT *, item_file.filename AS fname, item_info.filename AS dname FROM item_file, item_info, item_hdoc WHERE item_file.item_id = item_info.owner AND item_file.id = item_hdoc.file AND item_hdoc.id='.$var_hdoc);
  if ($item) {
    $path = $item['path'].'/'.$item['dname'];
    if ($path[0] == '/') $path = substr($path, 1);
    echo '<form method="post" action="hdoc.php'.pl('hdoc='.$var_hdoc.'&amp;item='.$var_item).'">'."\n";
    echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['updatehdoc_legend']."</legend>\n";
    echo '<textarea name="text" cols="80" rows="25">'.htmlspecialchars(file_get_contents($path.'/'.$item['fname']))."</textarea><br/>\n";
    echo '  <input type="submit" name="contenthdoc" value="'.@$lang['updatehdoc'].'" class="formButton"/>'."\n";
    echo "</fieldset>\n</form>\n";

  } else err_echo(@$lang['error_noitem']);
} else if ($var_op=='view') {
  $var_hdoc+=0;
  $var_langid+=0;
  $item = DB_SimpleQuery('SELECT *, item_file.filename AS fname, item_info.filename AS dname FROM item_file, item_info, item_hdoc, language WHERE language.id = item_hdoc.lang AND item_file.item_id = item_info.owner AND item_file.id = item_hdoc.file AND item_hdoc.id='.$var_hdoc);
  if ($item) {
    $path = $item['path'].'/'.$item['dname'];
    if ($path[0] == '/') $path = substr($path, 1);
    echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['viewhdoc_legend']."</legend>\n";
    echo '<div>'.@$lang['lang'].': '.$item['code']."</div>\n";
    echo '<div>'.@$lang['text']."</div>\n";
    echo '<div>'.file_get_contents($path.'/'.$item['fname'])."</div>\n";
    echo '<div><a href="hdoc.php'.pl('op=content&amp;for=edit&amp;hdoc='.$var_hdoc.'&amp;item='.$item['item_id']).'">'.@$lang['edit_content']."</a></div>\n";
    echo "</fieldset>\n";
  } else err_echo(@$lang['error_noitem']);
}
done(); ?>
