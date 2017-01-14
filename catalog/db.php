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

// PHP Catalog Web Interface: MySQL DB Library

error_reporting(E_ALL & ~E_NOTICE);

if (!function_exists('DB_Init')) {


$cfg['dbhost']='localhost';
$cfg['dbuname']='xbup';
$cfg['dbpass']='catalog';
$cfg['dbname']='xbcatalog-dev';
$cfg['dbprefix']='';

/*
$cfg['dbhost']='mysql4-x';
$cfg['dbuname']='x91848rw';
$cfg['dbpass']='xbufubx';
$cfg['dbname']='catalog';
$cfg['dbprefix']='x91848_';
*/
$DB_LastResults = array();  // Dočasné uchování výsledků

// Inicializace databaze
function DB_Init($host,$user,$password,$name)
{
  global $db_link, $DB_Tables;
  $db_link = mysqli_connect($host,$user,$password, $name);
  if(mysqli_connect_errno()==1049) {
	db_query("CREATE DATABASE $name");
	$db_link = mysqli_connect($host,$user,$password, $name);
  }
//  $Tables = mysqli_list_tables($db_link, $name);
//  $DB_Tables = array();
//  for($I=0;$I<mysqli_num_rows($Tables);$I++) {
//    $DB_Tables[$I] = mysqli_tablename($Tables,$I);
//  }
}

function DB_Close() {
  global $db_link;
  if ($db_link) mysqli_close($db_link);
}

// Dotaz na databázi
function DB_Query($query)
{
  global $db_result,$db_link,$DB_LastResults;
  //echo('DB: Požadavek('.$query.')<br>');
  //$DB_LastResults[0] = mysqli_query($db_link, $query);
  //System_ShowArray($DB_LastResults);
  $db_result = mysqli_query($db_link, $query);
  if(mysqli_error($db_link)) echo('DB: Chyba požadavku číslo '.mysqli_errno($db_link).'!('.mysqli_error($db_link).')<br>Požadavek: '.$query.'<br>');
}

// Výběr dalšího řádku
function DB_Row()
{
  global $db_result;
  return(mysqli_fetch_array($db_result));
}

function DB_SimpleQuery($query)
{
  global $db_result,$DB_LastResults;
  DB_Query($query);
  if (@$db_result) { return(mysqli_fetch_array($db_result)); } else return array();
}

// Pocet vracenych radku
function DB_NumRows()
{
  global $db_result;
  return(mysqli_num_rows($db_result));
}

// Uschova vysledek
function DB_Save()
{
  global $db_result,$DB_LastResults;
  array_push($DB_LastResults,$db_result);
  //System_ShowArray($DB_LastResults);
}

// Nacte predchozi vysledek
function DB_Load()
{
  global $db_result,$DB_LastResults;
  $db_result = array_pop($DB_LastResults);
}

// Vlozeni noveho radku do databaze
function DB_Insert($table,$data)
{
  $name = '';
  $values = '';
  foreach($data as $key => $value)
  {
    $value = strtr($value,'"','\"');
    $name .= ",".$key;
    if($value=='NOW()') $values .= ",".$value;
    else $values .= ',"'.$value.'"';
  }
  $name = substr($name,1);
  $values = substr($values,1);
  db_query("INSERT INTO $table ($name) VALUES($values)");
  //echo("INSERT INTO $table ($name) VALUES($values)");
}

// Vlozeni noveho radku do databaze
function DB_Update($table,$condition,$data)
{
  $name = '';
  $values = '';
  foreach($data as $key => $value)
  {
    $value = strtr($value,'"','\"');
    if($value!='NOW()') $value = '"'.$value.'"';
    $values .= ", ".$key."=".$value;
  }
  $values = substr($values,2);
  DB_Query("UPDATE $table SET $values WHERE ($condition)");
  //echo("DB_Update: UPDATE $table SET $values WHERE ($condition)\n");
}

DB_Init($cfg['dbhost'],$cfg['dbuname'],$cfg['dbpass'],$cfg['dbprefix'].$cfg['dbname']);
DB_Query("SET NAMES 'utf8'");

} ?>
