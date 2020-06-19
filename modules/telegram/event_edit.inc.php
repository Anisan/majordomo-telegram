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

if(defined('SETTINGS_CODEEDITOR_TURNONSETTINGS')) {
    $out['SETTINGS_CODEEDITOR_TURNONSETTINGS'] = SETTINGS_CODEEDITOR_TURNONSETTINGS;
    $out['SETTINGS_CODEEDITOR_UPTOLINE'] = SETTINGS_CODEEDITOR_UPTOLINE;
    $out['SETTINGS_CODEEDITOR_SHOWERROR'] = SETTINGS_CODEEDITOR_SHOWERROR;
}
    
if ($this->mode=='update') { 
  $ok=1;
  if ($this->tab=='') {

    // NAME
    global $title;
    $rec['TITLE']=$title;
    global $description;
    $rec['DESCRIPTION']=$description;
    global $code;
    $old_code=$rec['CODE'];
    $rec['CODE'] = $code;
    global $enable;
    $rec['ENABLE']=$enable;
    
    global $type_event;
    $rec['TYPE_EVENT']=$type_event;
    
    if ($rec['TITLE'] == "")
    {
        $out['ERR']=1;
        $ok=0;
    }
    if ($rec['CODE'] != '') {
        $errors = php_syntax_error($rec['CODE']);
        if ($errors) {
            $out['ERR_LINE'] = preg_replace('/[^0-9]/', '', substr(stristr($errors, 'php on line '), 0, 18))-2;
            $errorStr = explode('Parse error: ', htmlspecialchars(strip_tags(nl2br($errors))));
            $errorStr = explode('Errors parsing', $errorStr[1]);
            $errorStr = explode(' in ', $errorStr[0]);
            $out['ERRORS'] = $errorStr[0];
            $out['ERR_FULL'] = $errorStr[0].' '.$errorStr[1];
            $out['ERR_OLD_CODE'] = $old_code;
            $error_code=1;
            $out['ERR']=1;
            $ok=0;
        } else {
            $error_code=0;
            $ok=1;
        }
    } else {
        $error_code=0;
        $ok=1;
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
      if($error_code == 0) $out['OK']=1;
    } else {
      $out['ERR']=1;
    }
  }
    $ok=1;
}

outHash($rec, $out);
 
?>
