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

// PHP Catalog Web Interface: Plugins Management

$GLOBALS['current']="plugin.php";
extract($_GET, EXTR_PREFIX_ALL, 'var'); extract($_POST, EXTR_PREFIX_ALL, 'var');
if (@$var_lang=='cs') { include "lang/plugin-cs.php"; } else include "lang/plugin-en.php";
$pagename=@$lang['pagename'];
$dateform='j.n.Y';
include "auth.php"; global $auth; include "include.php";
echo '<div style="text-align: right;" align="right">';
if (isset($auth)) {
  echo @$lang['user'].': <a href="account.php'.$GLOBALS['pl'].'">'.$auth['login'].'</a> <a href="item.php'.pl('logout=1&amp;item='.$var_item).'">['.@$lang['logout'].']</a>';
} else echo '<a href="login.php'.$GLOBALS['pl'].'">'.@$lang['login'].'</a>';
echo '</div>'."\n";

$var_plug+=0;
if (isset($var_plug)) {
  if ($var_op=='edit') {
    $item = DB_SimpleQuery('SELECT * FROM XBXPLUGIN plug, XBITEMINFO item_info WHERE item_info.owner_id = plug.owner_id AND plug.id='.$var_plug);
    $var_folder = $item['FOLDER_ID'];
  } else echo '<div>'.@$lang['return_pre'].'<a href="plugin.php'.pl('op=edit&amp;plug='.$var_plug).'">'.@$lang['return'].'</a>'.@$lang['return_post']."</div>\n";
}
if (isset($var_folder)) echo '<div>'.@$lang['return_pre'].'<a href="data.php'.pl('folder='.$var_folder).'">'.@$lang['return'].'</a>'.@$lang['return_post']."</div>\n";

if (@$var_addplugin) {
// Add new plugin
  $var_folder+=0;
  $var_plugin+=0;
  $var_file+=0;
  if (@$var_folder) {
    $item = DB_SimpleQuery("SELECT * FROM XBITEM item, XBITEMINFO item_info WHERE item_info.owner_id = item.id AND item.dtype = 'XBNode' AND item.id=".$var_folder);
    if ($item) {
      $path = $item['path'];
      if ($path[0] == '/') $path = substr($path, 1);
      DB_Query("INSERT INTO XBXPLUGIN (owner_id, pluginindex, PLUGINFILE_ID) VALUES ({$var_folder},{$var_plugin},{$var_file},0,0,0)");
      echo '<div class="message">'.@$lang['addplugin_ok'].".</div>\n";
      $var_op='add';
    } else err_echo(@$lang['error_noplugin']);
  } else err_echo(@$lang['error_noparent']);
} else if (@$var_updateplugin) {
// Update & Replace Plugin
  $var_plug+=0;
  $var_plugin+=0;
  $var_parent+=0;
  $var_plugin+=0;
  $var_file+=0;
  $item = DB_SimpleQuery('SELECT * FROM XBXPLUGIN plug WHERE plug.id='.$var_plug);
  if ($item) {
    DB_Query("UPDATE XBXPLUGIN SET owner_id=".$var_parent.", pluginindex=".$var_plugin.", PLUGINFILE_ID=".$var_file." WHERE id = ".$var_plug);
    echo '<div class="message">'.@$lang['updateplugin_ok'].".</div>\n";
    $var_op='edit';
  } else err_echo(@$lang['error_noplugin']);
} else if (@$var_deleteplugin) {
// Delete Plugin
  if ($var_really) {
    $var_plug+=0;
    $item = DB_SimpleQuery('SELECT * FROM XBXPLUGIN plug WHERE id='.$var_plug);
    if ($item) {
      DB_Query('DELETE FROM XBXPLUGIN WHERE id='.$var_plug);
      echo '<div class="message">'.@$lang['deleteplugin_ok'].".</div>\n";
    } else echo '<div class="error">'.@$lang['deleteplugin_failed'].".</div>\n";
  } else echo '<div class="error">'.@$lang['error_mustpermit'].".</div>\n";
} else if ($var_op=='select') {
// Select target file
  echo '<div>'.@$lang['cancelselect_pre'].'<a href="plugin.php'.pl('op='.$var_for.'&amp;folder='.$var_folder.'&amp;plug='.$var_plug.'&amp;line='.$var_line.'&amp;rev='.$var_rev.'&amp;plugin='.$var_plugin.'&amp;priority='.$var_priority).'">'.@$lang['cancelselect'].'</a>'.@$lang['cancelselect_post']."</div><br/>\n";
  $var_folder+=0;
  $var_node+=0;
  $var_rev+=0;
  $var_plug+=0;
  $var_plugin+=0;
  $var_priority+=0;
  $var_line+=0;
  if ($var_node) {
    $folder = DB_SimpleQuery('SELECT * FROM XBITEM item, XBITEMINFO item_info WHERE item_info.owner_id = item.id AND item.id = '.$var_node);
  } else if (@$var_plug>0) {
    $plug = DB_SimpleQuery('SELECT * FROM XBXPLUGIN WHERE id='.$var_plug);
    if ($plug) {
      $folder = DB_SimpleQuery('SELECT * FROM XBITEM item, XBITEMINFO item_info WHERE item_info.owner_id = item.id AND item.id = '.$plug['folder_id']);
    }
  } else if ($var_folder) {
    $folder = DB_SimpleQuery('SELECT * FROM XBITEM item, XBITEMINFO item_info WHERE item_info.owner_id = item.id AND item.id = '.$var_folder);
  }
  if (!@$folder) $folder = DB_SimpleQuery('SELECT * FROM XBITEM item, XBITEMINFO item_info WHERE item_info.owner_id = item.id AND item.owner_id IS NULL');
  if (@$folder) {
    echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['selectparent_legend']."</legend>\n";

    if ($folder['parent']>0) echo '<a href="plugin.php'.pl('op=select&amp;for='.$var_for.'&amp;folder='.$var_folder.'&amp;plug='.$var_plug.'&amp;plugin='.$var_plugin.'&amp;node='.$folder['parent'].'&amp;line='.$var_line.'&amp;rev='.$var_rev.'&amp;priority='.$var_priority).'">[..]</a><br/>'."\n";
    // Print folders
    DB_Query("SELECT * FROM XBITEM item, XBITEMINFO item_info WHERE item.dtype = 'XBNode' AND item_info.owner_id = item.id AND item.owner_id = ".$folder['owner'].' ORDER BY filename');
    $lastid = 0;
    while ($row=DB_Row()) if ($row['id']!=$lastid) {
      echo '<a href="plugin.php'.pl('op=select&amp;for='.$var_for.'&amp;folder='.$var_folder.'&amp;plug='.$var_plug.'&amp;plugin='.$var_plugin.'&amp;node='.$row['owner'].'&amp;line='.$var_line.'&amp;rev='.$var_rev.'&amp;priority='.$var_priority).'">';
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
      echo '<a href="plugin.php'.pl('op='.$var_for.'&amp;folder='.$var_folder.'&amp;plug='.$var_plug.'&amp;plugin='.$var_plugin.'&amp;file='.$row['id'].'&amp;line='.$var_line.'&amp;rev='.$var_rev.'&amp;priority='.$var_priority).'">';
      echo '['.@$lang['file'].' '.$row['id'].'] '.$row['filename'];
      $lastid = $row['id'];
      echo '</a><br/>'."\n";
    }
    echo '</fieldset>'."\n";
  } else err_echo(@$lang['error_noitem']);
} else if (@$var_addplugline) {
// Add new plugin line editor
  $var_plug+=0;
  $var_line+=0;
  $item = DB_SimpleQuery('SELECT * FROM XBXPLUGIN plug WHERE plug.id='.$var_plug);
  if ($item) {
    DB_Query("INSERT INTO plug_line (plug, line) VALUES ({$var_plug},{$var_line})");
    echo '<div class="message">'.@$lang['addplugline_ok'].".</div>\n";
    $var_op='addline';
  } else err_echo(@$lang['error_noplugin']);
} else if (@$var_op=='delline') {
// Delete plugin line editor
  $var_plug+=0;
  $var_line+=0;
  $item = DB_SimpleQuery('SELECT * FROM plug_line WHERE plug='.$var_plug.' AND line='.$var_line);
  if ($item) {
    DB_Query('DELETE FROM plug_line WHERE id='.$item['id']);
    echo '<div class="message">'.@$lang['delplugline_ok'].".</div>\n";
    $var_op='edit';
  } else echo '<div class="error">'.@$lang['delplugline_failed'].".</div>\n";
} else if (@$var_addplugpane) {
// Add new plugin panel editor
  $var_plug+=0;
  $var_pane+=0;
  $item = DB_SimpleQuery('SELECT * FROM plug WHERE plug.id='.$var_plug);
  if ($item) {
    DB_Query("INSERT INTO plug_pane (plug, pane) VALUES ({$var_plug},{$var_pane})");
    echo '<div class="message">'.@$lang['addplugpane_ok'].".</div>\n";
    $var_op='addpane';
  } else err_echo(@$lang['error_noplugin']);
} else if (@$var_op=='delpane') {
// Delete plugin panel editor
  $var_plug+=0;
  $var_pane+=0;
  $item = DB_SimpleQuery('SELECT * FROM plug_pane WHERE plug='.$var_plug.' AND pane='.$var_pane);
  if ($item) {
    DB_Query('DELETE FROM plug_pane WHERE id='.$item['id']);
    echo '<div class="message">'.@$lang['delplugpane_ok'].".</div>\n";
    $var_op='edit';
  } else echo '<div class="error">'.@$lang['delplugpane_failed'].".</div>\n";
} else if (@$var_addplugtran) {
// Add new plugin transformation
  $var_plug+=0;
  $var_tran+=0;
  $item = DB_SimpleQuery('SELECT * FROM plug WHERE plug.id='.$var_plug);
  if ($item) {
    DB_Query("INSERT INTO plug_tran (plug, tran) VALUES ({$var_plug},{$var_tran})");
    echo '<div class="message">'.@$lang['addplugtran_ok'].".</div>\n";
    $var_op='addtran';
  } else err_echo(@$lang['error_noplugin']);
} else if (@$var_op=='deltran') {
// Delete plugin transformation
  $var_plug+=0;
  $var_tran+=0;
  $item = DB_SimpleQuery('SELECT * FROM plug_tran WHERE plug='.$var_plug.' AND tran='.$var_tran);
  if ($item) {
    DB_Query('DELETE FROM plug_tran WHERE id='.$item['id']);
    echo '<div class="message">'.@$lang['delplugtran_ok'].".</div>\n";
    $var_op='edit';
  } else echo '<div class="error">'.@$lang['delplugtran_failed'].".</div>\n";
} 

if ($var_op=='add') {
// Add new plugin dialog
  if (@$var_folder) {
    $item = DB_SimpleQuery("SELECT * FROM XBITEM item WHERE dtype = 'XBNode' AND id=".$var_folder);
    if ($item) {
//      $prev = DB_SimpleQuery('SELECT MAX(xbindex) FROM item WHERE parent='.$var_parent);

      echo '<form method="post" action="plugin.php'.pl('folder='.$var_folder).'" class="regForm" enctype="multipart/form-data">'."\n";
      echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['addplugin_legend']."</legend>\n";
      echo '  <div>'.@$lang['parentid'].': '.$var_folder."</div>\n";
      echo '  <label>'.@$lang['plugin']."</label><br/>\n";
      echo '  <input class="formText" type="text" name="plugin" value="'.$item['plugin']."\"/><br/>\n";
      echo '  <label>'.@$lang['file']."</label><br/>\n";
      echo '  <input class="formText" type="text" name="file" value="'.$var_file.'"/> <a href="plugin.php'.pl('op=select&amp;for=add&amp;folder='.$var_folder.'&amp;line='.$var_line.'&amp;rev='.$var_rev.'&amp;plug='.$var_plug.'&amp;priority='.$var_priority).'">'.@$lang['select_file'].'</a><br/>'."\n";
      echo '  <input type="submit" name="addplugin" value="'.@$lang['addplugin'].'" class="formButton"/>'."\n";
      echo "</fieldset>\n</form>\n";
    } else err_echo(@$lang['error_noplugin']);
  } else err_echo(@$lang['error_noparent']);
} else if ($var_op=='edit') {
  $var_plug+=0;
  $item = DB_SimpleQuery('SELECT * FROM XBXPLUGIN plug, XBITEMINFO item_info WHERE item_info.owner_id = plug.folder_id AND plug.id='.$var_plug);
  if ($item) {
    if (!isset($var_plugin)) { $var_plugin = $item['PLUGININDEX']; } else $var_plugin+=0; 
    if (!isset($var_file)) { $var_file = $item['PLUGINFILE_ID']; } else $var_file+=0; 

    echo '<form method="post" action="plugin.php'.pl('folder='.$var_folder.'&amp;plug='.$var_plug).'" class="regForm" enctype="multipart/form-data">'."\n";
    echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['updateplugin_legend']."</legend>\n";
    echo '  <label>'.@$lang['parentid']."</label><br/>\n";
    echo '  <input class="formText" type="text" name="parent" readonly="readonly" value="'.$item['folder_id']."\"/><br/>\n";
    echo '  <label>'.@$lang['plugin']."</label><br/>\n";
    echo '  <input class="formText" type="text" name="plugin" value="'.$var_plugin."\"/><br/>\n";
    echo '  <label>'.@$lang['file']."</label><br/>\n";
    echo '  <input class="formText" type="text" name="file" value="'.$var_file.'"/> <a href="plugin.php'.pl('op=select&amp;for=edit&amp;folder='.$item['folder_id'].'&amp;plug='.$var_plug.'&amp;line='.$var_line.'&amp;rev='.$var_rev.'&amp;plugin='.$var_plugin.'&amp;priority='.$var_priority).'">'.@$lang['select_file'].'</a><br/>'."\n";
    echo '  <input type="submit" name="updateplugin" value="'.@$lang['updateplugin'].'" class="formButton"/>'."\n";
    echo "</fieldset>\n</form>\n";

    echo '<form method="post" action="plugin.php'.pl('plug='.$var_plug.'&amp;folder='.$item['item_id']).'" class="regForm">'."\n";
    echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['deleteplugin_legend']."</legend>\n";
    echo '  <input class="formText" type="checkbox" name="really"/>'.@$lang['deleteplugin_really']."<br/>\n";
    echo '  <input type="submit" name="deleteplugin" value="'.@$lang['deleteplugin'].'" class="formButton"/>'."\n";
    echo "</fieldset>\n</form>\n";
  } else err_echo(@$lang['error_noplugin']);

  // Print line editors
  DB_Query('SELECT * FROM plug_line WHERE plug = '.$var_plug);
  echo '  <fieldset><legend>'.@$lang['pluglines']."</legend>\n";
  if (DB_NumRows()==0) echo '    '.@$lang['pluglines_notfound']."<br/>\n"; 
  while ($row = DB_Row()) {
    echo '    '.@$lang['item_plugline'].' ['.$row['id'].'] : '.$row['line'];
    if (@$auth) echo ' <a href="plugin.php'.pl('op=delline&amp;plug='.$var_plug.'&amp;line='.$row['line']).'">'.@$lang['del_plugline'].'</a>';
    echo "<br/>\n";
  }
  if (@$auth) {
    echo '    <a href="plugin.php'.pl('op=addline&amp;plug='.$var_plug).'">'.@$lang['add_plugline']."</a>\n";
  }
  echo "  </fieldset>\n";

  // Print panel editors
  DB_Query('SELECT * FROM plug_pane WHERE plug = '.$var_plug);
  echo '  <fieldset><legend>'.@$lang['plugpanes']."</legend>\n";
  if (DB_NumRows()==0) echo '    '.@$lang['plugpanes_notfound']."<br/>\n"; 
  while ($row = DB_Row()) {
    echo '    '.@$lang['item_plugpane'].' ['.$row['id'].'] : '.$row['pane'];
    if (@$auth) echo ' <a href="plugin.php'.pl('op=delpane&amp;plug='.$var_plug.'&amp;pane='.$row['pane']).'">'.@$lang['del_plugpane'].'</a>';
    echo "<br/>\n";
  }
  if (@$auth) {
    echo '    <a href="plugin.php'.pl('op=addpane&amp;plug='.$var_plug).'">'.@$lang['add_plugpane']."</a>\n";
  }
  echo "  </fieldset>\n";

  // Print transformations
  DB_Query('SELECT * FROM plug_tran WHERE plug = '.$var_plug);
  echo '  <fieldset><legend>'.@$lang['plugtrans']."</legend>\n";
  if (DB_NumRows()==0) echo '    '.@$lang['plugtrans_notfound']."<br/>\n"; 
  while ($row = DB_Row()) {
    echo '    '.@$lang['item_plugtran'].' ['.$row['id'].'] : '.$row['tran'];
    if (@$auth) echo ' <a href="plugin.php'.pl('op=deltran&amp;plug='.$var_plug.'&amp;tran='.$row['tran']).'">'.@$lang['del_plugtran'].'</a>';
    echo "<br/>\n";
  }
  if (@$auth) {
    echo '    <a href="plugin.php'.pl('op=addtran&amp;plug='.$var_plug).'">'.@$lang['add_plugtran']."</a>\n";
  }
  echo "  </fieldset>\n";
} else if ($var_op=='addline') {  
// Add new plugin line editor dialog
  if (@$var_plug) {
    $item = DB_SimpleQuery('SELECT *, (SELECT MAX(line)+1 FROM plug_line WHERE plug_line.plug = plug.id) AS line FROM plug WHERE id='.$var_plug);
    if ($item) {
      echo '<form method="post" action="plugin.php'.pl('plug='.$var_plug).'" class="regForm" enctype="multipart/form-data">'."\n";
      echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['addplugin_legend']."</legend>\n";
      echo '  <div>'.@$lang['plugid'].': '.$var_plug."</div>\n";
      echo '  <label>'.@$lang['order']."</label><br/>\n";
      echo '  <input class="formText" type="text" name="line" value="'.$item['line']."\"/><br/>\n";
      echo '  <input type="submit" name="addplugline" value="'.@$lang['add_plugline'].'" class="formButton"/>'."\n";
      echo "</fieldset>\n</form>\n";
    } else err_echo(@$lang['error_noplugin']);
  } else err_echo(@$lang['error_noparent']);
} else if ($var_op=='addpane') {  
// Add new plugin panel editor dialog
  if (@$var_plug) {
    $item = DB_SimpleQuery('SELECT *, (SELECT MAX(pane)+1 FROM plug_pane WHERE plug_pane.plug = plug.id) AS pane FROM plug WHERE id='.$var_plug);
    if ($item) {
      echo '<form method="post" action="plugin.php'.pl('plug='.$var_plug).'" class="regForm" enctype="multipart/form-data">'."\n";
      echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['addplugin_legend']."</legend>\n";
      echo '  <div>'.@$lang['plugid'].': '.$var_plug."</div>\n";
      echo '  <label>'.@$lang['order']."</label><br/>\n";
      echo '  <input class="formText" type="text" name="pane" value="'.$item['pane']."\"/><br/>\n";
      echo '  <input type="submit" name="addplugpane" value="'.@$lang['add_plugpane'].'" class="formButton"/>'."\n";
      echo "</fieldset>\n</form>\n";
    } else err_echo(@$lang['error_noplugin']);
  } else err_echo(@$lang['error_noparent']);
} else if ($var_op=='addtran') {  
// Add new plugin transformation dialog
  if (@$var_plug) {
    $item = DB_SimpleQuery('SELECT *, (SELECT MAX(tran)+1 FROM plug_tran WHERE plug_tran.plug = plug.id) AS tran FROM plug WHERE id='.$var_plug);
    if ($item) {
      echo '<form method="post" action="plugin.php'.pl('plug='.$var_plug).'" class="regForm" enctype="multipart/form-data">'."\n";
      echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['addplugin_legend']."</legend>\n";
      echo '  <div>'.@$lang['plugid'].': '.$var_plug."</div>\n";
      echo '  <label>'.@$lang['order']."</label><br/>\n";
      echo '  <input class="formText" type="text" name="tran" value="'.$item['tran']."\"/><br/>\n";
      echo '  <input type="submit" name="addplugtran" value="'.@$lang['add_plugtran'].'" class="formButton"/>'."\n";
      echo "</fieldset>\n</form>\n";
    } else err_echo(@$lang['error_noplugin']);
  } else err_echo(@$lang['error_noparent']);
}
done(); ?>
