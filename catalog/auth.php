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

// PHP Catalog Web Interface: Authentication validation

require 'db.php';
global $auth, $session_notclose;
if (!session_id()) {
  session_start();
  session_register('login');
  session_register('pwd');
}

if (@!$_SESSION["login"]) {
  if ($_COOKIE["LOGIN"]) {
    $login=$_COOKIE["LOGIN"];
    $pwd=$_COOKIE["PWD"];
    unset($_SESSION['login']);
    unset($_SESSION['pwd']);
    $_SESSION['login']=$login;
    $_SESSION['pwd']=$pwd;
    $auth=DB_SimpleQuery("SELECT * FROM XBXUSER WHERE login = '".htmlspecialchars($_SESSION["login"])."'");
    if ($auth['ID']) {
      if ($auth['CURRLOGIN']>$auth['LASTLOGIN']) {
        DB_Query("UPDATE XBXUSERINFO user_info SET lastlogin=currlogin, currlogin=FROM_UNIXTIME(".time().") WHERE id=".$auth['ID']);
      } else DB_Query("UPDATE XBXUSERINFO user_info SET currlogin=FROM_UNIXTIME(".time().") WHERE id=".$auth['ID']);
    }
    $auth['id'] = $auth['ID'];
    $auth['login'] = $auth['LOGIN'];
  }
}
if (@$_SESSION["login"]) {
  $auth=DB_SimpleQuery("SELECT * FROM XBXUSER WHERE login = '".htmlspecialchars($_SESSION["login"])."'");
  if ($_SESSION["pwd"]!=$auth['PASSWD']) {
    echo 'Chyba v SESSION!';
    unset($_SESSION['login']);
    unset($_SESSION['pwd']);
    session_destroy;
    unset($auth);
  } // otherwise trying to hack account?
  $auth['id'] = $auth['ID'];
  $auth['login'] = $auth['LOGIN'];
} else { unset($auth); }

// if (!$session_notclose) session_write_close();

if ((@$_GET['logout'])&& isset($auth)) {
  unset($_SESSION['login']);
  unset($_SESSION['pwd']);
  session_destroy;
  unset($auth);
  setcookie ("LOGIN", '',time()-60);
  setcookie ("PWD", '',time()-60);
  session_write_close();
} ?>
