<?php

  global $session;

  $qry = "1";

  $uid = gr('uid');
  if ($uid!='') {
   $qry.=" AND USER_ID LIKE '%".DBSafe($uid)."%'";
   $out['USER_ID']=$uid;
  }
  
  $name = gr('name');
  if ($name!='') {
   $qry.=" AND NAME LIKE '%".DBSafe($name)."%'";
   $out['NAME']=$name;
  }
  
  // FIELDS ORDER
  $sortby_user = gr('sortby_user');
  if (!$sortby_user) {
   $sortby_user=isset($session->data['tlg_user_sort'])?$session->data['tlg_user_sort']:'';
  } else {
   if (isset($session->data['tlg_user_sort']) && $session->data['tlg_user_sort']==$sortby_user) {
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
  $res=SQLSelect("SELECT * FROM tlg_user WHERE $qry ORDER BY ".$sortby_user);
  if (isset($res[0])) {
    paging($res, 20, $out); // search result paging
    colorizeArray($res);
    $out['RESULT']=$res;
  }  

