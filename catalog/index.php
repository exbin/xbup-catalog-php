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

// PHP Catalog Web Interface: Index Page

$GLOBALS['current']="index.php"; 
extract($_GET, EXTR_PREFIX_ALL, 'var'); extract($_POST, EXTR_PREFIX_ALL, 'var');

function getspecbyxbpath($path) {
  $maxjoin = 2;// Maximum allowed join depth
  $spath = @explode('/',$path);
  $result = '';
  $i = 1;
  $query = '';
  $specid = @array_pop($spath);
  @array_push($spath,'');
  while (($xb = @array_shift($spath))!=='') {
    if ($i>$maxjoin) {
      if ($result) {
        $query .= " WHERE n1.dtype='XBNode' AND n1.id=".$result['ID'];
      } else $query .= " WHERE n1.dtype='XBNode' AND n1.owner_id IS NULL ";
      $query = 'SELECT n'.$i.'.* FROM XBITEM AS n1' .$query;
      $result = DB_SimpleQuery($query);
      $i = 1;
      $query = '';
    }
    $xb+=0;
    $i++;
    $query .=' LEFT JOIN XBITEM AS n'.$i.' ON (n'.$i.'.owner_id = n'.($i-1).'.id) AND n'.$i.'.xbindex = '.$xb.' AND n'.$i.".dtype = 'XBNode'";
  }
  if ($result) {
    $query .= " WHERE n1.dtype='XBNode' AND n1.id=".$result['ID'];
  } else $query .= " WHERE n1.dtype='XBNode' AND n1.owner_id IS NULL";
  $query = 'SELECT n'.$i.'.* FROM XBITEM AS n1' .$query;
  $node = DB_SimpleQuery($query);
  if ($node) {
    $spec = DB_SimpleQuery('SELECT * FROM XBITEM WHERE owner_id = '.$node['ID'].' AND xbindex='.$specid." AND dtype = 'XBBlockSpec'");
  }
  return $spec;
}

if (@$var_lang=='cs') { include "lang/index-cs.php"; } else include "lang/index-en.php";
$pagename=@$lang['pagename'];
$dateform='j.n.Y';
include "db.php";
if (isset($var_spec)) {
  $spec = getspecbyxbpath($var_spec);
  header( 'Location: browse.php?item='.$spec['ID'] );
  exit;
}
include "include.php";

include "auth.php"; global $auth; 
include 'include-menu.php';
echo "<div>\n";

// Print information about catalog
echo '  <fieldset><legend>'.@$lang['about_legend']."</legend>\n";
echo '  '.@$lang['about_text']."\n";
echo "  </fieldset>\n";

// Print catalog status
echo '  <fieldset><legend>'.@$lang['status_legend']."</legend>\n";
echo '  <p>'.@$lang['status_text']."</p>\n";
$result = DB_SimpleQuery('SELECT COUNT(*) FROM XBITEM');
echo '  '.@$lang['status_items'].': '.$result[0]."<br/>\n";
$result = DB_SimpleQuery('SELECT COUNT(*) FROM XBREV');
echo '  '.@$lang['status_revisions'].': '.$result[0]."<br/>\n";
$result = DB_SimpleQuery('SELECT COUNT(*) FROM XBSPECDEF');
echo '  '.@$lang['status_defs'].': '.$result[0]."<br/>\n";
$result = DB_SimpleQuery('SELECT COUNT(*) FROM XBITEMINFO');
echo '  '.@$lang['status_infos'].': '.$result[0]."<br/>\n";
$result = DB_SimpleQuery('SELECT COUNT(*) FROM XBXNAME');
echo '  '.@$lang['status_names'].': '.$result[0]."<br/>\n";
$result = DB_SimpleQuery('SELECT COUNT(*) FROM XBXDESC');
echo '  '.@$lang['status_descs'].': '.$result[0]."<br/>\n";
$result = DB_SimpleQuery('SELECT COUNT(*) FROM XBXSTRI');
echo '  '.@$lang['status_stris'].': '.$result[0]."<br/>\n";
$result = DB_SimpleQuery('SELECT COUNT(*) FROM XBXICON');
echo '  '.@$lang['status_icons'].': '.$result[0]."<br/>\n";
$result = DB_SimpleQuery('SELECT COUNT(*) FROM XBITEMLIMI');
echo '  '.@$lang['status_limits'].': '.$result[0]."<br/>\n";
echo "  </fieldset>\n";

// Print supported levels
echo '  <fieldset><legend>'.@$lang['level_legend']."</legend>\n";
echo '  <p>'.@$lang['level_text']."</p>\n";
while ($lev = array_shift($lang['level_support'])) {
  echo '  '.$lev."<br/>\n";
}
echo '  <p>'.@$lang['level_text2']."</p>\n";
while ($lev = array_shift($lang['level_planned'])) {
  echo '  '.$lev."<br/>\n";
}
echo "  </fieldset>\n";

// Print supported extensions
echo '  <fieldset><legend>'.@$lang['extension_legend']."</legend>\n";
echo '  <p>'.@$lang['extension_text']."</p>\n";
while ($ext = array_shift($lang['extension_support'])) {
  echo '  '.$ext."<br/>\n";
}
echo '  <p>'.@$lang['extension_text2']."</p>\n";
while ($ext = array_shift($lang['extension_planned'])) {
  echo '  '.$ext."<br/>\n";
}
echo "  </fieldset>\n";

echo '<div><br/>'.@$lang['lastupdate'].' '.$GLOBALS['catalog_updated'].', '.$GLOBALS['catalog_version'].' '.$GLOBALS['catalog_mode'].' - '.$GLOBALS['catalog_license'].' '.$GLOBALS['catalog_copyright']."</div>\n";
echo "</div>\n";

done(); ?>
