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

// PHP Catalog Web Interface: Browse items

include 'include-head.php'; include 'db.php';
import_request_variables('gP','var_');
function pl($text) {
  if ($text) {
    if (@$GLOBALS['pl']) {
      return $GLOBALS['pl'].'&amp;'.$text;
    } else return '?'.$text; 
  } else return $GLOBALS['pl'];
}

if (@$var_lang=='cs') { include "lang/browse-cs.php"; } else include "lang/browse-en.php";
$pagename=@$lang['pagename'];

$GLOBALS['current'] = 'browse-item.php';
//include 'browse.php';

  $target=' target="main"';
  echo '<p style="background-color: #DFDFFF; margin: 5px 5px 5px 5px; padding: 5px 5px 5px 5px; text-align: right;">'.$lang['split_title'].' '.$GLOBALS['catalog_version'].(($GLOBALS['catalog_mode'])?' '.$GLOBALS['catalog_mode']:'').'</p>'."\n";

echo '<div style="padding: 5px 5px 5px 5px;"><div>';
$var_item+=0;
$itemlang = 1;

if ($var_item) {
  $item = DB_SimpleQuery('SELECT * FROM XBITEM WHERE id = '.$var_item);
} else {
  $item = DB_SimpleQuery('SELECT * FROM XBITEM WHERE owner_id IS NULL');
  $var_item = $item['ID'];
}
if (!$item) {
  echo 'Item not found!';
  die;
}

$type_caption = array('XBNode'=>'Node','XBFormatSpec'=>'Format','XBGroupSpec'=>'Group','XBBlockSpec'=>'Block','Limitation','Attribute');

$icon = DB_SimpleQuery('SELECT stri.nodepath, stri.text AS fname, item_file.* FROM XBXSTRI stri, XBXICON item_icon, XBXFILE item_file WHERE item_file.id = item_icon.iconfile_id AND stri.item_id = item_file.node_id AND item_icon.owner_id = '.$var_item);

$name = DB_SimpleQuery('SELECT * FROM XBXNAME item_name WHERE item_id = '.$var_item.' AND lang_id = '.$itemlang);
echo '<h3>';

if ($icon) {
//  $path = $icon['fullpath'].'/'.$icon['fname'];
  $path = 'root'.$icon['NODEPATH'];
  if ($path[0] == '/') $path = substr($path, 1);
  echo '<img src="'.$path.'/'.$icon['TEXT'].'" alt="'.$icon['TEXT'].'"';
  if ($icon['MODE_ID'] == 1) {
    echo ' width="16" height="16"';
  } else if ($icon['MODE_ID'] == 2) {
    echo ' width="32" height="32"';
  }
  echo ' hspace="4" />';
}
echo $type_caption[$item['DTYPE']].': '.$name['TEXT'].' ('.$item['XBINDEX'].')';
if (!$target) echo ' [<a href="item.php'.pl('item='.$var_item).'">spec</a>]';
echo "</h3>\n";

$desc = DB_SimpleQuery('SELECT * FROM XBXDESC item_desc WHERE item_id = '.$var_item.' AND lang_id = '.$itemlang);
if ($desc['text']) echo '<p><em>'.$lang['item_desc_text'].': '.$desc['TEXT']."</em></p>\n";

echo '<table border="1" width="95%" align="center"><tr><td style="background-color: #DFDFFF;" colspan="2">'.$lang['overview'].'</td></tr>'."\n";
$path = array();
$pathitem = $item;
while ($pathitem['parent']) {
  $pathitem = DB_SimpleQuery('SELECT * FROM XBITEM item LEFT JOIN XBXNAME item_name ON item_name.item_id = item.id WHERE item.id = '.$pathitem['parent']);
  if ($pathitem['parent']) array_unshift($path,'<a href="'.$GLOBALS['current'].pl('item='.$pathitem['ID']).'">'.$pathitem['TEXT'].'</a> ('.$pathitem['XBINDEX'].')&nbsp;');
}
echo '<tr><td>Path</td><td>'.implode($path,'/&nbsp;')."</td></tr>\n";

switch ($item['DTYPE']) {
  case 'XBNode': {
    break;
  }
}
echo "</table><br/>\n";

/*
$comm = DB_SimpleQuery('SELECT * FROM XBXCOMM item_comm WHERE item_id = '.$var_item.' AND lang_id = '.$itemlang);
if ($comm['TEXT']) echo '<p>'.$comm['TEXT']."</p>\n";
*/

// TODO: FormatBind FormatJoin ...
if (($item['DTYPE']=='XBFormatSpec')||($item['DTYPE']=='XBGroupSpec')||($item['DTYPE']=='XBBlockSpec')) {
  echo '<table border="1" width="95%" align="center"><tr><td style="background-color: #DFDFFF;">'.$lang['definition'].'</td></tr>'."\n";
  $rev_id = 0;
  DB_Query('SELECT *, item.xbindex AS myorder FROM XBITEM item, XBSPECDEF def
    LEFT JOIN XBREV item_rev ON item_rev.id = def.target_id
    WHERE def.id = item.id AND def.id = '.$var_item.' ORDER BY item.xbindex');
  $def=DB_Row();
  $def_id = 0;
  while ($def) {
    DB_Save();
    if (($rev_id==0)||(($rev_cnt==0)&&(isset($rev)))) {
      $rev = DB_SimpleQuery('SELECT * FROM XBITEM item, XBREV item_rev WHERE item.id = item_rev.id AND item.id = '.$var_item.' AND item.xbindex >= '.$rev_id.' ORDER BY item.xbindex LIMIT 1');
      if ($rev) {
        if ($rev['xblimit']>0) {
          echo '<tr><td>'.$lang['revision'].' '.$rev['XBINDEX']."</td></tr>\n";
          $rev_cnt = $rev['XBLIMIT'];
        } else {
          echo '<tr><td>List '.$rev['XBINDEX']."</td></tr>\n";
          $rev_cnt = 1;
        }
        $rev_id = $rev['XBINDEX']+1;
      } else {
        echo '<tr><td>'.$lang['unspecified_revision'].' '."</td></tr>\n";
        $rev_id++;
        unset($rev);
      }
    }
    if ($def['myorder'] == $def_id) {
      if ($def['owner']>0) {
        $name = DB_SimpleQuery('SELECT * FROM item_name WHERE id = '.$def['SPEC_ID'].' AND lang = '.$itemlang);
        $desc = DB_SimpleQuery('SELECT * FROM item_desc WHERE id = '.$def['SPEC_ID'].' AND lang = '.$itemlang);
      } else {
        $name = '';
        $desc = '';
      }
    }
    DB_Load();
    echo '<tr><td>['.$def_id.'] ';
    if ($def['myorder'] == $def_id) {
      if ($desc['text']) $desc['text'] = ' - '.$desc['text'];
      if (($item['dtype']=='XBBlockSpec')&&(! $def['TARGET_ID'] > 0)) {
        if ($def['DTYPE'] == '') { // TODO: specdef type
          echo 'Join: Attribute';
        } else {
          echo 'Consist: Data Block';
        }
      } else {
        if ($def['DTYPE'] == 'XBFormatSpec') {
          echo 'Consist: ';
        } else {
          echo 'Join: ';
        }
        echo '<a href="'.$GLOBALS['current'].pl('item='.$def['SPEC_ID']).'"'.$target.'>'.$name['text'].'</a>&nbsp;('.$def['XBINDEX'].')'.$desc['text'];
      }
      $def=DB_Row();
    } else {
      echo 'Consist: General Block';
    }
    echo "</td></tr>\n";
    $def_id++;
    if (isset($rev)) $rev_cnt--;
  }
  echo "</table><br/>\n";
}

switch ($item['DTYPE']) {
  case 'XBNode': {
    echo '<table border="1" width="95%" align="center"><tr><td style="background-color: #DFDFFF;">'.$lang['sbar_nodes'].'</td></tr>'."\n";
    DB_Query("SELECT * FROM XBITEM item WHERE item.dtype='XBNode' AND item.owner_id = ".$var_item);
    while ($sub=DB_Row()) {
      DB_Save();
      $name = DB_SimpleQuery('SELECT * FROM XBXNAME item_name WHERE item_id = '.$sub['ID'].' AND lang_id = '.$itemlang);
      $desc = DB_SimpleQuery('SELECT * FROM XBXDESC item_desc WHERE item_id = '.$sub['ID'].' AND lang_id = '.$itemlang);
      if ($desc['TEXT']) $desc['TEXT'] = ' - '.$desc['TEXT']; 
      echo '<tr><td><a href="'.$GLOBALS['current'].pl('item='.$sub['ID']).'"'.$target.'>'.$name['TEXT'].'</a>&nbsp;('.$sub['XBINDEX'].')'.$desc['TEXT']."&nbsp;</td></tr>\n";
      DB_Load();
    }
    echo "</table><br/>\n";

    echo '<table border="1" width="95%" align="center"><tr><td style="background-color: #DFDFFF;">'.$lang['sbar_formats'].'</td></tr>'."\n";
    DB_Query("SELECT * FROM XBITEM item WHERE dtype='XBFormatSpec' AND item.owner_id = ".$var_item);
    while ($sub=DB_Row()) {
      DB_Save();
      $name = DB_SimpleQuery('SELECT * FROM XBXNAME item_name WHERE item_id = '.$sub['ID'].' AND lang_id = '.$itemlang);
      $desc = DB_SimpleQuery('SELECT * FROM XBXDESC item_desc WHERE item_id = '.$sub['ID'].' AND lang_id = '.$itemlang);
      if ($desc['TEXT']) $desc['TEXT'] = ' - '.$desc['TEXT']; 
      echo '<tr><td><a href="'.$GLOBALS['current'].pl('item='.$sub['ID']).'"'.$target.'>'.$name['TEXT'].'</a>&nbsp;('.$sub['XBINDEX'].')'.$desc['TEXT']."</td></tr>\n";
      DB_Load();
    }
    echo "</table><br/>\n";

    echo '<table border="1" width="95%" align="center"><tr><td style="background-color: #DFDFFF;">'.$lang['sbar_groups'].'</td></tr>'."\n";
    DB_Query("SELECT * FROM XBITEM item WHERE dtype='XBGroupSpec' AND item.owner_id = ".$var_item);
    while ($sub=DB_Row()) {
      DB_Save();
      $name = DB_SimpleQuery('SELECT * FROM XBXNAME item_name WHERE item_id = '.$sub['ID'].' AND lang_id = '.$itemlang);
      $desc = DB_SimpleQuery('SELECT * FROM XBXDESC item_desc WHERE item_id = '.$sub['ID'].' AND lang_id = '.$itemlang);
      if ($desc['TEXT']) $desc['TEXT'] = ' - '.$desc['TEXT']; 
      echo '<tr><td><a href="'.$GLOBALS['current'].pl('item='.$sub['ID']).'"'.$target.'>'.$name['TEXT'].'</a>&nbsp;('.$sub['XBINDEX'].')'.$desc['TEXT']."</td></tr>\n";
      DB_Load();
    }
    echo "</table><br/>\n";

    echo '<table border="1" width="95%" align="center"><tr><td style="background-color: #DFDFFF;">'.$lang['sbar_blocks'].'</td></tr>'."\n";
    DB_Query("SELECT * FROM XBITEM item WHERE dtype='XBBlockSpec' AND item.owner_id = ".$var_item);
    while ($sub=DB_Row()) {
      DB_Save();
      $name = DB_SimpleQuery('SELECT * FROM XBXNAME item_name WHERE item_id = '.$sub['ID'].' AND lang_id = '.$itemlang);
      $desc = DB_SimpleQuery('SELECT * FROM XBXDESC item_desc WHERE item_id = '.$sub['ID'].' AND lang_id = '.$itemlang);
      if ($desc['TEXT']) $desc['TEXT'] = ' - '.$desc['TEXT']; 
      echo '<tr><td><a href="'.$GLOBALS['current'].pl('item='.$sub['ID']).'"'.$target.'>'.$name['TEXT'].'</a>&nbsp;('.$sub['XBINDEX'].')'.$desc['TEXT']."</td></tr>\n";
      DB_Load();
    }
    echo "</table><br/>\n";
    break;
  }
  case 1: {
/*    echo '<table border="1" width="95%" align="center"><tr><td style="background-color: #DFDFFF;">Groups</td></tr>'."\n";
    DB_Query('SELECT *,item_bind.xbindex AS myorder FROM item_bind LEFT JOIN item ON item_bind.target = item.id WHERE item_bind.origin = '.$var_item.' ORDER BY item_bind.xbindex');
    while ($bind=DB_Row()) {
      if (!$bind['id']) break;
      DB_Save();
      $name = DB_SimpleQuery('SELECT * FROM item_name WHERE id = '.$bind['id'].' AND lang = '.$itemlang);
      $desc = DB_SimpleQuery('SELECT * FROM item_desc WHERE id = '.$bind['id'].' AND lang = '.$itemlang);
      if ($desc['TEXT']) $desc['TEXT'] = ' - '.$desc['TEXT']; 
      echo '<tr><td>['.$bind['myorder'].'] <a href="'.$GLOBALS['current'].pl('item='.$bind['id']).'"'.$target.'>'.$name['TEXT'].'</a>&nbsp;('.$bind['xbindex'].')'.$desc['TEXT']."</td></tr>\n";
      DB_Load();
    }
    echo "</table><br/>\n"; */
    break;
  }
  case 2:{
/*    echo '<table border="1" width="95%" align="center"><tr><td style="background-color: #DFDFFF;">Blocks</td></tr>'."\n";
    DB_Query('SELECT *,item_bind.xbindex AS myorder FROM item_bind LEFT JOIN item ON item_bind.target = item.id WHERE item_bind.origin = '.$var_item.' ORDER BY item_bind.xbindex');
    while ($bind=DB_Row()) {
      DB_Save();
      $name = DB_SimpleQuery('SELECT * FROM item_name WHERE id = '.$bind['id'].' AND lang = '.$itemlang);
      $desc = DB_SimpleQuery('SELECT * FROM item_desc WHERE id = '.$bind['id'].' AND lang = '.$itemlang);
      if ($desc['TEXT']) $desc['TEXT'] = ' - '.$desc['TEXT']; 
      echo '<tr><td>['.$bind['myorder'].'] <a href="'.$GLOBALS['current'].pl('item='.$bind['id']).'"'.$target.'>'.$name['TEXT'].'</a>&nbsp;('.$bind['xbindex'].')'.$desc['TEXT']."</td></tr>\n";
      DB_Load();
    }
    echo "</table><br/>\n";
    echo '<table border="1" width="95%" align="center"><tr><td style="background-color: #DFDFFF;">Known usages</td></tr>'."\n";
    DB_Query('SELECT * FROM item_bind LEFT JOIN item ON item_bind.origin = item.id WHERE item_bind.target = '.$var_item);
    while ($bind=DB_Row()) {
      DB_Save();
      $name = DB_SimpleQuery('SELECT * FROM item_name WHERE id = '.$bind['id'].' AND lang = '.$itemlang);
      $desc = DB_SimpleQuery('SELECT * FROM item_desc WHERE id = '.$bind['id'].' AND lang = '.$itemlang);
      if ($desc['TEXT']) $desc['TEXT'] = ' - '.$desc['TEXT']; 
      echo '<tr><td> <a href="'.$GLOBALS['current'].pl('item='.$bind['id']).'"'.$target.'>'.$name['TEXT'].'</a>&nbsp;('.$bind['xbindex'].')'.$desc['TEXT']."</td></tr>\n";
      DB_Load();
    }
    echo "</table><br/>\n"; */
    break;
  } 
  case 3: {
/*    echo '<table border="1" width="95%" align="center"><tr><td style="background-color: #DFDFFF;">Attributes</td></tr>'."\n";
    DB_Query('SELECT *,item_bind.xbindex AS myorder FROM item_bind LEFT JOIN item ON item_bind.target = item.id WHERE item_bind.origin = '.$var_item.' ORDER BY item_bind.xbindex');
    while ($bind=DB_Row()) {
      DB_Save();
      $name = DB_SimpleQuery('SELECT * FROM item_name WHERE id = '.$bind['id'].' AND lang = '.$itemlang);
      $desc = DB_SimpleQuery('SELECT * FROM item_desc WHERE id = '.$bind['id'].' AND lang = '.$itemlang);
      if ($desc['TEXT']) $desc['TEXT'] = ' - '.$desc['TEXT']; 
      echo '<tr><td>['.$bind['myorder'].'] <a href="'.$GLOBALS['current'].pl('item='.$bind['id']).'"'.$target.'>'.$name['TEXT'].'</a>&nbsp;('.$bind['xbindex'].')'.$desc['TEXT']."</td></tr>\n";
      DB_Load();
    }
    echo "</table><br/>\n";
    echo '<table border="1" width="95%" align="center"><tr><td style="background-color: #DFDFFF;">Known usages</td></tr>'."\n";
    DB_Query('SELECT * FROM item_bind LEFT JOIN item ON item_bind.origin = item.id WHERE item_bind.target = '.$var_item);
    while ($bind=DB_Row()) {
      DB_Save();
      $name = DB_SimpleQuery('SELECT * FROM item_name WHERE id = '.$bind['id'].' AND lang = '.$itemlang);
      $desc = DB_SimpleQuery('SELECT * FROM item_desc WHERE id = '.$bind['id'].' AND lang = '.$itemlang);
      if ($desc['TEXT']) $desc['TEXT'] = ' - '.$desc['TEXT']; 
      echo '<tr><td> <a href="'.$GLOBALS['current'].pl('item='.$bind['id']).'"'.$target.'>'.$name['TEXT'].'</a>&nbsp;('.$bind['xbindex'].')'.$desc['TEXT']."</td></tr>\n";
      DB_Load();
    }
    echo "</table><br/>\n"; */
    break;
  }
}

// Show HTML Documentation Extension
$hdoc = DB_SimpleQuery('SELECT item_file.content AS content FROM XBXHDOC item_hdoc, XBXFILE item_file WHERE item_hdoc.item_id = '.$var_item.' AND item_hdoc.lang_id = '.$itemlang.' AND item_hdoc.docfile_id = item_file.id');
echo $hdoc['content'];
/*
// $hdoc = DB_SimpleQuery('SELECT stri.nodepath, item_file.filename, stri.text AS name, item_file.content AS content FROM XBXHDOC item_hdoc, XBXFILE item_file, XBXSTRI stri WHERE item_hdoc.item_id = // '.$var_item.' AND item_hdoc.lang_id = '.$itemlang.' AND item_hdoc.docfile_id = item_file.id AND item_file.node_id = stri.item_id');
if ($hdoc['FILENAME']) {
  $fpath = $hdoc['NODEPATH'].'/'.$hdoc['NAME'].'/'.$hdoc['FILENAME'];
  $fpath = substr($fpath,1,strlen($fpath)-1);
  readfile($fpath);
} */

done(); ?>
