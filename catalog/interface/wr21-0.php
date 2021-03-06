<?php error_reporting(E_ALL & ~E_NOTICE); include 'db.php';
extract($_GET, EXTR_PREFIX_ALL, 'var'); extract($_POST, EXTR_PREFIX_ALL, 'var');
global $command_id, $dtype_names, $dtype_codes, $btype_names, $btype_codes;
$command_id = 0;

$dtype_names = array(0 => 'XBENode',1 => 'XBEFormatSpec', 2 => 'XBEGroupSpec', 3 => 'XBEBlockSpec');
$dtype_codes = array('XBENode' => 0,'XBEFormatSpec' => 1, 'XBEGroupSpec' => 2, 'XBEBlockSpec' => 3);
$spec_codes = array('XBEFormatSpec' => 0,'XBEGroupSpec' => 1, 'XBEBlockSpec' => 2);
$btype_names = array(0 => 'XBEBlockCons',1 => 'XBEBlockJoin', 2 => 'XBEBlockListCons', 3 => 'XBEBlockListJoin');
$btype_codes = array(
  0 => array('XBEFormatCons' => 0,'XBEFormatJoin' => 1, 'XBEFormatListCons' => 2, 'XBEFormatListJoin' => 3),
  1 => array('XBEGroupCons' => 0,'XBEGroupJoin' => 1, 'XBEGroupListCons' => 2, 'XBEGroupListJoin' => 3),
  2 => array('XBEBlockCons' => 0,'XBEBlockJoin' => 1, 'XBEBlockListCons' => 2, 'XBEBlockListJoin' => 3)
);

//global $fl;
//@$fl = fopen("compare3.log",'r');

function getnodebyxbpath($path) {
  $maxjoin = 2;// Maximum allowed join depth
  $spath = @explode('/',$path);
  $result = '';
  $i = 1;
  $query = '';
  while (($xb = @array_shift($spath))!=='') {
    if ($i>$maxjoin) {
      if ($result) {
        $query .= " WHERE n1.dtype='XBENode' AND n1.id=".$result['ID'];
      } else $query .= " WHERE n1.dtype='XBENode' AND n1.owner_id IS NULL";
      $query = 'SELECT n'.$i.'.* FROM XBITEM AS n1' .$query;
      $result = DB_SimpleQuery($query);
      $i = 1;
      $query = '';
    }
    $xb+=0;
    $i++;
    $query .=' LEFT JOIN XBITEM AS n'.$i.' ON (n'.$i.'.owner_id = n'.($i-1).'.id) AND n'.$i.'.xbindex = '.$xb.' AND n'.$i.".dtype = 'XBENode'";
  }
  if ($result) {
    $query .= " WHERE n1.dtype='XBENode' AND n1.id=".$result['ID'];
  } else $query .= " WHERE n1.dtype= 'XBENode' AND n1.owner_id IS NULL";
  $query = 'SELECT n'.$i.'.* FROM XBITEM AS n1' .$query;
  return DB_SimpleQuery($query);
}

// Main processing block -------------------------------------------------------
function processCommand($command) {
global $var_node,$var_path,$var_file,$var_dtype,$var_spec,$var_lang,$var_id,$var_owner,$var_xbindex,$var_prev,$var_plugin,$var_plug,$var_line,$var_pane,$var_code,$var_origin,$var_prior,$var_rev, $var_debug;
if ($var_debug) echo 'DEBUG: CALL: ?op='.$command;
switch($command) {
  case "getnode": {
    if ($var_debug) echo "&path=".$var_path;
    global $node;
    $node = getnodebyxbpath($var_path);
    if ($node['ID']) {
      if (!$var_debug) echo "id\n".$node['ID']."\nxbindex\n".$node['XBINDEX']."\n"; //xblimit\n".$node['xblimit']."\n";
    }
    break;
  }

  case "getnodemax": {
    if ($var_debug) echo "&dtype=".$var_dtype."&path=".$var_path;
    global $spec, $dtype_names;
//    echo "dtype: ".$var_dtype."\n";
    $var_dtype += 0;
    $node = getnodebyxbpath($var_path);
    if ($node['ID']) {
      $spec = DB_SimpleQuery('SELECT MAX(xbindex) AS max FROM XBITEM item WHERE owner_id = '.$node['ID']." AND dtype = '".$dtype_names[$var_dtype]."'");
      if ($spec) {
        if (!$var_debug) echo "max\n".$spec['max']."\n";
      }
    }
    break;
  }

  case "getspec": {
    if ($var_debug) echo "&spec=".$var_spec."&dtype=".$var_dtype."&path=".$var_path;
    global $spec, $dtype_names;
    $var_dtype += 1;
    $var_spec += 0;
    $node = getnodebyxbpath($var_path);
//    echo "parent: ".$node['id']." xbindex: ".$var_spec."\n";
    if ($node['ID']) {
      $spec = DB_SimpleQuery('SELECT * FROM XBITEM item WHERE owner_id = '.$node['ID'].' AND xbindex='.$var_spec." AND dtype = '".$dtype_names[$var_dtype]."'");
      if ($spec) {
        if (!$var_debug) echo "id\n".$spec['ID']."\nxbindex\n".$spec['XBINDEX']."\nxblimit\n".$spec['XBLIMIT']."\n";
      }
    }
    break;
  }

  case "getname": {
    if ($var_debug) echo "&id=".$var_id."&lang=".$var_lang;
    global $name;
    $var_lang += 0;
    $var_id += 0;
    $name = DB_SimpleQuery('SELECT * FROM XBXNAME item_name WHERE item_id = '.$var_id.' AND lang_id='.$var_lang);
    if ($name) {
      if (!$var_debug) echo "id\n".$name['ID']."\ntext\n".$name['TEXT']."\n";
    }
    break;
  }

  case "getdesc": {
    if ($var_debug) echo "&id=".$var_id."&lang=".$var_lang;
    global $desc;
    $var_lang += 0;
    $var_id += 0;
    $desc = DB_SimpleQuery('SELECT * FROM XBXDESC item_desc WHERE item_id = '.$var_id.' AND lang_id='.$var_lang);
    if ($desc) {
      if (!$var_debug) echo "id\n".$desc['ID']."\ntext\n".$desc['TEXT']."\n";
    }
    break;
  }

  case "getstri": {
    if ($var_debug) echo "&id=".$var_id;
    global $stri;
    $var_id += 0;
    $stri = DB_SimpleQuery('SELECT * FROM XBXSTRI item_stri WHERE item_id = '.$var_id);
    if ($stri) {
      if (!$var_debug) echo "id\n".$stri['ID']."\ntext\n".$stri['TEXT']."\nnodepath\n".$stri['NODEPATH']."\n";
    }
    break;
  }

  case "getdef": {
    if ($var_debug) echo "&origin=".$var_origin."&xbindex=".$var_xbindex;
    global $def, $btype_codes, $spec_codes;
    $var_origin += 0;
    $var_xbindex += 0;
    $def = DB_SimpleQuery('SELECT *, rev_item.xbindex AS revxb, rev_item.owner_id AS spec, item.dtype AS btype, origin.dtype AS spectype FROM XBITEM item, XBSPECDEF def, XBREV item_rev, XBITEM rev_item, XBITEM origin WHERE item.id = def.id AND rev_item.id = item_rev.id AND origin.id = item.owner_id AND item.owner_id = '.$var_origin.' AND item.xbindex='.$var_xbindex.' AND item_rev.id = def.target_id');
    if ($def) {
      if (!$var_debug) echo "id\n".$def['ID']."\ntarget\n".$def['TARGET_ID']."\nbtype\n".$btype_codes[$spec_codes[$def['spectype']]][$def['btype']]."\nspec\n".$def['spec']."\nrevxb\n".$def['revxb']."\n";
    }
    break;
  }

  case "getdefmax": {
    if ($var_debug) echo "&origin=".$var_origin;
    global $def;
    $var_origin += 0;
    $def = DB_SimpleQuery('SELECT MAX(xbindex) AS max FROM XBSPECDEF def WHERE def.spec_id = '.$var_origin);
    if ($def) {
      if (!$var_debug) echo "max\n".$def['max']."\n";
    }
    break;
  }

  case "getrev": {
    if ($var_debug) echo "&owner=".$var_owner."&xbindex=".$var_xbindex;
    global $rev;
    $var_owner += 0;
    $var_xbindex += 0;
    $rev = DB_SimpleQuery('SELECT *, item_rev.ID AS ID FROM XBREV item_rev, XBITEM item WHERE item.id = item_rev.id AND item.owner_id = '.$var_owner.' AND item.xbindex='.$var_xbindex);
    if ($rev) {
      if (!$var_debug) echo "id\n".$rev['ID']."\nxbindex\n".$rev['XBINDEX']."\nxblimit\n".$rev['XBLIMIT']."\n";
    }
    break;
  }

  case "getrevmax": {
    if ($var_debug) echo "&owner=".$var_owner;
    global $rev;
    $var_owner += 0;
    $rev = DB_SimpleQuery('SELECT MAX(xbindex) AS max FROM XBREV item_rev, XBITEM item WHERE item.id = item_rev.id AND item.owner_id = '.$var_owner);
    if ($rev) {
      if (!$var_debug) echo "max\n".$rev['max']."\n";
    }
    break;
  }

  case "getlang": {
    global $lang;
    $lang = DB_SimpleQuery("SELECT * FROM XBXLANGUAGE lang WHERE langcode = '".htmlspecialchars($var_code)."'");
    if ($lang) {
      if (!$var_debug) echo "id\n".$lang['ID']."\ncaption\n".$lang['LANGCODE']."\nname\n".$lang['NAME']."\n";
    }
    break;
  }

  case "getnodepath": {
    if ($var_debug) echo "&node=".$var_node;
    global $path;
    $var_node += 0;
    $node = DB_SimpleQuery('SELECT * FROM XBITEM item WHERE id = '.$var_node);
    if ($node) {
      $path = $node['XBINDEX'];
      while ($node['OWNER_ID']>0) {
        $node = DB_SimpleQuery('SELECT * FROM XBITEM item WHERE id = '.$node['OWNER_ID']);
        if ($node['OWNER_ID']>0) $path = $node['XBINDEX'].'/'.$path;
      }
      if (!$var_debug) echo "path\n".$path."\n";
    }
    break;
  }

 // TODO: Remove?
 case "getinfo": {
    global $info;
    $var_owner += 0;
    $info = DB_SimpleQuery('SELECT * FROM item_info WHERE owner = '.$var_owner);
    if ($info) {
      if (!$var_debug) echo "id\n".$info['id']."\nfilename\n".$info['filename']."\npath\n".$info['path']."\n";
    }
    break;
  }

  case "getfile": {
    if ($var_debug) echo "&node=".$var_node;
    global $file;
    $var_node += 0;
    $var_prev += 0;
    $var_file += 0;
    if ($var_file>0) {
        $file = DB_SimpleQuery('SELECT * FROM XBXFILE item_file WHERE id = '.$var_file.' AND NOT EXISTS(SELECT 1 FROM XBXFILE fl WHERE fl.node_id = item_file.node_id AND fl.id < item_file.id)');
    } else if (isset($var_name)) {
        $file = DB_SimpleQuery('SELECT * FROM XBXFILE item_file WHERE node_id = '.$var_node." AND filename = ".htmlspecialchars($var_name)."'");
    } else {
      if ($var_prev>0) {
        $file = DB_SimpleQuery('SELECT * FROM XBXFILE item_file WHERE node_id = '.$var_node.' AND id > '.$var_prev.' AND NOT EXISTS(SELECT 1 FROM XBXFILE fl WHERE fl.id > '.$var_prev.' AND fl.node_id= item_file.node_id AND fl.id < item_file.id)');
      } else {
        $file = DB_SimpleQuery('SELECT * FROM XBXFILE item_file WHERE node_id = '.$var_node.' AND NOT EXISTS(SELECT 1 FROM XBXFILE fl WHERE fl.node_id= item_file.node_id AND fl.id < item_file.id)');
      }
    }
    if ($file) {
      if (!$var_debug) echo "id\n".$file['ID']."\nowner\n".$file['NODE_ID']."\nfilename\n".$file['FILENAME']."\n";
    }
    break;
  }

  case "geticon": {
    if ($var_debug) echo "&owner=".$var_owner."&xbindex=".$var_xbindex;
    global $icon;
    $var_owner += 0;
    $var_xbindex += 0;
    $icon = DB_SimpleQuery('SELECT item_icon.id AS id, item_icon.*, item_file.filename, item_file.node_id AS node FROM XBXICON item_icon, XBXFILE item_file WHERE item_file.id = item_icon.iconfile_id AND item_icon.owner_id = '.$var_owner.' AND item_icon.xbindex='.$var_xbindex);
    if ($icon) {
      if (!$var_debug) echo "id\n".$icon['id']."\nowner\n".$icon['OWNER_ID']."\nmode\n".$icon['MODE_ID']."\nicon\n".$icon['ICONFILE_ID']."\nnode\n".$icon['node']."\nfilename\n".$icon['filename']."\n";
    }
    break;
  }

  case "geticonmax": {
    if ($var_debug) echo "&owner=".$var_owner;
    global $icon;
    $var_owner += 0;
    $icon = DB_SimpleQuery('SELECT MAX(xbindex) AS max FROM XBXICON item_icon WHERE owner_id = '.$var_owner);
    if ($icon) {
      if (!$var_debug) echo "max\n".$icon['max']."\n";
    }
    break;
  }

  case "getlimi": {
    global $limi;
    $var_owner += 0;
    $var_xbindex += 0;
    $limi = DB_SimpleQuery('SELECT * FROM XBITEMLIMI item_limi WHERE owner_id = '.$var_owner.' AND xbindex='.$var_xbindex);
    if ($limi) {
      if (!$var_debug) echo "id\n".$limi['ID']."\ntarget\n".$limi['TARGET']."\n";
    }
    break;
  }

  case "getlimimax": {
    if ($var_debug) echo "&owner=".$var_owner;
    global $limi;
    $var_owner += 0;
    $limi = DB_SimpleQuery('SELECT MAX(xbindex) AS max FROM XBITEMLIMI item_limi WHERE owner_id = '.$var_owner);
    if ($limi) {
      if (!$var_debug) echo "max\n".$limi['max']."\n";
    }
    break;
  }

  case "gettran": {
    global $tran;
    $var_owner += 0;
    $var_prev += 0;
    if ($var_prev>0) {
      $tran = DB_SimpleQuery('SELECT item_tran.* FROM XBTRAN item_tran, XBTRAN fp WHERE item_tran.item_id = '.$var_owner.' AND fp.item_id = item_tran.item_id AND fp.id = '.$var_prev.' AND item_tran.id > fp.id AND NOT EXISTS(SELECT 1 FROM XBTRAN fl WHERE fl.item_id='.$var_owner.' AND fl.id < item_tran.id AND fl.id > fp.id)');
    } else {
      $tran = DB_SimpleQuery('SELECT * FROM XBTRAN item_tran WHERE item_id = '.$var_owner.' AND NOT EXISTS(SELECT 1 FROM XBTRAN fl WHERE fl.item_id = item_tran.item_id AND fl.id < item_tran.id)');
    }
    if ($tran) {
      if (!$var_debug) echo "id\n".$tran['ID']."\ntarget\n".$tran['TARGET_ID']."\nlimt\n".$tran['LIMIT_ID']."\nexcp\n".$tran['EXCEPT_ID']."\n";
    }
    break;
  }

  case "getplug": {
    if ($var_debug) echo "&node=".$var_node."&plugin=".$var_plugin;
    global $plug;
    $var_node += 0;
    $var_plugin += 0;
    $plug = DB_SimpleQuery('SELECT plug.id AS plug_id, plug.*, item_file.* FROM XBXPLUGIN plug LEFT JOIN XBXFILE item_file ON item_file.id = plug.pluginfile_id WHERE owner_id = '.$var_node.' AND pluginindex='.$var_plugin);
    if ($plug) {
      if (!$var_debug) echo "id\n".$plug['plug_id']."\nplugin\n".$plug['PLUGININDEX']."\nfile\n".$plug['PLUGINFILE_ID']."\nfilenode\n".$plug['NODE_ID']."\nfilename\n".$plug['FILENAME']."\n";
    }
    break;
  }

  case "getplugmax": {
    if ($var_debug) echo "&node=".$var_node;
    global $plug;
    $var_node += 0;
    $plug = DB_SimpleQuery('SELECT MAX(pluginindex) AS max FROM XBXPLUGIN plug WHERE owner_id = '.$var_node);
    if ($plug) {
      if (!$var_debug) echo "max\n".$plug['max']."\n";
    }
    break;
  }

  case "getplugline": {
    if ($var_debug) echo "&plug=".$var_plug."&line=".$var_line;
    global $plug;
    $var_plug += 0;
    $var_line += 0;
    $plug = DB_SimpleQuery('SELECT * FROM XBXPLUGLINE plug_line WHERE plugin_id = '.$var_plug.' AND lineindex='.$var_line);
    if ($plug) {
      if (!$var_debug) echo "id\n".$plug['ID']."\nplug\n".$plug['PLUGIN_ID']."\nline\n".$rev['LINEINDEX']."\n";
    }
    break;
  }

  case "getpluglinemax": {
    if ($var_debug) echo "&plug=".$var_plug;
    global $plug;
    $var_plug += 0;
    $plug = DB_SimpleQuery('SELECT MAX(lineindex) AS max FROM XBXPLUGLINE plug_line WHERE plugin_id = '.$var_plug);
    if ($plug) {
      if (!$var_debug) echo "max\n".$plug['max']."\n";
    }
    break;
  }

  case "getplugpane": {
    if ($var_debug) echo "&plug=".$var_plug."&pane=".$var_pane;
    global $plug;
    $var_plug += 0;
    $var_pane += 0;
    $plug = DB_SimpleQuery('SELECT * FROM XBXPLUGPANE plug_pane WHERE plugin_id = '.$var_plug.' AND paneindex='.$var_pane);
    if ($plug) {
      if (!$var_debug) echo "id\n".$plug['ID']."\nplug\n".$plug['PLUGIN_ID']."\npane\n".$rev['PANEINDEX']."\n";
    }
    break;
  }

  case "getplugpanemax": {
    if ($var_debug) echo "&plug=".$var_plug;
    global $plug;
    $var_plug += 0;
    $plug = DB_SimpleQuery('SELECT MAX(paneindex) AS max FROM XBXPLUGPANE plug_pane WHERE plugin_id = '.$var_plug);
    if ($plug) {
      if (!$var_debug) echo "max\n".$plug['max']."\n";
    }
    break;
  }

  case "getline": {
    if ($var_debug) echo "&rev=".$var_rev."&prior=".$var_prior;
    global $line;
    $var_rev += 0;
    $var_prior += 0;
    $line = DB_SimpleQuery('SELECT item_line.id AS id, plug_line.line AS plugin_id, item_line.line_id AS lineid, item_line.*, plug_line.*, plug.* FROM XBXBLOCKLINE item_line LEFT JOIN XBXPLUGLINE plug_line ON plug_line.id = item_line.line_id LEFT JOIN XBXPLUGIN plug ON plug.id = plug_line.plugin_id WHERE blockrev_id = '.$var_rev.' AND priority='.$var_prior);
    if ($line) {
      if (!$var_debug) echo "id\n".$line['id']."\nlineid\n".$line['lineid']."\nline\n".$line['line']."\nplug\n".$line['LINEINDEX']."\nplugin\n".$line['PLUGININDEX']."\nplugnode\n".$line['OWNER_ID']."\n";
    }
    break;
  }

  case "getlinemax": {
    if ($var_debug) echo "&rev=".$var_rev;
    global $line;
    $var_rev += 0;
    $line = DB_SimpleQuery('SELECT MAX(priority) AS max FROM XBXBLOCKLINE item_line WHERE blockrev_id = '.$var_rev);
    if ($line) {
      if (!$var_debug) echo "max\n".$line['max']."\n";
    }
    break;
  }

  case "getpane": {
    if ($var_debug) echo "&rev=".$var_rev."&prior=".$var_prior;
    global $pane;
    $var_rev += 0;
    $var_prior += 0;
    $pane = DB_SimpleQuery('SELECT item_pane.id AS id, plug_pane.plugin_id AS pluginid, item_pane.pane_id AS paneid, item_pane.*, plug_pane.*, plug.* FROM XBXBLOCKPANE item_pane LEFT JOIN XBXPLUGPANE plug_pane ON plug_pane.id = item_pane.pane_id LEFT JOIN XBXPLUGIN plug ON plug.id = plug_pane.plugin_id WHERE blockrev_id = '.$var_rev.' AND priority='.$var_prior);
    if ($pane) {
      if (!$var_debug) echo "id\n".$pane['id']."\npane\n".$pane['PANEINDEX']."\npaneid\n".$pane['paneid']."\nplug\n".$pane['pluginid']."\nplugin\n".$pane['PLUGININDEX']."\nplugnode\n".$pane['OWNER_ID']."\n";
    }
    break;
  }

  case "getpanemax": {
    if ($var_debug) echo "&rev=".$var_rev;
    global $pane;
    $var_rev += 0;
    $pane = DB_SimpleQuery('SELECT MAX(priority) AS max FROM XBXBLOCKPANE item_pane WHERE blockrev_id = '.$var_rev);
    if ($pane) {
      if (!$var_debug) echo "max\n".$pane['max']."\n";
    }
    break;
  }

  case "getfull": {
    if ($var_debug) echo "\n";
    $var_lang = 1;

    $node_id = getCatalogNodeInfo("");
    processAllNodes($node_id, "");
    processAllPlugins($node_id, "");
    processAllBinds($node_id, "");
    break;
  }
  default: {
    echo 'Unknown command: ' . $command;
  }
}
}

function compositeCommand($command) {
  global $command_id;
//  global $fl;
  $command_id++;
//  echo "Command:".$command." [".$command_id."]\n";
//  $line = getline($fl);
//  if (substr($line,0,strlen($command)) != $command) exit();
  processCommand($command);
  echo "\n";
}

function getCatalogNodeInfo($node_path) {
  global $var_id, $var_path, $node;
  $var_path = $node_path;
  compositeCommand("getnode");
  $var_id = $node['ID'];
  if ($var_id>0) {
    compositeCommand("getname");
    compositeCommand("getdesc");
  }
  return $node['ID'];
}

function processAllNodes($node_id, $node_path) {
  if ($node_id > 0) {
    processNodeFiles($node_id, $node_path);
    processNodeIcons($node_id, $node_path);

    $max = getSubNodeCatalogMaxIndex($node_id, $node_path);
    if (isset($max)) {
      global $var_path;
      for($i=0;$i<=$max;$i++) {
        $sub_path = $node_path.$i."/";
        $sub_id = getCatalogNodeInfo($sub_path);
        processAllNodes($sub_id,$sub_path);
      }
    }

    processNodeFormatSpecs($node_id, $node_path);
    processNodeGroupSpecs($node_id, $node_path);
    processNodeBlockSpecs($node_id, $node_path);
  }
}

function getSubNodeCatalogMaxIndex($node_id, $node_path) {
  if ($node_id>0) {
    global $var_path, $var_dtype, $spec;
    $var_path = $node_path;
    $var_dtype = 0;
    compositeCommand("getnodemax");
    return $spec['max'];
  }
}

function processNodeFiles($node_id, $node_path) {
  if ($node_id > 0) {
    global $file;
    $info_id = getCatalogNodeFile($node_path);
    if ($info_id > 0) {
      unset($prev);
      do {
        getCatalogFile($node_path, $prev);
        $prev = $file['ID'];
      } while ($file['ID']>0);
    }
  }
}

function getCatalogNodeFile($node_path) {
  global $var_path, $var_owner, $node, $stri;
  $var_path = $node_path;
  compositeCommand("getnode");
  $var_owner = $node['ID'];
  compositeCommand("getstri");
  return $stri['ID'];
}

function getCatalogFile($node_path, $prev) {
  global $var_path, $var_prev, $var_node, $node, $file;
  $var_path = $node_path;
  compositeCommand("getnode");
  if ($node['ID'] > 0) {
    $var_node = $node['ID'];
    $var_prev = $prev;
    unset($var_file);
    compositeCommand("getfile");
  }
}

function processNodeIcons($node_id, $node_path) {
  global $var_path, $node;
  if ($node_id > 0) {
    $var_path = $node_path;
    compositeCommand("getnode");
    processItemIcons($node_id, $node_path);
  }
}

function processItemIcons($item_id, $node_path) {
  if ($item_id > 0) {
    $max = getItemIconMaxIndex($item_id);
    if (isset($max)) {
      for($i = 0;$i<=$max;$i++) {
        $icon = getItemIcon($item_id, $i);
        global $var_node;
      }
      $var_node = $icon['node'];
      compositeCommand("getnodepath");
    }
  }
}

function getItemIconMaxIndex($item_id) {
  global $var_owner, $icon;
  $var_owner = $item_id;
  compositeCommand("geticonmax");
  return $icon['max'];
}

function getItemIcon($node_id, $xbindex) {
  if ($node_id > 0) {
    global $var_owner, $var_xbindex, $icon;
    $var_owner = $node_id;
    $var_xbindex = $xbindex;
    compositeCommand("geticon");
    return $icon;
  }
}

function processNodeFormatSpecs($node_id, $node_path) {
  if ($node_id>0) {
    $max = getFormatCatalogSpecMaxIndex($node_path);
    if (isset($max)) {
      for($i = 0;$i<=$max;$i++) {
        $spec_id = addFormatSpecFromWS($node_id, $node_path, $i);
        if ($spec_id>0) processFormatSpecIcons($node_id, $node_path, $i);
      }
    }
  }
}

function processFormatSpecIcons($node_id, $node_path, $i) {
  $spec_id = getCatalogFormatSpecId($node_path, $i);
  processItemIcons($spec_id, $node_path);
}

function getCatalogFormatSpecId($node_path, $i) {
  global $var_dtype, $var_spec, $var_path, $spec;
  $var_dtype = 0;
  $var_spec = $i;
  $var_path = $node_path;
  compositeCommand("getspec");
  return $spec['ID'];
}

function getFormatCatalogSpecMaxIndex($node_path) {
  global $var_dtype, $var_path, $spec;
  $var_dtype = 1;
  $var_path = $node_path;
  compositeCommand("getnodemax");
  return $spec['max'];
}

function addFormatSpecFromWS($node_id, $node_path, $i) {
  $spec_id = getFormatCatalogSpecInfo($node_path, $i);
  if ($spec_id>0) {
    $max = getFormatCatalogSpecMaxRevIndex($node_id, $node_path, $i);
    if (isset($max)) {
      for($j = 0;$j<=$max;$j++) {
        getFormatSpecRevision($node_id, $node_path, $i, $j);
      }
    }
  }
  return $spec_id;
}

function getFormatCatalogSpecMaxRevIndex($node_id, $node_path, $i) {
  global $var_spec, $var_dtype, $var_path, $spec;
  $var_dtype = 0;
  $var_path = $node_path;
  $var_spec = $i;
  compositeCommand("getspec");

  global $var_owner, $rev;
  $var_owner = $spec["ID"];
  compositeCommand("getrevmax");
  return $rev['max'];
}

function getFormatSpecRevision($node_id, $node_path, $i, $j) {
  global $var_spec, $var_dtype, $var_path, $spec, $var_owner, $var_xbindex;
  $var_spec = $i;
  $var_path = $node_path;
  $var_dtype = 0;
  compositeCommand("getspec");
  if ($spec["ID"]>0) {
    $var_owner = $spec['ID'];
    $var_xbindex = $j;
    compositeCommand("getrev");
  }
}

function getFormatCatalogSpecInfo($node_path, $spec_id) {
  global $var_spec, $spec, $var_dtype, $var_path, $spec, $var_id;
  $var_spec = $spec_id;
  $var_path = $node_path;
  $var_dtype = 0;
  compositeCommand("getspec");
  $var_id = $spec['ID'];
  if ($var_id>0) {
    compositeCommand("getname");
    compositeCommand("getdesc");
  }
  return $spec['ID'];
}

function processNodeGroupSpecs($node_id, $node_path) {
  if ($node_id>0) {
    $max = getGroupCatalogSpecMaxIndex($node_path);
    if (isset($max)) {
      for($i = 0;$i<=$max;$i++) {
        $spec_id = addGroupSpecFromWS($node_id, $node_path, $i);
        if ($spec_id>0) processGroupSpecIcons($node_id, $node_path, $i);
      }
    }
  }
}

function processGroupSpecIcons($node_id, $node_path, $i) {
  $spec_id = getCatalogGroupSpecId($node_path, $i);
  processItemIcons($spec_id, $node_path);
}

function getCatalogGroupSpecId($node_path, $i) {
  global $var_dtype, $var_spec, $var_path, $spec;
  $var_dtype = 1;
  $var_spec = $i;
  $var_path = $node_path;
  compositeCommand("getspec");
  return $spec['ID'];
}

function getGroupCatalogSpecMaxIndex($node_path) {
  global $var_dtype, $var_path, $spec;
  $var_dtype = 2;
  $var_path = $node_path;
  compositeCommand("getnodemax");
  return $spec['max'];
}

function addGroupSpecFromWS($node_id, $node_path, $i) {
  $spec_id = getGroupCatalogSpecInfo($node_path, $i);
  if ($spec_id>0) {
    $max = getGroupCatalogSpecMaxRevIndex($node_id, $node_path, $i);
    if (isset($max)) {
      for($j = 0;$j<=$max;$j++) {
        getGroupSpecRevision($node_id, $node_path, $i, $j);
      }
    }
  }
  return $spec_id;
}

function getGroupCatalogSpecMaxRevIndex($node_id, $node_path, $i) {
  global $var_spec, $var_dtype, $var_path, $spec;
  $var_dtype = 1;
  $var_path = $node_path;
  $var_spec = $i;
  compositeCommand("getspec");

  global $var_owner, $rev;
  $var_owner = $spec["ID"];
  compositeCommand("getrevmax");
  return $rev['max'];
}

function getGroupSpecRevision($node_id, $node_path, $i, $j) {
  global $var_spec, $var_dtype, $var_path, $spec, $var_owner, $var_xbindex;
  $var_spec = $i;
  $var_path = $node_path;
  $var_dtype = 1;
  compositeCommand("getspec");
  if ($spec["ID"]>0) {
    $var_owner = $spec['ID'];
    $var_xbindex = $j;
    compositeCommand("getrev");
  }
}

function getGroupCatalogSpecInfo($node_path, $spec_id) {
  global $var_spec, $spec, $var_dtype, $var_path, $spec, $var_id;
  $var_spec = $spec_id;
  $var_path = $node_path;
  $var_dtype = 1;
  compositeCommand("getspec");
  $var_id = $spec['ID'];
  if ($var_id>0) {
    compositeCommand("getname");
    compositeCommand("getdesc");
  }
  return $spec['ID'];
}

function processNodeBlockSpecs($node_id, $node_path) {
  if ($node_id>0) {
    $max = getBlockCatalogSpecMaxIndex($node_path);
    if (isset($max)) {
      for($i = 0;$i<=$max;$i++) {
        $spec_id = addBlockSpecFromWS($node_id, $node_path, $i);
        if ($spec_id>0) processBlockSpecIcons($node_id, $node_path, $i);
      }
    }
  }
}

function processBlockSpecIcons($node_id, $node_path, $i) {
  $spec_id = getCatalogBlockSpecId($node_path, $i);
  processItemIcons($spec_id, $node_path);
}

function getCatalogBlockSpecId($node_path, $i) {
  global $var_dtype, $var_spec, $var_path, $spec;
  $var_dtype = 2;
  $var_spec = $i;
  $var_path = $node_path;
  compositeCommand("getspec");
  return $spec['ID'];
}

function getBlockCatalogSpecMaxIndex($node_path) {
  global $var_dtype, $var_path, $spec;
  $var_dtype = 3;
  $var_path = $node_path;
  compositeCommand("getnodemax");
  return $spec['max'];
}

function addBlockSpecFromWS($node_id, $node_path, $i) {
  $spec_id = getBlockCatalogSpecInfo($node_path, $i);
  if ($spec_id>0) {
    $max = getBlockCatalogSpecMaxRevIndex($node_id, $node_path, $i);
    if (isset($max)) {
      for($j = 0;$j<=$max;$j++) {
        getBlockSpecRevision($node_id, $node_path, $i, $j);
      }
    }
  }
  return $spec_id;
}

function getBlockCatalogSpecMaxRevIndex($node_id, $node_path, $i) {
  global $var_spec, $var_dtype, $var_path, $spec;
  $var_dtype = 2;
  $var_path = $node_path;
  $var_spec = $i;
  compositeCommand("getspec");

  global $var_owner, $rev;
  $var_owner = $spec["ID"];
  compositeCommand("getrevmax");
  return $rev['max'];
}

function getBlockSpecRevision($node_id, $node_path, $i, $j) {
  global $var_spec, $var_dtype, $var_path, $spec, $var_owner, $var_xbindex;
  $var_spec = $i;
  $var_path = $node_path;
  $var_dtype = 2;
  compositeCommand("getspec");
  if ($spec["ID"]>0) {
    $var_owner = $spec['ID'];
    $var_xbindex = $j;
    compositeCommand("getrev");
  }
}

function getBlockCatalogSpecInfo($node_path, $spec_id) {
  global $var_spec, $spec, $var_dtype, $var_path, $spec, $var_id;
  $var_spec = $spec_id;
  $var_path = $node_path;
  $var_dtype = 2;
  compositeCommand("getspec");
  $var_id = $spec['ID'];
  if ($var_id>0) {
    compositeCommand("getname");
    compositeCommand("getdesc");
  }
  return $spec['ID'];
}

function processAllPlugins($node_id, $node_path) {
  if ($node_id > 0) {
    DB_Query("SELECT * FROM XBITEM item WHERE dtype='XBENode' AND owner_id=".$node_id." ORDER BY xbindex");
    while ($row=DB_Row()) {
      DB_Save();
      processAllPlugins($row['ID'],$node_path.$row['XBINDEX'].'/');
      DB_Load();
    }
    processNodePlugins($node_id, $node_path);
  }
}

function processNodePlugins($node_id, $node_path) {
  if ($node_id>0) {
    getCatalogNodeId($node_path);
    $max = getPluginCatalogNodeMaxPluginIndex($node_id);
    if (isset($max)) {
      for($i = 0;$i<=$max;$i++) {
        $plug = getPluginCatalogNodePlugin($node_id, $i);
        getNodePath($node_id);
        $plug_id = $plug['plug_id'];
        processPluginLines($plug_id);
        processPluginPanes($plug_id);
      }
    }
  }
}

function getCatalogNodeId($node_path) {
  global $var_path, $node;
  $var_path = $node_path;
  compositeCommand("getnode");
  return $node['ID'];
}

function getPluginCatalogNodeMaxPluginIndex($node_id) {
  if ($node_id>0) {
    global $var_node, $plug;
    $var_node = $node_id;
    compositeCommand("getplugmax");
    return $plug['max'];
  }
}

function getPluginCatalogNodePlugin($node_id, $i) {
  if ($node_id>0) {
    global $var_node, $var_plugin, $plug;
    $var_node = $node_id;
    $var_plugin = $i;
    compositeCommand("getplug");
    return $plug;
  }
}

function getNodePath($node_id) {
  if ($node_id>0) {
    global $var_node, $path;
    $var_node = $node_id;
    compositeCommand("getnodepath");
    return $path;
  }
}

function processPluginLines($plug_id) {
  if ($plug_id>0) {
    $max = getPluginCatalogPlugLineMaxIndex($plug_id);
    if (isset($max)) {
      for($i = 0;$i<=$max;$i++) {
        getPluginCatalogPlugLine($plug_id, $i);
      }
    }
  }
}

function getPluginCatalogPlugLineMaxIndex($plug_id) {
  if ($plug_id>0) {
    global $var_plug, $plug;
    $var_plug = $plug_id;
    compositeCommand("getpluglinemax");
    return $plug['max'];
  }
}

function getPluginCatalogPlugLine($plug_id, $i) {
  if ($plug_id>0) {
    global $var_plug, $var_line, $line;
    $var_plug = $plug_id;
    $var_line = $i;
    compositeCommand("getplugline");
    return $line['max'];
  }
}

function processPluginPanes($plug_id) {
  if ($plug_id>0) {
    $max = getPluginCatalogPlugPaneMaxIndex($plug_id);
    if (isset($max)) {
      for($i = 0;$i<=$max;$i++) {
        getPluginCatalogPlugPane($plug_id, $i);
      }
    }
  }
}

function getPluginCatalogPlugPaneMaxIndex($plug_id) {
  if ($plug_id>0) {
    global $var_plug, $plug;
    $var_plug = $plug_id;
    compositeCommand("getplugpanemax");
    return $plug['max'];
  }
}

function getPluginCatalogPlugPane($plug_id, $i) {
  if ($plug_id>0) {
    global $var_plug, $var_pane, $pane;
    $var_plug = $plug_id;
    $var_pane = $i;
    compositeCommand("getplugpane");
    return $pane['max'];
  }
}

function processAllBinds($node_id, $node_path) {
  global $dtype_names;
  if ($node_id>0) {
    DB_Query("SELECT * FROM XBITEM item WHERE dtype='".$dtype_names[0]."' AND owner_id=".$node_id.' ORDER BY xbindex');
    while ($row=DB_Row()) {
      DB_Save();
      processAllBinds($row['ID'],$node_path.$row['XBINDEX'].'/');
      DB_Load();
    }
    DB_Query("SELECT * FROM XBITEM item WHERE dtype='".$dtype_names[1]."' AND owner_id=".$node_id.' ORDER BY xbindex');
    while ($row=DB_Row()) {
      DB_Save();
      processFormatSpecBinds($node_path, $row['ID'], $row['XBINDEX']);
      DB_Load();
    }
    DB_Query("SELECT * FROM XBITEM item WHERE dtype='".$dtype_names[2]."' AND owner_id=".$node_id.' ORDER BY xbindex');
    while ($row=DB_Row()) {
      DB_Save();
      processGroupSpecBinds($node_path, $row['ID'], $row['XBINDEX']);
      DB_Load();
    }
    DB_Query("SELECT * FROM XBITEM item WHERE dtype='".$dtype_names[3]."' AND owner_id=".$node_id.' ORDER BY xbindex');
    while ($row=DB_Row()) {
      DB_Save();
      processBlockSpecBinds($node_path, $row['ID'], $row['XBINDEX']);
      DB_Query('SELECT item_rev.id AS id, item_rev.*, item.* FROM XBREV item_rev, XBITEM item WHERE item.id = item_rev.id AND item.owner_id='.$row['ID'].' ORDER BY xbindex');
      while ($rev=DB_Row()) {
        DB_Save();
          processRevLines($rev['id']);
          processRevPanes($rev['id']);
        DB_Load();
      }
      DB_Load();
    }
  }
}

function processFormatSpecBinds($node_path, $spec_id, $spec_xb) {
  if ($spec_id>0) {
    $max = getFormatCatalogSpecMaxBindId($node_path, $spec_id, $spec_xb);
    if (isset($max)) {
      for($i = 0;$i<=$max;$i++) {
        getFormatCatalogBindTargetPath($node_path, $spec_id, $spec_xb, $i);
      }
    }
  }
}

function getFormatCatalogSpecMaxBindId($node_path, $spec_id, $spec_xb) {
  global $var_spec, $var_dtype, $var_path, $var_origin, $spec, $bind, $def;
  $var_spec = $spec_xb;
  $var_path = $node_path;
  $var_dtype = 0;
  compositeCommand("getspec");
  $var_origin = $spec_id;
  compositeCommand("getdefmax");
  return $def['max'];
}

function getFormatCatalogBindTargetPath($node_path, $spec_id, $spec_xb, $i) {
  global $var_spec, $var_dtype, $var_path, $var_origin, $bind, $var_xbindex, $def;
  $var_spec = $spec_xb;
  $var_path = $node_path;
  $var_dtype = 0;
  compositeCommand("getspec");
  $var_origin = $spec_id;
  $var_xbindex = $i;
  compositeCommand("getdef");
  if ($def['spec']) {
    global $var_node, $path;
    $var_node = $def['spec'];
    compositeCommand("getnodepath");
  }
}

function processGroupSpecBinds($node_path, $spec_id, $spec_xb) {
  if ($spec_id>0) {
    $max = getGroupCatalogSpecMaxBindId($node_path, $spec_id, $spec_xb);
    if (isset($max)) {
      for($i = 0;$i<=$max;$i++) {
        getGroupCatalogBindTargetPath($node_path, $spec_id, $spec_xb, $i);
      }
    }
  }
}

function getGroupCatalogSpecMaxBindId($node_path, $spec_id, $spec_xb) {
  global $var_spec, $var_dtype, $var_path, $var_origin, $spec, $bind, $def;
  $var_spec = $spec_xb;
  $var_path = $node_path;
  $var_dtype = 1;
  compositeCommand("getspec");
  $var_origin = $spec_id;
  compositeCommand("getdefmax");
  return $def['max'];
}

function getGroupCatalogBindTargetPath($node_path, $spec_id, $spec_xb, $i) {
  global $var_spec, $var_dtype, $var_path, $var_origin, $bind, $var_xbindex, $def;
  $var_spec = $spec_xb;
  $var_path = $node_path;
  $var_dtype = 1;
  compositeCommand("getspec");
  $var_origin = $spec_id;
  $var_xbindex = $i;
  compositeCommand("getdef");
  if ($def['spec']) {
    global $var_node, $path;
    $var_node = $def['spec'];
    compositeCommand("getnodepath");
  }
}

function processBlockSpecBinds($node_path, $spec_id, $spec_xb) {
  if ($spec_id>0) {
    $max = getBlockCatalogSpecMaxBindId($node_path, $spec_id, $spec_xb);
    if (isset($max)) {
      for($i = 0;$i<=$max;$i++) {
        getBlockCatalogBindTargetPath($node_path, $spec_id, $spec_xb, $i);
      }
    }
  }
}

function getBlockCatalogSpecMaxBindId($node_path, $spec_id, $spec_xb) {
  global $var_spec, $var_dtype, $var_path, $var_origin, $spec, $bind, $def;
  $var_spec = $spec_xb;
  $var_path = $node_path;
  $var_dtype = 2;
  compositeCommand("getspec");
  $var_origin = $spec_id;
  compositeCommand("getdefmax");
  return $def['max'];
}

function getBlockCatalogBindTargetPath($node_path, $spec_id, $spec_xb, $i) {
  global $var_spec, $var_dtype, $var_path, $var_origin, $bind, $var_xbindex, $def;
  $var_spec = $spec_xb;
  $var_path = $node_path;
  $var_dtype = 2;
  compositeCommand("getspec");
  $var_origin = $spec_id;
  $var_xbindex = $i;
  compositeCommand("getdef");
  if ($def['spec']) {
    global $var_node, $path;
    $var_node = $def['spec'];
    compositeCommand("getnodepath");
  }
}

function processRevLines($rev_id) {
  if ($rev_id>0) {
    $max = getBlockCatalogRevMaxLineIndex($rev_id);
    if (isset($max)) {
      for($i = 0;$i<=$max;$i++) {
        $line = getBlockCatalogRevLine($rev_id, $i);
        getNodePath($line['FOLDER_ID']);
      }
    }
  }
}

function getBlockCatalogRevMaxLineIndex($rev_id) {
  global $var_rev, $line;
  $var_rev = $rev_id;
  compositeCommand("getlinemax");
  return $line['max'];
}

function getBlockCatalogRevLine($rev_id, $i) {
  global $var_rev, $var_prior, $line;
  $var_rev = $rev_id;
  $var_prior = $i;
  compositeCommand("getline");
  return $line;
}

function processRevPanes($rev_id) {
  if ($rev_id>0) {
    $max = getBlockCatalogRevMaxPaneIndex($rev_id);
    if (isset($max)) {
      for($i = 0;$i<=$max;$i++) {
        $pane = getBlockCatalogRevPane($rev_id, $i);
        getNodePath($pane['OWNER_ID']);
      }
    }
  }
}

function getBlockCatalogRevMaxPaneIndex($rev_id) {
  global $var_rev, $pane;
  $var_rev = $rev_id;
  compositeCommand("getpanemax");
  return $pane['max'];
}

function getBlockCatalogRevPane($rev_id, $i) {
  global $var_rev, $var_prior, $pane;
  $var_rev = $rev_id;
  $var_prior = $i;
  compositeCommand("getpane");
  return $pane;
}

function getline($fl) {
 $fp = @fgets($fl, 65536);
  $fp = substr($fp,0, strlen($fp)-1);
  return $fp;
}

processCommand($var_op); ?>
