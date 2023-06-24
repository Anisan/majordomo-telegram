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


$table_name='tlg_cmd';
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
    $rec['TITLE']=gr('title');
    $rec['DESCRIPTION']=gr('description');

    $old_code=isset($rec['CODE'])?$rec['CODE']:'';

    $rec['CODE'] = gr('code');
    $rec['ACCESS']=gr('select_access');
    $rec['SHOW_MODE']=gr('show_mode');
    $rec['LINKED_OBJECT']=gr('linked_object_new');
    $rec['LINKED_PROPERTY']=gr('linked_property_new');
    $rec['CONDITION']=gr('condition_new');
    $rec['CONDITION_VALUE']=gr('condition_value_new');
    $rec['PRIORITY']=gr('priority','int');

    $users_id = gr('user_id');
    
    if ($rec['TITLE'] == "")
    {
        $out['ERR']=1;
        $ok=0;
    }

    //UPDATING RECORD
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
    
    
    if ($ok) {
      if (isset($rec['ID'])) {
        SQLUpdate($table_name, $rec); // update
        updateAccess($rec['ID'],$users_id);
      } else {
        $new_rec=1; 
        $rec['ID']=SQLInsert($table_name, $rec); // adding new record
        updateAccess($rec['ID'],$users_id);
        $id=$rec['ID'];
      } 
      if($error_code == 0) $out['OK']=1;
    } else {
      $out['ERR']=1;
    }
  }
    $ok=1;
}

$res = SQLSelect("select ID,NAME,ADMIN, (SELECT count(*) FROM tlg_user_cmd where CMD_ID='$id' and tlg_user_cmd.USER_ID=tlg_user.ID) as ACCESS_USER from tlg_user");
if (isset($res[0])) {
    $out['LIST_ACCESS'] = $res;
}
$res = SQLSelect("SELECT * from tlg_user_cmd where CMD_ID='$id'");
if (isset($res[0])) {
    $qs    = array();
    foreach ($res as $row) {
        $qs[] = $row['USER_ID'];
    }
    $out['USERS_ID'] = implode(',', $qs);
}

outHash($rec, $out);

function updateAccess($cmd_id, $users_id) {
    SQLExec("DELETE from tlg_user_cmd where CMD_ID=".$cmd_id);
    $users = explode(",", $users_id);
    foreach ( $users as $value ) {
        if ($value == "") continue;
        $recCU=array();
        $recCU['CMD_ID']=$cmd_id;
        $recCU['USER_ID']=$value;
        $recCU['ID']=SQLInsert('tlg_user_cmd', $recCU);
    }
}
  

