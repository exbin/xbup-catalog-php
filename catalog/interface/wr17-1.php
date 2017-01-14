<?php include 'db.php';
extract($_GET, EXTR_PREFIX_ALL, 'var'); extract($_POST, EXTR_PREFIX_ALL, 'var');

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
switch($var_op) {
  case "getnode": {
    $node = getnodebyxbpath($var_path);
    if ($node['id']) {
      echo "id\n".$node['id']."\nxbindex\n".$node['xbindex']."\nxblimit\n".$node['xblimit']."\n";
    }
    break;
  }

  case "getnodemax": {
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
    $var_dtype += 1;
    $var_spec += 0;
    $node = getnodebyxbpath($var_path);
    if ($node['id']) {
      $spec = DB_SimpleQuery('SELECT * FROM item WHERE parent = '.$node['id'].' AND xbindex='.$var_spec.' AND dtype = '.$var_dtype);
      if ($spec) {
        echo "id\n".$spec['id']."\nxbindex\n".$spec['xbindex']."\nxblimit\n".$spec['xblimit']."\n";
      }
    }
    break;
  }

  case "getname": {
    $var_lang += 0;
    $var_id += 0;
    $name = DB_SimpleQuery('SELECT * FROM item_name WHERE id = '.$var_id.' AND lang='.$var_lang);
    if ($name) {
      echo "id\n".$name['id']."\ntext\n".$name['text']."\n";
    }
    break;
  }

  case "getdesc": {
    $var_lang += 0;
    $var_id += 0;
    $name = DB_SimpleQuery('SELECT * FROM item_desc WHERE id = '.$var_id.' AND lang='.$var_lang);
    if ($name) {
      echo "id\n".$name['id']."\ntext\n".$name['text']."\n";
    }
    break;
  }

  case "getcomm": {
    $var_lang += 0;
    $var_id += 0;
    $name = DB_SimpleQuery('SELECT * FROM item_comm WHERE id = '.$node['id'].' AND lang='.$var_lang);
    if ($name) {
      echo "id\n".$comm['id']."\ntext\n".$name['text']."\n";
    }
    break;
  }

  case "getbind": {
    $var_origin += 0;
    $var_xbindex += 0;
    $bind = DB_SimpleQuery('SELECT *, item_rev.xbindex AS revxb, item_rev.owner AS spec FROM item_bind, item_rev WHERE item_bind.origin = '.$var_origin.' AND item_bind.xbindex='.$var_xbindex.' AND item_rev.id = item_bind.target');
    if ($bind) {
      echo "id\n".$bind['id']."\ntarget\n".$bind['target']."\nbtype\n".$bind['btype']."\nspec\n".$bind['spec']."\nrevxb\n".$bind['revxb']."\n";
    }
    break;
  }

  case "getbindmax": {
    $var_origin += 0;
    $bind = DB_SimpleQuery('SELECT MAX(xbindex) AS max FROM item_bind WHERE origin = '.$var_origin);
    if ($bind) {
      echo "max\n".$bind['max']."\n";
    }
    break;
  }

  case "getrev": {
    $var_owner += 0;
    $var_xbindex += 0;
    $rev = DB_SimpleQuery('SELECT * FROM item_rev WHERE owner = '.$var_owner.' AND xbindex='.$var_xbindex);
    if ($rev) {
      echo "id\n".$bind['id']."\nxbindex\n".$rev['xbindex']."\nxblimit\n".$rev['xblimit']."\n";;
    }
    break;
  }

  case "getrevmax": {
    $var_owner += 0;
    $rev = DB_SimpleQuery('SELECT MAX(xbindex) AS max FROM item_rev WHERE owner = '.$var_owner);
    if ($rev) {
      echo "max\n".$rev['max']."\n";
    }
    break;
  }

  case "getlang": {
    $lang = DB_SimpleQuery("SELECT * FROM language WHERE code = '".htmlspecialchars($var_code)."'");
    if ($lang) {
      echo "id\n".$lang['id']."\ncaption\n".$lang['caption']."\n";
    }
    break;
  }

  case "getnodepath": {
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
} ?>
