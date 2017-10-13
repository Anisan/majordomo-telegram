<?php
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
require_once("./modules/telegram/Telegram.php");
            
class telegram extends module {
    /**
     * blank
     *
     * Module class constructor
     *
     * @access private
     */
    private $telegramBot;
     
    function __construct() {
        $this->name = "telegram";
        $this->title = "Telegram";
        $this->module_category = "<#LANG_SECTION_APPLICATIONS#>";
        $this->checkInstalled();
        
        $this->getConfig();
        $this->telegramBot = new TelegramBot($this->config['TLG_TOKEN']);
    }
    /**
     * saveParams
     *
     * Saving module parameters
     *
     * @access public
     */
    function saveParams($data=0) {
        $p = array();
        if(IsSet($this->id)) {
            $p["id"] = $this->id;
        }
        if(IsSet($this->view_mode)) {
            $p["view_mode"] = $this->view_mode;
        }
        if(IsSet($this->edit_mode)) {
            $p["edit_mode"] = $this->edit_mode;
        }
        if(IsSet($this->tab)) {
            $p["tab"] = $this->tab;
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
        if(isset($id)) {
            $this->id = $id;
        }
        if(isset($mode)) {
            $this->mode = $mode;
        }
        if(isset($view_mode)) {
            $this->view_mode = $view_mode;
        }
        if(isset($edit_mode)) {
            $this->edit_mode = $edit_mode;
        }
        if(isset($tab)) {
            $this->tab = $tab;
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
        $out = array();
        if($this->action == 'admin') {
            $this->admin($out);
        } else {
            $this->usual($out);
        }
        if(IsSet($this->owner->action)) {
            $out['PARENT_ACTION'] = $this->owner->action;
        }
        if(IsSet($this->owner->name)) {
            $out['PARENT_NAME'] = $this->owner->name;
        }
        $out['VIEW_MODE'] = $this->view_mode;
        $out['EDIT_MODE'] = $this->edit_mode;
        $out['MODE'] = $this->mode;
        $out['ACTION'] = $this->action;
        $out['DATA_SOURCE'] = $this->data_source;
        $out['TAB'] = $this->tab;
        if($this->single_rec) {
            $out['SINGLE_REC'] = 1;
        }
        $this->data = $out;
        $p = new parser(DIR_TEMPLATES . $this->name . "/" . $this->name . ".html", $this->data, $this);
        $this->result = $p->result;
    }
    function debug($content) {
        if($this->config['TLG_DEBUG'])
            $this->log(print_r($content,true));
    }
    function log($message) {
        //echo $message . "\n";
        // DEBUG MESSAGE LOG
        if(!is_dir(ROOT . 'debmes')) {
            mkdir(ROOT . 'debmes', 0777);
        }
        $today_file = ROOT . 'debmes/log_' . date('Y-m-d') . '-telegram.php.txt';
        $data = date("H:i:s")." " . $message . "\n";
        file_put_contents($today_file, $data, FILE_APPEND | LOCK_EX);
    }
    /**
     * BackEnd
     *
     * Module backend
     *
     * @access public
     */
    function admin(&$out) {
        $this->getConfig();
        global $ajax;
        global $filter;
        global $atype;
        if($ajax) {
            header("HTTP/1.0: 200 OK\n");
            header('Content-Type: text/html; charset=utf-8');
            $limit = 50;
            // Find last midifed
            $filename = ROOT . 'debmes/log_*-telegram.php.txt';
            foreach(glob($filename) as $file) {
                $LastModified[] = filemtime($file);
                $FileName[] = $file;
            }
            $files = array_multisort($LastModified, SORT_NUMERIC, SORT_ASC, $FileName);
            $lastIndex = count($LastModified) - 1;
            // Open file
            $data = LoadFile($FileName[$lastIndex]);
            $lines = explode("\n", $data);
            $lines = array_reverse($lines);
            $res_lines = array();
            $total = count($lines);
            $added = 0;
            for($i = 0; $i < $total; $i++) {
                if(trim($lines[$i]) == '') {
                    continue;
                }
                if($filter && preg_match('/' . preg_quote($filter) . '/is', $lines[$i])) {
                    $res_lines[] = $lines[$i];
                    $added++;
                } elseif(!$filter) {
                    $res_lines[] = $lines[$i];
                    $added++;
                }
                if($added >= $limit) {
                    break;
                }
            }
            echo implode("<br/>", $res_lines);
            exit;
        }
        global $webhookinfo;
        if ($webhookinfo)
        {
            header("HTTP/1.0: 200 OK\n");
            header('Content-Type: text/html; charset=utf-8');
            $webhookInfo = $this->telegramBot->endpoint("getWebhookInfo", array(), false);;
            $this->debug($webhookInfo);
            $info = "<b>Url:</b> ".$webhookInfo["result"]["url"];
            if ($info["result"]["has_custom_certificate"] == 1)
                $info .= "</br><b>Use custom certificate</b>";
            $info .= "</br><b>Pending update count:</b> ".$webhookInfo["result"]["pending_update_count"];
            if (isset($webhookInfo["result"]["last_error_date"]))
            {
                $err_date = date('d M Y H:i:s',$webhookInfo["result"]["last_error_date"]);
                $info .= "</br><b>Last error date:</b> ".$err_date;
            }
            if (isset($webhookInfo["result"]["last_error_message"]))
                $info .= "</br><b>Last error:</b> ".$webhookInfo["result"]["last_error_message"];
            echo $info;
            exit;
        }
        global $setwebhook;
        if ($setwebhook)
        {
			global $tlg_webhook_url;
            $this->config['TLG_WEBHOOK_URL'] = $tlg_webhook_url;
            global $tlg_webhook_cert;
            $this->config['TLG_WEBHOOK_CERT'] = $tlg_webhook_cert;
            $this->saveConfig();
            header("HTTP/1.0: 200 OK\n");
            header('Content-Type: text/html; charset=utf-8');
            $webhookRes = $this->telegramBot->setWebhook($this->config['TLG_WEBHOOK_URL']."/webhook_telegram.php",$this->config['TLG_WEBHOOK_CERT']);
            $this->debug($webhookRes);
            echo $webhookRes[description];
            exit;
        }
        global $cleanwebhook;
        if ($cleanwebhook)
        {
            header("HTTP/1.0: 200 OK\n");
            header('Content-Type: text/html; charset=utf-8');
            $webhookRes = $this->telegramBot->deleteWebhook();
            $this->debug($webhookRes);
            echo $webhookRes[description];
            exit;
        }
        global $sendMessage;
        if ($sendMessage)
        {
            header("HTTP/1.0: 200 OK\n");
            header('Content-Type: text/html; charset=utf-8');
            global $user;
            global $text;
            $this->sendMessageToUser($user,$text);
            echo "Ok";
            exit;
        }
        $out['TLG_TOKEN'] = $this->config['TLG_TOKEN'];
        $out['TLG_STORAGE'] = $this->config['TLG_STORAGE'];
        $out['TLG_COUNT_ROW'] = $this->config['TLG_COUNT_ROW'];
        if(!$out['TLG_COUNT_ROW'])
            $out['TLG_COUNT_ROW'] = 3;
        $out['TLG_DEBUG'] = $this->config['TLG_DEBUG'];
        $out['TLG_test'] = $this->data_source . "_" . $this->view_mode . "_" . $this->tab;
        // get webhook info
        $out['TLG_WEBHOOK'] = $this->config['TLG_WEBHOOK'];
        $out['TLG_WEBHOOK_URL'] = $this->config['TLG_WEBHOOK_URL'];
        $out['TLG_WEBHOOK_CERT'] = $this->config['TLG_WEBHOOK_CERT'];
        
        if($this->data_source == 'telegram' || $this->data_source == '') {
            if($this->view_mode == 'update_settings') {
                global $tlg_token;
                $this->config['TLG_TOKEN'] = $tlg_token;
                global $tlg_storage;
                $this->config['TLG_STORAGE'] = $tlg_storage;
                global $tlg_count_row;
                $this->config['TLG_COUNT_ROW'] = $tlg_count_row;
                global $tlg_debug;
                $this->config['TLG_DEBUG'] = $tlg_debug;
                global $tlg_webhook;
                $this->config['TLG_WEBHOOK'] = $tlg_webhook;
                global $tlg_webhook_url;
                $this->config['TLG_WEBHOOK_URL'] = $tlg_webhook_url;
                global $tlg_webhook_cert;
                $this->config['TLG_WEBHOOK_CERT'] = $tlg_webhook_cert;
                $this->saveConfig();
                if (!$this->config['TLG_WEBHOOK'])
                {
                    setGlobal('cycle_telegram','restart');
                    $this->log("Init cycle restart");
                }
                $this->redirect("?");
            }
            if($this->view_mode == 'user_edit') {
                $this->edit_user($out, $this->id);
            }
            if($this->view_mode == 'cmd_edit') {
                $this->edit_cmd($out, $this->id);
            }
            if($this->view_mode == 'event_edit') {
                $this->edit_event($out, $this->id);
            }
            if($this->view_mode == 'user_delete') {
                $this->delete_user($this->id);
                $this->redirect("?");
            }
            if($this->view_mode == 'cmd_delete') {
                $this->delete_cmd($this->id);
                $this->redirect("?tab=cmd");
            }
            if($this->view_mode == 'event_delete') {
                $this->delete_event($this->id);
                $this->redirect("?tab=events");
            }
			if ($this->view_mode=='export_command') {
				$this->export_command($out, $this->id);
			}
			if ($this->view_mode=='import_command') {
				$this->import_command($out);
			}
            if ($this->view_mode=='export_event') {
				$this->export_event($out, $this->id);
			}
			if ($this->view_mode=='import_event') {
				$this->import_event($out);
			}
            if($this->view_mode == '' || $this->view_mode == 'search_ms') {
                if($this->tab == 'cmd') {
                    $this->tlg_cmd($out);
                } else if($this->tab == 'events') {
                    $this->tlg_events($out);
                } else if($this->tab == 'log') {
                    $this->tlg_log($out);
                } else {
                    $this->tlg_users($out);
                }
            }
        }
		global $update_user_info;
		if ($update_user_info) {
			$this->log("Update user info");
			$users = $this->getUsers("");
			foreach($users as $user) {
				$this->updateInfo($user);
			}
			$this->redirect("?");
		} 
    }
    /**
     * Edit/add
     *
     * @access public
     */
    function edit_user(&$out, $id) {
        require(DIR_MODULES . $this->name . '/user_edit.inc.php');
    }
    function edit_cmd(&$out, $id) {
        require(DIR_MODULES . $this->name . '/cmd_edit.inc.php');
    }
    function edit_event(&$out, $id) {
        require(DIR_MODULES . $this->name . '/event_edit.inc.php');
    }
	
	/**
     * Export/import
     *
     * @access public
     */
	function removeBOM($data) {
		if (0 === strpos(bin2hex($data), 'efbbbf')) {
			return substr($data, 3);
		}
	}
	function export_command(&$out, $id) {
		$command=SQLSelectOne("SELECT * FROM tlg_cmd WHERE ID='".(int)$id."'");
		unset($command['ID']);
		$data=json_encode($command);
		$filename="Command_Telegram_".urlencode($command['TITLE']);
		$ext = "txt";
		$mime_type = (PMA_USR_BROWSER_AGENT == 'IE' || PMA_USR_BROWSER_AGENT == 'OPERA')
			? 'application/octetstream' : 'application/octet-stream';
		header('Content-Type: ' . $mime_type);
		if (PMA_USR_BROWSER_AGENT == 'IE')
		{
			header('Content-Disposition: inline; filename="' . $filename . '.' . $ext . '"');
			header("Content-Transfer-Encoding: binary");
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
			print $data;
		} else {
			header('Content-Disposition: attachment; filename="' . $filename . '.' . $ext . '"');
			header("Content-Transfer-Encoding: binary");
			header('Expires: 0');
			header('Pragma: no-cache');
			print $data;
		}
	exit;
	}
	function import_command(&$out) {
		global $file;
		global $overwrite;
		$data=LoadFile($file);
		$command=json_decode($this->removeBOM($data), true);
		if (is_array($command)) {
			$rec=SQLSelectOne("SELECT * FROM tlg_cmd WHERE TITLE='". DBSafe($command["TITLE"]) . "'");
			if ($rec['ID'])
			{
				if ($overwrite)
				{
					$command{'ID'} = $rec['ID'];
					SQLUpdate("tlg_cmd", $command); // update
				}
				else
				{
					$command["TITLE"] .= "_copy";
					SQLInsert("tlg_cmd", $command); // adding new record
				}
			}	
			else
				SQLInsert("tlg_cmd", $command); // adding new record
		}
		$this->redirect("?tab=cmd");
 	}
	function export_event(&$out, $id) {
		$event=SQLSelectOne("SELECT * FROM tlg_event WHERE ID='".(int)$id."'");
		unset($event['ID']);
		$data=json_encode($event);
		$filename="Event_Telegram_".urlencode($event['TITLE']);
		$ext = "txt";
		$mime_type = (PMA_USR_BROWSER_AGENT == 'IE' || PMA_USR_BROWSER_AGENT == 'OPERA')
			? 'application/octetstream' : 'application/octet-stream';
		header('Content-Type: ' . $mime_type);
		if (PMA_USR_BROWSER_AGENT == 'IE')
		{
			header('Content-Disposition: inline; filename="' . $filename . '.' . $ext . '"');
			header("Content-Transfer-Encoding: binary");
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
			print $data;
		} else {
			header('Content-Disposition: attachment; filename="' . $filename . '.' . $ext . '"');
			header("Content-Transfer-Encoding: binary");
			header('Expires: 0');
			header('Pragma: no-cache');
			print $data;
		}
	exit;
	}
	function import_event(&$out) {
		global $file;
		global $overwrite;
		$data=LoadFile($file);
		$event=json_decode($this->removeBOM($data), true);
		if (is_array($event)) {
			$rec=SQLSelectOne("SELECT * FROM tlg_event WHERE TITLE='". DBSafe($event["TITLE"]) . "'");
			if ($rec['ID'])
			{
				if ($overwrite)
				{
					$event{'ID'} = $rec['ID'];
					SQLUpdate("tlg_event", $event); // update
				}
				else
				{
					$event["TITLE"] .= "_copy";
					SQLInsert("tlg_event", $event); // adding new record
				}
			}	
			else
				SQLInsert("tlg_event", $event); // adding new record
		}
		$this->redirect("?tab=events");
 	}
    /**
     * Delete user
     *
     * @access public
     */
    function delete_user($id) {
        $rec = SQLSelectOne("SELECT * FROM tlg_user WHERE ID='$id'");
        // some action for related tables
        SQLExec("DELETE FROM tlg_user WHERE ID='" . $rec['ID'] . "'");
        SQLExec("DELETE FROM tlg_user_cmd WHERE USER_ID='" . $rec['ID'] . "'");
    }
    function delete_cmd($id) {
        $rec = SQLSelectOne("SELECT * FROM tlg_cmd WHERE ID='$id'");
        // some action for related tables
        SQLExec("DELETE FROM tlg_cmd WHERE ID='" . $rec['ID'] . "'");
        SQLExec("DELETE FROM tlg_user_cmd WHERE CMD_ID='" . $rec['ID'] . "'");
    }
    function delete_event($id) {
        $rec = SQLSelectOne("SELECT * FROM tlg_event WHERE ID='$id'");
        // some action for related tables
        SQLExec("DELETE FROM tlg_event WHERE ID='" . $rec['ID'] . "'");
    }
    function tlg_users(&$out) {
        require(DIR_MODULES . $this->name . '/tlg_users.inc.php');
    }
    function tlg_log(&$out) {
        require(DIR_MODULES . $this->name . '/tlg_log.inc.php');
    }
    function tlg_cmd(&$out) {
        require(DIR_MODULES . $this->name . '/tlg_cmd.inc.php');
    }
    function tlg_events(&$out) {
        require(DIR_MODULES . $this->name . '/tlg_events.inc.php');
    }
    function getKeyb($user) {
        $visible = true;
        // Create option for the custom keyboard. Array of array string
        if($user['CMD'] == 0) {
            $option = array();
            $visible = false;
        } else {
            //$option = array( array("A", "B"), array("C", "D") );
            $option = array();
            $sql = "SELECT *,(select VALUE from pvalues where Property_name=`LINKED_OBJECT`+'.'+`LINKED_PROPERTY` ORDER BY updated DESC limit 1) as pvalue" . " FROM tlg_cmd where ACCESS=3 or ((select count(*) from tlg_user_cmd where tlg_cmd.ID=tlg_user_cmd.CMD_ID and tlg_user_cmd.USER_ID=" . $user['ID'] . ")>0 and ACCESS>0) order by tlg_cmd.PRIORITY desc, tlg_cmd.TITLE;";
            //$this->debug($sql);
            $rec = SQLSelect($sql);
            $total = count($rec);
            if($total) {
                for($i = 0; $i < $total; $i++) {
                    $view = false;
                    if($rec[$i]["SHOW_MODE"] == 1)
                        $view = true;
                    elseif($rec[$i]["SHOW_MODE"] == 3) {
                        if($rec[$i]["CONDITION"] == 1 && $rec[$i]["pvalue"] == $rec[$i]["CONDITION_VALUE"])
                            $view = true;
                        if($rec[$i]["CONDITION"] == 2 && $rec[$i]["pvalue"] > $rec[$i]["CONDITION_VALUE"])
                            $view = true;
                        if($rec[$i]["CONDITION"] == 3 && $rec[$i]["pvalue"] < $rec[$i]["CONDITION_VALUE"])
                            $view = true;
                        if($rec[$i]["CONDITION"] == 4 && $rec[$i]["pvalue"] <> $rec[$i]["CONDITION_VALUE"])
                            $view = true;
                    }
                    if($view)
                        $option[] = $rec[$i]["TITLE"];
                }
                $count_row = $this->config['TLG_COUNT_ROW'];
                if(!$count_row)
                    $count_row = 3;
                $option = array_chunk($option, $count_row);
            }
        }
        // Get the keyboard
        $keyb = $this->telegramBot->buildKeyBoard($option, false, true, $selective = $visible);
        //print_r($keyb);
        return $keyb;
    }
    function buildInlineKeyboardButton($text, $url = "", $callback_data = "", $switch_inline_query = "") {
        return $this->telegramBot->buildInlineKeyboardButton($text, $url, $callback_data, $switch_inline_query);
    }
    function buildInlineKeyBoard(array $option) {
        return $this->telegramBot->buildInlineKeyBoard($option);
    }
    function sendContent($content, $endpoint = "sendMessage") {
        $this->debug($content);
        $res = $this->telegramBot->endpoint($endpoint, $content);
        $this->debug($res);
    return $res;
    }
    
    function sendAnswerCallbackQuery($callback_id, $text, $show_alert = false ) {
        $content = array('text' => $text, 'callback_query_id'=>$callback_id, 'show_alert'=>$show_alert);
        $this->sendContent($content,"answerCallbackQuery");
    }
    
    function getUsers($where) {
        $query = "SELECT * FROM tlg_user";
        if($where != "")
            $query = $query . " WHERE " . $where;
        $users = SQLSelect($query);
        return $users;
    }
    
    function getUserName($chat_id) {
        $query = "SELECT * FROM tlg_user WHERE USER_ID=" . $chat_id;
        $user = SQLSelectOne($query);
        if($user)
            return $user['NAME'];
        return "Unknow";
    }
    
    function editMessage($user_id, $message_id, $message, $keyboard = '') {
        $content = array(
            'chat_id' => $user_id,
            'message_id' => $message_id,
            'text' => $message,
            'reply_markup' => $keyboard
        );
        $res = $this->telegramBot->editMessageText($content);
        $this->debug($res);
    return $res;
    }
    
    function deleteMessage($user_id, $message_id) {
        $content = array(
            'chat_id' => $user_id,
            'message_id' => $message_id
        );
        $res = $this->telegramBot->deleteMessage($content);
        $this->debug($res);
    return $res;
    }
    
    // Chat Action
    //typing for text messages
    //upload_photo for photos
    //record_video or upload_video for videos
    //record_audio or upload_audio for audio files
    //upload_document for general files
    //find_location for location data
    function sendAction($user_id, $action = 'typing') {
        $content = array(
            'chat_id' => $user_id,
            'action' => $action
        );
        $res = $this->telegramBot->sendChatAction($content);
        $this->debug($res);
    return $res;
    }
    
    // send message
    function sendMessage($user_id, $message, $keyboard = '', $parse_mode = 'HTML') {
        $content = array(
            'chat_id' => $user_id,
            'text' => $message,
            'reply_markup' => $keyboard,
            'parse_mode' => $parse_mode
        );
        $res = $this->telegramBot->sendMessage($content);
        $this->debug($res);
    return $res;
    }
    function sendMessageTo($where, $message, array $key = NULL) {
        $users = $this->getUsers($where);
        foreach($users as $user) {
            $user_id = $user['USER_ID'];
            if ($user_id === '0') $user_id = $user['NAME'];
            if($key == NULL)
                $keyboard = $this->getKeyb($user);
            else
                $keyboard = $this->telegramBot->buildKeyBoard($key, false, true);
            $this->debug($keyboard);
            $content = array(
                'chat_id' => $user_id,
                'text' => $message,
                'reply_markup' => $keyboard,
                'parse_mode' => 'HTML'
            );
            $res = $this->telegramBot->sendMessage($content);
            $this->debug($res);
        }
    }
    function sendMessageToUser($user_id, $message, $key = NULL) {
        $this->sendMessageTo('(USER_ID="' . DBSafe($user_id) . '" OR NAME LIKE "' . DBSafe($user_id) .  '")', $message, $key);
    }
    function sendMessageToAdmin($message, $key = NULL) {
        $this->sendMessageTo("ADMIN=1", $message, $key);
    }
    function sendMessageToAll($message, $key = NULL) {
        $this->sendMessageTo("", $message, $key);
    }
    ///send image
    function sendImage($user_id, $image_path, $message = '', $keyboard = '') {
        $img = curl_file_create($image_path, 'image/png');
        $content = array(
            'chat_id' => $user_id,
            'photo' => $img,
            'caption' => $message,
            'reply_markup' => $keyboard
        );
        $res = $this->telegramBot->sendPhoto($content);
        $this->debug($res);
    return $res;
    }
    function sendImageTo($where, $image_path, $message = '', array $key = NULL) {
        $img = curl_file_create($image_path, 'image/png');
        $users = $this->getUsers($where);
        foreach($users as $user) {
            $user_id = $user['USER_ID'];
            if ($user_id === '0') $user_id = $user['NAME'];
            if($key == NULL)
                $keyboard = $this->getKeyb($user);
            else
                $keyboard = $this->telegramBot->buildKeyBoard($key, false, true);
            $content = array(
                'chat_id' => $user_id,
                'photo' => $img,
                'caption' => $message,
                'reply_markup' => $keyboard
            );
            $res = $this->telegramBot->sendPhoto($content);
            $this->debug($res);
        }
    }
    function sendImageToUser($user_id, $image_path, $message = '', $key = NULL) {
        $this->sendImageTo('(USER_ID="' . DBSafe($user_id) . '" OR NAME LIKE "' . DBSafe($user_id) .  '")', $image_path, $message, $key);
    }
    function sendImageToAdmin($image_path, $message = '', $key = NULL) {
        $this->sendImageTo("ADMIN=1", $image_path, $message, $key);
    }
    function sendImageToAll($image_path, $message = '', $key = NULL) {
        $this->sendImageTo("", $image_path, $message, $key);
    }
    function sendFile($user_id, $file_path, $message = '', $keyboard = '') {
        $file = curl_file_create($file_path);
        $content = array(
            'chat_id' => $user_id,
            'document' => $file,
            'caption' => $message,
            'reply_markup' => $keyboard
        );
        $res = $this->telegramBot->sendDocument($content);
        $this->debug($res);
    return $res;
    }
    function sendFileTo($where, $file_path, $message = '', array $key = NULL) {
        $file = curl_file_create($file_path);
        $users = $this->getUsers($where);
        foreach($users as $user) {
            $user_id = $user['USER_ID'];
            if ($user_id === '0') $user_id = $user['NAME'];
            if($key == NULL)
                $keyboard = $this->getKeyb($user);
            else
                $keyboard = $this->telegramBot->buildKeyBoard($key, false, true);
            $content = array(
                'chat_id' => $user_id,
                'document' => $file,
                'caption' => $message,
                'reply_markup' => $keyboard
            );
            $res = $this->telegramBot->sendDocument($content);
            $this->debug($res);
        }
    }
    function sendFileToUser($user_id, $file_path, $message = '', $key = NULL) {
        $this->sendFileTo('(USER_ID="' . DBSafe($user_id) . '" OR NAME LIKE "' . DBSafe($user_id) .  '")', $file_path, $message, $key);
    }
    function sendFileToAdmin($file_path, $message = '', $key = NULL) {
        $this->sendFileTo("ADMIN=1", $file_path, $message, $key);
    }
    function sendFileToAll($file_path, $message = '', $key = NULL) {
        $this->sendFileTo("", $file_path, $message, $key);
    }
    function sendSticker($user_id, $sticker, $keyboard = '') {
        $content = array(
            'chat_id' => $user_id,
            'sticker' => $sticker,
            'reply_markup' => $keyboard
        );
        $res = $this->telegramBot->sendSticker($content);
        $this->debug($res);
    return $res;
    }
    function sendStickerTo($where, $sticker, array $key = NULL) {
        $users = $this->getUsers($where);
        foreach($users as $user) {
            $user_id = $user['USER_ID'];
            if ($user_id === '0') $user_id = $user['NAME'];
            if($key == NULL)
                $keyboard = $this->getKeyb($user);
            else
                $keyboard = $this->telegramBot->buildKeyBoard($key, false, true);
            $content = array(
                'chat_id' => $user_id,
                'sticker' => $sticker,
                'reply_markup' => $keyboard
            );
            $res = $this->telegramBot->sendSticker($content);
            $this->debug($res);
        }
    }
    function sendStickerToUser($user_id, $sticker, $key = NULL) {
        $this->sendStickerTo('(USER_ID="' . DBSafe($user_id) . '" OR NAME LIKE "' . DBSafe($user_id) .  '")', $sticker, $key);
    }
    function sendStickerToAdmin($sticker, $key = NULL) {
        $this->sendStickerTo("ADMIN=1", $sticker, $key);
    }
    function sendStickerToAll($sticker, $key = NULL) {
        $this->sendStickerTo("", $sticker, $key);
    }
    function sendLocation($user_id, $lat, $lon, $keyboard = '') {
        $content = array(
            'chat_id' => $user_id,
            'latitude' => $lat,
            'longitude' => $lon,
            'reply_markup' => $keyboard
        );
        $res = $this->telegramBot->sendLocation($content);
        $this->debug($res);
    return $res;
    }
    function sendLocationTo($where, $lat, $lon, array $key = NULL) {
        $users = $this->getUsers($where);
        foreach($users as $user) {
            $user_id = $user['USER_ID'];
            if ($user_id === '0') $user_id = $user['NAME'];
            if($key == NULL)
                $keyboard = $this->getKeyb($user);
            else
                $keyboard = $this->telegramBot->buildKeyBoard($key, false, true);
            $content = array(
                'chat_id' => $user_id,
                'latitude' => $lat,
                'longitude' => $lon,
                'reply_markup' => $keyboard
            );
            $res = $this->telegramBot->sendLocation($content);
            $this->debug($res);
        }
    }
    function sendLocationToUser($user_id, $lat, $lon, $key = NULL) {
        $this->sendLocationTo('(USER_ID="' . DBSafe($user_id) . '" OR NAME LIKE "' . DBSafe($user_id) .  '")', $lat, $lon, $key);
    }
    function sendLocationToAdmin($lat, $lon, $key = NULL) {
        $this->sendLocationTo("ADMIN=1", $lat, $lon, $key);
    }
    function sendLocationToAll($lat, $lon, $key = NULL) {
        $this->sendLocationTo("", $lat, $lon, $key);
    }
    function sendVenue($user_id, $lat, $lon, $title, $address, $keyboard = '') {
        $content = array(
            'chat_id' => $user_id,
            'latitude' => $lat,
            'longitude' => $lon,
            'title' => $title,
            'address' => $address,
            'reply_markup' => $keyboard
        );
        $res = $this->telegramBot->sendVenue($content);
        $this->debug($res);
    return $res;
    }
    function sendVenueTo($where, $lat, $lon, $title, $address, array $key = NULL) {
        $users = $this->getUsers($where);
        foreach($users as $user) {
            $user_id = $user['USER_ID'];
            if ($user_id === '0') $user_id = $user['NAME'];
            if($key == NULL)
                $keyboard = $this->getKeyb($user);
            else
                $keyboard = $this->telegramBot->buildKeyBoard($key, false, true);
            $content = array(
                'chat_id' => $user_id,
                'latitude' => $lat,
                'longitude' => $lon,
                'title' => $title,
                'address' => $address,
                'reply_markup' => $keyboard
            );
            $res = $this->telegramBot->sendVenue($content);
            $this->debug($res);
        }
    }
    function sendVenueToUser($user_id, $lat, $lon, $title, $address, $key = NULL) {
        $this->sendVenueTo('(USER_ID="' . DBSafe($user_id) . '" OR NAME LIKE "' . DBSafe($user_id) .  '")', $lat, $lon, $title, $address, $key);
    }
    function sendVenueToAdmin($lat, $lon, $title, $address, $key = NULL) {
        $this->sendVenueTo("ADMIN=1", $lat, $lon, $title, $address, $key);
    }
    function sendVenueToAll($lat, $lon, $title, $address, $key = NULL) {
        $this->sendVenueTo("", $lat, $lon, $title, $address, $key);
    }
    
    function sendVoice($user_id, $file_path, $caption='', $keyboard = '') {
        $file = curl_file_create($file_path);
		$content = array(
			'chat_id' => $user_id,
			'voice' => $file,
			'caption' => $caption,
			'reply_markup' => $keyboard
		);
		$res = $this->telegramBot->sendVoice($content);
		$this->debug($res);
		return $res;
    }
    function sendVoiceTo($where, $file_path, $caption='', array $key = NULL) {
        $file = curl_file_create($file_path);
		$users = $this->getUsers($where);
        foreach($users as $user) {
            $user_id = $user['USER_ID'];
            if ($user_id === '0') $user_id = $user['NAME'];
			if($key == NULL)
				$keyboard = $this->getKeyb($user);
			else
				$keyboard = $this->telegramBot->buildKeyBoard($key, false, true);
			$content = array(
				'chat_id' => $user_id,
				'voice' => $file,
				'caption' => $caption,
				'reply_markup' => $keyboard
			);
			$res = $this->telegramBot->sendVoice($content);
			$this->debug($res);
		}
    }
	function sendVoiceToUser($user_id, $file_path, $caption='', $key = NULL) {
        $this->sendVoiceTo('(USER_ID="' . DBSafe($user_id) . '" OR NAME LIKE "' . DBSafe($user_id) .  '")', $file_path, $caption, $key);
    }
    function sendVoiceToAdmin($file_path, $caption='', $key = NULL) {
        $this->sendVoiceTo("ADMIN=1", $file_path, $caption, $key);
    }
    function sendVoiceToAll($file_path, $caption='', $key = NULL) {
        $this->sendVoiceTo("", $file_path, $caption, $key);
    }
    
    function photoIdBigSize($data) {
        $photo_id="";
        $photos = $data["message"]["photo"];
        if ($photos){
            $size = 0;
            foreach ($photos as $photo) {
                if ($size < $photo["file_size"])
                {
                    $size = $photo["file_size"];
                    $photo_id=$photo["file_id"];
                }
            }
        }
        return $photo_id;
    }
    
    function init() {
        $this->log("Token bot - " . $this->config['TLG_TOKEN']);
        // create bot
        $me = $this->telegramBot->getMe();
        $this->debug($me);
        if($me)
        {
            $this->log("Me: @" . $me["result"]["username"] . " (" . $me["result"]["id"] . ")");
            $this->config['TLG_BOTNAME'] = $me["result"]["username"];
            $this->saveConfig();
        }
        else {
            $this->log("Error connect or invalid token");
            return;
        }
        $this->log("Update user info");
        $users = $this->getUsers("");
        foreach($users as $user) {
           $this->updateInfo($user);
        }
    }
    function updateInfo($user) {
        $content = array('chat_id' => $user['USER_ID']);
        $chat = $this->telegramBot->getChat($content);
        // set name
        $old_user_name = $user["NAME"];
        if($chat["result"]["type"] == "private")
            $user["NAME"] = $chat["result"]["first_name"] . " " . $chat["result"]["last_name"];
        else
            $user["NAME"] = $chat["result"]["title"];
        if ($user["NAME"] == '' && $old_user_name!='') $user['NAME'] = $old_user_name;
        SQLUpdate("tlg_user", $user);
        if($chat["result"]["photo"]) {
            $image = $chat["result"]["photo"]["big_file_id"];
            $this->debug($image);
            $file = $this->telegramBot->getFile($image);
            $this->debug($file);
            $file_path = ROOT . "cached" . DIRECTORY_SEPARATOR . "telegram" . DIRECTORY_SEPARATOR . $user['USER_ID'] . ".jpg";
            $path_parts = pathinfo($file_path);
            if(!is_dir($path_parts['dirname']))
                mkdir($path_parts['dirname'], 0777, true);
            $this->telegramBot->downloadFile($file["result"]["file_path"], $file_path);
        }
    }
    
    function processCycle() {
        $this->getConfig();
        if ($this->config['TLG_WEBHOOK'])
            return;
        // Get all the new updates and set the new correct update_id
        $req = $this->telegramBot->getUpdates($timeout = 5);
        if(isset($req['error_code']))
        {
            if($this->config['TLG_DEBUG'])
                $this->debug($req);
            else
                $this->log($req['description']);
            return;
        }
        for($i = 0; $i < $this->telegramBot->UpdateCount(); $i++) {
            // You NEED to call serveUpdate before accessing the values of message in Telegram Class
            $this->telegramBot->serveUpdate($i);
            $this->processMessage();
        }
    }
    function processMessage() {
        $skip = false;
        $data = $this->telegramBot->getData();
        $this->debug($data);
        $bot_name = $this->config['TLG_BOTNAME'];
        $text = $this->telegramBot->Text();
        $callback = $this->telegramBot->Callback_Data();
        if($callback) {
            $chat_id = $data["callback_query"]["from"]["id"];
            $username = $data["callback_query"]["message"]["chat"]["username"];
            $fullname = $data["callback_query"]["from"]["first_name"].' '.$data["callback_query"]["from"]["last_name"];
        }else{
            $chat_id = $this->telegramBot->ChatID();
            $username = $this->telegramBot->Username();
            $fullname = $this->telegramBot->FirstName() . ' ' . $this->telegramBot->LastName();
        }
                    
        // поиск в базе пользователя
        $user = SQLSelectOne("SELECT * FROM tlg_user WHERE USER_ID LIKE '" . DBSafe($chat_id) . "';");
        if($chat_id < 0 && substr($text, 0, strlen('@' . $bot_name)) === '@' . $bot_name) {
            $this->debug("Direct message to bot: ".$bot_name. " ($text)");
            $text = str_replace('@' . $bot_name, '', $text);
            $source_user = SQLSelectOne("SELECT * FROM tlg_user WHERE TRIM(NAME) LIKE '" . DBSafe(trim($username)) . "'");
            if($source_user['ID']) {
                $user = $source_user;
                $this->debug("New user check: ".serialize($user));
            } else {
                $this->debug("Cannot find user: ".$username);
            }
        } else {
            $this->debug("Chatid: ".$chat_id."; Bot-name: ".$bot_name."; Message: ".$text);
        }
        
        if($text == "/start" || $text == "/start@" . $bot_name) {
            // если нет добавляем
            if(!$user['ID']) {
                $user['USER_ID'] = $chat_id;
                $user['CREATED'] = date('Y/m/d H:i:s');
                $user['ID'] = SQLInsert('tlg_user', $user);
                $this->log("Added new user: " . $username . " - " . $chat_id);
            }
            $reply = "Вы зарегистрированы! Обратитесь к администратору для получения доступа к функциям.";
            $content = array(
                'chat_id' => $chat_id,
                'text' => $reply
            );
            $this->sendContent($content);
            $this->updateInfo($user);
            return;
        }
        
        // пользователь не найден
        if(!$user['ID']) 
        {
            $this->debug("Unknow user: ".$chat_id."; Message: ".$text);
            return;
        }
        
        $document = $data["message"]["document"];
        $audio = $data["message"]["audio"];
        $video = $data["message"]["video"];
        $voice = $data["message"]["voice"];
        $sticker = $data["message"]["sticker"];
        $photo_id = $this->PhotoIdBigSize($data);
        $location = $this->telegramBot->Location();
        if($callback) {
            $cbm = $this->telegramBot->Callback_Message();
            $message_id = $cbm["message_id"];
            $callback_id = $this->telegramBot->Callback_ID();
            // get events for callback
            $events = SQLSelect("SELECT * FROM tlg_event WHERE TYPE_EVENT=9 and ENABLE=1;");
            foreach($events as $event) {
                if($event['CODE']) {
                    $this->log("Execute code event " . $event['TITLE']);
                    try {
                        eval($event['CODE']);
                    }
                    catch(Exception $e) {
                        registerError('telegram', sprintf('Exception in "%s" method ' . $e->getMessage(), $text));
                    }
                }
                if($skip) {
                    $this->log("Skip next processing events callback");
                    break;
                }
            }
            return;
        }
            
        if($location) {
                $latitude = $location["latitude"];
                $longitude = $location["longitude"];
                $this->log("Get location from " . $chat_id . " - " . $latitude . "," . $longitude);
                if($user['MEMBER_ID']) {
                    $sqlQuery = "SELECT * FROM users WHERE ID = '" . $user['MEMBER_ID'] . "'";
                    $userObj = SQLSelectOne($sqlQuery);
                    if($userObj['LINKED_OBJECT']) {
                        $this->log("Update location to user '" . $userObj['LINKED_OBJECT']."'");
                        setGlobal($userObj['LINKED_OBJECT'] . '.Coordinates', $latitude . ',' . $longitude);
                        setGlobal($userObj['LINKED_OBJECT'] . '.CoordinatesUpdated', date('H:i'));
                        setGlobal($userObj['LINKED_OBJECT'] . '.CoordinatesUpdatedTimestamp', time());
                    }
                }
                // get events for location
                $events = SQLSelect("SELECT * FROM tlg_event WHERE TYPE_EVENT=8 and ENABLE=1;");
                foreach($events as $event) {
                    if($event['CODE']) {
                        $this->log("Execute code event " . $event['TITLE']);
                        try {
                            eval($event['CODE']);
                        }
                        catch(Exception $e) {
                            registerError('telegram', sprintf('Exception in "%s" method ' . $e->getMessage(), $text));
                        }
                    }
                    if($skip) {
                        $this->log("Skip next processing events location");
                        break;
                    }
                }
                return;
        }
            //permission download file
            if($user['DOWNLOAD'] == 1) {
                $type = 0;
                //папку с файлами в настройках
                $storage = $this->config['TLG_STORAGE'] . DIRECTORY_SEPARATOR;
                if($photo_id) {
                    $file = $this->telegramBot->getFile($photo_id);
                    $this->debug($file);
                    $this->log("Get photo from " . $chat_id . " - " . $file["result"]["file_path"]);
                    $file_path = $storage . $chat_id . DIRECTORY_SEPARATOR . $file["result"]["file_path"];
                    $type = 2;
                }
                if($document) {
                    $file = $this->telegramBot->getFile($document["file_id"]);
                    $this->debug($file);
                    $this->log("Get document from " . $chat_id . " - " . $document["file_name"]);
                    if(!isset($file['error_code'])) {
                        $file_path = $storage . $chat_id . DIRECTORY_SEPARATOR . "document" . DIRECTORY_SEPARATOR . $document["file_name"];
                        if(file_exists($file_path))
                            $file_path = $storage . $chat_id . DIRECTORY_SEPARATOR . "document" . DIRECTORY_SEPARATOR . $this->telegramBot->UpdateID() . "_" . $document["file_name"];
                    } else {
                        $file_path = "";
                        $this->log($file['description']);
                    }
                    $type = 6;
                }
                if($audio) {
                    $file = $this->telegramBot->getFile($audio["file_id"]);
                    $this->debug($file);
                    $this->log("Get audio from " . $chat_id . " - " . $file["result"]["file_path"]);
                    $path_parts = pathinfo($file["result"]["file_path"]);
                    $filename = $path_parts["basename"];
                    //use title and performer
                    if(isset($audio['title']))
                        $filename = $audio['title'] . "." . $path_parts['extension'];
                    if(isset($audio['performer']))
                        $filename = $audio['performer'] . "-" . $filename;
                    $file_path = $storage . $chat_id . DIRECTORY_SEPARATOR . "audio" . DIRECTORY_SEPARATOR . $filename;
                    $type = 4;
                }
                if($voice) {
                    $file = $this->telegramBot->getFile($voice["file_id"]);
                    $this->debug($file);
                    $this->log("Get voice from " . $chat_id . " - " . $file["result"]["file_path"]);
                    $file_path = $storage . $chat_id . DIRECTORY_SEPARATOR . $file["result"]["file_path"];
                    $type = 3;
                }
                if($video) {
                    $file = $this->telegramBot->getFile($video["file_id"]);
                    $this->debug($file);
                    $this->log("Get video from " . $chat_id . " - " . $file["result"]["file_path"]);
                    $file_path = $storage . $chat_id . DIRECTORY_SEPARATOR . $file["result"]["file_path"];
                    $type = 5;
                }
                if($sticker) {
                    $file = $this->telegramBot->getFile($sticker["file_id"]);
                    $this->debug($file);
                    $this->log("Get sticker from " . $chat_id . " - " . $sticker["file_id"]);
                    $file_path = $storage.'stickers'.DIRECTORY_SEPARATOR.$file["result"]["file_path"];
                    $sticker_id = $sticker["file_id"];
                    $type = 7;
                }
                if($file_path) {
                    // качаем файл
                    $path_parts = pathinfo($file_path);
                    if(!is_dir($path_parts['dirname']))
                        mkdir($path_parts['dirname'], 0777, true);
                    $this->telegramBot->downloadFile($file["result"]["file_path"], $file_path);
                }
                if($voice && $user['PLAY'] == 1) {
                    //проиграть голосовое сообщение
                    $this->log("Play voice from " . $chat_id . " - " . $file_path);
                    @touch($file_path);
                    playSound($file_path, 1, $level);
                }
                if($file_path || $sticker_id) {
                    // get events
                    $events = SQLSelect("SELECT * FROM tlg_event WHERE TYPE_EVENT=" . $type . " and ENABLE=1;");
                    foreach($events as $event) {
                        if($event['CODE']) {
                            $this->log("Execute code event " . $event['TITLE']);
                            try {
                                eval($event['CODE']);
                            }
                            catch(Exception $e) {
                                registerError('telegram', sprintf('Exception in "%s" method ' . $e->getMessage(), $text));
                            }
                        }
                        if($skip) {
                            $this->log("Skip next processing events type = ".$type);
                            break;
                        }
                    }
                }
                $file_path = "";
            }
            if($text == "") {
                return;
            }
            $this->log($chat_id . " (" . $username . ", " . $fullname . ")=" . $text);
            // get events for text message
            $events = SQLSelect("SELECT * FROM tlg_event WHERE TYPE_EVENT=1 and ENABLE=1;");
            foreach($events as $event) {
                if($event['CODE']) {
                    $this->log("Execute code event " . $event['TITLE']);
                    try {
                        eval($event['CODE']);
                    }
                    catch(Exception $e) {
                        registerError('telegram', sprintf('Exception in "%s" method ' . $e->getMessage(), $text));
                    }
                }
                if($skip) {
                    $this->log("Skip next processing events message");
                    break;
                }
            }
            // пропуск дальнейшей обработки если с обработчике событий установили $skip
            if($skip) {
                $this->log("Skip next processing message");
                return;
            }
            
            if($user['ID']) {
                //смотрим разрешения на обработку команд
                if($user['CMD'] == 1) {
                    $sql = "SELECT * FROM tlg_cmd where '" . DBSafe($text) . "' LIKE CONCAT(tlg_cmd.TITLE,'%') and (ACCESS=3  OR ((select count(*) from tlg_user_cmd where tlg_user_cmd.USER_ID=" . $user['ID'] . " and tlg_cmd.ID=tlg_user_cmd.CMD_ID)>0 and ACCESS>0))";
                    //$this->debug($sql);
                    $cmd = SQLSelectOne($sql);
                    if($cmd['ID']) {
                        $this->log("Find command");
                        //нашли команду
                        if($cmd['CODE']) {
                            $this->log("Execute user`s code command");
                            try {
                                $success = eval($cmd['CODE']);
                                $this->log("Command:" . $text . " Result:" . $success);
                                if($success == false) {
                                    //нет в выполняемом куске кода return
                                    //$content = array('chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => "Ошибка выполнения кода команды ".$text);
                                    //$this->telegramBot->sendMessage($content);
                                } else {
                                    $keyb = $this->getKeyb($user);
                                    $content = array(
                                        'chat_id' => $chat_id,
                                        'reply_markup' => $keyb,
                                        'text' => $success,
                                        'parse_mode' => 'HTML'
                                    );
                                    $this->sendContent($content);
                                    $this->log("Send result to " . $chat_id . ". Command:" . $text . " Result:" . $success);
                                }
                            }
                            catch(Exception $e) {
                                registerError('telegram', sprintf('Exception in "%s" method ' . $e->getMessage(), $text));
                                $keyb = $this->getKeyb($user);
                                $content = array(
                                    'chat_id' => $chat_id,
                                    'reply_markup' => $keyb,
                                    'text' => "Ошибка выполнения кода команды " . $text
                                );
                                $this->sendContent($content);
                            }
                            return;
                        }
                        // если нет кода, который надо выполнить, то передаем дальше на обработку
                    } else
                        $this->log("Command not found");
                }
                if ($user['PATTERNS'] == 1)
                    say(htmlspecialchars($text), 0, $user['MEMBER_ID'], 'telegram' . $user['ID']);
            }
    }
	
	function execCommand($chat_id, $command)
	{
		$user = SQLSelectOne("SELECT * FROM tlg_user WHERE USER_ID LIKE '" . DBSafe($chat_id) . "';");
		$cmd = SQLSelectOne("SELECT * FROM tlg_cmd INNER JOIN tlg_user_cmd on tlg_cmd.ID=tlg_user_cmd.CMD_ID where (ACCESS=3  OR (tlg_user_cmd.USER_ID=" . $user['ID'] . " and ACCESS>0)) and '" . DBSafe($command) . "' LIKE CONCAT(TITLE,'%');");
        if($cmd['ID']) {
			$this->log("execCommand => Find command");
            if($cmd['CODE']) {
                $this->log("execCommand => Execute user`s code command");
                try {
					$text = $command;
                    $success = eval($cmd['CODE']);
                    $this->log("Command:" . $text . " Result:" . $success);
                    if($success == false) {
                        //нет в выполняемом куске кода return
                    } else {
                        $content = array(
                        'chat_id' => $chat_id,
                        'text' => $success,
                        'parse_mode' => 'HTML'
                        );
                        $this->sendContent($content);
                        $this->log("Send result to " . $chat_id . ". Command:" . $text . " Result:" . $success);
                    }
                }
                catch(Exception $e) {
                    registerError('telegram', sprintf('Exception in "%s" method ' . $e->getMessage(), $text));
                }
			}
        }        
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
    function processSubscription($event, &$details) {
        $this->getConfig();
        if($event == 'SAY') { // || $event=='SAYTO' || $event=='REPLYTO'
            $level = $details['level'];
            $message = $details['message'];
            if($details['destination']) {
                $destination = $details['destination'];
            } elseif($details['source']) {
                $destination = $details['source'];
            }
            $me = $this->telegramBot->getMe();
            $bot_name = $me["result"]["username"];
            $users = SQLSelect("SELECT * FROM tlg_user WHERE HISTORY=1;");
            $c_users = count($users);
            if($c_users) {
                $reply = $message;
                for($j = 0; $j < $c_users; $j++) {
                    $user_id = $users[$j]['USER_ID'];
                    if ($user_id === '0') {
                        $user_id = $users[$j]['NAME'];
                    }
                    if($destination == 'telegram' . $users[$j]['ID'] || (!$destination && ($level >= $users[$j]['HISTORY_LEVEL']))) {
                        $this->log(" Send to " . $user_id . " - " . $reply);
                        $keyb = $this->getKeyb($users[$j]);
                        $content = array(
                            'chat_id' => $user_id,
                            'text' => $reply,
                            'reply_markup' => $keyb,
                            'parse_mode' => 'HTML'
                        );
                        $this->sendContent($content);
                    }
                }
                $this->debug("Sended - " . $reply);
            } else {
                $this->log("No users to send data");
            }
        }
    }
    /**
     * Install
     *
     * Module installation routine
     *
     * @access private
     */
    function install($data='') {
        subscribeToEvent($this->name, 'SAY', '', 10);
        subscribeToEvent($this->name, 'SAYTO', '', 10);
        subscribeToEvent($this->name, 'SAYREPLY', '', 10);
        parent::install();
    }
    /**
     * Uninstall
     *
     * Module uninstall routine
     *
     * @access public
     */
    function uninstall() {
        SQLExec('DROP TABLE IF EXISTS tlg_user_cmd');
        SQLExec('DROP TABLE IF EXISTS tlg_user');
        SQLExec('DROP TABLE IF EXISTS tlg_cmd');
        SQLExec('DROP TABLE IF EXISTS tlg_event');
        unsubscribeFromEvent($this->name, 'SAY'); 
        unsubscribeFromEvent($this->name, 'SAYTO'); 
        unsubscribeFromEvent($this->name, 'SAYREPLY'); 
        parent::uninstall();
    }
    /**
     * dbInstall
     *
     * Database installation routine
     *
     * @access private
     */
    function dbInstall($data) {
        $data = <<<EOD
 tlg_user: ID int(10) unsigned NOT NULL auto_increment
 tlg_user: NAME varchar(255) NOT NULL DEFAULT ''
 tlg_user: USER_ID varchar(25) NOT NULL DEFAULT '0'
 tlg_user: MEMBER_ID int(10) NOT NULL DEFAULT '1'
 tlg_user: CREATED datetime
 tlg_user: ADMIN int(3) unsigned NOT NULL DEFAULT '0' 
 tlg_user: HISTORY int(3) unsigned NOT NULL DEFAULT '0' 
 tlg_user: HISTORY_LEVEL int(3) unsigned NOT NULL DEFAULT '0' 
 tlg_user: CMD int(3) unsigned NOT NULL DEFAULT '0' 
 tlg_user: PATTERNS int(3) unsigned NOT NULL DEFAULT '0' 
 tlg_user: DOWNLOAD int(3) unsigned NOT NULL DEFAULT '0' 
 tlg_user: PLAY int(3) unsigned NOT NULL DEFAULT '0' 
 
 tlg_cmd: ID int(10) unsigned NOT NULL auto_increment
 tlg_cmd: TITLE varchar(255) NOT NULL DEFAULT ''
 tlg_cmd: DESCRIPTION text
 tlg_cmd: CODE text
 tlg_cmd: ACCESS int(10) NOT NULL DEFAULT '0'
 tlg_cmd: SHOW_MODE int(10) NOT NULL DEFAULT '1'
 tlg_cmd: LINKED_OBJECT varchar(255) NOT NULL DEFAULT ''
 tlg_cmd: LINKED_PROPERTY varchar(255) NOT NULL DEFAULT '' 
 tlg_cmd: CONDITION int(10) NOT NULL DEFAULT '1' 
 tlg_cmd: CONDITION_VALUE varchar(255) NOT NULL DEFAULT '' 
 tlg_cmd: PRIORITY int(10) NOT NULL DEFAULT '1' 
 
 tlg_user_cmd: ID int(10) unsigned NOT NULL auto_increment
 tlg_user_cmd: USER_ID int(10) NOT NULL
 tlg_user_cmd: CMD_ID int(10) NOT NULL
 
 tlg_event: ID int(10) unsigned NOT NULL auto_increment
 tlg_event: TITLE varchar(255) NOT NULL DEFAULT ''
 tlg_event: DESCRIPTION text
 tlg_event: TYPE_EVENT int(3) unsigned NOT NULL DEFAULT '1' 
 tlg_event: ENABLE int(3) unsigned NOT NULL DEFAULT '0' 
 tlg_event: CODE text
  
EOD;
        parent::dbInstall($data);
        $cmds = SQLSelectOne("SELECT * FROM tlg_cmd;");
        if(count($cmds) == 0) {
            $rec['TITLE'] = 'Ping';
            $rec['DESCRIPTION'] = 'Example command Ping-Pong';
            $rec['CODE'] = 'return "Pong!";';
            $rec['ACCESS'] = 2;
            SQLInsert('tlg_cmd', $rec);
        }
    }
    // --------------------------------------------------------------------
}
?>
