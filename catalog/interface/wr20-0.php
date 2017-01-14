<?php error_reporting(E_ALL); include 'db.php';
extract($_GET, EXTR_PREFIX_ALL, 'var'); extract($_POST, EXTR_PREFIX_ALL, 'var');
global $command_id;
$command_id = 0;

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
        $query .= ' WHERE n1.dtype=0 AND n1.id='.$result['id'];
      } else $query .= ' WHERE n1.dtype=0 AND n1.parent=0';
      $query = 'SELECT n'.$i.'.* FROM item AS n1' .$query;
      $result = DB_SimpleQuery($query);
      $i = 1;
      $query = '';
    }
    $xb+=0;
    $i++;
    $query .=' LEFT JOIN item AS n'.$i.' ON (n'.$i.'.parent = n'.($i-1).'.id) AND n'.$i.'.xbindex = '.$xb.' AND n'.$i.'.dtype = 0';
  }
  if ($result) {
    $query .= ' WHERE n1.dtype=0 AND n1.id='.$result['id'];
  } else $query .= ' WHERE n1.dtype=0 AND n1.parent=0';
  $query = 'SELECT n'.$i.'.* FROM item AS n1' .$query;
  return DB_SimpleQuery($query);
}

// Main processing block -------------------------------------------------------
function processCommand($command) {
global $var_node,$var_path,$var_file,$var_dtype,$var_spec,$var_lang,$var_id,$var_owner,$var_xbindex,$var_prev,$var_plugin,$var_plug,$var_line,$var_pane,$var_code,$var_origin,$var_prior,$var_rev;
switch($command) {
  case "getnode": {
//    echo "path: ".$var_path."\n";
    global $node;
    $node = getnodebyxbpath($var_path);
    if ($node['id']) {
      echo "id\n".$node['id']."\nxbindex\n".$node['xbindex']."\nxblimit\n".$node['xblimit']."\n";
    }
    break;
  }

  case "getnodemax": {
    global $spec;
//    echo "dtype: ".$var_dtype."\n";
    $var_dtype += 0;
    $node = getnodebyxbpath($var_path);
    if ($node['id']) {
      $spec = DB_SimpleQuery('SELECT MAX(xbindex) AS max FROM item WHERE parent = '.$node['id'].' AND dtype = '.$var_dtype);
      if ($spec) {
        echo "max\n".$spec['max']."\n";
      }
    }
    break;
  }

  case "getspec": {
    global $spec;
    $var_dtype += 1;
    $var_spec += 0;
    $node = getnodebyxbpath($var_path);
//    echo "parent: ".$node['id']." xbindex: ".$var_spec."\n";
    if ($node['id']) {
      $spec = DB_SimpleQuery('SELECT * FROM item WHERE parent = '.$node['id'].' AND xbindex='.$var_spec.' AND dtype = '.$var_dtype);
      if ($spec) {
        echo "id\n".$spec['id']."\nxbindex\n".$spec['xbindex']."\nxblimit\n".$spec['xblimit']."\n";
      }
    }
    break;
  }

  case "getname": {
    global $name;
    $var_lang += 0;
    $var_id += 0;
    $name = DB_SimpleQuery('SELECT * FROM item_name WHERE id = '.$var_id.' AND lang='.$var_lang);
    if ($name) {
      echo "id\n".$name['id']."\ntext\n".$name['text']."\n";
    }
    break;
  }

  case "getdesc": {
    global $desc;
    $var_lang += 0;
    $var_id += 0;
    $desc = DB_SimpleQuery('SELECT * FROM item_desc WHERE id = '.$var_id.' AND lang='.$var_lang);
    if ($desc) {
      echo "id\n".$desc['id']."\ntext\n".$desc['text']."\n";
    }
    break;
  }

  case "getcomm": {
    global $comm;
    $var_lang += 0;
    $var_id += 0;
    $comm = DB_SimpleQuery('SELECT * FROM item_comm WHERE id = '.$node['id'].' AND lang='.$var_lang);
    if ($comm) {
      echo "id\n".$comm['id']."\ntext\n".$comm['text']."\n";
    }
    break;
  }

  case "getbind": {
    global $bind;
    $var_origin += 0;
    $var_xbindex += 0;
    $bind = DB_SimpleQuery('SELECT *, item_rev.xbindex AS revxb, item_rev.owner AS spec FROM item_bind, item_rev WHERE item_bind.origin = '.$var_origin.' AND item_bind.xbindex='.$var_xbindex.' AND item_rev.id = item_bind.target');
    if ($bind) {
      echo "id\n".$bind['id']."\ntarget\n".$bind['target']."\nbtype\n".$bind['btype']."\nspec\n".$bind['spec']."\nrevxb\n".$bind['revxb']."\n";
    }
    break;
  }

  case "getbindmax": {
    global $bind;
    $var_origin += 0;
    $bind = DB_SimpleQuery('SELECT MAX(xbindex) AS max FROM item_bind WHERE origin = '.$var_origin);
    if ($bind) {
      echo "max\n".$bind['max']."\n";
    }
    break;
  }

  case "getrev": {
    global $rev;
    $var_owner += 0;
    $var_xbindex += 0;
    $rev = DB_SimpleQuery('SELECT * FROM item_rev WHERE owner = '.$var_owner.' AND xbindex='.$var_xbindex);
    if ($rev) {
      echo "id\n".$rev['id']."\nxbindex\n".$rev['xbindex']."\nxblimit\n".$rev['xblimit']."\n";
    }
    break;
  }

  case "getrevmax": {
    global $rev;
    $var_owner += 0;
    $rev = DB_SimpleQuery('SELECT MAX(xbindex) AS max FROM item_rev WHERE owner = '.$var_owner);
    if ($rev) {
      echo "max\n".$rev['max']."\n";
    }
    break;
  }

  case "getlang": {
    global $lang;
    $lang = DB_SimpleQuery("SELECT * FROM language WHERE code = '".htmlspecialchars($var_code)."'");
    if ($lang) {
      echo "id\n".$lang['id']."\ncaption\n".$lang['caption']."\n";
    }
    break;
  }

  case "getnodepath": {
    global $path;
    $var_node += 0;
    $node = DB_SimpleQuery('SELECT * FROM item WHERE id = '.$var_node);
    if ($node) {
      $path = $node['xbindex'];
      while ($node['parent']>0) {
        $node = DB_SimpleQuery('SELECT * FROM item WHERE id = '.$node['parent']);
        if ($node['parent']>0) $path = $node['xbindex'].'/'.$path;
      }
      echo "path\n".$path."\n";
    }
    break;
  }

  case "getinfo": {
    global $info;
    $var_owner += 0;
    $info = DB_SimpleQuery('SELECT * FROM item_info WHERE owner = '.$var_owner);
    if ($info) {
      echo "id\n".$info['id']."\nfilename\n".$info['filename']."\npath\n".$info['path']."\n";
    }
    break;
  }

  case "getfile": {
    global $file;
    $var_node += 0;
    $var_prev += 0;
    $var_file += 0;
    if ($var_file>0) {
        $file = DB_SimpleQuery('SELECT * FROM item_file WHERE id = '.$var_file.' AND NOT EXISTS(SELECT 1 FROM item_file fl WHERE fl.item_id= item_file.item_id AND fl.id < item_file.id)');
    } else if (isset($var_name)) {
        $file = DB_SimpleQuery('SELECT * FROM item_file WHERE item_id = '.$var_node." AND filename = ".htmlspecialchars($var_name)."'");
    } else {
      if ($var_prev>0) {
        $file = DB_SimpleQuery('SELECT * FROM item_file WHERE item_id = '.$var_node.' AND id > '.$var_prev.' AND NOT EXISTS(SELECT 1 FROM item_file fl WHERE fl.id > '.$var_prev.' AND fl.item_id= item_file.item_id AND fl.id < item_file.id)');
      } else {
        $file = DB_SimpleQuery('SELECT * FROM item_file WHERE item_id = '.$var_node.' AND NOT EXISTS(SELECT 1 FROM item_file fl WHERE fl.item_id= item_file.item_id AND fl.id < item_file.id)');
      }
    }
    if ($file) {
      echo "id\n".$file['id']."\nowner\n".$file['item_id']."\nfilename\n".$file['filename']."\n";
    }
    break;
  }

  case "geticon": {
    global $icon;
    $var_owner += 0;
    $var_xbindex += 0;
    $icon = DB_SimpleQuery('SELECT item_icon.*, item_file.filename, item_file.item_id AS node FROM item_icon, item_file WHERE item_file.id = item_icon.icon_id AND item_icon.item_id = '.$var_owner.' AND item_icon.xbindex='.$var_xbindex);
    if ($icon) {
      echo "id\n".$icon['id']."\nowner\n".$icon['item_id']."\nmode\n".$icon['mode']."\nicon\n".$icon['icon_id']."\nnode\n".$icon['node']."\nfilename\n".$icon['filename']."\n";
    }
    break;
  }

  case "geticonmax": {
    global $icon;
    $var_owner += 0;
    $icon = DB_SimpleQuery('SELECT MAX(xbindex) AS max FROM item_icon WHERE item_id = '.$var_owner);
    if ($icon) {
      echo "max\n".$icon['max']."\n";
    }
    break;
  }

  case "getlimi": {
    global $limi;
    $var_owner += 0;
    $var_xbindex += 0;
    $limi = DB_SimpleQuery('SELECT * FROM item_limi WHERE item_id = '.$var_owner.' AND xbindex='.$var_xbindex);
    if ($limi) {
      echo "id\n".$limi['id']."\ntarget\n".$limi['target']."\n";
    }
    break;
  }

  case "getlimimax": {
    global $limi;
    $var_owner += 0;
    $limi = DB_SimpleQuery('SELECT MAX(xbindex) AS max FROM item_limi WHERE item_id = '.$var_owner);
    if ($limi) {
      echo "max\n".$limi['max']."\n";
    }
    break;
  }

  case "gettran": {
    global $tran;
    $var_owner += 0;
    $var_prev += 0;
    if ($var_prev>0) {
      $tran = DB_SimpleQuery('SELECT item_tran.* FROM item_tran, item_tran fp WHERE item_tran.item_id = '.$var_owner.' AND fp.item_id = item_tran.item_id AND fp.id = '.$var_prev.' AND item_tran.id > fp.id AND NOT EXISTS(SELECT 1 FROM item_tran fl WHERE fl.item_id='.$var_owner.' AND fl.id < item_tran.id AND fl.id > fp.id)');
    } else {
      $tran = DB_SimpleQuery('SELECT * FROM item_tran WHERE item_id = '.$var_owner.' AND NOT EXISTS(SELECT 1 FROM item_tran fl WHERE fl.item_id= item_tran.item_id AND fl.id < item_tran.id)');
    }
    if ($tran) {
      echo "id\n".$tran['id']."\ntarget\n".$tran['target']."\nlimt\n".$tran['limitation']."\nexcp\n".$tran['exception']."\n";
    }
    break;
  }

  case "getplug": {
    global $plug;
    $var_node += 0;
    $var_plugin += 0;
    $plug = DB_SimpleQuery('SELECT plug.id AS plug_id, plug.*, item_file.* FROM plug LEFT JOIN item_file ON item_file.id = plug.file_id WHERE folder_id = '.$var_node.' AND plugin='.$var_plugin);
    if ($plug) {
      echo "id\n".$plug['plug_id']."\nplugin\n".$plug['plugin']."\nfile\n".$plug['file_id']."\nfilenode\n".$plug['item_id']."\nfilename\n".$plug['filename']."\n";
    }
    break;
  }

  case "getplugmax": {
    global $plug;
    $var_node += 0;
    $plug = DB_SimpleQuery('SELECT MAX(plugin) AS max FROM plug WHERE folder_id = '.$var_node);
    if ($plug) {
      echo "max\n".$plug['max']."\n";
    }
    break;
  }

  case "getplugline": {
    global $plug;
    $var_plug += 0;
    $var_line += 0;
    $plug = DB_SimpleQuery('SELECT * FROM plug_line WHERE plug = '.$var_plug.' AND line='.$var_line);
    if ($plug) {
      echo "id\n".$plug['id']."\nplug\n".$plug['plug']."\nline\n".$rev['line']."\n";
    }
    break;
  }

  case "getpluglinemax": {
    global $plug;
    $var_plug += 0;
    $plug = DB_SimpleQuery('SELECT MAX(line) AS max FROM plug_line WHERE plug = '.$var_plug);
    if ($plug) {
      echo "max\n".$plug['max']."\n";
    }
    break;
  }

  case "getplugpane": {
    global $plug;
    $var_plug += 0;
    $var_pane += 0;
    $plug = DB_SimpleQuery('SELECT * FROM plug_pane WHERE plug = '.$var_plug.' AND pane='.$var_pane);
    if ($plug) {
      echo "id\n".$plug['id']."\nplug\n".$plug['plug']."\npane\n".$rev['pane']."\n";
    }
    break;
  }

  case "getplugpanemax": {
    global $plug;
    $var_plug += 0;
    $plug = DB_SimpleQuery('SELECT MAX(pane) AS max FROM plug_pane WHERE plug = '.$var_plug);
    if ($plug) {
      echo "max\n".$plug['max']."\n";
    }
    break;
  }

  case "getline": {
    global $line;
    $var_rev += 0;
    $var_prior += 0;
    $line = DB_SimpleQuery('SELECT item_line.id AS id, plug_line.line AS line, item_line.line AS lineid, item_line.*, plug_line.*, plug.* FROM item_line LEFT JOIN plug_line ON plug_line.id = item_line.line LEFT JOIN plug ON plug.id = plug_line.plug WHERE rev = '.$var_rev.' AND priority='.$var_prior);
    if ($line) {
      echo "id\n".$line['id']."\nlineid\n".$line['lineid']."\nline\n".$line['line']."\nplug\n".$line['plug']."\nplugin\n".$line['plugin']."\nplugnode\n".$line['folder_id']."\n";
    }
    break;
  }

  case "getlinemax": {
    global $line;
    $var_rev += 0;
    $line = DB_SimpleQuery('SELECT MAX(priority) AS max FROM item_line WHERE rev = '.$var_rev);
    if ($line) {
      echo "max\n".$line['max']."\n";
    }
    break;
  }

  case "getpane": {
    global $pane;
    $var_rev += 0;
    $var_prior += 0;
    $pane = DB_SimpleQuery('SELECT item_pane.id AS id, plug_pane.pane AS pane, item_pane.pane AS paneid, item_pane.*, plug_pane.*, plug.* FROM item_pane LEFT JOIN plug_pane ON plug_pane.id = item_pane.pane LEFT JOIN plug ON plug.id = plug_pane.plug WHERE rev = '.$var_rev.' AND priority='.$var_prior);
    if ($pane) {
      echo "id\n".$pane['id']."\npane\n".$pane['pane']."\npaneid\n".$pane['paneid']."\nplug\n".$pane['plug']."\nplugin\n".$pane['plugin']."\nplugnode\n".$pane['folder_id']."\n";
    }
    break;
  }

  case "getpanemax": {
    global $pane;
    $var_rev += 0;
    $pane = DB_SimpleQuery('SELECT MAX(priority) AS max FROM item_pane WHERE rev = '.$var_rev);
    if ($pane) {
      echo "max\n".$pane['max']."\n";
    }
    break;
  }

  case "getfull": {
    $var_lang = 1;

    $node_id = getCatalogNodeInfo("");
    processAllNodes($node_id, "");
    processAllPlugins($node_id, "");
    processAllBinds($node_id, "");
    break;
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
  $var_id = $node['id'];
  if ($var_id>0) {
    compositeCommand("getname");
    compositeCommand("getdesc");
  }
  return $node['id'];
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
        $prev = $file['id'];
      } while ($file['id']>0);
    }
  }
}

function getCatalogNodeFile($node_path) {
  global $var_path, $var_owner, $node, $info;
  $var_path = $node_path;
  compositeCommand("getnode");
  $var_owner = $node['id'];
  compositeCommand("getinfo");
  return $info['id'];
}

function getCatalogFile($node_path, $prev) {
  global $var_path, $var_prev, $var_node, $node, $file;
  $var_path = $node_path;
  compositeCommand("getnode");
  if ($node['id'] > 0) {
    $var_node = $node['id'];
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
        $var_node = $icon['node'];
        compositeCommand("getnodepath");
      }
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
  return $spec['id'];
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
  $var_owner = $spec["id"];
  compositeCommand("getrevmax");
  return $rev['max'];
}

function getFormatSpecRevision($node_id, $node_path, $i, $j) {
  global $var_spec, $var_dtype, $var_path, $spec, $var_owner, $var_xbindex;
  $var_spec = $i;
  $var_path = $node_path;
  $var_dtype = 0;
  compositeCommand("getspec");
  if ($spec["id"]>0) {
    $var_owner = $spec['id'];
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
  $var_id = $spec['id'];
  if ($var_id>0) {
    compositeCommand("getname");
    compositeCommand("getdesc");
  }
  return $spec['id'];
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
  return $spec['id'];
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
  $var_owner = $spec["id"];
  compositeCommand("getrevmax");
  return $rev['max'];
}

function getGroupSpecRevision($node_id, $node_path, $i, $j) {
  global $var_spec, $var_dtype, $var_path, $spec, $var_owner, $var_xbindex;
  $var_spec = $i;
  $var_path = $node_path;
  $var_dtype = 1;
  compositeCommand("getspec");
  if ($spec["id"]>0) {
    $var_owner = $spec['id'];
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
  $var_id = $spec['id'];
  if ($var_id>0) {
    compositeCommand("getname");
    compositeCommand("getdesc");
  }
  return $spec['id'];
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
  return $spec['id'];
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
  $var_owner = $spec["id"];
  compositeCommand("getrevmax");
  return $rev['max'];
}

function getBlockSpecRevision($node_id, $node_path, $i, $j) {
  global $var_spec, $var_dtype, $var_path, $spec, $var_owner, $var_xbindex;
  $var_spec = $i;
  $var_path = $node_path;
  $var_dtype = 2;
  compositeCommand("getspec");
  if ($spec["id"]>0) {
    $var_owner = $spec['id'];
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
  $var_id = $spec['id'];
  if ($var_id>0) {
    compositeCommand("getname");
    compositeCommand("getdesc");
  }
  return $spec['id'];
}

function processAllPlugins($node_id, $node_path) {
  if ($node_id > 0) {
    DB_Query('SELECT * FROM item WHERE dtype=0 AND parent='.$node_id.' ORDER BY xbindex');
    while ($row=DB_Row()) {
      DB_Save();
      processAllPlugins($row['id'],$node_path.$row['xbindex'].'/');
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
  compositeCommand("getnode");
  return $node['id'];
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
  if ($node_id>0) {
    DB_Query('SELECT * FROM item WHERE dtype=0 AND parent='.$node_id.' ORDER BY xbindex');
    while ($row=DB_Row()) {
      DB_Save();
      processAllBinds($row['id'],$node_path.$row['xbindex'].'/');
      DB_Load();
    }
    DB_Query('SELECT * FROM item WHERE dtype=1 AND parent='.$node_id.' ORDER BY xbindex');
    while ($row=DB_Row()) {
      DB_Save();
      processFormatSpecBinds($node_path, $row['id'], $row['xbindex']);
      DB_Load();
    }
    DB_Query('SELECT * FROM item WHERE dtype=2 AND parent='.$node_id.' ORDER BY xbindex');
    while ($row=DB_Row()) {
      DB_Save();
      processGroupSpecBinds($node_path, $row['id'], $row['xbindex']);
      DB_Load();
    }
    DB_Query('SELECT * FROM item WHERE dtype=3 AND parent='.$node_id.' ORDER BY xbindex');
    while ($row=DB_Row()) {
      DB_Save();
      processBlockSpecBinds($node_path, $row['id'], $row['xbindex']);
      DB_Query('SELECT * FROM item_rev WHERE owner='.$row['id'].' ORDER BY xbindex');
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
  global $var_spec, $var_dtype, $var_path, $var_origin, $spec, $bind;
  $var_spec = $spec_xb;
  $var_path = $node_path;
  $var_dtype = 0;
  compositeCommand("getspec");
  $var_origin = $spec_id;
  compositeCommand("getbindmax");
  return $bind['max'];
}

function getFormatCatalogBindTargetPath($node_path, $spec_id, $spec_xb, $i) {
  global $var_spec, $var_dtype, $var_path, $var_origin, $bind, $var_xbindex;
  $var_spec = $spec_xb;
  $var_path = $node_path;
  $var_dtype = 0;
  compositeCommand("getspec");
  $var_origin = $spec_id;
  $var_xbindex = $i;
  compositeCommand("getbind");
  if ($bind['spec']) {
    global $var_node, $path;
    $var_node = $bind['spec'];
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
  global $var_spec, $var_dtype, $var_path, $var_origin, $spec, $bind;
  $var_spec = $spec_xb;
  $var_path = $node_path;
  $var_dtype = 1;
  compositeCommand("getspec");
  $var_origin = $spec_id;
  compositeCommand("getbindmax");
  return $bind['max'];
}

function getGroupCatalogBindTargetPath($node_path, $spec_id, $spec_xb, $i) {
  global $var_spec, $var_dtype, $var_path, $var_origin, $bind, $var_xbindex;
  $var_spec = $spec_xb;
  $var_path = $node_path;
  $var_dtype = 1;
  compositeCommand("getspec");
  $var_origin = $spec_id;
  $var_xbindex = $i;
  compositeCommand("getbind");
  if ($bind['spec']) {
    global $var_node, $path;
    $var_node = $bind['spec'];
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
  global $var_spec, $var_dtype, $var_path, $var_origin, $spec, $bind;
  $var_spec = $spec_xb;
  $var_path = $node_path;
  $var_dtype = 2;
  compositeCommand("getspec");
  $var_origin = $spec_id;
  compositeCommand("getbindmax");
  return $bind['max'];
}

function getBlockCatalogBindTargetPath($node_path, $spec_id, $spec_xb, $i) {
  global $var_spec, $var_dtype, $var_path, $var_origin, $bind, $var_xbindex;
  $var_spec = $spec_xb;
  $var_path = $node_path;
  $var_dtype = 2;
  compositeCommand("getspec");
  $var_origin = $spec_id;
  $var_xbindex = $i;
  compositeCommand("getbind");
  if ($bind['spec']) {
    global $var_node, $path;
    $var_node = $bind['spec'];
    compositeCommand("getnodepath");
  }
}

function processRevLines($rev_id) {
  if ($rev_id>0) {
    $max = getBlockCatalogRevMaxLineIndex($rev_id);
    if (isset($max)) {
      for($i = 0;$i<=$max;$i++) {
        $line = getBlockCatalogRevLine($rev_id, $i);
        getNodePath($line['folder_id']);
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
        getNodePath($pane['folder_id']);
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
