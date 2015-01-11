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

// PHP Catalog Web Interface: Revision Management

$GLOBALS['current']="rev.php";
import_request_variables('gP','var_');
if (@$var_lang=='cs') { include "lang/rev-cs.php"; } else include "lang/rev-en.php";
// $GLOBALS['stylesheets']='<link rel="stylesheet" href="styles/news.css" type="text/css" media="screen,projection" />'."\n";
$pagename=@$lang['pagename'];
$dateform='j.n.Y';
include "auth.php"; global $auth; include "include.php";  
echo '<div style="text-align: right;" align="right">';
if (isset($auth)) {
  echo @$lang['user'].': <a href="account.php'.$GLOBALS['pl'].'">'.$auth['login'].'</a> <a href="item.php'.pl('logout=1&amp;item='.$var_item).'">['.@$lang['logout'].']</a>';
} else echo '<a href="login.php'.$GLOBALS['pl'].'">'.@$lang['login'].'</a>';
echo '</div>'."\n";

if (@$var_addrev) {
// Add new rev
  $var_item+=0;
  if (@$var_item) {
    $var_xbindex+=0;
    $var_xblimit+=0;
    $item = DB_SimpleQuery('SELECT * FROM XBITEM item WHERE id='.$var_item);
    if (($item)&&(($item['dtype']<4)&&($item['dtype']>0))) {
      DB_Query("INSERT INTO item_rev (owner, xbindex, xblimit) VALUES ({$var_item},{$var_xbindex},{$var_xblimit})");
      echo '<div class="message">'.@$lang['addrev_ok'].".</div>\n";
      $var_op='add';
    } else err_echo(@$lang['error_norev']);
  } else err_echo(@$lang['error_noitem']);
} if (@$var_updaterev) {
// Update rev Values
  $var_rev+=0;
  $var_xbitem+=0;
  $var_xbindex+=0;
  $var_xblimit+=0;
  $item = DB_SimpleQuery('SELECT * FROM XBREV item_rev WHERE id='.$var_rev);
  if ($item) {
//    $target = DB_SimpleQuery('SELECT * FROM item WHERE id='.$var_target);
//    if (($item['dtype']==7)&&($target['dtype']==6)) $target['dtype']=8; 
//    if (($target)&&($target['dtype']==$item['dtype']+1)) {
    DB_Query("UPDATE item_rev SET xbindex={$var_xbindex}, xblimit={$var_xblimit} WHERE id = ".$var_rev);
    echo '<div class="message">'.@$lang['updaterev_ok'].".</div>\n";
    $var_op='edit';
  } else err_echo(@$lang['error_norev']);
} if (@$var_deleterev) {
// Delete rev Values
  echo '<div>'.@$lang['return_pre'].'<a href="item.php'.pl('item='.$var_owner).'">'.@$lang['return'].'</a>'.@$lang['return_post']."</div>\n";
  if ($var_really) {
    $var_rev+=0;
    DB_Query('DELETE FROM XBREV item_rev WHERE id='.$var_rev);
    echo '<div class="message">'.@$lang['deleterev_ok'].".</div>\n";
  } else echo '<div class="error">'.@$lang['error_mustpermit'].".</div>\n";
} else if ($var_op=='add') {
// Add new rev dialog
  if (@$var_item) {
    $item = DB_SimpleQuery('SELECT * FROM XBITEM item WHERE id='.$var_item);
    if ($item) {
      echo '<div>'.@$lang['return_pre'].'<a href="item.php'.pl('item='.$var_item).'">'.@$lang['return'].'</a>'.@$lang['return_post']."</div>\n";

      echo '<form method="post" action="rev.php'.pl('item='.$var_item).'" class="regForm">'."\n";
            echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['addrev_legend']."</legend>\n";
      echo '  <div>'.@$lang['itemid'].': '.$var_item."</div>\n";
      echo '  <label>'.@$lang['xbindex']."</label><br/>\n";
      echo '  <input class="formText" type="text" name="xbindex" value="'.$var_xbindex."\"/><br/>\n";
      echo '  <label>'.@$lang['xblimit']."</label><br/>\n";
      echo '  <input class="formText" type="text" name="xblimit" value="'.$var_xblimit."\"/><br/>\n";
      echo '  <input type="submit" name="addrev" value="'.@$lang['addrev'].'" class="formButton"/>'."\n";
      echo "</fieldset>\n</form>\n";
    } else err_echo(@$lang['error_norev']);
  } else err_echo(@$lang['error_noitem']);
} if ($var_op=='edit') {
  $var_rev+=0;
  $item = DB_SimpleQuery('SELECT * FROM XBREV item_rev WHERE id='.$var_rev);
  if ($item) {
    echo '<div>'.@$lang['return_pre'].'<a href="item.php'.pl('item='.$item['owner']).'">'.@$lang['return'].'</a>'.@$lang['return_post']."</div>\n";
  
    echo '<form method="post" action="rev.php'.pl('rev='.$var_rev).'" class="regForm">'."\n";
    echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['updaterev_legend']."</legend>\n";
    echo '  <label>'.@$lang['xbindex']."</label><br/>\n";
    echo '  <input class="formText" type="text" name="xbindex" value="'.$item['xbindex']."\"/><br/>\n";
    echo '  <label>'.@$lang['xblimit']."</label><br/>\n";
    echo '  <input class="formText" type="text" name="xblimit" value="'.$item['xblimit']."\"/><br/>\n";
    echo '  <input type="submit" name="updaterev" value="'.@$lang['updaterev'].'" class="formButton"/>'."\n";
    echo "</fieldset>\n</form>\n";

    echo '<form method="post" action="rev.php'.pl('rev='.$var_rev.'&owner='.$item['owner']).'" class="regForm">'."\n";
    echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['deleterev_legend']."</legend>\n";
    echo '  <input class="formText" type="checkbox" name="really"/>'.@$lang['deleterev_really']."<br/>\n";
    echo '  <input type="submit" name="deleterev" value="'.@$lang['deleterev'].'" class="formButton"/>'."\n";
    echo "</fieldset>\n</form>\n";
  } else err_echo(@$lang['error_norev']);
}
done(); ?>
