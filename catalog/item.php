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

// PHP Catalog Web Interface: Item's Management

$GLOBALS['current']="item.php";
import_request_variables('gP','var_');

if (@$var_lang=='cs') { include "lang/item-cs.php"; } else include "lang/item-en.php";
$pagename=@$lang['pagename'];
$dateform='j.n.Y';
include "auth.php"; global $auth; include "include.php";
include 'include-menu.php';

$dtype_class = array('XBNode' => 'node',
  'XBFormatSpec' => 'spec', 'XBGroupSpec' => 'spec', 'XBBlockSpec' => 'spec',
  'XBFormatCons' => 'def','XBFormatJoin' => 'def',
  'XBGroupCons' => 'def','XBGroupJoin' => 'def',
  'XBBlockCons' => 'def','XBBlockJoin' => 'def', 'XBBlockListCons' => 'def', 'XBBlockListJoin' => 'def'
);

$lang_item = DB_SimpleQuery("SELECT * FROM XBXLANGUAGE WHERE langcode='".$var_langcode."'");
$lang_id = $lang_item['ID'];

// Acquiring item
$var_item+=0;
$var_type+=0;
unset($item);
unset($spec);
unset($node);
if ($var_item==0) {
  if (($var_type<0)||($var_type>2)) { err_echo(@$lang['error_unknowtype']); }
  else {
    $item = DB_SimpleQuery("SELECT * FROM XBITEM item WHERE dtype='XBNode' AND owner_id IS NULL");
    if (!$item) err_echo(@$lang['error_missingroot']);
  }
} else {
  $item = DB_SimpleQuery('SELECT * FROM XBITEM item WHERE id='.$var_item);
  if (!$item) err_echo(@$lang['error_noitem']);
}

if (@$item['ID']>0) {
  // Print list of children or siblings
  echo '<div id="sidebar">'."\n";
  echo '  <fieldset><legend>'.@$lang['sbar_nodes']."</legend>\n";
  if ($dtype_class[$item['DTYPE']]!='node') {
    if ($dtype_class[$item['DTYPE']]=='def') {
      $spec = DB_SimpleQuery('SELECT * FROM XBITEM item WHERE id = '.$item['OWNER_ID']);
      $node = DB_SimpleQuery('SELECT * FROM XBITEM item WHERE id = '.$spec['OWNER_ID']);
    } else {
      $spec = $item;
      $node = DB_SimpleQuery('SELECT * FROM XBITEM item WHERE id = '.$item['OWNER_ID']);
    }
    echo '<a href="'.pl('item='.$node['ID']).'">[.]</a><br/>'."\n";
  } else $node = $item; // $node &= $item;
  // Link to parent node
  if ($node['OWNER_ID']>0) echo '<a href="'.pl('item='.$node['OWNER_ID']).'">[..]</a><br/>'."\n";
  // Print nodes
  DB_Query('SELECT *, item.id AS id FROM XBITEM item LEFT JOIN XBXNAME name ON item.id = name.item_id AND name.lang_id = '.$lang_id.' WHERE (owner_id = '.$node['ID'].") AND dtype = 'XBNode' ORDER BY xbindex");
  $lastid = 0;
  while ($row=DB_Row()) if ($row['id']!=$lastid) {
    if ($row['ID']==$var_item) echo '<span style="background-color: yellow;">';
    echo '<a href="'.pl('item='.$row['id']).'">';
    echo '['.@$lang['type_'.$row['DTYPE']].' '.$row['XBINDEX'].']';
    echo '</a> '.$row['TEXT'];
    if ($row['ID']==$var_item) echo '</span>';
    echo "<br/>\n";
    $lastid = $row['id'];
  }
  echo "  </fieldset>\n";

  echo '  <fieldset><legend>'.@$lang['sbar_formats']."</legend>\n";
  // Print format specifications
  DB_Query('SELECT *, item.id AS id FROM XBITEM item LEFT JOIN XBXNAME item_name ON item.id = item_name.item_id AND item_name.lang_id = '.$lang_id.' WHERE (owner_id = '.$node['ID'].") AND dtype = 'XBFormatSpec' ORDER BY xbindex");
  $lastid = 0;
  while ($row=DB_Row()) if ($row['id']!=$lastid) {
    if ($row['id']==$var_item) {
      echo '<span style="background-color: yellow;">';
    } else if ($row['id']==$spec['ID']) echo '<span style="background-color: lightgreen;">';
    echo '<a href="'.pl('item='.$row['id']).'">';
    echo '['.@$lang['type_'.$row['DTYPE']].' '.$row['XBINDEX'].']';
    echo '</a> '.$row['TEXT'];
    if (($row['id']==$var_item)||($row['id']==$spec['ID'])) echo '</span>';
    echo "<br/>\n";
    $lastid = $row['id'];
  }
  echo "  </fieldset>\n";

  echo '  <fieldset><legend>'.@$lang['sbar_groups']."</legend>\n";
  // Print group specifications
  DB_Query('SELECT *, item.id AS id FROM XBITEM item LEFT JOIN XBXNAME item_name ON item.id = item_name.item_id AND item_name.lang_id = '.$lang_id.' WHERE (owner_id = '.$node['ID'].") AND dtype = 'XBGroupSpec' ORDER BY xbindex");
  $lastid = 0;
  while ($row=DB_Row()) if ($row['id']!=$lastid) {
    if ($row['id']==$var_item) {
      echo '<span style="background-color: yellow;">';
    } else if ($row['id']==$spec['ID']) echo '<span style="background-color: lightgreen;">';
    echo '<a href="'.pl('item='.$row['id']).'">';
    echo '['.@$lang['type_'.$row['DTYPE']].' '.$row['XBINDEX'].']';
    echo '</a> '.$row['TEXT'];
    if (($row['id']==$var_item)||($row['id']==$spec['ID'])) echo '</span>';
    echo "<br/>\n";
    $lastid = $row['id'];
  }
  echo "  </fieldset>\n";

  echo '  <fieldset><legend>'.@$lang['sbar_blocks']."</legend>\n";
  // Print block specifications
  DB_Query('SELECT *, item.id AS id FROM XBITEM item LEFT JOIN XBXNAME item_name ON item.id = item_name.item_id AND item_name.lang_id = '.$lang_id.' WHERE (owner_id = '.$node['ID'].") AND dtype = 'XBBlockSpec' ORDER BY xbindex");
  $lastid = 0;
  while ($row=DB_Row()) if ($row['id']!=$lastid) {
    if ($row['id']==$var_item) {
      echo '<span style="background-color: yellow;">';
    } else if ($row['id']==$spec['ID']) echo '<span style="background-color: lightgreen;">';
    echo '<a href="'.pl('item='.$row['id']).'">';
    echo '['.@$lang['type_'.$row['DTYPE']].' '.$row['XBINDEX'].']';
    echo '</a> '.$row['TEXT'];
    if (($row['id']==$var_item)||($row['id']==$spec['ID'])) echo '</span>';
    echo "<br/>\n";
    $lastid = $row['id'];
  }
  echo "  </fieldset>\n";

  echo '  <fieldset><legend>'.@$lang['sbar_limits']."</legend>\n";
  // Print limitations
  DB_Query('SELECT *, item.id AS id FROM XBITEM item LEFT JOIN XBXNAME item_name ON item.id = item_name.item_id AND item_name.lang_id = '.$lang_id.' WHERE (owner_id = '.$node['ID'].") AND dtype = 'XBLimitSpec' ORDER BY xbindex");
  $lastid = 0;
  while ($row=DB_Row()) if ($row['id']!=$lastid) {
    if ($row['id']==$var_item) echo '<span style="background-color: yellow;">';
    echo '<a href="'.pl('item='.$row['id']).'">';
    echo '['.@$lang['type_'.$row['DTYPE']].' '.$row['XBINDEX'].']';
    echo '</a> '.$row['TEXT'];
    if ($row['id']==$var_item) echo '</span>';
    echo "<br/>\n";
    $lastid = $row['id'];
  }
  echo "  </fieldset>\n";

  if ($spec) {
    echo '  <fieldset><legend>'.@$lang['sbar_defs']."</legend>\n";
    // Print spec definition
    DB_Query('SELECT *, item.id AS id FROM XBITEM item LEFT JOIN XBXNAME item_name ON item.id = item_name.item_id AND item_name.lang_id = '.$lang_id.' WHERE (owner_id = '.$spec['ID'].") AND (dtype LIKE '%Cons' OR dtype LIKE '%Join') ORDER BY xbindex");
    $lastid = 0;
    while ($row=DB_Row()) if ($row['id']!=$lastid) {
      if ($row['id']==$var_item) echo '<span style="background-color: yellow;">';
      echo '<a href="'.pl('item='.$row['id']).'">';
      echo '['.@$lang['type_'.$row['DTYPE']].' '.$row['XBINDEX'].']';
      echo '</a> '.$row['TEXT'];
      if ($row['id']==$var_item) echo '</span>';
      echo "<br/>\n";
      $lastid = $row['id'];
    }
    echo "  </fieldset>\n";
  }

  if (@$auth) {
    echo '<p><a href="node.php'.pl('op=add&amp;parent='.$node['ID']).'">'.@$lang['add_node']."</a><br/>\n"; 
    echo '<a href="spec.php'.pl('op=add&amp;parent='.$node['ID']).'">'.@$lang['add_spec']."</a></p>\n"; 
  }

  echo '</div>'."\n";

  // Print information about current item
  // Print basic informations
  echo '<div id="mainbar">'."\n";
  echo '  <fieldset><legend>'.@$lang['item_basic']."</legend>\n";
  echo '    '.@$lang['item_basic_type'].': '.@$lang['desc_'.$item['DTYPE']]."<br/>\n";
  echo '    '.@$lang['item_basic_index'].': '.$item['ID']."<br/>\n";
  echo '    '.@$lang['item_basic_parentid'].': '.$item['OWNER_ID']."<br/>\n";
  echo '    '.@$lang['item_basic_xbindex'].': '.$item['XBINDEX']."<br/>\n";
  echo '<a href="browse.php'.pl('item='.$item['ID']).'">'.@$lang['browse_item']."</a>\n";
  if (@$auth) {
    if ($item['DTYPE']=='XBNode') {
      echo '    <a href="node.php'.pl('op=edit&amp;node='.$item['ID']).'">'.@$lang['edit_item']."</a>\n";
    } else {
      echo '    <a href="spec.php'.pl('op=edit&amp;spec='.$item['ID']).'">'.@$lang['edit_item']."</a>\n";
    }
  }
  echo "  </fieldset>\n";

  // Print REVISION extension
  if (($item['DTYPE']=='XBFormatSpec')||($item['DTYPE']=='XBGroupSpec')||($item['DTYPE']=='XBBlockSpec')) {
    DB_Query('SELECT *, item.id AS id FROM XBITEM item, XBREV item_rev WHERE item.id = item_rev.id AND item.owner_id = '.$item['ID'].' ORDER BY xblimit');
    echo '  <fieldset><legend>'.@$lang['item_rev']."</legend>\n";
    if (DB_NumRows()==0) echo '    '.@$lang['value_notpresented']."\n"; 
    while ($row = DB_Row()) {
      echo '    '.@$lang['item_rev_text'].' ['.$row['XBINDEX'].'] : '.$row['XBLIMIT'];
      if (@$auth) echo ' <a href="rev.php'.pl('op=edit&amp;rev='.$row['id']).'">'.@$lang['edit_rev'].'</a>';
      echo "<br/>\n";
    }
    if (@$auth) {
      echo '    <a href="rev.php'.pl('op=add&amp;item='.$item['ID']).'">'.@$lang['add_rev']."</a>\n";
    }
    echo "  </fieldset>\n";
  }

  // Print DEFINITIONS
  if (($item['DTYPE']=='XBFormatSpec')||($item['DTYPE']=='XBGroupSpec')||($item['DTYPE']=='XBBlockSpec')) {
    $item_bind =DB_Query('SELECT *, item.id AS ID, item_name.text AS name, item.dtype AS dtype, item.xbindex AS xbindex FROM XBITEM item, XBSPECDEF def LEFT JOIN XBITEM item_rev ON item_rev.id = def.target_id LEFT JOIN XBXNAME item_name ON item_name.item_id = item_rev.owner_id WHERE item.id = def.id AND item.owner_id = '.$item['ID'].' ORDER BY item.xbindex');
    echo '  <fieldset><legend>'.@$lang['item_bind'].' ('.@$lang['level1'].")</legend>\n";
    if (DB_NumRows()==0) echo '    '.@$lang['value_notpresented']."\n";
    while ($row = DB_Row()) {
      if (($row['dtype']=='XBBlockCons')||($row['dtype']=='XBGroupCons')||($row['dtype']=='XBFormatCons')) {
        echo '    '.@$lang['item_bind_consist'].': ';
        if ($row['TARGET_ID']>0) {
          echo $row['name'].' <a href="item.php'.pl('item='.$row['OWNER_ID']).'">['.@$lang['item_bind_target'].' '.$row['OWNER_ID'].']</a>';
        } else echo @$lang['item_bind_data'];
      } else if (($row['dtype']=='XBBlockJoin')||($row['dtype']=='XBGroupJoin')||($row['dtype']=='XBFormatJoin')) {
        echo '    '.@$lang['item_bind_join'].': ';
        if ($row['TARGET_ID']>0) {
          echo $row['name'].' <a href="item.php'.pl('item='.$row['OWNER_ID']).'">['.@$lang['item_bind_target'].' '.$row['OWNER_ID'].']</a>';
        } else echo @$lang['item_bind_attrib'];
      } else if (($row['dtype']=='XBBlockListCons')||($row['dtype']=='XBGroupListCons')||($row['dtype']=='XBFormatListCons')) {
        echo '    '.@$lang['item_bind_listconsist'].': ';
        if ($row['TARGET_ID']>0) {
          echo $row['name'].' <a href="item.php'.pl('item='.$row['OWNER_ID']).'">['.@$lang['item_bind_target'].' '.$row['OWNER_ID'].']</a>';
        } else echo @$lang['item_bind_data'];
      } else if (($row['dtype']=='XBBlockListJoin')||($row['dtype']=='XBGroupListJoin')||($row['dtype']=='XBFormatListJoin')) {
        echo '    '.@$lang['item_bind_listjoin'].': ';
        if ($row['TARGET_ID']>0) {
          echo $row['name'].' <a href="item.php'.pl('item='.$row['OWNER_ID']).'">['.@$lang['item_bind_target'].' '.$row['OWNER_ID'].']</a>';
        } else echo @$lang['item_bind_attrib'];
      } else if ($row['dtype']=='XBBind') {
        echo '    '.@$lang['item_bind'].': ';
        if ($row['TARGET_ID']>0) {
          echo $row['name'].' <a href="item.php'.pl('item='.$row['OWNER_ID']).'">['.@$lang['item_bind_target'].' '.$row['OWNER_ID'].']</a>';
        } else echo @$lang['item_bind_attrib'];
      }
      echo ' '.@$lang['item_bind_xbindex'].' '.$row['xbindex'];
      if (@$auth) {
        echo '    <a href="specdef.php'.pl('op=edit&amp;def='.$row['ID']).'&amp;btype='.$row['dtype'].'">'.@$lang['edit_bind']."</a>";
      }
      echo "<br/>\n";
    }
    if (@$auth) {
      echo '    <a href="specdef.php'.pl('op=add&amp;item='.$item['ID']).'">'.@$lang['add_bind']."</a>\n";
    }
    echo "  </fieldset>\n";
  }

  // Print LIMITATIONS extension
  if ($item['DTYPE']=='XBBlockSpec') {
    DB_Query('SELECT item_name.text AS NAME, item_limi.* FROM XBITEMLIMI item_limi LEFT JOIN XBXNAME item_name ON item_name.item_id = item_limi.target_id WHERE item_limi.owner_id = '.$item['ID'].' ORDER BY xbindex');
    echo '  <fieldset><legend>'.@$lang['item_limi']."</legend>\n";
    if (DB_NumRows()==0) echo '    '.@$lang['value_notpresented']."\n"; 
    while ($row = DB_Row()) {
      echo '    '.@$lang['item_limi_name'].' ['.$row['XBINDEX'].'] : '.$row['NAME'];
      if (@$auth) echo ' <a href="limi.php'.pl('op=edit&amp;limi='.$row['ID']).'">'.@$lang['edit_limi'].'</a>';
      echo "<br/>\n";
    }
    if (@$auth) {
      echo '    <a href="limi.php'.pl('op=add&amp;item='.$item['ID']).'">'.@$lang['add_limi']."</a>\n";
    }
    echo "  </fieldset>\n";
  }

  // Print TRANSFORMATIONS extension
  if ($item['DTYPE']=='XBBlockSpec') {
    DB_Query('SELECT item_tran.*, item_name.text FROM XBTRAN item_tran, XBREV item_rev, XBXNAME item_name WHERE item_rev.id = item_tran.target_id AND item_name.id = item_rev.id AND item_tran.owner_id = '.$item['ID']);
    echo '  <fieldset><legend>'.@$lang['item_tran']."</legend>\n";
    if (DB_NumRows()==0) echo '    '.@$lang['value_notpresented']."\n"; 
    while ($row = DB_Row()) {
      echo '    '.@$lang['item_tran_name'].' ['.$row['TEXT'].']';
      if (@$auth) echo ' <a href="tran.php'.pl('op=edit&amp;tran='.$row['ID']).'">'.@$lang['edit_tran'].'</a>';
      echo "<br/>\n";
    }
    if (@$auth) {
      echo '    <a href="tran.php'.pl('op=add&amp;item='.$item['ID']).'">'.@$lang['add_tran']."</a>\n";
    }
    echo "  </fieldset>\n";
  }

  // Print STRI extension
  DB_Query('SELECT * FROM XBXSTRI stri WHERE stri.item_id = '.$item['ID']);
  echo '  <fieldset><legend>'.@$lang['item_stri']."</legend>\n";
  if (DB_NumRows()==0) echo '    '.@$lang['value_notpresented']."\n"; 
  while ($row = DB_Row()) {
    echo '    '.@$lang['item_stri_text'].' : '.$row['TEXT'];
    if (@$auth) echo ' <a href="stri.php'.pl('op=edit&amp;item='.$item['ID']).'">'.@$lang['edit_stri'].'</a>';
    echo "<br/>\n";
  }
  if (@$auth) {
    echo '    <a href="stri.php'.pl('op=add&amp;item='.$item['ID']).'">'.@$lang['add_stri']."</a>\n";
  }
  echo "  </fieldset>\n";

  // Print NAME extension
  DB_Query('SELECT * FROM XBXNAME item_name,XBXLANGUAGE language WHERE item_name.item_id = '.$item['ID'].' AND language.id = item_name.lang_id');
  echo '  <fieldset><legend>'.@$lang['item_name']."</legend>\n";
  if (DB_NumRows()==0) echo '    '.@$lang['value_notpresented']."\n";
  while ($row = DB_Row()) {
    echo '    '.@$lang['item_name_name'].' ['.$row['LANGCODE'].'] : '.$row['TEXT'];
    if (@$auth) echo ' <a href="name.php'.pl('op=edit&amp;item='.$item['ID'].'&amp;langid='.$row['LANG_ID']).'">'.@$lang['edit_name'].'</a>';
    echo "<br/>\n";
  }
  if (@$auth) {
    echo '    <a href="name.php'.pl('op=add&amp;item='.$item['ID']).'">'.@$lang['add_name']."</a>\n";
  }
  echo "  </fieldset>\n";

  // Print DESC extension
  DB_Query('SELECT * FROM XBXDESC item_desc, XBXLANGUAGE language WHERE item_desc.item_id = '.$item['ID'].' AND language.id = item_desc.lang_id');
  echo '  <fieldset><legend>'.@$lang['item_desc']."</legend>\n";
  if (DB_NumRows()==0) echo '    '.@$lang['value_notpresented']."\n"; 
  while ($row = DB_Row()) {
    echo '    '.@$lang['item_desc_text'].' ['.$row['LANGCODE'].'] : '.$row['TEXT'];
    if (@$auth) echo ' <a href="desc.php'.pl('op=edit&amp;item='.$item['ID'].'&amp;langid='.$row['LANG_ID']).'">'.@$lang['edit_desc'].'</a>';
    echo "<br/>\n";
  }
  if (@$auth) {
    echo '    <a href="desc.php'.pl('op=add&amp;item='.$item['ID']).'">'.@$lang['add_desc']."</a>\n";
  }
  echo "  </fieldset>\n";

  // Print LINE extension
  if ($item['DTYPE']=='XBBlockSpec') {
    DB_Query('SELECT item_line.*, item_name.text FROM XBXBLOCKLINE item_line, XBITEM item_rev, XBXNAME item_name WHERE item_rev.id = item_line.blockrev_id AND item_name.id = item_rev.owner_id AND item_rev.owner_id = '.$item['ID']);
    echo '  <fieldset><legend>'.@$lang['item_line']."</legend>\n";
    if (DB_NumRows()==0) echo '    '.@$lang['value_notpresented']."\n"; 
    while ($row = DB_Row()) {
      echo '    ['.$row['ID'].'] '.$row['priority'];
      if (@$auth) echo ' <a href="line.php'.pl('op=edit&amp;line='.$row['ID']).'">'.@$lang['edit_line'].'</a>';
      echo "<br/>\n";
    }
    if (@$auth) {
      echo '    <a href="line.php'.pl('op=add&amp;item='.$item['ID']).'">'.@$lang['add_line']."</a>\n";
    }
    echo "  </fieldset>\n";
  }

  // Print PANE extension
  if ($item['DTYPE']=='XBBlockSpec') {
    DB_Query('SELECT item_pane.* FROM XBXBLOCKPANE item_pane, XBITEM item_rev WHERE item_rev.id = item_pane.blockrev_id AND item_rev.owner_id = '.$item['ID']);
    echo '  <fieldset><legend>'.@$lang['item_pane']."</legend>\n";
    if (DB_NumRows()==0) echo '    '.@$lang['value_notpresented']."\n"; 
    while ($row = DB_Row()) {
      echo '    ['.$row['ID'].']';
      if (@$auth) echo ' <a href="pane.php'.pl('op=edit&amp;pane='.$row['ID']).'">'.@$lang['edit_pane'].'</a>';
      echo "<br/>\n";
    }
    if (@$auth) {
      echo '    <a href="pane.php'.pl('op=add&amp;item='.$item['ID']).'">'.@$lang['add_pane']."</a>\n";
    }
    echo "  </fieldset>\n";
  }

  // Print INFO extension
  $item_info =DB_SimpleQuery('SELECT * FROM XBITEMINFO item_info WHERE owner_id = '.$item['ID']);
  echo '  <fieldset><legend>'.@$lang['item_info']."</legend>\n";
  if ($item_info) {
    echo '    '.@$lang['item_info_owner'].': '.$item_info['owner']."<br/>\n";
    echo '    '.@$lang['item_info_created'].': '.date($dateform,$item_info['created'])."<br/>\n";
    echo '    '.@$lang['item_info_updated'].': '.date($dateform,$item_info['updated'])."<br/>\n";
    echo '    '.@$lang['item_info_filename'].': '.$item_info['FILENAME']."<br/>\n";
    if (@$auth) {
      echo '    <a href="info.php'.pl('op=edit&amp;item='.$item['ID'].'&amp;info='.$item_info['id']).'">'.@$lang['edit_info']."</a>\n";
      echo '    <a href="info.php'.pl('op=remove&amp;item='.$item['ID'].'&amp;info='.$item_info['id']).'">'.@$lang['remove_info']."</a>\n";
    }
  } else {
    echo '    '.@$lang['value_notpresented']."\n";
    if (@$auth) {
      echo '    <a href="info.php'.pl('op=add&amp;item='.$item['ID']).'">'.@$lang['add_info']."</a>\n";
    }
  }
  echo "  </fieldset>\n";

  // Print ICON extension
  DB_Query('SELECT * FROM XBXICON item_icon WHERE item_icon.owner_id = '.$item['ID']);
  echo '  <fieldset><legend>'.@$lang['item_icon']."</legend>\n";
  if (DB_NumRows()==0) echo '    '.@$lang['value_notpresented']."\n"; 
  while ($row = DB_Row()) {
    echo '    '.@$lang['item_icon_text'].' ['.$row['ID'].'] ';
    echo ' <a href="icon.php'.pl('op=view&amp;item='.$item['ID'].'&amp;icon='.$row['ID']).'">'.@$lang['view_icon'].'</a>';
    if (@$auth) echo ' <a href="icon.php'.pl('op=edit&amp;item='.$item['ID'].'&amp;icon='.$row['ID']).'">'.@$lang['edit_icon'].'</a>';
    echo "<br/>\n";
  }
  if (@$auth) {
    echo '    <a href="icon.php'.pl('op=add&amp;item='.$item['ID']).'">'.@$lang['add_icon']."</a>\n";
  }
  echo "  </fieldset>\n";

  // Print HDOC extension
  DB_Query('SELECT * FROM XBXLANGUAGE language, XBXHDOC item_hdoc WHERE language.id = item_hdoc.lang_id AND item_hdoc.item_id = '.$item['ID']);
  echo '  <fieldset><legend>'.@$lang['item_hdoc']."</legend>\n";
  if (DB_NumRows()==0) echo '    '.@$lang['value_notpresented']."\n"; 
  while ($row = DB_Row()) {
    echo '    '.@$lang['item_hdoc_text'].' ['.$row['code'].'] ';
    echo ' <a href="hdoc.php'.pl('op=view&amp;item='.$item['id'].'&amp;hdoc='.$row['id']).'">'.@$lang['view_hdoc'].'</a>';
    if (@$auth) echo ' <a href="hdoc.php'.pl('op=edit&amp;item='.$item['id'].'&amp;hdoc='.$row['id']).'">'.@$lang['edit_hdoc'].'</a>';
    echo "<br/>\n";
  }
  if (@$auth) {
    echo '    <a href="hdoc.php'.pl('op=add&amp;item='.$item['id']).'">'.@$lang['add_hdoc']."</a>\n";
  }
  echo "  </fieldset>\n";
/*
  // Print SUPP extension
  DB_Query('SELECT * FROM item_supp WHERE item_supp.item_id = '.$item['id']);
  echo '  <fieldset><legend>'.@$lang['item_supp']."</legend>\n";
  if (DB_NumRows()==0) echo '    '.@$lang['value_notpresented']."\n"; 
  while ($row = DB_Row()) {
    echo '    '.@$lang['item_supp_text'].' ['.$row['id'].'] ';
    echo ' <a href="supp.php'.pl('op=view&amp;item='.$item['id'].'&amp;supp='.$row['id']).'">'.@$lang['view_supp'].'</a>';
    if (@$auth) echo ' <a href="supp.php'.pl('op=edit&amp;item='.$item['id'].'&amp;supp='.$row['id']).'">'.@$lang['edit_supp'].'</a>';
    echo "<br/>\n";
  }
  if (@$auth) {
    echo '    <a href="supp.php'.pl('op=add&amp;item='.$item['id']).'">'.@$lang['add_supp']."</a>\n";
  }
  echo "  </fieldset>\n";
*/
  echo '</div>'."\n"; }
done(); ?>
