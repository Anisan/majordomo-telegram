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

$table_name='tlg_event';
$rec=SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");
    
if ($this->mode=='update') { 
  $ok=1;
  if ($this->tab=='') {

    // NAME
    global $title;
    $rec['TITLE']=$title;
    global $description;
    $rec['DESCRIPTION']=$description;
    global $code;
    $rec['CODE']=$code;
    global $enable;
    $rec['ENABLE']=$enable;
    
    global $type_event;
    $rec['TYPE_EVENT']=$type_event;
    
    if ($rec['TITLE'] == "")
    {
        $out['ERR']=1;
        $ok=0;
    }
    if ($rec['CODE']!='') {
        $errors=php_syntax_error($rec['CODE']);
        if ($errors) {
            $out['ERR']=1;
            $out['ERR_CODE']=1;
            $out['ERRORS']=$errors;
            $ok=0;
        }
    }
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
