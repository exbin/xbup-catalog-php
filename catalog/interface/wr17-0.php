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
    $bind = DB_SimpleQuery('SELECT * FROM item_bind WHERE origin = '.$var_origin.' AND xbindex='.$var_xbindex);
    if ($bind) {
      echo "id\n".$bind['id']."\ntarget\n".$bind['target']."\n";
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
