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

// PHP Catalog Web Interface: Account Management

$GLOBALS['current']="account.php";
import_request_variables('gP','var_');
if (@$var_lang=='cs') { include "lang/account-cs.php"; } else include "lang/account-en.php";
$pagename=@$lang['pagename'];
include "auth.php"; global $auth; include "include.php";

echo '<div style="text-align: right;" align="right">';
if (isset($auth)) {
  echo @$lang['user'].': <a href="account.php'.$GLOBALS['pl'].'">'.$auth['login'].'</a> <a href="item.php'.pl('logout=1&amp;item='.$var_item).'">['.@$lang['logout'].']</a>';
} else echo '<a href="login.php'.$GLOBALS['pl'].'">'.@$lang['login'].'</a>';
echo '</div>'."\n";

echo '<div>'.@$lang['return_pre'].'<a href="index.php">'.@$lang['return'].'</a>'.@$lang['return_post']."</div>\n";
if (isset($auth)) {
  import_request_variables('gP','data_');
  if (!@$data_id) $data_id=$auth['id'];
  if (($auth['id']==$data_id)||($auth['type']==1)) {
  } else $data_id = $auth['id'];
  $data_id +=0;
  $user = DB_SimpleQuery('SELECT * FROM XBXUSER WHERE id = '.$data_id);
  if (isset($data_name)) {
    if (MD5($data_passwd1)==$auth['passwd']) {
      DB_Query("UPDATE XBXUSER SET fullname='".htmlspecialchars($data_name)."' WHERE ID = ".$data_id);
      if ($post_passwd2) {
        if ($data_passwd2==$data_passwd3) {
          $_SESSION["pwd"]=MD5($data_passwd2);
          DB_Query("UPDATE XBXUSER SET passwd='".$_SESSION["pwd"]."' WHERE ID=".$data_id);
        } else echo '<p class="error">'.@$lang['error_nomatch'].'</p>'."\n";
      }
      session_write_close();
      echo '<p>'.@$lang['submit_ok'].'</p>'."\n";
    } else echo '<p class="error">Neplatné původní heslo!</p>'."\n";
  }
  session_write_close();
  if (!$data_name) $data_name=$user['FULLNAME'];
  echo'<p><form method="post" action="account-edit.php" class="regForm">'."\n";
  echo'  <label for="name">'.@$lang['name'].'</label> <input class="formText" type="text" id="name" name="name" value="'.$data_name.'"/><br/>'."\n";
  echo'  <label for="passwd1">'.@$lang['passwd1'].'</label> <input class="formText" type="password" id="passwd1" name="passwd1"/><br/>'."\n";
  echo'  <label for="passwd2">'.@$lang['passwd2'].'</label> <input class="formText" type="password" id="passwd2" name="passwd2"/><br/>'."\n";
  echo'  <label for="passwd3">'.@$lang['passwd3'].'</label> <input class="formText" type="password" id="passwd3" name="passwd3"/><br/>'."\n";
  echo'  <input class="formButton" type="submit" value="'.@$lang['submit'].'"/>'."\n";
  echo'</form></p>'."\n";
} else err_echo(@$lang['error_notlogedin']);
?>
