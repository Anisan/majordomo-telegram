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

if ($this->mode=='update') { 
  $ok=1;
  if ($this->tab=='') {

    // NAME
    global $name;
    $rec['NAME']=$name;
    global $admin;
    $rec['ADMIN']=$admin;
    global $history;
    $rec['HISTORY']=$history;
    global $cmd;
    $rec['CMD']=$cmd;
    
    //UPDATING RECORD
    if ($ok) {
      if ($rec['ID']) {
        SQLUpdate($table_name, $rec); // update
      } 
      $out['OK']=1;
    } else {
      $out['ERR']=1;
    }
  }
    $ok=1;

      if ($rec['ID']) {
        $total=count($sensors);
        for($i=0;$i<$total;$i++) {
          global ${'linked_object'.$sensors[$i]['ID']};
          global ${'linked_property'.$sensors[$i]['ID']};
          
					global ${'ack'.$sensors[$i]['ID']};					
          if (${'ack'.$sensors[$i]['ID']}) {
            $sensors[$i]['ACK']=1;            
          } else {
            $sensors[$i]['ACK']=0;            
          } 
					
					global ${'req'.$sensors[$i]['ID']};
					if (${'req'.$sensors[$i]['ID']}) {
            $sensors[$i]['REQ']=1;            
          } else {
            $sensors[$i]['REQ']=0;            
          } 
					SQLUpdate('msnodeval', $sensors[$i]);					
          
          $old_linked_object=$sensors[$i]['LINKED_OBJECT'];
          $old_linked_property=$sensors[$i]['LINKED_PROPERTY'];
          
          if (${'linked_object'.$sensors[$i]['ID']} && ${'linked_property'.$sensors[$i]['ID']}) {
            $sensors[$i]['LINKED_OBJECT']=${'linked_object'.$sensors[$i]['ID']};
            $sensors[$i]['LINKED_PROPERTY']=${'linked_property'.$sensors[$i]['ID']};
            SQLUpdate('msnodeval', $sensors[$i]);
          } elseif ($sensors[$i]['LINKED_OBJECT'] || $sensors[$i]['LINKED_PROPERTY']) {
            $sensors[$i]['LINKED_OBJECT']='';
            $sensors[$i]['LINKED_PROPERTY']='';
            SQLUpdate('msnodeval', $sensors[$i]);
          }

          if ($sensors[$i]['LINKED_OBJECT'] && $sensors[$i]['LINKED_PROPERTY']) {
            addLinkedProperty($sensors[$i]['LINKED_OBJECT'], $sensors[$i]['LINKED_PROPERTY'], $this->name);          
          }

          if ($old_linked_object && $old_linked_object!=$sensors[$i]['LINKED_OBJECT'] && $old_linked_property && $old_linked_property!=$sensors[$i]['LINKED_PROPERTY']) {
            removeLinkedProperty($old_linked_object, $old_linked_property, $this->name);          
          }
        }
      }
    }
 


outHash($rec, $out);
  
?>
