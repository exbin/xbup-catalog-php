<?php
/*
 * Copyright (C) XBUP Project (http://xbup.org)
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

// PHP Catalog Web Interface: Transformation's Management

$GLOBALS['current']="tran.php";
import_request_variables('gP','var_');
if (@$var_lang=='cs') { include "lang/tran-cs.php"; } else include "lang/tran-en.php";
// $GLOBALS['stylesheets']='<link rel="stylesheet" href="styles/news.css" type="text/css" media="screen,projection" />'."\n";
$pagename=@$lang['pagename'];
$dateform='j.n.Y';
include "auth.php"; global $auth; include "include.php";  
echo '<div style="text-align: right;" align="right">';
if (isset($auth)) {
  echo @$lang['user'].': <a href="account.php'.$GLOBALS['pl'].'">'.$auth['login'].'</a> <a href="item.php'.pl('logout=1&amp;item='.$var_item).'">['.@$lang['logout'].']</a>';
} else echo '<a href="login.php'.$GLOBALS['pl'].'">'.@$lang['login'].'</a>';
echo '</div>'."\n";

if (@$var_addtran) {
// Add new transformation
  $var_item+=0;
  if (@$var_item) {
    $var_xbindex+=0;
    $var_target+=0;
    $var_limit+=0;
    $var_except+=0;
    $item = DB_SimpleQuery('SELECT * FROM item WHERE id='.$var_item);
    if (($item)&&(($item['dtype']<4))) {
//      $target = DB_SimpleQuery('SELECT item.id it FROM item_rev, item it1, item it2 WHERE item_rev.id='.$var_target.' AND it.id='.$var_);
//      if ((($target)&&(($item['dtype']<3)&&($target['dtype']==$item['dtype']+1)))||((($target['dtype']==3)||($var_target==0))&&($item['dtype']==3))) {
        if ($var_target == 0) $var_target = 'NULL';
        if ($var_limit == 0) $var_limit = 'NULL';
        if ($var_except == 0) $var_except = 'NULL';
//        if (($var_btype > 1 + ($item['dtype']==3 ? 2:0))||($var_btype < 0)) $var_btype = 0;
        DB_Query("INSERT INTO item_tran (item_id, target, limitation, exception) VALUES ({$var_item},{$var_target},{$var_limit},{$var_except})");
        echo '<div class="message">'.@$lang['addtran_ok'].".</div>\n";
        $var_op='add';
//      } else err_echo(@$lang['error_wrongtarget']);
    } else err_echo(@$lang['error_notran']);
  } else err_echo(@$lang['error_noitem']);
} if (@$var_updatetran) {
// Update transformation's Values
  $var_tran+=0;
  $var_item+=0;
  $var_target+=0;
  $var_limit+=0;
  $var_except+=0;
  $item = DB_SimpleQuery('SELECT * FROM item_tran, item WHERE item_tran.id='.$var_tran.' AND item.id = item_tran.item_id');
  if ($item) {
//    $target = DB_SimpleQuery('SELECT item.dtype AS dtype FROM item_rev, item WHERE item.id = item_rev.owner AND item_rev.id='.$var_target);
//    if ((($target)&&(($item['dtype']<3)&&($target['dtype']==$item['dtype']+1)))||((($target['dtype']==3)||($var_target==0))&&($item['dtype']==3))) {
      if ($var_target == 0) $var_target = 'NULL';
      if ($var_limit == 0) $var_limit = 'NULL';
      if ($var_except == 0) $var_except = 'NULL';
      DB_Query("UPDATE item_tran SET item_id={$var_item}, target={$var_target}, limitation={$var_limit}, exception={$var_except} WHERE id = ".$var_tran);
      echo '<div class="message">'.@$lang['updatetran_ok'].".</div>\n";
      $var_op='edit';
//    } else err_echo($var_target.@$lang['error_wrongtarget']);
  } else err_echo(@$lang['error_notran']);
} if (@$var_deletetran) {
// Delete transformation
  echo '<div>'.@$lang['return_pre'].'<a href="item.php'.pl('item='.$var_item).'">'.@$lang['return'].'</a>'.@$lang['return_post']."</div>\n";
  if ($var_really) {
    $var_tran+=0;
    DB_Query('DELETE FROM item_tran WHERE id='.$var_tran);
    echo '<div class="message">'.@$lang['deletetran_ok'].".</div>\n";
  } else echo '<div class="error">'.@$lang['error_mustpermit'].".</div>\n";
} else if ($var_op=='select') {
// Select target for transformation
  echo '<div>'.@$lang['cancelselect_pre'].'<a href="tran.php'.pl('op='.$var_for.'&amp;item='.$var_item.'&amp;tran='.$var_tran.'&amp;target='.$var_target.'&amp;limit='.$var_limit.'&amp;except='.$var_except).'">'.@$lang['cancelselect'].'</a>'.@$lang['cancelselect_post']."</div><br/>\n";
  $var_item+=0;
  $var_node+=0;
  $var_spec+=0;
  $var_mode+=0;
  $var_target+=0;
  $var_limit+=0;
  $var_except+=0;
  $var_tran+=0;
  $item = DB_SimpleQuery('SELECT * FROM item WHERE id='.$var_item);
  if (@$var_spec) {
    $spec = DB_SimpleQuery('SELECT * FROM item WHERE id = '.$var_spec.' AND dtype = 3');
    if ($spec['parent']>0) $node = DB_SimpleQuery('SELECT * FROM item WHERE id = '.$spec['parent']);
  } else if (!@$var_node) {
    if ($item) $node = DB_SimpleQuery('SELECT * FROM item WHERE id = '.$item['parent']);
  } else $node = DB_SimpleQuery('SELECT * FROM item WHERE id = '.$var_node.' AND dtype = 0');

/*  if ($item['dtype']<4) {
    if ($item['dtype'] == 3) {
      $dtype = 3;
    } else if ($var_btype==0) { $dtype = $item['dtype']+1; } else $dtype = $item['dtype'];
    $ntype = 0;
    if (@$var_spec) {
      $spec = DB_SimpleQuery('SELECT * FROM item WHERE id = '.$var_spec.' AND dtype = '.$dtype);
      if ($spec['parent']>0) $node = DB_SimpleQuery('SELECT * FROM item WHERE id = '.$spec['parent']);
    } else if (!@$var_node) {
      if ($item) $node = DB_SimpleQuery('SELECT * FROM item WHERE id = '.$item['parent']);
    } else $node = DB_SimpleQuery('SELECT * FROM item WHERE id='.$var_node);
  } */
  if (@$spec && ($var_mode != 1)) {
    echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['selecttran_legend']."</legend>\n";

    echo '<a href="tran.php'.pl('op='.$var_op.'&amp;for='.$var_for.'&amp;item='.$var_item.'&amp;tran='.$var_tran.'&amp;target='.$var_target.'&amp;limit='.$var_limit.'&amp;except='.$var_except.'&amp;node='.$spec['parent']).'">[..]</a><br/>'."\n";
    // Print items
    DB_Query('SELECT *, item_rev.id AS id FROM item_rev WHERE item_rev.owner = '.$var_spec.' ORDER BY xbindex');
    $lastid = 0;
    while ($row=DB_Row()) if ($row['id']!=$lastid) {
      echo '<a href="tran.php';
      if ($var_mode == 1) {
        echo pl('op='.$var_for.'&amp;item='.$var_item.'&amp;tran='.$var_tran.'&amp;target='.$var_target.'&amp;limit='.$row['id'].'&amp;except='.$var_except);
        echo '">';
        echo '['.@$lang['limit'].' '.$row['xbindex'].'] '.$row['text'];
      } else if ($var_mode == 2) {
        echo pl('op='.$var_for.'&amp;item='.$var_item.'&amp;tran='.$var_tran.'&amp;target='.$var_target.'&amp;limit='.$var_limit.'&amp;except='.$row['id']);
        echo '">';
        echo '['.@$lang['rev'].' '.$row['xbindex'].'] '.$row['text'];
      } else {
        echo pl('op='.$var_for.'&amp;item='.$var_item.'&amp;tran='.$var_tran.'&amp;target='.$row['id'].'&amp;limit='.$var_limit.'&amp;except='.$var_except);
        echo '">';
        echo '['.@$lang['rev'].' '.$row['xbindex'].'] '.$row['text'];
      }
      $lastid = $row['id'];
      echo '</a><br/>'."\n";
    }
    echo '</fieldset>'."\n";
  } else if (@$node) {
    if ($var_mode==1) { $dtype = 4; } else $dtype = 3;
    echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['selecttran_legend']."</legend>\n";

    if ($node['parent']>0) echo '<a href="tran.php'.pl('op='.$var_op.'&amp;for='.$var_for.'&amp;mode='.$var_mode.'&amp;item='.$var_item.'&amp;tran='.$var_tran.'&amp;target='.$var_target.'&amp;limit='.$var_limit.'&amp;except='.$var_except.'&amp;node='.$node['parent']).'">[..]</a><br/>'."\n";
    // Print items
    DB_Query('SELECT *, item.id AS id FROM item LEFT JOIN item_name ON item.id = item_name.id WHERE (parent = '.$node['id'].') AND (dtype = 0 OR dtype = '.$dtype.') ORDER BY dtype');
    $lastid = 0;
    while ($row=DB_Row()) if ($row['id']!=$lastid) {
      if ($row['dtype']>0) {
        if ($var_mode == 1) {
          echo '<a href="tran.php'.pl('op='.$var_for.'&amp;item='.$var_item.'&amp;tran='.$var_tran.'&amp;target='.$var_target.'&amp;limit='.$row['id'].'&amp;except='.$var_except).'">';
          echo '['.@$lang['limit'].' '.$row['xbindex'].'] '.$row['text'];
        } else {
          echo '<a href="tran.php'.pl('op='.$var_op.'&amp;for='.$var_for.'&amp;mode='.$var_mode.'&amp;item='.$var_item.'&amp;tran='.$var_tran.'&amp;target='.$var_target.'&amp;limit='.$var_limit.'&amp;except='.$var_except.'&amp;spec='.$row['id']).'">';
          echo '['.@$lang['spec'].' '.$row['xbindex'].'] '.$row['text'];
        }
      } else {
        echo '<a href="tran.php'.pl('op='.$var_op.'&amp;for='.$var_for.'&amp;mode='.$var_mode.'&amp;item='.$var_item.'&amp;tran='.$var_tran.'&amp;target='.$var_target.'&amp;limit='.$var_limit.'&amp;except='.$var_except.'&amp;node='.$row['id']).'">';
        echo '['.@$lang['node'].' '.$row['id'].'] '.$row['text'];
      }
      $lastid = $row['id'];
      echo '</a><br/>'."\n";
    }
    echo '</fieldset>'."\n";
  } else err_echo(@$lang['error_noitem']);
}

if ($var_op=='add') {
// Add new transformation dialog
  if (@$var_item) {
    $item = DB_SimpleQuery('SELECT * FROM item WHERE id='.$var_item);
    if ($item) {
      echo '<div>'.@$lang['return_pre'].'<a href="item.php'.pl('item='.$var_item).'">'.@$lang['return'].'</a>'.@$lang['return_post']."</div>\n";

      echo '<form method="post" action="tran.php'.pl('item='.$var_item).'" class="regForm">'."\n";
            echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['addtran_legend']."</legend>\n";
      echo '  <div>'.@$lang['itemid'].': '.$var_item."</div>\n";
      echo '  <label>'.@$lang['target']."</label><br/>\n";
      echo '  <input class="formText" type="text" name="target" value="'.$var_target.'"/> <a href="tran.php'.pl('op=select&amp;for=add&amp;mode=0&amp;item='.$var_item.'&amp;tran='.$var_tran.'&amp;target='.$var_target.'&amp;limit='.$var_limit.'&amp;except='.$var_except).'">'.@$lang['select_target'].'</a><br/>'."\n";
      echo '  <label>'.@$lang['limit']."</label><br/>\n";
      echo '  <input class="formText" type="text" name="limit" value="'.$var_limit.'"/> <a href="tran.php'.pl('op=select&amp;for=add&amp;mode=1&amp;item='.$var_item.'&amp;tran='.$var_tran.'&amp;target='.$var_target.'&amp;limit='.$var_limit.'&amp;except='.$var_except).'">'.@$lang['select_limit'].'</a><br/>'."\n";
      echo '  <label>'.@$lang['except']."</label><br/>\n";
      echo '  <input class="formText" type="text" name="except" value="'.$var_except.'"/> <a href="tran.php'.pl('op=select&amp;for=add&amp;mode=2&amp;item='.$var_item.'&amp;tran='.$var_tran.'&amp;target='.$var_target.'&amp;limit='.$var_limit.'&amp;except='.$var_except).'">'.@$lang['select_except'].'</a><br/>'."\n";
      echo '  <input type="submit" name="addtran" value="'.@$lang['addtran'].'" class="formButton"/>'."\n";
      echo "</fieldset>\n</form>\n";
    } else err_echo(@$lang['error_notran']);
  } else err_echo(@$lang['error_noitem']);
} if ($var_op=='edit') {
  $var_tran+=0;
  $item = DB_SimpleQuery('SELECT * FROM item_tran, item WHERE item_tran.id='.$var_tran.' AND item.id = item_tran.item_id');
  if ($item) {
    if (!isset($var_target)) { $var_target = $item['target']; } else $var_target+=0;
    if (!isset($var_limit)) { $var_limit = $item['limitation']; } else $var_limit+=0;
    if (!isset($var_except)) { $var_except = $item['exception']; } else $var_except+=0;
    echo '<div>'.@$lang['return_pre'].'<a href="item.php'.pl('item='.$item['item_id']).'">'.@$lang['return'].'</a>'.@$lang['return_post']."</div>\n";

    echo '<form method="post" action="tran.php'.pl('tran='.$var_tran).'" class="regForm">'."\n";
    echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['updatetran_legend']."</legend>\n";
    echo '  <label>'.@$lang['itemid']."</label><br/>\n";
    echo '  <input class="formText" type="text" name="item" value="'.$item['item_id']."\" readonly=\"readonly\"/><br/>\n";
    echo '  <label>'.@$lang['target']."</label><br/>\n";
    echo '  <input class="formText" type="text" name="target" value="'.$var_target.'"/> <a href="tran.php'.pl('op=select&amp;for=edit&amp;mode=0&amp;item='.$item['item_id'].'&amp;tran='.$var_tran.'&amp;target='.$var_target.'&amp;limit='.$var_limit.'&amp;except='.$var_except).'">'.@$lang['select_consist'].'</a><br/>'."\n";
    echo '  <label>'.@$lang['limit']."</label><br/>\n";
    echo '  <input class="formText" type="text" name="limit" value="'.$var_limit.'"/> <a href="tran.php'.pl('op=select&amp;for=edit&amp;mode=1&amp;item='.$item['item_id'].'&amp;tran='.$var_tran.'&amp;target='.$var_target.'&amp;limit='.$var_limit.'&amp;except='.$var_except).'">'.@$lang['select_limit'].'</a><br/>'."\n";
    echo '  <label>'.@$lang['except']."</label><br/>\n";
    echo '  <input class="formText" type="text" name="except" value="'.$var_except.'"/> <a href="tran.php'.pl('op=select&amp;for=edit&amp;mode=2&amp;item='.$item['item_id'].'&amp;tran='.$var_tran.'&amp;target='.$var_target.'&amp;limit='.$var_limit.'&amp;except='.$var_except).'">'.@$lang['select_except'].'</a><br/>'."\n";
    echo '  <input type="submit" name="updatetran" value="'.@$lang['updatetran'].'" class="formButton"/>'."\n";
    echo "</fieldset>\n</form>\n";

    echo '<form method="post" action="tran.php'.pl('tran='.$var_tran.'&amp;item='.$item['item_id']).'" class="regForm">'."\n";
    echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['deletetran_legend']."</legend>\n";
    echo '  <input class="formText" type="checkbox" name="really"/>'.@$lang['deletetran_really']."<br/>\n";
    echo '  <input type="submit" name="deletetran" value="'.@$lang['deletetran'].'" class="formButton"/>'."\n";
    echo "</fieldset>\n</form>\n";
  } else err_echo(@$lang['error_notran']);
}
done(); ?>
