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

// PHP Catalog Web Interface: Login dialogs

$GLOBALS['current']="login.php"; 
extract($_GET, EXTR_PREFIX_ALL, 'var'); extract($_POST, EXTR_PREFIX_ALL, 'var');
if (@$var_lang=='cs') { include "lang/login-cs.php"; } else include "lang/login-en.php";

$pagename=@$lang['pagename'];
include "db.php";

if (isset($var_register)) {
  $user = DB_SimpleQuery("SELECT * FROM XBXUSER WHERE login = '".htmlspecialchars($var_reglogin)."'");
  if (!$user) {
    if ($var_passwd1==$var_passwd2) {
      $curtime = time();
      DB_Query("INSERT INTO XBXUSER (login, passwd) VALUES ('".htmlspecialchars($var_reglogin)."','".MD5($var_passwd1)."')");
      $newuser = DB_SimpleQuery("SELECT id FROM XBXUSER WHERE login = '".htmlspecialchars($var_reglogin)."'");
      DB_Query('INSERT INTO XBXUSERINFO user_info (id,created,updated,lastlogin) VALUES ('.$newuser['id'].','.$curtime.','.$curtime.','.$curtime.')');
      unset($_SESSION['login']);
      unset($_SESSION['pwd']);
      session_destroy;
      session_start();
      $SESSION['login'] = $login;
      $SESSION['pwd'] = $pwd;
      setcookie("LOGIN", $var_reglogin,time()+3600*24*365);
      setcookie("PWD", MD5($var_passwd1),time()+3600*24*365);
      header('Location: account.php');
      exit;
    }
  }
}

if (isset($var_login)) {
  $auth=DB_SimpleQuery("SELECT * FROM XBXUSER WHERE login='".htmlspecialchars($var_userlogin)."'");
  $pwd = MD5($var_passwd);
  if ($pwd==$auth['PASSWD']) {
    $login = $var_userlogin;
    unset($_SESSION['login']);
    unset($_SESSION['pwd']);
    session_destroy;
    session_start();
    $SESSION['login'] = $login;
    $SESSION['pwd'] = $pwd;
    setcookie ("LOGIN", $login,time()+3600*24*365);
    setcookie ("PWD", $pwd,time()+3600*24*365);
    $item = DB_SimpleQuery("SELECT * FROM XBXUSERINFO user_info WHERE id=".$auth['ID']);
    if ($item['CURRLOGIN']>$item['LASTLOGIN']) {
      DB_Query("UPDATE XBXUSERINFO user_info SET lastlogin=currlogin, curraccess=FROM_UNIXTIME(".time().") WHERE id=".$auth['ID']);
    } else {
      DB_Query("UPDATE XBXUSERINFO user_info SET currlogin=FROM_UNIXTIME(".time().") WHERE id=".$auth['ID']);
    }
    header('Location: index.php');
    exit;
  }
}

include "include.php";
echo '<div>'.@$lang['return_pre'].'<a href="index.php">'.@$lang['return'].'</a>'.@$lang['return_post']."</div>\n";

if (isset($var_register)) {
  if ($user) {
    err_echo(@$lang['error_alreadyexists']);
  } else if ($var_passwd1!=$var_passwd2) err_echo(@$lang['error_paswordsdontmatch']); 
}

if (@$var_login) {
  if ($auth['passwd'][1]=='*') {
    err_echo(@$lang['error_accountblocked']);
	} else err_echo(@$lang['error_loginerror']);
}

// Login Dialog
echo '<form method="post" action="login.php" class="regForm">'."\n";
echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['login_form']."</legend>\n";
echo '  <label for="login">'.@$lang['login']."</label><br/>\n";
echo '  <input class="formText" id="login" type="text" name="userlogin" value="'.$var_userlogin."\"/><br/>\n";
echo '  <label for="passwd">'.@$lang['password']."</label><br/>\n";
echo '  <input class="formText" id="passwd" type="password" name="passwd"/><br/>'."\n";
echo '  <input type="submit" name="login" value="'.@$lang['login_submit'].'" class="formButton"/>'."\n";
echo "</fieldset>\n</form>\n";

// Registration Dialog
echo'<form method="post" action="login.php" class="regForm">'."\n";
echo '<fieldset style="padding: 5px 5px 5px 5px;"><legend>'.@$lang['reg_form']."</legend>\n";
echo'  <label for="login">'.@$lang['login']."</label><br/>\n";
echo'  <input class="formText" type="text" id="reglogin" name="reglogin" value="'.@$var_reglogin.'"/><br/>'."\n";
echo'  <label for="passwd1">'.@$lang['password']."</label><br/>\n";
echo'  <input class="formText" type="password" id="passwd1" name="passwd1"/><br/>'."\n";
echo'  <label for="passwd1">'.@$lang['reg_check']."</label><br/>\n";
echo'  <input class="formText" type="password" id="passwd2" name="passwd2"/><br/>'."\n";
  echo'  <input class="formButton" type="submit" name="register" value="'.@$lang['reg_submit'].'"/>'."\n";
echo "</fieldset>\n</form>\n";
done(); ?>
