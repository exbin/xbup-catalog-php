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

// PHP Catalog Web Interface: Specification Definition's Management

$GLOBALS['current']="specdef.php";
extract($_GET, EXTR_PREFIX_ALL, 'var'); extract($_POST, EXTR_PREFIX_ALL, 'var');
if (@$var_lang=='cs') { include "lang/specdef-cs.php"; } else include "lang/specdef-en.php";
// $GLOBALS['stylesheets']='<link rel="stylesheet" href="styles/news.css" type="text/css" media="screen,projection" />'."\n";
$pagename=@$lang['pagename'];
$dateform='j.n.Y';
include "auth.php"; global $auth; include "include.php";  
echo '<div style="text-align: right;" align="right">';
if (isset($auth)) {
  echo @$lang['user'].': <a href="account.php'.$GLOBALS['pl'].'">'.$auth['login'].'</a> <a href="item.php'.pl('logout=1&amp;item='.$var_item).'">['.@$lang['logout'].']</a>';
} else echo '<a href="login.php'.$GLOBALS['pl'].'">'.@$lang['login'].'</a>';
echo '</div>'."\n";

if (@$var_adddef) {
// Add new definition
  $var_item+=0;
  if (@$var_item) {
    $var_xbindex+=0;
    $var_target+=0;
    $var_btype+=0;
    $item = DB_SimpleQuery('SELECT * FROM XBITEM item WHERE id='.$var_item);
    if (($item)&&(($item['dtype']<'XBNode')||($item['dtype']<'XBFormatSpec')||($item['dtype']<'XBGroupSpec')||($item['dtype']<'XBBlockSpec'))) {
      $target = DB_SimpleQuery('SELECT item.dtype AS dtype FROM XBREV item_rev, XBITEM item WHERE item.id = item_rev.owner AND item_rev.id='.$var_target);
// TODO dtype
      if ((($target)&&(($item['dtype']<3)&&($target['dtype']==$item['dtype']+1)))||((($target['dtype']==3)||($var_target==0))&&($item['dtype']==3))) {
        if ($var_target == 0) $var_target = 'NULL';
        if (($var_btype > 1 + ($item['dtype']==3 ? 2:0))||($var_btype < 0)) $var_btype = 0;
        DB_Query("INSERT INTO XBSPECDEF (origin, btype, target, xbindex) VALUES ({$var_item},{$var_btype},{$var_target},{$var_xbindex})");
        echo '<div class="message">'.@$lang['adddef_ok'].".</div>\n";
        $var_op='add';
      } else err_echo(@$lang['error_wrongtarget']);
    } else err_echo(@$lang['error_nodef']);
  } else err_echo(@$lang['error_noitem']);
} if (@$var_updatedef) {
// Update definition's Values
  $var_def+=0;
  $var_xbindex+=0;
  $var_origin+=0;
  $var_target+=0;
  $var_btype+=0;
  $item = DB_SimpleQuery('SELECT * FROM XBSPECDEF def WHERE def.id='.$var_def.' AND item.id = def.spec_id');
  if ($item) {
    $target = DB_SimpleQuery('SELECT item.dtype AS dtype FROM XBREV item_rev, XBITEM item WHERE item.id = item_rev.owner AND item_rev.id='.$var_target);
    if ((($target)&&(($item['dtype']<3)&&($target['dtype']==$item['dtype']+1)))||((($target['dtype']==3)||($var_target==0))&&($item['dtype']==3))) {
      if ($var_target == 0) $var_target = 'NULL';
      DB_Query("UPDATE XBSPECDEF SET spec_id={$var_origin}, btype={$var_btype}, target_id={$var_target}, xbindex={$var_xbindex} WHERE id = ".$var_def);
      echo '<div class="message">'.@$lang['updatedef_ok'].".</div>\n";
      $var_op='edit';
    } else err_echo($var_target.@$lang['error_wrongtarget']);
  } else err_echo(@$lang['error_nodef']);
} if (@$var_deletedef) {
// Delete definition Values
  echo '<div>'.@$lang['return_pre'].'<a href="item.php'.pl('item='.$var_origin).'">'.@$lang['return'].'</a>'.@$lang['return_post']."</div>\n";
  if ($var_really) {
    $var_def+=0;
    DB_Query('DELETE FROM XBSPECDEF def WHERE id='.$var_def);
    echo '<div class="message">'.@$lang['deletedef_ok'].".</div>\n";
  } else echo '<div class="error">'.@$lang['error_mustpermit'].".</div>\n";
} else if ($var_op=='select') {
// Select target for definition
  echo '<div>'.@$lang['cancelselect_pre'].'<a href="specdef.php'.pl('op='.$var_for.'&amp;item='.$var_item.'&amp;def='.$var_def.'&amp;btype='.$var_btype.'&amp;target='.$var_target).'">'.@$lang['cancelselect'].'</a>'.@$lang['cancelselect_post']."</div><br/>\n";
  $var_node+=0;
  $var_spec+=0;
  $var_btype+=0;
  $var_target+=0;
  $var_item+=0;
  $var_def+=0;
  $item = DB_SimpleQuery('SELECT * FROM XBITEM item WHERE id='.$var_item);
  if ($item['dtype']<4) {
    if ($item['dtype'] == 3) {
      $dtype = 3;
    } else if ($var_btype==0) { $dtype = $item['dtype']+1; } else $dtype = $item['dtype'];
    $ntype = 0;
    if (@$var_spec) {
      $spec = DB_SimpleQuery('SELECT * FROM XBITEM item WHERE id = '.$var_spec.' AND dtype = '.$dtype);
      if ($spec['parent']>0) $node = DB_SimpleQuery('SELECT * FROM XBITEM item WHERE id = '.$spec['parent']);
    } else if (!@$var_node) {
      if ($item) $node = DB_SimpleQuery('SELECT * FROM XBITEM item WHERE id = '.$item['parent']);
    } else $node = DB_SimpleQuery('SELECT * FROM XBITEM item WHERE id='.$var_node);
  }
  if (@$spec) {
    echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['selectdef_legend']."</legend>\n";

    echo '<a href="specdef.php'.pl('op='.$var_op.'&amp;for='.$var_for.'&amp;item='.$var_item.'&amp;def='.$var_def.'&amp;btype='.$var_btype.'&amp;target='.$var_target.'&amp;node='.$spec['parent']).'">[..]</a><br/>'."\n";
    // Print items
    DB_Query('SELECT *, item_rev.id AS id FROM XBREV item_rev WHERE item_rev.owner = '.$var_spec.' ORDER BY xbindex');
    $lastid = 0;
    while ($row=DB_Row()) if ($row['id']!=$lastid) {
      echo '<a href="specdef.php';
      echo pl('op='.$var_for.'&amp;item='.$var_item.'&amp;def='.$var_def.'&amp;btype='.$var_btype.'&amp;target='.$row['id'].'&amp;xbindex='.$row['xbindex']);
      echo '">';
      echo '['.@$lang['rev'].' '.$row['xbindex'].'] '.$row['text'];
      $lastid = $row['id'];
      echo '</a><br/>'."\n";
    }
    echo '</fieldset>'."\n";
  } else if (@$node) {
    echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['selectdef_legend']."</legend>\n";

    if ($node['parent']>0) echo '<a href="specdef.php'.pl('op='.$var_op.'&amp;for='.$var_for.'&amp;item='.$var_item.'&amp;def='.$var_def.'&amp;btype='.$var_btype.'&amp;target='.$var_target.'&amp;node='.$node['parent']).'">[..]</a><br/>'."\n";
    // Print items
    DB_Query('SELECT *, item.id AS id FROM XBITEM item LEFT JOIN XBXNAME item_name ON item.id = item_name.id WHERE (parent = '.$node['id'].') AND (dtype = '.$ntype.' OR dtype = '.$dtype.') ORDER BY dtype');
    $lastid = 0;
    while ($row=DB_Row()) if ($row['id']!=$lastid) {
      if (($row['dtype']>0)&&($row['dtype']!=4)) {
        echo '<a href="specdef.php'.pl('op='.$var_op.'&amp;for='.$var_for.'&amp;item='.$var_item.'&amp;def='.$var_def.'&amp;btype='.$var_btype.'&amp;target='.$var_target.'&amp;spec='.$row['id'].'&amp;xbindex='.$row['xbindex']).'">';
        echo '['.@$lang['spec'].' '.$row['xbindex'].'] '.$row['text'];
      } else {
        echo '<a href="specdef.php'.pl('op='.$var_op.'&amp;for='.$var_for.'&amp;item='.$var_item.'&amp;def='.$var_def.'&amp;btype='.$var_btype.'&amp;target='.$var_target.'&amp;node='.$row['id']).'">';
        echo '['.@$lang['node'].' '.$row['id'].'] '.$row['text'];
      }
      $lastid = $row['id'];
      echo '</a><br/>'."\n";
    }
    echo '</fieldset>'."\n";
  } else err_echo(@$lang['error_noitem']);
} if ($var_op=='add') {
// Add new definition dialog
  if (@$var_item) {
    $item = DB_SimpleQuery('SELECT * FROM XBITEM item WHERE id='.$var_item);
    if ($item) {
      echo '<div>'.@$lang['return_pre'].'<a href="item.php'.pl('item='.$var_item).'">'.@$lang['return'].'</a>'.@$lang['return_post']."</div>\n";

      echo '<form method="post" action="specdef.php'.pl('item='.$var_item).'" class="regForm">'."\n";
            echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['adddef_legend']."</legend>\n";
      echo '  <div>'.@$lang['itemid'].': '.$var_item."</div>\n";
      echo '  <label>'.@$lang['btype']."</label><br/>\n";
      echo "  <select name=\"btype\">\n";
      for($i=0;$i<2+($item['dtype']==3 ? 2:0);$i++) echo '    <option value="'.$i.'">'.$lang['btype'.$i]."</option>\n";
      echo "  </select><br/>\n";
      echo '  <label>'.@$lang['target']."</label><br/>\n";
      echo '  <input class="formText" type="text" name="target" value="'.$var_target.'"/> <a href="specdef.php'.pl('op=select&amp;for=add&amp;item='.$var_item.'&amp;def='.$var_def.'&amp;target='.$var_target.'&amp;btype='.$var_btype).'">'.@$lang['select_consist'].'</a><br/>'."\n";
      echo '  <label>'.@$lang['xbindex']."</label><br/>\n";
      echo '  <input class="formText" type="text" name="xbindex" value="'.$var_xbindex."\"/><br/>\n";
      echo '  <input type="submit" name="adddef" value="'.@$lang['adddef'].'" class="formButton"/>'."\n";
      echo "</fieldset>\n</form>\n";
    } else err_echo(@$lang['error_nodef']);
  } else err_echo(@$lang['error_noitem']);
} if ($var_op=='edit') {
  $var_def+=0;
  $item = DB_SimpleQuery('SELECT * FROM XBSPECDEF def WHERE def.id='.$var_def);
  if ($item) {
    if (!isset($var_target)) { $var_target = $item['target']; } else $var_target+=0;
    $rev = '';
    if ($var_target) $rev = DB_SimpleQuery('SELECT * FROM XBREV item_rev WHERE id = '.$var_target);
    if (!isset($var_btype)) { $var_btype = $item['btype']; } else $var_btype+=0;
    echo '<div>'.@$lang['return_pre'].'<a href="item.php'.pl('item='.$item['origin']).'">'.@$lang['return'].'</a>'.@$lang['return_post']."</div>\n";

    echo '<form method="post" action="specdef.php'.pl('def='.$var_def).'" class="regForm">'."\n";
    echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['updatedef_legend']."</legend>\n";
    echo '  <label>'.@$lang['itemid']."</label><br/>\n";
    echo '  <input class="formText" type="text" name="origin" value="'.$item['origin']."\" readonly=\"readonly\"/><br/>\n";
    echo '  <label>'.@$lang['btype']."</label><br/>\n";
    echo "  <select name=\"btype\">\n"; // disabled=\"disabled\"
    for($i=0;$i<2+($item['dtype']==3 ? 2:0);$i++) {
      echo '    <option value="'.$i.'"';
      if ($i==$item['btype']) echo ' selected="selected"';
      echo '>'.$lang['btype'.$i]."</option>\n";
    }
    echo "  </select><br/>\n";
    echo '  <label>'.@$lang['target']."</label><br/>\n";
    echo '  <input class="formText" type="text" name="target" value="'.$var_target.'"/> <a href="specdef.php'.pl('op=select&amp;for=edit&amp;item='.$item['origin'].'&amp;def='.$var_def.'&amp;target='.$var_target.(($rev) ? '&amp;spec='.$rev['owner'] : '').'&amp;btype='.$var_btype).'">'.@$lang['select_consist'].'</a><br/>'."\n";
    echo '  <label>'.@$lang['xbindex']."</label><br/>\n";
    echo '  <input class="formText" type="text" name="xbindex" value="'.$item['xbindex']."\"/><br/>\n";
    echo '  <input type="submit" name="updatedef" value="'.@$lang['updatedef'].'" class="formButton"/>'."\n";
    echo "</fieldset>\n</form>\n";

    echo '<form method="post" action="specdef.php'.pl('def='.$var_def.'&amp;origin='.$item['origin']).'" class="regForm">'."\n";
    echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['deletedef_legend']."</legend>\n";
    echo '  <input class="formText" type="checkbox" name="really"/>'.@$lang['deletedef_really']."<br/>\n";
    echo '  <input type="submit" name="deletedef" value="'.@$lang['deletedef'].'" class="formButton"/>'."\n";
    echo "</fieldset>\n</form>\n";
  } else err_echo(@$lang['error_nodef']);
}
done(); ?>
