<?php

  global $session;
    
  $qry="1";
  
  global $name;
  if ($name!='') {
   $qry.=" AND TITLE LIKE '%".DBSafe($name)."%'";
   $out['TITLE']=$name;
  }
  
  
  // QUERY READY
  global $save_qry;
  if ($save_qry) {
   $qry=$session->data['telegram_qry'];
  } else {
   $session->data['telegram_qry']=$qry;
  }
  if (!$qry) $qry="1";
  
  // FIELDS ORDER
  global $sortby_tlg;
  if (!$sortby_tlg) {
   $sortby_tlg=$session->data['telegram_sort'];
  } else {
   if ($session->data['telegram_sort']==$sortby_tlg) {
    if (Is_Integer(strpos($sortby_tlg, ' DESC'))) {
     $sortby_tlg=str_replace(' DESC', '', $sortby_tlg);
    } else {
     $sortby_tlg=$sortby_tlg." DESC";
    }
   }
   $session->data['telegram_sort']=$sortby_tlg;
  }
  if (!$sortby_tlg) $sortby_tlg="TITLE";
  $out['SORTBY']=$sortby_tlg;
  
  // SEARCH RESULTS  
  $res=SQLSelect("SELECT * FROM tlg_cmd WHERE $qry ORDER BY ".$sortby_tlg);
  if ($res[0]['ID']) {   
    colorizeArray($res);
    $total=count($res);
    for($i=0;$i<$total;$i++) {
     // some action for every record if required
    }
    $out['RESULT']=$res;
  }  
?>
