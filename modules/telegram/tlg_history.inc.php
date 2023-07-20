<?php
$where = "";
$filter = gr('filter');
$user_id = gr('user_id');
$filter_type = gr('filter_type');
if ($filter)
    $where = "where DIRECTION IN (".$filter.") OR USER_ID NOT IN (SELECT USER_ID from tlg_user)";
if ($user_id)
    $where = "where USER_ID = '".$user_id."'";
if ($filter_type!='')
    $where = "where TYPE = '".$filter_type."'";
  // SEARCH RESULTS  
  $res=SQLSelect('SELECT ID,CREATED,USER_ID,DIRECTION,TYPE,MESSAGE FROM tlg_history '.$where.' ORDER BY CREATED DESC, ID DESC');
  $users_rec=SQLSelect('SELECT USER_ID, NAME FROM tlg_user');
  $users = [];
  foreach ($users_rec as $user)
    $users[$user["USER_ID"]] = $user["NAME"];
  
  if (isset($res[0])) {
    $out['COUNT']=count($res);
    $st = array_count_values(array_column($res, 'DIRECTION'));
    $out['COUNT_IN']=$st["0"] ?? 0;
    $out['COUNT_OUT']=$st["1"] ?? 0;
    $out['COUNT_OUT_ERROR']=$st["2"] ?? 0;
    $out['COUNT_OUT_RESEND']=$st["3"] ?? 0;
    $out['COUNT_OUT_SKIP']=$st["4"] ?? 0;
    $st = array_count_values(array_column($res, 'USER_ID'));
    $stat = [];
    if (is_array($st)) {
        foreach ($st as $key => $value) {
            if ($key != "0" && $key != "")
                $stat[] = array('ID'=> $key, 'KEY' => $users[$key] ?? $key, 'VALUE' => $value);
        }
    }
    $out['STAT_USERS']=$stat;
    $st = array_count_values(array_column($res, 'TYPE'));
    $stat = [];
    if (is_array($st)) {
        foreach ($st as $key => $value) {
            $stat[] = array('KEY' => $key, 'VALUE' => $value);
        }
    }
    $out['STAT_TYPES']=$stat;
    paging($res, 50, $out); // search result paging
    colorizeArray($res);
    $total=count($res);
    for($i=0;$i<$total;$i++) {
     // some action for every record if required
     $res[$i]['MESSAGE'] = nl2br($res[$i]['MESSAGE']);
     $res[$i]['NAME'] = $users[$res[$i]['USER_ID']];
    }
    $out['RESULT']=$res;
    $out['HISTORY_DAYS'] = $this->config['TLG_HISTORY_DAYS'] !== "" ? $this->config['TLG_HISTORY_DAYS'] : 7;
  }  
?>
