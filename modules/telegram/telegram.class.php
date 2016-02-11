<?
/**
* Telegram Bot 
*
*
* @package project
* @author Isupov Andrey <eraser1981@gmail.com>
* @copyright (c)
*/
//
//
class telegram extends module {
/**
* blank
*
* Module class constructor
*
* @access private
*/
function telegram() {
  $this->name="telegram";
  $this->title="Telegram";
  $this->module_category="<#LANG_SECTION_APPLICATIONS#>";
  $this->checkInstalled();
}
/**
* saveParams
*
* Saving module parameters
*
* @access public
*/
function saveParams() {
 $p=array();
 if (IsSet($this->id)) {
  $p["id"]=$this->id;
 }
 if (IsSet($this->view_mode)) {
  $p["view_mode"]=$this->view_mode;
 }
 if (IsSet($this->edit_mode)) {
  $p["edit_mode"]=$this->edit_mode;
 }
 if (IsSet($this->tab)) {
  $p["tab"]=$this->tab;
 }
 return parent::saveParams($p);
}
/**
* getParams
*
* Getting module parameters from query string
*
* @access public
*/
function getParams() {
  global $id;
  global $mode;
  global $view_mode;
  global $edit_mode;
  global $tab;
  if (isset($id)) {
   $this->id=$id;
  }
  if (isset($mode)) {
   $this->mode=$mode;
  }
  if (isset($view_mode)) {
   $this->view_mode=$view_mode;
  }
  if (isset($edit_mode)) {
   $this->edit_mode=$edit_mode;
  }
  if (isset($tab)) {
   $this->tab=$tab;
  }
}
/**
* Run
*
* Description
*
* @access public
*/
function run() {
 global $session;
  $out=array();
  if ($this->action=='admin') {
   $this->admin($out);
  } else {
   $this->usual($out);
  }
  if (IsSet($this->owner->action)) {
   $out['PARENT_ACTION']=$this->owner->action;
  }
  if (IsSet($this->owner->name)) {
   $out['PARENT_NAME']=$this->owner->name;
  }
  $out['VIEW_MODE']=$this->view_mode;
  $out['EDIT_MODE']=$this->edit_mode;
  $out['MODE']=$this->mode;
  $out['ACTION']=$this->action;
  $out['DATA_SOURCE']=$this->data_source;
  $out['TAB']=$this->tab; 
  if ($this->single_rec) {
   $out['SINGLE_REC']=1;
  }
  $this->data=$out;
  $p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
  $this->result=$p->result;
}
/**
* BackEnd
*
* Module backend
*
* @access public
*/
function admin(&$out) {
    global $ajax; 
    global $filter;
    global $atype;
    
    if ($ajax) {
        header ("HTTP/1.0: 200 OK\n");
        header ('Content-Type: text/html; charset=utf-8');
        $limit=50;
        
        // Find last midifed
        $filename=ROOT.'debmes/log_*-cycle_telegram.php.txt';
        foreach(glob($filename) as $file) {      
          $LastModified[] = filemtime($file);
          $FileName[] = $file;
        }    
        $files = array_multisort($LastModified, SORT_NUMERIC, SORT_ASC, $FileName);
        $lastIndex = count($LastModified) - 1;
    
        // Open file
        $data=LoadFile( $FileName[$lastIndex] );    
    
        $lines=explode("\n", $data);
        $lines=array_reverse($lines);
        $res_lines=array();
        $total=count($lines);
        $added=0;
        for($i=0;$i<$total;$i++) {
            if (trim($lines[$i])=='') {
            continue;
            }

            if ($filter && preg_match('/'.preg_quote($filter).'/is', $lines[$i])) {
                $res_lines[]=$lines[$i];
                $added++;
            } elseif (!$filter) {
                $res_lines[]=$lines[$i];
                $added++;
            }

            if ($added>=$limit) {
                break;
            }
        }

    echo implode("<br/>", $res_lines);
    exit;
    }
    
    $this->getConfig();
    $out['TLG_TOKEN']=$this->config['TLG_TOKEN'];
    $out['TLG_test']=$this->data_source."_".$this->view_mode."_".$this->tab;
    if ($this->data_source=='telegram' || $this->data_source=='') {
        if ($this->view_mode=='update_settings') {
            global $tlg_token;
            $this->config['TLG_TOKEN']=$tlg_token;
            $this->saveConfig();
            $this->redirect("?");      
            }
        if ($this->view_mode=='user_edit') {
            $this->edit_user($out, $this->id);
        }
        if ($this->view_mode=='user_delete') {
          $this->delete_user($this->id);
          $this->redirect("?");
        } 
        
        if ($this->view_mode=='' || $this->view_mode=='search_ms') {
          if ($this->tab=='mesh'){
            //$this->search_mesh($out);
          } else if ($this->tab=='log'){
            $this->tlg_log($out);
          } else {
            $this->tlg_users($out);
          }
        }
    }
}

/**
* Edit/add
*
* @access public
*/
function edit_user(&$out, $id) {
  require(DIR_MODULES.$this->name.'/user_edit.inc.php');
}
/**
* Delete user
*
* @access public
*/
function delete_user($id) {
  $rec=SQLSelectOne("SELECT * FROM tlg_user WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM tlg_user WHERE ID='".$rec['ID']."'"); 
}

function tlg_users(&$out) {
  require(DIR_MODULES.$this->name.'/tlg_users.inc.php');
}

function tlg_log(&$out) {
  require(DIR_MODULES.$this->name.'/tlg_log.inc.php');
}
/**
* FrontEnd
*
* Module frontend
*
* @access public
*/
function usual(&$out) {
 $this->admin($out);
}
/**
* Install
*
* Module installation routine
*
* @access private
*/
 function install() {
  parent::install();
 }
 
 /**
* dbInstall
*
* Database installation routine
*
* @access private
*/
 function dbInstall($data) {
/*
usbdevices - usbdevices
*/
  $data = <<<EOD
 tlg_user: ID int(10) unsigned NOT NULL auto_increment
 tlg_user: NAME varchar(255) NOT NULL DEFAULT ''
 tlg_user: USER_ID int(10) NOT NULL DEFAULT '0'
 tlg_user: CREATED datetime
 tlg_user: ADMIN int(3) unsigned NOT NULL DEFAULT '0' 
 tlg_user: HISTORY int(3) unsigned NOT NULL DEFAULT '0' 
 tlg_user: CMD int(3) unsigned NOT NULL DEFAULT '0' 
EOD;
  parent::dbInstall($data);
 }
// --------------------------------------------------------------------
}
?>