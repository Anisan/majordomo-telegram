<?php

  global $session;
    
  global $uid;
  if ($nid!='') {
   $qry.=" AND USER_ID LIKE '%".DBSafe($nid)."%'";
   $out['USER_ID']=$nid;
  }
  
  global $name;
  if ($name!='') {
   $qry.=" AND NAME LIKE '%".DBSafe($name)."%'";
   $out['NAME']=$name;
  }
  
  // FIELDS ORDER
  global $sortby_user;
  if (!$sortby_user) {
   $sortby_user=$session->data['tlg_user_sort'];
  } else {
   if ($session->data['tlg_user_sort']==$sortby_user) {
    if (Is_Integer(strpos($sortby_user, ' DESC'))) {
     $sortby_user=str_replace(' DESC', '', $sortby_user);
    } else {
     $sortby_user=$sortby_user." DESC";
    }
   }
   $session->data['tlg_user_sort']=$sortby_user;
  }
  if (!$sortby_user) $sortby_user="USER_ID";
  $out['SORTBY']=$sortby_user;
  
  // SEARCH RESULTS  
  $res=SQLSelect("SELECT * FROM tlg_user ORDER BY ".$sortby_user);
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
