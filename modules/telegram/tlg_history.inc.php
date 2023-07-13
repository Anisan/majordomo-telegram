<?php
$where = "";
$filter = gr('filter');
if ($filter)
    $where = "where DIRECTION IN (".$filter.")";
  // SEARCH RESULTS  
  $res=SQLSelect('SELECT tlg_history.ID, tlg_history.CREATED, tlg_history.USER_ID,DIRECTION,TYPE,MESSAGE,tlg_user.NAME FROM tlg_history LEFT JOIN tlg_user ON tlg_history.USER_ID = tlg_user.USER_ID '.$where.' ORDER BY CREATED DESC, ID DESC');
  if (isset($res[0])) {
    $out['COUNT']=count($res);
    $st = array_count_values(array_column($res, 'DIRECTION'));
    $out['COUNT_IN']=$st["0"] ?? 0;
    $out['COUNT_OUT']=$st["1"] ?? 0;
    $out['COUNT_OUT_ERROR']=$st["2"] ?? 0;
    $out['COUNT_OUT_RESEND']=$st["3"] ?? 0;
    $out['COUNT_OUT_SKIP']=$st["4"] ?? 0;
    $st = array_count_values(array_column($res, 'NAME'));
    $stat = [];
    if (is_array($st)) {
        foreach ($st as $key => $value) {
            $stat[] = array('KEY' => $key, 'VALUE' => $value);
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
    }
    $out['RESULT']=$res;
    $out['HISTORY_DAYS'] = $this->config['TLG_HISTORY_DAYS'] !== "" ? $this->config['TLG_HISTORY_DAYS'] : 7;
  }  
?>
