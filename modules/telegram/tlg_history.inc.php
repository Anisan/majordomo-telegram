<?php

  // SEARCH RESULTS  
  $res=SQLSelect("SELECT * FROM tlg_history ORDER BY CREATED DESC, ID DESC");
  if (isset($res[0])) {
    $out['COUNT']=count($res);
    $st = array_count_values(array_column($res, 'DIRECTION'));
    $out['COUNT_IN']=$st["0"] ?? 0;
    $out['COUNT_OUT']=$st["1"] ?? 0;
    $out['COUNT_OUT_ERROR']=$st["2"] ?? 0;
    $out['COUNT_OUT_RESEND']=$st["3"] ?? 0;
    $out['COUNT_OUT_SKIP']=$st["4"] ?? 0;
    paging($res, 50, $out); // search result paging
    colorizeArray($res);
    $total=count($res);
    for($i=0;$i<$total;$i++) {
     // some action for every record if required
        unset($res[$i]['RAW']);
    }
    $out['RESULT']=$res;
  }  
?>
