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

// PHP Catalog Web Interface: Specification Management

$GLOBALS['current']="spec.php";
extract($_GET, EXTR_PREFIX_ALL, 'var'); extract($_POST, EXTR_PREFIX_ALL, 'var');
if (@$var_lang=='cs') { include "lang/spec-cs.php"; } else include "lang/spec-en.php";
$pagename=@$lang['pagename'];
$dateform='j.n.Y';
include "auth.php"; global $auth; include "include.php";  
echo '<div style="text-align: right;" align="right">';
if (isset($auth)) {
  echo @$lang['user'].': <a href="account.php'.$GLOBALS['pl'].'">'.$auth['login'].'</a> <a href="item.php'.pl('logout=1&amp;item='.$var_item).'">['.@$lang['logout'].']</a>';
} else echo '<a href="login.php'.$GLOBALS['pl'].'">'.@$lang['login'].'</a>';
echo '</div>'."\n";
if (@$var_spec) { $return = $var_spec; } else $return = @$var_parent; 

if (@$var_addspec) {
// Add new spec
  $var_parent+=0;
  if (@$var_parent) {
    $var_xbindex+=0;
    $var_type+=0;
    $item = DB_SimpleQuery('SELECT * FROM XBITEM item WHERE id='.$var_parent);
    if (($item)&&($var_type>0)&&($var_type<5)) {
      DB_Query("INSERT INTO item (parent, dtype, xbindex) VALUES ({$var_parent},".($var_type).",{$var_xbindex})");
      $var_spec=mysql_insert_id();
      echo '<div class="message">'.@$lang['addspec_ok'].".</div>\n";
      $var_op='edit';
    } else err_echo(@$lang['error_nospec']);
  } else err_echo(@$lang['error_noparent']);
} if (@$var_updatespec) {
// Update spec Values
  $var_parent+=0;
  if (@$var_parent) {
    $var_spec+=0;
    $var_xbindex+=0;
    $item = DB_SimpleQuery('SELECT * FROM XBITEM item WHERE id='.$var_spec);
    if ($item) {
      $parent = DB_SimpleQuery('SELECT * FROM XBITEM item WHERE id='.$var_parent);
      if ($parent) {
        DB_Query("UPDATE XBITEM SET owner_id={$var_parent}, xbindex={$var_xbindex} WHERE id=".$var_spec);
        echo '<div class="message">'.@$lang['updatespec_ok'].".</div>\n";
        $var_op='edit';
      } else err_echo(@$lang['error_noparent']);
    } else err_echo(@$lang['error_nospec']);
  } else err_echo(@$lang['error_noparent']);
} if (@$var_deletespec) {
// Delete spec Values
  if ($var_really) {
    echo '<div>'.@$lang['return_pre'].'<a href="item.php'.pl('item='.$var_spec).'">'.@$lang['return'].'</a>'.@$lang['return_post']."</div>\n";
    $var_spec+=0;
    DB_Query('DELETE FROM XBITEM WHERE id='.$var_spec);
    echo '<div class="message">'.@$lang['deletespec_ok'].".</div>\n";
  } else echo '<div class="error">'.@$lang['error_mustpermit'].".</div>\n";
}

if ($var_op=='add') {
// Add new spec dialog
  if (@$var_parent) {
    $item = DB_SimpleQuery('SELECT * FROM XBITEM item WHERE id='.$var_parent);
    if ($item) {
      echo '<div>'.@$lang['return_pre'].'<a href="item.php'.pl('item='.$return).'">'.@$lang['return'].'</a>'.@$lang['return_post']."</div>\n";
      $prev = DB_SimpleQuery('SELECT MAX(xbindex) FROM XBITEM item WHERE owner_id='.$var_parent);

      echo '<form method="post" action="spec.php'.pl('owner_id='.$var_parent).'" class="regForm">'."\n";
            echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['addspec_legend']."</legend>\n";
      echo '  <div>'.@$lang['parentid'].': '.$var_parent."</div>\n";
      echo '  <label>'.@$lang['type']."</label><br/>\n";
      echo "  <select name=\"type\">\n";
      for($i=1;$i<5;$i++) echo '    <option value="'.$i.'">'.$lang['type'.$i]."</option>\n";
      echo "  </select><br/>\n";
      echo '  <label>'.@$lang['xbindex']."</label><br/>\n";
      echo '  <input class="formText" type="text" name="xbindex" value="'.(($prev)?($prev[0]+1):($var_xbindex))."\"/><br/>\n";
//      echo '  <label>'.@$lang['xblimit']."</label><br/>\n";
//      echo '  <input class="formText" type="text" name="xblimit"/><br/>'."\n";
      echo '  <input type="submit" name="addspec" value="'.@$lang['addspec'].'" class="formButton"/>'."\n";
      echo "</fieldset>\n</form>\n";
    } else err_echo(@$lang['error_nospec']);
  } else err_echo(@$lang['error_noparent']);
} if ($var_op=='select') {
  echo '<div>'.@$lang['cancelselect_pre'].'<a href="spec.php'.pl('op='.$var_for.'&spec='.$var_item).'">'.@$lang['cancelselect'].'</a>'.@$lang['cancelselect_post']."</div><br/>\n";
  $var_node+=0;
  $var_item+=0;
  $item = DB_SimpleQuery('SELECT * FROM XBITEM item WHERE id='.$var_item);
  if ($item['DTYPE']<5) {
    $dtype = 0; //$item['dtype']+1;
    $ntype = 0;
    if (!@$var_node) {
      if ($item) $node = DB_SimpleQuery('SELECT * FROM XBITEM item WHERE id = '.$item['OWNER_ID']);
    } else $node = DB_SimpleQuery('SELECT * FROM XBITEM item WHERE id='.$var_node);
  }
  if (@$node) {
    echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['selectparent_legend']."</legend>\n";

    echo '<a href="spec.php'.pl('op='.$var_for.'&amp;spec='.$var_item.'&amp;owner_id='.$node['ID']).'">[.]</a><br/>'."\n";
    if ($node['OWNER_ID']>0) echo '<a href="spec.php'.pl('op=select&amp;for='.$var_for.'&amp;item='.$var_item.'&amp;node='.$node['OWNER_ID']).'">[..]</a><br/>'."\n";
    // Print items
    DB_Query('SELECT *, item.id AS id FROM XBITEM item LEFT JOIN XBXNAME item_name ON item.id = item_name.id WHERE (parent = '.$node['ID'].') AND (dtype = '.$ntype.' OR dtype = '.$dtype.') ORDER BY dtype');
    $lastid = 0;
    while ($row=DB_Row()) if ($row['ID']!=$lastid) {
      if (($row['DTYPE']>0)&&($row['DTYPE']!=4)) {
        echo '<a href="spec.php'.pl('op='.$var_for.'&amp;item='.$var_item.'&amp;target='.$row['ID']).'&amp;xbindex='.$row['XBINDEX'].'">';
        echo '['.@$lang['spec'].' '.$row['XBINDEX'].'] '.$row['TEXT'];
      } else {
        echo '<a href="spec.php'.pl('op=select&amp;for='.$var_for.'&amp;item='.$var_item.'&amp;node='.$row['ID']).'">';
        echo '['.@$lang['node'].' '.$row['ID'].'] '.$row['TEXT'];
      }
      $lastid = $row['id'];
      echo '</a><br/>'."\n";
    }
    echo '</fieldset>'."\n";
  } else err_echo(@$lang['error_noitem']);
} if ($var_op=='edit') {
  $var_spec+=0;
  $var_parent+=0;
  $item = DB_SimpleQuery('SELECT * FROM XBITEM item WHERE id='.$var_spec);
  if ($item) {
    if ($var_parent == 0) $var_parent = $item['OWNER_ID'];
    echo '<div>'.@$lang['return_pre'].'<a href="item.php'.pl('item='.$return).'">'.@$lang['return'].'</a>'.@$lang['return_post']."</div>\n";

    echo '<form method="post" action="spec.php'.pl('spec='.$var_spec).'" class="regForm">'."\n";
    echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['updatespec_legend']."</legend>\n";
    echo '  <label>'.@$lang['parentid']."</label><br/>\n";
    echo '  <input class="formText" type="text" name="parent" value="'.$var_parent."\"/>".' <a href="spec.php'.pl('op=select&amp;for=edit&amp;item='.$var_spec).'">'.@$lang['select_target'].'</a><br/>'."\n";
    echo '  <label>'.@$lang['type']."</label><br/>\n";
    echo "  <select name=\"type\">\n";
    for($i=1;$i<5;$i++) {
      echo '    <option value="'.$i.'"';
      if ($i==$item['DTYPE']) echo ' selected="selected"';
      echo '>'.$lang['type'.$i]."</option>\n";
    }
    echo "  </select><br/>\n";
    echo '  <label>'.@$lang['xbindex']."</label><br/>\n";
    echo '  <input class="formText" type="text" name="xbindex" value="'.$item['XBINDEX']."\"/><br/>\n";
//    echo '  <label>'.@$lang['xblimit']."</label><br/>\n";
//    echo '  <input class="formText" type="text" name="xblimit" value="'.$item['xblimit'].'"/><br/>'."\n";
    echo '  <input type="submit" name="updatespec" value="'.@$lang['updatespec'].'" class="formButton"/>'."\n";
    echo "</fieldset>\n</form>\n";

    echo '<form method="post" action="spec.php'.pl('spec='.$var_spec).'" class="regForm">'."\n";
    echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['deletespec_legend']."</legend>\n";
    echo '  <input class="formText" type="checkbox" name="really"/>'.@$lang['deletespec_really']."<br/>\n";
    echo '  <input type="submit" name="deletespec" value="'.@$lang['deletespec'].'" class="formButton"/>'."\n";
    echo "</fieldset>\n</form>\n";
  } else err_echo(@$lang['error_nospec']);
}
done(); ?>
