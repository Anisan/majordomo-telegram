<?php

  global $session;
    
  global $name;
  if ($name!='') {
   $qry.=" AND TITLE LIKE '%".DBSafe($name)."%'";
   $out['TITLE']=$name;
  }
  
  
  // FIELDS ORDER
  $sortby_event = gr('sortby_event');
  if (!$sortby_event) {
   $sortby_event=isset($session->data['telegram_sort_event'])?$session->data['telegram_sort_event']:'';
  } else {
   if (isset($session->data['telegram_sort_event']) && $session->data['telegram_sort_event']==$sortby_event) {
    if (Is_Integer(strpos($sortby_event, ' DESC'))) {
     $sortby_event=str_replace(' DESC', '', $sortby_event);
    } else {
     $sortby_event=$sortby_event." DESC";
    }
   }
   $session->data['telegram_sort_event']=$sortby_event;
  }
  if (!$sortby_event) $sortby_event="TITLE";
  $out['SORTBY']=$sortby_event;
  
  // SEARCH RESULTS  
  $res=SQLSelect("SELECT * FROM tlg_event ORDER BY ".$sortby_event);
  if (isset($res[0])) {
    paging($res, 20, $out); // search result paging
    colorizeArray($res);
    $total=count($res);
    for($i=0;$i<$total;$i++) {
     // some action for every record if required
    }
    $out['RESULT']=$res;
  }  
?>
