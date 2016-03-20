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

// PHP Catalog Web Interface: Menu Library

if (@$var_lang=='cs') { include "lang/headlist-cs.php"; } else include "lang/headlist-en.php";
global $auth;
echo '<div><span style="float: left"><a href="index.php'.$GLOBALS['pl'].'"';
if ($GLOBALS['current']=='index.php') echo ' style="background-color: yellow;"';
echo ' >'.@$lang['about'].'</a>&nbsp;&nbsp;<a href="browse.php'.$GLOBALS['pl'].'"';
if ($GLOBALS['current']=='browse.php') echo ' style="background-color: yellow;"';
echo '>'.@$lang['browse'].'</a>&nbsp;&nbsp;<a href="item.php'.$GLOBALS['pl'].'"';
if ($GLOBALS['current']=='item.php') echo ' style="background-color: yellow;"';
echo '>'.@$lang['specifications'].'</a>&nbsp;&nbsp;<a href="data.php'.$GLOBALS['pl'].'"';
if ($GLOBALS['current']=='data.php') echo ' style="background-color: yellow;"';
echo '>'.@$lang['files'].'</a>&nbsp;&nbsp;<a href="search.php'.$GLOBALS['pl'].'"';
if ($GLOBALS['current']=='search.php') echo ' style="background-color: yellow;"';
echo '>'.@$lang['search'].'</a></span>';
echo '<div style="text-align: right;" align="right">';
if (isset($auth)) {
  echo @$lang['user'].': <a href="account.php'.$GLOBALS['pl'].'">'.$auth['login'].'</a> <a href="'.$GLOBALS['current'].pl('logout=1&amp;item='.$var_item).'">['.@$lang['logout'].']</a>';
} else echo '<a href="login.php'.$GLOBALS['pl'].'">'.@$lang['login'].'</a>';
echo '</div></div>'."\n";
