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

// PHP Catalog Web Interface: Node Management

$GLOBALS['current']="node.php";
import_request_variables('gP','var_');
if (@$var_lang=='cs') { include "lang/node-cs.php"; } else include "lang/node-en.php";
$pagename=@$lang['pagename'];
$dateform='j.n.Y';
include "auth.php"; global $auth; include "include.php";  
echo '<div style="text-align: right;" align="right">';
if (isset($auth)) {
  echo @$lang['user'].': <a href="account.php'.$GLOBALS['pl'].'">'.$auth['login'].'</a> <a href="item.php'.pl('logout=1&amp;item='.$var_item).'">['.@$lang['logout'].']</a>';
} else echo '<a href="login.php'.$GLOBALS['pl'].'">'.@$lang['login'].'</a>';
echo '</div>'."\n";
echo '<div>'.@$lang['return_pre'].'<a href="item.php'.pl('item='.($var_op=='add' ? $var_parent : $var_node)).'">'.@$lang['return'].'</a>'.@$lang['return_post']."</div>\n";

if (@$var_addnode) {
// Add new node
  $var_parent+=0;
  if (@$var_parent) {
    $var_xbindex+=0;
    $var_xblimit+=0;
    $item = DB_SimpleQuery('SELECT * FROM XBITEM item WHERE id='.$var_parent);
    if ($item) {
      DB_Query("INSERT INTO XBXITEM (owner_id, dtype, xbindex) VALUES ({$var_parent},{$item['DTYPE']},{$var_xbindex})");
      $var_node=mysql_insert_id();
      echo '<div class="message">'.@$lang['addnode_ok'].".</div>\n";
      $var_op='edit';
    } else err_echo(@$lang['error_nonode']);
  } else err_echo(@$lang['error_noparent']);
} if (@$var_updatenode) {
// Update Node Values
  $var_node+=0;
  $var_xbindex+=0;
  $var_xblimit+=0;
  $item = DB_SimpleQuery('SELECT * FROM XBITEM item WHERE id='.$var_node);
  if ($item) {
    DB_Query("UPDATE XBITEM item SET owner_id={$var_parent}, xbindex={$var_xbindex} WHERE id=".$var_node);
    echo '<div class="message">'.@$lang['updatenode_ok'].".</div>\n";
    $var_op='edit';
  } else err_echo(@$lang['error_nonode']);
} if (@$var_deletenode) {
// Delete Node Values
  if ($var_really) {
    $var_node+=0;
    DB_Query('DELETE FROM XBITEM WHERE id='.$var_node);
    echo '<div class="message">'.@$lang['deletenode_ok'].".</div>\n";
  } else echo '<div class="error">'.@$lang['error_mustpermit'].".</div>\n";
}


if ($var_op=='add') {
// Add new node dialog
  if (@$var_parent) {
    $item = DB_SimpleQuery('SELECT * FROM XBITEM item WHERE id='.$var_parent);
    if ($item) {
      $prev = DB_SimpleQuery('SELECT MAX(xbindex) FROM XBITEM item WHERE owner_id='.$var_parent);

      echo '<form method="post" action="node.php'.pl('parent='.$var_parent).'" class="regForm">'."\n";
            echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['addnode_legend']."</legend>\n";
      echo '  <div>'.@$lang['parentid'].': '.$var_parent."</div>\n";
      echo '  <label>'.@$lang['xbindex']."</label><br/>\n";
      echo '  <input class="formText" type="text" name="xbindex" value="'.(($prev)?($prev[0]+1):($var_xbindex))."\"/><br/>\n";
      echo '  <label>'.@$lang['xblimit']."</label><br/>\n";
      echo '  <input class="formText" type="text" name="xblimit"/><br/>'."\n";
      echo '  <input type="submit" name="addnode" value="'.@$lang['addnode'].'" class="formButton"/>'."\n";
      echo "</fieldset>\n</form>\n";
    } else err_echo(@$lang['error_nonode']);
  } else err_echo(@$lang['error_noparent']);
} if ($var_op=='edit') {
  $var_node+=0;
  $item = DB_SimpleQuery('SELECT * FROM XBITEM item WHERE id='.$var_node);
  if ($item) {

    echo '<form method="post" action="node.php'.pl('node='.$var_node).'" class="regForm">'."\n";
    echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['updatenode_legend']."</legend>\n";
    echo '  <label>'.@$lang['parentid']."</label><br/>\n";
    echo '  <input class="formText" type="text" name="parent" readonly="readonly" value="'.$item['OWNER_ID']."\"/><br/>\n";
    echo '  <label>'.@$lang['xbindex']."</label><br/>\n";
    echo '  <input class="formText" type="text" name="xbindex" value="'.$item['XBINDEX']."\"/><br/>\n";
//    echo '  <label>'.@$lang['xblimit']."</label><br/>\n";
//    echo '  <input class="formText" type="text" name="xblimit" value="'.$item['xbindex'].'"/><br/>'."\n";
    echo '  <input type="submit" name="updatenode" value="'.@$lang['updatenode'].'" class="formButton"/>'."\n";
    echo "</fieldset>\n</form>\n";

    echo '<form method="post" action="node.php'.pl('node='.$var_node).'" class="regForm">'."\n";
    echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['deletenode_legend']."</legend>\n";
    echo '  <input class="formText" type="checkbox" name="really"/>'.@$lang['deletenode_really']."<br/>\n";
    echo '  <input type="submit" name="deletenode" value="'.@$lang['deletenode'].'" class="formButton"/>'."\n";
    echo "</fieldset>\n</form>\n";
  } else err_echo(@$lang['error_nonode']);
}
done(); ?>
