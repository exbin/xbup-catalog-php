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

// PHP Catalog Web Interface: Page Head Library

/* echo '<?xml version="1.0" encoding="utf-8"?>'; */
import_request_variables('gP','var_'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<meta http-equiv="Content-Language" content="<?php
global $var_lang, $var_langcode;
if (@$var_lang=='cs') {
  echo 'cs';
  $var_langcode = 'cs_CZ';
  $GLOBALS['pl']='?lang=cs';
} else {
  echo 'en';
  $var_langcode = 'en';
  $var_lang='en';
  $GLOBALS['pl']='';
}

$GLOBALS['catalog_version'] = 'V.0.2.0';
$GLOBALS['catalog_mode'] = 'DEV';
$GLOBALS['catalog_updated'] = '2016-03-20';
$GLOBALS['catalog_license'] = 'GNU LGPL';
$GLOBALS['catalog_author'] = 'ExBin Project';
$GLOBALS['catalog_copyright'] = '(C) ExBin Project';
$GLOBALS['catalog_description'] = 'XBUP Catalog written in PHP';
$GLOBALS['catalog_homepage'] = 'http://xbup.exbin.org';
$GLOBALS['catalog_title'] = 'XBUP Catalog';
?>"/>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta name="author" content="<?php echo $GLOBALS['catalog_author'].' '.$GLOBALS['catalog_homepage']; ?>"/>
<meta name="copyright" content="<?php echo $GLOBALS['catalog_copyright'].', '.$GLOBALS['catalog_license']; ?>"/>
<meta name="description" content="<?php echo $GLOBALS['catalog_description']; ?>"/>
<meta name="keywords" content="xbup, xbuf, extensible, binary, protocol, format, universal, catalog"/>
<meta name="robots" content="index,follow"/>
<link rel="shortcut icon" href="favicon.ico"/>
<link rel="stylesheet" href="styles/global.css" type="text/css" media="screen, projection" />
<?php echo @$GLOBALS['stylesheets']; ?>
<title><?php echo $GLOBALS['catalog_title']; if (@$pagename) echo ' - '.$pagename; ?></title>
</head>
<body><?php function done() { ?>
</div></div>

</div></body></html><?php } ?>
