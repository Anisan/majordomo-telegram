<?php

  global $session;
    
  global $name;
  if ($name!='') {
   $qry.=" AND TITLE LIKE '%".DBSafe($name)."%'";
   $out['TITLE']=$name;
  }
  
  
  // FIELDS ORDER
  global $sortby_cmd;
  if (!$sortby_cmd) {
   $sortby_cmd=$session->data['telegram_sort_cmd'];
  } else {
   if ($session->data['telegram_sort_cmd']==$sortby_cmd) {
    if (Is_Integer(strpos($sortby_cmd, ' DESC'))) {
     $sortby_cmd=str_replace(' DESC', '', $sortby_cmd);
    } else {
     $sortby_cmd=$sortby_cmd." DESC";
    }
   }
   $session->data['telegram_sort_cmd']=$sortby_cmd;
  }
  if (!$sortby_cmd) $sortby_cmd="TITLE";
  $out['SORTBY']=$sortby_cmd;
  
  // SEARCH RESULTS  
  $res=SQLSelect("SELECT * FROM tlg_cmd ORDER BY ".$sortby_cmd);
  if ($res[0]['ID']) {   
    paging($res, 20, $out); // search result paging
    colorizeArray($res);
    $total=count($res);
    for($i=0;$i<$total;$i++) {
     // some action for every record if required
    }
    $out['RESULT']=$res;
  }  
?>
