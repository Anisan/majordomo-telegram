<?php

if ($this->mode=='setvalue') {
   global $prop_id;
   global $new_value;
   global $id;
   $this->setProperty($prop_id, $new_value, 1);   
   $this->redirect("?id=".$id."&view_mode=".$this->view_mode."&edit_mode=".$this->edit_mode."&tab=".$this->tab);
} 

if ($this->mode=='cmd') {
    global $data;
    $this->cmd($data);
}


  
if ($this->owner->name=='panel') {
  $out['CONTROLPANEL']=1;
}

$table_name='tlg_user';
$rec=SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");
    
    $res = SQLSelect("SELECT * FROM users");
    if ($res[0]) {
        $out['LIST_MEMBER'] = $res;
    }

if ($this->mode=='update') { 
  $ok=1;
  if ($this->tab=='') {
    if (!$rec['ID'])
    {
        global $user_id;
        $rec['USER_ID']=$user_id;
    }
    global $name;
    $rec['NAME']=$name;
    global $admin;
    $rec['ADMIN']=$admin;
    global $history;
    $rec['HISTORY']=$history;
    global $history_level;
    $rec['HISTORY_LEVEL']=$history_level;
    global $cmd;
    $rec['CMD']=$cmd;
    global $patterns;
    $rec['PATTERNS']=$patterns;
    global $download;
    $rec['DOWNLOAD']=$download;
    global $play;
    $rec['PLAY']=$play;
    global $select_member;
    $rec['MEMBER_ID']=$select_member;
    
    //UPDATING RECORD
    if ($ok) {
      if ($rec['ID']) {
        SQLUpdate($table_name, $rec); // update
      } else {
        $new_rec=1; 
        $rec['ID']=SQLInsert($table_name, $rec); // adding new record
        $id=$rec['ID'];
      }  
      $out['OK']=1;
    } else {
      $out['ERR']=1;
    }
  }
    $ok=1;
}
 


outHash($rec, $out);
  
?>
