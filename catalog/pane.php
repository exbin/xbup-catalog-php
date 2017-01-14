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

// PHP Catalog Web Interface: Panel Editor's Management

$GLOBALS['current']="pane.php";
extract($_GET, EXTR_PREFIX_ALL, 'var'); extract($_POST, EXTR_PREFIX_ALL, 'var');
if (@$var_lang=='cs') { include "lang/pane-cs.php"; } else include "lang/pane-en.php";
// $GLOBALS['stylesheets']='<link rel="stylesheet" href="styles/news.css" type="text/css" media="screen,projection" />'."\n";
$pagename=@$lang['pagename'];
$dateform='j.n.Y';
include "auth.php"; global $auth; include "include.php";  
echo '<div style="text-align: right;" align="right">';
if (isset($auth)) {
  echo @$lang['user'].': <a href="account.php'.$GLOBALS['pl'].'">'.$auth['login'].'</a> <a href="item.php'.pl('logout=1&amp;item='.$var_item).'">['.@$lang['logout'].']</a>';
} else echo '<a href="login.php'.$GLOBALS['pl'].'">'.@$lang['login'].'</a>';
echo '</div>'."\n";

if (@$var_addpane) {
// Add new panel
  $var_rev+=0;
  if (@$var_rev) {
    $var_plug+=0;
    $var_priority+=0;
    $item = DB_SimpleQuery('SELECT * FROM item_rev WHERE item_rev.id='.$var_rev);
    if ($item) {
      DB_Query("INSERT INTO item_pane (rev, pane, priority) VALUES ({$var_rev},{$var_plug},{$var_priority})");
      echo '<div class="message">'.@$lang['addpane_ok'].".</div>\n";
      $var_op='add';
    } else err_echo(@$lang['error_nopane']);
  } else err_echo(@$lang['error_noitem']);
} if (@$var_updatepane) {
// Update Panel Values
  $var_pane+=0;
  $var_rev+=0;
  $var_plug+=0;
  $var_priority+=0;
  $item = DB_SimpleQuery('SELECT * FROM item_pane WHERE id='.$var_pane);
  if ($item) {
//    $target = DB_SimpleQuery('SELECT * FROM item WHERE id='.$var_target);
//    if (($item['DTYPE']==7)&&($target['DTYPE']==6)) $target['DTYPE']=8; 
//    if (($target)&&($target['DTYPE']==$item['DTYPE']+1)) {
    DB_Query("UPDATE item_pane SET rev={$var_rev}, pane={$var_plug}, priority={$var_priority} WHERE id = ".$var_pane);
    echo '<div class="message">'.@$lang['updatepane_ok'].".</div>\n";
    $var_op='edit';
  } else err_echo(@$lang['error_nopane']);
} if (@$var_deletepane) {
// Delete Panel Values
  if ($var_really) {
    $var_pane+=0;
    $item = DB_SimpleQuery('SELECT *, item_pane.id AS id FROM item_pane, item_rev WHERE item_rev.id = item_pane.rev AND item_pane.id='.$var_pane);
    echo '<div>'.@$lang['return_pre'].'<a href="item.php'.pl('item='.$item['owner']).'">'.@$lang['return'].'</a>'.@$lang['return_post']."</div>\n";

    DB_Query('DELETE FROM item_pane WHERE id='.$var_pane);
    echo '<div class="message">'.@$lang['deletepane_ok'].".</div>\n";
  } else {
    echo '<div>'.@$lang['return_pre'].'<a href="pane.php'.pl('op=edit&amp;pane='.$var_pane).'">'.@$lang['return'].'</a>'.@$lang['return_post']."</div>\n";
    echo '<div class="error">'.@$lang['error_mustpermit'].".</div>\n";
  }
} else if ($var_op=='select') {
// Select target for transformation
  echo '<div>'.@$lang['cancelselect_pre'].'<a href="pane.php'.pl('op='.$var_for.'&amp;item='.$var_item.'&amp;pane='.$var_pane.'&amp;rev='.$var_rev.'&amp;plug='.$var_plug.'&amp;priority='.$var_priority).'">'.@$lang['cancelselect'].'</a>'.@$lang['cancelselect_post']."</div><br/>\n";
  $var_item+=0;
  $var_node+=0;
  $var_spec+=0;
  $var_mode+=0;
  $var_rev+=0;
  $var_plug+=0;
  $var_plugin+=0;
  $var_priority+=0;
  $var_pane+=0;
  $item = DB_SimpleQuery('SELECT * FROM item WHERE id='.$var_item);
  if (@$var_spec && ($var_mode == 0)) {
    $spec = DB_SimpleQuery('SELECT * FROM item WHERE id = '.$var_spec." AND dtype = 'XBBlockSpec'");
    if ($spec['parent']>0) $node = DB_SimpleQuery('SELECT * FROM item WHERE id = '.$spec['parent']);
  } else if (@$var_spec && ($var_mode == 1)) {
    $spec = DB_SimpleQuery('SELECT * FROM plug WHERE id = '.$var_spec);
    $spec['parent'] = $spec['folder_id'];
    if ($spec['parent']>0) $node = DB_SimpleQuery('SELECT * FROM item WHERE id = '.$spec['folder_id']);
  } else if (!@$var_node) {
    if ($item) $node = DB_SimpleQuery('SELECT * FROM item WHERE id = '.$item['parent']);
  } else $node = DB_SimpleQuery('SELECT * FROM item WHERE id = '.$var_node." AND dtype = 'XBNode'");

  if (@$spec) {
    echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['selectrev_legend']."</legend>\n";
    echo '<a href="pane.php'.pl('op='.$var_op.'&amp;for='.$var_for.'&amp;mode='.$var_mode.'&amp;item='.$var_item.'&amp;pane='.$var_pane.'&amp;rev='.$var_rev.'&amp;plug='.$var_plug.'&amp;priority='.$var_priority.'&amp;node='.$spec['parent']).'">[..]</a><br/>'."\n";

    // Print items
    if ($var_mode == 1) {
      DB_Query('SELECT * FROM plug_pane WHERE plug = '.$var_spec.' ORDER BY pane');
    } else {
      DB_Query('SELECT *, item_rev.id AS id FROM item_rev WHERE item_rev.owner = '.$var_spec.' ORDER BY xbindex');
    }
    $lastid = 0;
    while ($row=DB_Row()) if ($row['id']!=$lastid) {
      echo '<a href="pane.php';
      if ($var_mode == 1) {
        echo pl('op='.$var_for.'&amp;item='.$var_item.'&amp;pane='.$var_pane.'&amp;rev='.$var_rev.'&amp;plug='.$row['id'].'&amp;priority='.$var_priority);
        echo '">';
        echo '['.@$lang['pane'].' '.$row['id'].'] '.$row['pane'];
      } else {
        echo pl('op='.$var_for.'&amp;item='.$var_item.'&amp;pane='.$var_pane.'&amp;rev='.$row['id'].'&amp;plug='.$var_plug.'&amp;priority='.$var_priority);
        echo '">';
        echo '['.@$lang['rev'].' '.$row['xbindex'].'] '.$row['text'];
      }
      $lastid = $row['id'];
      echo '</a><br/>'."\n";
    }
    echo '</fieldset>'."\n";
  } else if (@$node) {
    if ($var_mode==1) { $dtype = 'XBNode'; } else $dtype = 'XBBlockSpec';
    echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['selectplug_legend']."</legend>\n";

    if ($node['parent']>0) echo '<a href="pane.php'.pl('op='.$var_op.'&amp;for='.$var_for.'&amp;mode='.$var_mode.'&amp;item='.$var_item.'&amp;pane='.$var_pane.'&amp;rev='.$var_rev.'&amp;plug='.$var_plug.'&amp;priority='.$var_priority.'&amp;node='.$node['parent']).'">[..]</a><br/>'."\n";
    // Print items
    DB_Query('SELECT *, item.id AS id FROM item LEFT JOIN item_name ON item.id = item_name.id WHERE (parent = '.$node['id'].") AND (dtype = 'XBNode' OR dtype = '".$dtype."') ORDER BY dtype");
    $lastid = 0;
    while ($row=DB_Row()) if ($row['id']!=$lastid) {
      if ($row['DTYPE'] != 'XBNode') {
        echo '<a href="pane.php'.pl('op='.$var_op.'&amp;for='.$var_for.'&amp;mode='.$var_mode.'&amp;item='.$var_item.'&amp;pane='.$var_pane.'&amp;rev='.$var_rev.'&amp;plug='.$var_plug.'&amp;priority='.$var_priority.'&amp;spec='.$row['id']).'">';
        echo '['.@$lang['spec'].' '.$row['xbindex'].'] '.$row['text'];
      } else {
        echo '<a href="pane.php'.pl('op='.$var_op.'&amp;for='.$var_for.'&amp;mode='.$var_mode.'&amp;item='.$var_item.'&amp;pane='.$var_pane.'&amp;rev='.$var_rev.'&amp;plug='.$var_plug.'&amp;priority='.$var_priority.'&amp;node='.$row['id']).'">';
        echo '['.@$lang['node'].' '.$row['id'].'] '.$row['text'];
      }
      $lastid = $row['id'];
      echo '</a><br/>'."\n";
    }
    if ($var_mode==1) {
      // Print plugins
      DB_Query('SELECT * FROM plug WHERE folder_id = '.$node['id'].' ORDER BY plugin');
      $lastid = 0;
      while ($row=DB_Row()) if ($row['id']!=$lastid) {
        echo '<a href="pane.php'.pl('op='.$var_op.'&amp;for='.$var_for.'&amp;mode='.$var_mode.'&amp;item='.$var_item.'&amp;pane='.$var_pane.'&amp;rev='.$var_rev.'&amp;plug='.$var_plug.'&amp;priority='.$var_priority).'&amp;spec='.$row['id'].'">';
        echo '['.@$lang['plugin'].' '.$row['plugin'].'] '.$row['text'];
        $lastid = $row['id'];
        echo '</a><br/>'."\n";
      }
    }
    echo '</fieldset>'."\n";
  } else err_echo(@$lang['error_noitem']);
}

if ($var_op=='add') {
// Add new panel dialog
  if (@$var_item) {
    $item = DB_SimpleQuery('SELECT * FROM item WHERE id='.$var_item);
    if ($item) {
      echo '<div>'.@$lang['return_pre'].'<a href="item.php'.pl('item='.$var_item).'">'.@$lang['return'].'</a>'.@$lang['return_post']."</div>\n";

      echo '<form method="post" action="pane.php'.pl('item='.$var_item).'" class="regForm">'."\n";
      echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['addpane_legend']."</legend>\n";
      echo '  <div>'.@$lang['itemid'].': '.$var_item."</div>\n";
      echo '  <label>'.@$lang['rev']."</label><br/>\n";
      echo '  <input class="formText" type="text" name="rev" value="'.$var_rev.'"/> <a href="pane.php'.pl('op=select&amp;for=add&amp;mode=0&amp;item='.$var_item.'&amp;pane='.$var_pane.'&amp;rev='.$var_rev.'&amp;plug='.$var_plug.'&amp;priority='.$var_priority).'">'.@$lang['select_rev'].'</a><br/>'."\n";
      echo '  <label>'.@$lang['plug']."</label><br/>\n";
      echo '  <input class="formText" type="text" name="plug" value="'.$var_plug.'"/> <a href="pane.php'.pl('op=select&amp;for=add&amp;mode=1&amp;item='.$var_item.'&amp;pane='.$var_pane.'&amp;rev='.$var_rev.'&amp;plug='.$var_plug.'&amp;priority='.$var_priority).'">'.@$lang['select_plug'].'</a><br/>'."\n";
      echo '  <label>'.@$lang['priority']."</label><br/>\n";
      echo '  <input class="formText" type="text" name="priority" value="'.$var_priority."\"/><br/>\n";
      echo '  <input type="submit" name="addpane" value="'.@$lang['addpane'].'" class="formButton"/>'."\n";
      echo "</fieldset>\n</form>\n";
    } else err_echo(@$lang['error_nopane']);
  } else err_echo(@$lang['error_noitem']);
} if ($var_op=='edit') {
  $var_pane+=0;
  $item = DB_SimpleQuery('SELECT *, item_pane.id AS id FROM item_pane, item_rev WHERE item_rev.id = item_pane.rev AND item_pane.id='.$var_pane);
  if ($item) {
    echo '<div>'.@$lang['return_pre'].'<a href="item.php'.pl('item='.$item['owner']).'">'.@$lang['return'].'</a>'.@$lang['return_post']."</div>\n";

    if (!isset($var_rev)) { $var_rev = $item['rev']; } else $var_rev+=0; 
    if (!isset($var_plug)) { $var_plug = $item['pane']; } else $var_plug+=0; 
    if (!isset($var_priority)) { $var_priority = $item['priority']; } else $var_priority+=0; 

    echo '<form method="post" action="pane.php'.pl('pane='.$var_pane).'" class="regForm">'."\n";
    echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['updatepane_legend']."</legend>\n";
    echo '  <label>'.@$lang['rev']."</label><br/>\n";
    echo '  <input class="formText" type="text" name="rev" value="'.$var_rev.'"/> <a href="pane.php'.pl('op=select&amp;for=edit&amp;mode=0&amp;item='.$item['owner'].'&amp;pane='.$var_pane.'&amp;rev='.$var_rev.'&amp;plug='.$var_plug.'&amp;priority='.$var_priority).'">'.@$lang['select_rev'].'</a><br/>'."\n";
    echo '  <label>'.@$lang['plug']."</label><br/>\n";
    echo '  <input class="formText" type="text" name="plug" value="'.$var_plug.'"/> <a href="pane.php'.pl('op=select&amp;for=edit&amp;mode=1&amp;item='.$item['owner'].'&amp;pane='.$var_pane.'&amp;rev='.$var_rev.'&amp;plug='.$var_plug.'&amp;priority='.$var_priority).'">'.@$lang['select_plug'].'</a><br/>'."\n";
    echo '  <label>'.@$lang['priority']."</label><br/>\n";
    echo '  <input class="formText" type="text" name="priority" value="'.$var_priority."\"/><br/>\n";
    echo '  <input type="submit" name="updatepane" value="'.@$lang['updatepane'].'" class="formButton"/>'."\n";
    echo "</fieldset>\n</form>\n";

    echo '<form method="post" action="pane.php'.pl('pane='.$var_pane.'&amp;owner='.$item['owner']).'" class="regForm">'."\n";
    echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['deletepane_legend']."</legend>\n";
    echo '  <input class="formText" type="checkbox" name="really"/>'.@$lang['deletepane_really']."<br/>\n";
    echo '  <input type="submit" name="deletepane" value="'.@$lang['deletepane'].'" class="formButton"/>'."\n";
    echo "</fieldset>\n</form>\n";
  } else err_echo(@$lang['error_nopane']);
}
done(); ?>
