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
    private $last_update_id=0;
     
    function __construct() {
        $this->name = "telegram";
        $this->title = "Telegram";
        $this->module_category = "<#LANG_SECTION_APPLICATIONS#>";
        $this->checkInstalled();
        
        $this->getConfig();
        if (!$this->config['TLG_USEPROXY'])
            $this->telegramBot = new TelegramBot($this->config['TLG_TOKEN']);
        else
        {
            $type_proxy = CURLPROXY_SOCKS5;
            if ($this->config['TLG_PROXY_TYPE']==1)
                $type_proxy = CURLPROXY_HTTP;
            if ($this->config['TLG_PROXY_TYPE']==3)
                $type_proxy = CURLPROXY_SOCKS5_HOSTNAME;
            if ($this->config['TLG_PROXY_TYPE']==4)
                $type_proxy = CURLPROXY_HTTPS;
            $this->telegramBot = new TelegramBot($this->config['TLG_TOKEN'],$this->config['TLG_PROXY_URL'],$this->config['TLG_PROXY_LOGIN'].':'.$this->config['TLG_PROXY_PASSWORD'], $type_proxy);
        }
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
    
    function info($content) {
        if($this->config['TLG_DEBUG'] != 2)
            $this->log($content);
    }
    
    function debug($content) {
        if($this->config['TLG_DEBUG'] == 1)
            $this->log($content);
    }
    
    function warning($content) {
        $this->log($content);
    }
    
    function log($message) {
        //echo $message . "\n";
        // DEBUG MESSAGE LOG
        if (is_array($message))
            $message = json_encode($message, JSON_UNESCAPED_UNICODE);
        DebMes($message,"telegram");
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
        if (!gg('cycle_telegramRun')) {
            $out['CYCLERUN'] = 0;
        }
        else 
        {
            if ((time() - gg('cycle_telegramRun')) < $this->config['TLG_TIMEOUT']*2 )
                $out['CYCLERUN'] = 1;
            else
                $out['CYCLERUN'] = 0;
        }
        
        global $getlog;
        global $filter;
        global $limit;
        global $atype;
        if($getlog) {
            header("HTTP/1.0: 200 OK\n");
            header('Content-Type: text/html; charset=utf-8');
            //$limit = 50;
            if (defined('SETTINGS_SYSTEM_DEBMES_PATH') && SETTINGS_SYSTEM_DEBMES_PATH!='') {
                $path = SETTINGS_SYSTEM_DEBMES_PATH;
            } elseif (defined('LOG_DIRECTORY') && LOG_DIRECTORY!='') {
                $path = LOG_DIRECTORY;
            } else {
                $path = ROOT . 'cms/debmes';
            }
            $filename=$path.'/'.date('Y-m-d').'_telegram.log';
            if (!file_exists($filename))
            {
                echo "Empty log...";
                exit;
            }
            // Open file
            $data = LoadFile($filename);
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
            global $silent;
            if (!isset($silent)) {
                $silent = NULL;
            }
            $res = $this->sendMessageToUser($user, $text, null, '', $silent);
            echo "Ok";
            exit;
        }
        $out['TLG_TOKEN'] = $this->config['TLG_TOKEN'];
        $out['TLG_STORAGE'] = $this->config['TLG_STORAGE'];
        $out['TLG_COUNT_ROW'] = $this->config['TLG_COUNT_ROW'];
        $out['TLG_PLAYER'] = $this->config['TLG_PLAYER'];
        $out['TLG_TIMEOUT'] = $this->config['TLG_TIMEOUT'];
        $out['BOT_ID'] = $this->config['TLG_BOT_ID'];
        $out['BOT_NAME'] = $this->config['TLG_BOT_NAME'];
        $out['BOT_USERNAME'] = $this->config['TLG_BOT_USERNAME'];
        $out['BOT_JOIN_GROUP'] = $this->config['TLG_BOT_JOIN_GROUP'];
        $out['BOT_READ_GROUP'] = $this->config['TLG_BOT_READ_GROUP'];
        $out['BOT_SUPPORT_INLINE'] = $this->config['TLG_BOT_SUPPORT_INLINE'];
        if(!$out['TLG_COUNT_ROW'])
            $out['TLG_COUNT_ROW'] = 3;
        if(!$out['TLG_TIMEOUT'])
            $out['TLG_TIMEOUT'] = 30;
        if($out['TLG_TIMEOUT']>600)
            $out['TLG_TIMEOUT'] = 30;
        if(!$out['TLG_PROXY_TYPE'])
            $out['TLG_PROXY_TYPE'] = 2;
        $out['TLG_DEBUG'] = $this->config['TLG_DEBUG'];
        $out['TLG_REG_USER'] = $this->config['TLG_REG_USER'];
        $out['TLG_test'] = $this->data_source . "_" . $this->view_mode . "_" . $this->tab;
        // get webhook info
        $out['TLG_WEBHOOK'] = $this->config['TLG_WEBHOOK'];
        $out['TLG_WEBHOOK_URL'] = $this->config['TLG_WEBHOOK_URL'];
        $out['TLG_WEBHOOK_CERT'] = $this->config['TLG_WEBHOOK_CERT'];
        
        $out['TLG_USEPROXY'] = $this->config['TLG_USEPROXY'];
        $out['TLG_PROXY_TYPE'] = $this->config['TLG_PROXY_TYPE'];
        $out['TLG_PROXY_URL'] = $this->config['TLG_PROXY_URL'];
        $out['TLG_PROXY_LOGIN'] = $this->config['TLG_PROXY_LOGIN'];
        $out['TLG_PROXY_PASSWORD'] = $this->config['TLG_PROXY_PASSWORD'];
        
        if($this->data_source == 'telegram' || $this->data_source == '') {
            if($this->view_mode == 'update_settings') {
                global $tlg_token;
                $this->config['TLG_TOKEN'] = $tlg_token;
                global $tlg_storage;
                $this->config['TLG_STORAGE'] = $tlg_storage;
                global $tlg_count_row;
                $this->config['TLG_COUNT_ROW'] = $tlg_count_row;
                global $tlg_player;
                $this->config['TLG_PLAYER'] = $tlg_player;
                global $tlg_timeout;
                $this->config['TLG_TIMEOUT'] = $tlg_timeout;
                if($this->config['TLG_TIMEOUT']>600)
                    $this->config['TLG_TIMEOUT'] = 30;
                global $tlg_debug;
                $this->config['TLG_DEBUG'] = $tlg_debug;
                global $tlg_reg_user;
                $this->config['TLG_REG_USER'] = $tlg_reg_user;
                global $tlg_webhook;
                $this->config['TLG_WEBHOOK'] = $tlg_webhook;
                global $tlg_webhook_url;
                $this->config['TLG_WEBHOOK_URL'] = $tlg_webhook_url;
                global $tlg_webhook_cert;
                $this->config['TLG_WEBHOOK_CERT'] = $tlg_webhook_cert;
                global $tlg_useproxy;
                $this->config['TLG_USEPROXY'] = $tlg_useproxy;
                global $tlg_proxy_type;
                $this->config['TLG_PROXY_TYPE'] = $tlg_proxy_type;
                global $tlg_proxy_url;
                $this->config['TLG_PROXY_URL'] = $tlg_proxy_url;
                global $tlg_proxy_login;
                $this->config['TLG_PROXY_LOGIN'] = $tlg_proxy_login;
                global $tlg_proxy_password;
                $this->config['TLG_PROXY_PASSWORD'] = $tlg_proxy_password;
                $this->saveConfig();
                $this->info("Save config");
                if (!$this->config['TLG_WEBHOOK'])
                {
                    setGlobal('cycle_telegramControl','restart');
                    $this->info("Init cycle restart");
                }
                $this->redirect("?tab=".$this->tab);
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
			$this->info("Update user info");
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
        return preg_replace("/^\xEF\xBB\xBF/", '', $data);
    }
    
    function export_file($filename,$data)
    {
        $ie = false;
        $ua = htmlentities($_SERVER['HTTP_USER_AGENT'], ENT_QUOTES, 'UTF-8');
        if (preg_match('~MSIE|Internet Explorer~i', $ua) || (strpos($ua, 'Trident/7.0') !== false && strpos($ua, 'rv:11.0') !== false))
            $ie = true;
        if(!$ie)
            $mime_type = 'application/octetstream';
        else 
            $mime_type = 'application/octet-stream';
        header('Content-Type: ' . $mime_type);
        if(!$ie)
        {
            header('Content-Disposition: inline; filename="' . $filename . '"');
            header("Content-Transfer-Encoding: binary");
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            print $data;
        } else {
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header("Content-Transfer-Encoding: binary");
            header('Expires: 0');
            header('Pragma: no-cache');
            print $data;
        }
        exit;
    }
    
    function export_rec($table,$data,$overwrite)
    {   
        $data=json_decode($this->removeBOM($data),true);
        if (is_array($data)) 
        {
            $rec=SQLSelectOne("SELECT * FROM ".$table." WHERE TITLE='". DBSafe($data["TITLE"]) . "'");
            if ($rec['ID'])
            {
                if ($overwrite)
                {
                    $data{'ID'} = $rec['ID'];
                    SQLUpdate($table, $data); // update
                }
                else
                {
                    $data["TITLE"] .= "_copy";
                    SQLInsert($table, $data); // adding new record
                }
            }
            else
                SQLInsert($table, $data); // adding new record
        }
    }
    
    function export_command(&$out, $id) {
        $command=SQLSelectOne("SELECT * FROM tlg_cmd WHERE ID='".(int)$id."'");
        unset($command['ID']);
        $data=json_encode($command);
        $filename="Command_Telegram_".urlencode($command['TITLE']).".txt";
        $this->export_file($filename,$data);
    }
    function import_command(&$out) {
        global $file;
        global $overwrite;
        $data=LoadFile($file);
        $this->export_rec("tlg_cmd",$data,$overwrite);
        $this->redirect("?tab=cmd");
    }
    function export_event(&$out, $id) {
        $event=SQLSelectOne("SELECT * FROM tlg_event WHERE ID='".(int)$id."'");
        unset($event['ID']);
        $data=json_encode($event);
        $filename="Event_Telegram_".urlencode($event['TITLE']).".txt";
        $this->export_file($filename,$data);
    }
    function import_event(&$out) {
        global $file;
        global $overwrite;
        $data=LoadFile($file);
        $this->export_rec("tlg_event",$data,$overwrite);
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
            $sql = "SELECT * FROM tlg_cmd where ACCESS=3 or ((select count(*) from tlg_user_cmd where tlg_cmd.ID=tlg_user_cmd.CMD_ID and tlg_user_cmd.USER_ID=" . $user['ID'] . ")>0 and ACCESS>0) order by tlg_cmd.PRIORITY desc, tlg_cmd.TITLE;";
            //$this->log($sql);
            $rec = SQLSelect($sql);
            $total = count($rec);
            if($total) {
                for($i = 0; $i < $total; $i++) {
                    $view = false;
                    if($rec[$i]["SHOW_MODE"] == 1)
                        $view = true;
                    elseif($rec[$i]["SHOW_MODE"] == 3) {
                        if ($rec[$i]["LINKED_OBJECT"] && $rec[$i]["LINKED_PROPERTY"])
                        {
                            $val = gg($rec[$i]["LINKED_OBJECT"].".".$rec[$i]["LINKED_PROPERTY"]);
                            if($val!='')
                            {
                                if($rec[$i]["CONDITION"] == 1 && $val == $rec[$i]["CONDITION_VALUE"])
                                    $view = true;
                                if($rec[$i]["CONDITION"] == 2 && $val > $rec[$i]["CONDITION_VALUE"])
                                    $view = true;
                                if($rec[$i]["CONDITION"] == 3 && $val < $rec[$i]["CONDITION_VALUE"])
                                    $view = true;
                                if($rec[$i]["CONDITION"] == 4 && $val <> $rec[$i]["CONDITION_VALUE"])
                                    $view = true;
                            }
                        }
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
    function buildInlineKeyboardButton($text, $url = "", $callback_data = "", $switch_inline_query = NULL ,$switch_inline_query_current_chat = NULL) {
        return $this->telegramBot->buildInlineKeyboardButton($text, $url, $callback_data, $switch_inline_query, $switch_inline_query_current_chat);
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
    
    function sendPoll($user_id, $question, $options, $is_anonymous = false, $type='regular',$allows_multiple_answers = false, $correct_option_id = 0,$explanation = '',$open_period = 0,$close_date = 0)
    {
        $content = array(
            'chat_id' => $user_id,
            'question' => $question,
            'options' => json_encode($options,true),
            'is_anonymous' => $is_anonymous,
            'type' => $type,
            'allows_multiple_answers' => $allows_multiple_answers,
            'correct_option_id' => $correct_option_id,
        );
        if ($explanation != '')
        {
            $content["explanation"] = $explanation;
            $content["explanation_parse_mode"] = 'HTML';
        }
        if ($open_period != 0)
            $content["open_period"] = $open_period;
        if ($close_date != 0)
            $content["close_date"] = $close_date;
        return $this->sendContent($content,"sendPoll");
    }
    
    function sendAnswerCallbackQuery($callback_id, $text, $show_alert = false ) {
        $content = array('text' => $text, 'callback_query_id'=>$callback_id, 'show_alert'=>$show_alert);
        return $this->sendContent($content,"answerCallbackQuery");
    }
    
    function getUsers($where) {
        $query = "SELECT * FROM tlg_user";
        if($where != "")
            $query = $query . " WHERE " . $where;
        $users = SQLSelect($query);
        return $users;
    }
    
    function buildKeyboard($user, $key) {
        if($key == NULL)
            $keyboard = $this->getKeyb($user);
        else
            $keyboard = $this->telegramBot->buildKeyBoard($key, false, true);
        $this->debug($keyboard);
        return $keyboard;
    }
    
    function getUserName($chat_id) {
        $query = "SELECT * FROM tlg_user WHERE USER_ID=" . $chat_id;
        $user = SQLSelectOne($query);
        if($user)
            return $user['NAME'];
        return "Unknow";
    }
    
    function editMessage($user_id, $message_id, $message, $keyboard = '', $parse_mode = 'HTML') {
        $content = array(
            'chat_id' => $user_id,
            'message_id' => $message_id,
            'text' => $message,
            'reply_markup' => $keyboard,
            'parse_mode' => $parse_mode
        );
        $this->debug($content);
        $res = $this->telegramBot->editMessageText($content);
        $this->debug($res);
        return $res;
    }
    
    function editMessageCaption($user_id, $message_id, $caption, $keyboard = '', $parse_mode = 'HTML') {
        $content = array(
            'chat_id' => $user_id,
            'message_id' => $message_id,
            'caption' => $caption,
            'reply_markup' => $keyboard,
            'parse_mode' => $parse_mode
        );
        $this->debug($content);
        $res = $this->telegramBot->editMessageCaption($content);
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
    function sendMessage($user_id, $message, $keyboard = '', $parse_mode = 'HTML', $inline = '', $silent = false, $flags=array()) {
        $splited = str_split($message, 4096);
        foreach ($splited as $mess) {
            $content = array(
                'chat_id' => $user_id,
                'text' => $mess,
                'parse_mode' => $parse_mode,
                'disable_notification' => $silent
            );
	    if (count($flags)) foreach ($flags as $flagname => $flagvalue) $content[$flagname] = $flagvalue;
            if ($keyboard != "")
                 $content['reply_markup'] = $keyboard;
            if ($inline != "")
                 $content['reply_markup'] = $inline;
            $res = $this->telegramBot->sendMessage($content);
            $this->debug($res);
        }
        return $res;
    }
    function sendMessageTo($where, $message, array $key = NULL, $inline = '', $silent = NULL, $flags=array()) {
        $users = $this->getUsers($where);
        foreach($users as $user) {
            $user_id = $user['USER_ID'];
            if ($user_id === '0') $user_id = $user['NAME'];
            $keyboard = $this->buildKeyboard($user, $key);
            if ($silent == NULL)
                $silent = $user['SILENT'];
            $res = $this->sendMessage($user_id,$message, $keyboard, 'HTML', $inline, $silent, $flags);
            $this->debug($res);
        }
        return $res;
    }
    function sendMessageToUser($user_id, $message, $key = NULL, $inline = '', $silent = NULL, $flags=array()) {
        return $this->sendMessageTo('(USER_ID="' . DBSafe($user_id) . '" OR NAME LIKE "' . DBSafe($user_id) .  '")', $message, $key, $inline, $silent, $flags);
    }
    function sendMessageToAdmin($message, $key = NULL, $inline = '', $silent = NULL, $flags=array()) {
        return $this->sendMessageTo("ADMIN=1", $message, $key, $inline, $silent, $flags);
    }
    function sendMessageToAll($message, $key = NULL, $inline = '', $silent = NULL, $flags=array()) {
        return $this->sendMessageTo("", $message, $key, $inline, $silent, $flags);
    }
    ///send image
    function sendImage($user_id, $image_path, $message = '', $keyboard = '', $inline = '', $silent = false, $flags=array()) {
        $img = curl_file_create($image_path, 'image/png');
        $content = array(
            'chat_id' => $user_id,
            'photo' => $img,
            'caption' => $message,
            'reply_markup' => $keyboard,
            'disable_notification' => $silent
        );
        if ($inline != "")
            $content['reply_markup'] = $inline;
        foreach ($flags as $key => $value) $content[$key] = $value;
        $res = $this->telegramBot->sendPhoto($content);
        $this->debug($res);
        return $res;
    }
    function sendImageTo($where, $image_path, $message = '', array $key = NULL, $inline = '', $silent = NULL, $flags=array()) {
        $img = curl_file_create($image_path, 'image/png');
        $users = $this->getUsers($where);
        foreach($users as $user) {
            $user_id = $user['USER_ID'];
            if ($user_id === '0') $user_id = $user['NAME'];
            $keyboard = $this->buildKeyboard($user, $key);
            if ($silent == NULL)
                $silent = $user['SILENT'];
            $content = array(
                'chat_id' => $user_id,
                'photo' => $img,
                'caption' => $message,
                'reply_markup' => $keyboard,
                'disable_notification' => $silent
            );
            if ($inline != "")
                $content['reply_markup'] = $inline;
            foreach ($flags as $key => $value) $content[$key] = $value;
            $res = $this->telegramBot->sendPhoto($content);
            $this->debug($res);
        }
        return $res;
    }
    function sendImageToUser($user_id, $image_path, $message = '', $key = NULL, $inline = '', $silent = NULL, $flags=array()) {
        $this->sendImageTo('(USER_ID="' . DBSafe($user_id) . '" OR NAME LIKE "' . DBSafe($user_id) .  '")', $image_path, $message, $key, $inline, $silent, $flags);
    }
    function sendImageToAdmin($image_path, $message = '', $key = NULL, $inline = '', $silent = NULL, $flags=array()) {
        $this->sendImageTo("ADMIN=1", $image_path, $message, $key, $inline, $silent, $flags);
    }
    function sendImageToAll($image_path, $message = '', $key = NULL, $inline = '', $silent = NULL, $flags=array()) {
        $this->sendImageTo("", $image_path, $message, $key, $inline, $silent, $flags);
    }
    ///send video
    function sendVideo($user_id, $video_path, $message = '', $keyboard = '', $inline = '') {
        $video = curl_file_create($video_path);
        $content = array(
            'chat_id' => $user_id,
            'video' => $video,
            'caption' => $message,
            'reply_markup' => $keyboard
        );
        if ($inline != "")
                $content['reply_markup'] = $inline;
        $res = $this->telegramBot->sendVideo($content);
        $this->debug($res);
        return $res;
    }
    function sendVideoTo($where, $video_path, $message = '', array $key = NULL, $inline = '') {
        $video = curl_file_create($video_path);
        $users = $this->getUsers($where);
        foreach($users as $user) {
            $user_id = $user['USER_ID'];
            if ($user_id === '0') $user_id = $user['NAME'];
            $keyboard = $this->buildKeyboard($user, $key);
            $content = array(
                'chat_id' => $user_id,
                'video' => $video,
                'caption' => $message,
                'reply_markup' => $keyboard
            );
            if ($inline != "")
                $content['reply_markup'] = $inline;
            $res = $this->telegramBot->sendVideo($content);
            $this->debug($res);
        }
        return $res;
    }
    function sendVideoToUser($user_id, $video_path, $message = '', $key = NULL, $inline = '') {
        $this->sendVideoTo('(USER_ID="' . DBSafe($user_id) . '" OR NAME LIKE "' . DBSafe($user_id) .  '")', $video_path, $message, $key, $inline);
    }
    function sendVideoToAdmin($video_path, $message = '', $key = NULL, $inline = '') {
        $this->sendVideoTo("ADMIN=1", $video_path, $message, $key, $inline);
    }
    function sendVideoToAll($video_path, $message = '', $key = NULL, $inline = '') {
        $this->sendVideoTo("", $video_path, $message, $key, $inline);
    }
    ///send album
    function sendAlbum($user_id, $image_paths, $message = '', $keyboard = '') {
        if (count($image_paths) == 1)
        {
            $this->sendImage($user_id, $image_paths[0], $message, $keyboard);
            return;
        }
        $photos = array();
        $content = array(
            'chat_id' => $user_id
        );
        foreach($image_paths as $image) {
            $img = curl_file_create($image, 'image/png');
            $photo = array();
            $photo['caption'] = $message;
            $photo['type'] = 'photo';
            $photo['parse_mode'] = 'HTML';
            $photo['media'] = 'attach://'.basename($image);//$img;
            $photos[]=$photo;
            $content[basename($image)]=$img;
        }
        $content['media'] = json_encode($photos,true);
        $res = $this->telegramBot->sendMediaGroup($content);
        $this->debug($res);
        return $res;
    }
    function sendAlbumTo($where, $image_paths, $message = '', array $key = NULL) {
        $photos = array();
        $content = array();
        foreach($image_paths as $image) {
            $img = curl_file_create($image, 'image/png');
            $photo = array();
            $photo['caption'] = $message;
            $photo['type'] = 'photo';
            $photo['parse_mode'] = 'HTML';
            $photo['media'] = 'attach://'.basename($image);
            $photos[]=$photo;
            $content[basename($image)]=$img;
        }
        $content['media'] = json_encode($photos,true);
        $users = $this->getUsers($where);
        foreach($users as $user) {
            $user_id = $user['USER_ID'];
            $content['chat_id'] = $user_id;
            $res = $this->telegramBot->sendMediaGroup($content);
            $this->debug($res);
        }
        return $res;
    }
    function sendAlbumToUser($user_id, $image_paths, $message = '', $key = NULL) {
        $this->sendAlbumTo('(USER_ID="' . DBSafe($user_id) . '" OR NAME LIKE "' . DBSafe($user_id) .  '")', $image_paths, $message, $key);
    }
    function sendAlbumToAdmin($image_paths, $message = '', $key = NULL) {
        $this->sendAlbumTo("ADMIN=1", $image_paths, $message, $key);
    }
    function sendAlbumToAll($image_paths, $message = '', $key = NULL) {
        $this->sendAlbumTo("", $image_paths, $message, $key);
    }
    //
    function sendFile($user_id, $file_path, $message = '', $keyboard = '', $inline='') {
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
    function sendFileTo($where, $file_path, $message = '', array $key = NULL, $inline='') {
        $file = curl_file_create($file_path);
        $users = $this->getUsers($where);
        foreach($users as $user) {
            $user_id = $user['USER_ID'];
            if ($user_id === '0') $user_id = $user['NAME'];
            $keyboard = $this->buildKeyboard($user, $key);
            $content = array(
                'chat_id' => $user_id,
                'document' => $file,
                'caption' => $message,
                'reply_markup' => $keyboard
            );
            if ($inline != "")
                $content['reply_markup'] = $inline;
            $res = $this->telegramBot->sendDocument($content);
            $this->debug($res);
        }
        return $res;
    }
    function sendFileToUser($user_id, $file_path, $message = '', $key = NULL, $inline='') {
        $this->sendFileTo('(USER_ID="' . DBSafe($user_id) . '" OR NAME LIKE "' . DBSafe($user_id) .  '")', $file_path, $message, $key, $inline);
    }
    function sendFileToAdmin($file_path, $message = '', $key = NULL, $inline='') {
        $this->sendFileTo("ADMIN=1", $file_path, $message, $key, $inline);
    }
    function sendFileToAll($file_path, $message = '', $key = NULL, $inline='') {
        $this->sendFileTo("", $file_path, $message, $key, $inline);
    }
    function sendSticker($user_id, $sticker, $keyboard = '', $inline='') {
        $content = array(
            'chat_id' => $user_id,
            'sticker' => $sticker,
            'reply_markup' => $keyboard
        );
        if ($inline != "")
            $content['reply_markup'] = $inline;
        $res = $this->telegramBot->sendSticker($content);
        $this->debug($res);
        return $res;
    }
    function sendStickerTo($where, $sticker, array $key = NULL, $inline='') {
        $users = $this->getUsers($where);
        foreach($users as $user) {
            $user_id = $user['USER_ID'];
            if ($user_id === '0') $user_id = $user['NAME'];
            $keyboard = $this->buildKeyboard($user, $key);
            $content = array(
                'chat_id' => $user_id,
                'sticker' => $sticker,
                'reply_markup' => $keyboard
            );
            if ($inline != "")
                $content['reply_markup'] = $inline;
            $res = $this->telegramBot->sendSticker($content);
            $this->debug($res);
        }
        return $res;
    }
    function sendStickerToUser($user_id, $sticker, $key = NULL, $inline='') {
        $this->sendStickerTo('(USER_ID="' . DBSafe($user_id) . '" OR NAME LIKE "' . DBSafe($user_id) .  '")', $sticker, $key, $inline);
    }
    function sendStickerToAdmin($sticker, $key = NULL, $inline='') {
        $this->sendStickerTo("ADMIN=1", $sticker, $key, $inline);
    }
    function sendStickerToAll($sticker, $key = NULL, $inline='') {
        $this->sendStickerTo("", $sticker, $key, $inline);
    }
    function sendLocation($user_id, $lat, $lon, $keyboard = '', $inline='') {
        $content = array(
            'chat_id' => $user_id,
            'latitude' => $lat,
            'longitude' => $lon,
            'reply_markup' => $keyboard
        );
        if ($inline != "")
            $content['reply_markup'] = $inline;
        $res = $this->telegramBot->sendLocation($content);
        $this->debug($res);
        return $res;
    }
    function sendLocationTo($where, $lat, $lon, array $key = NULL, $inline='') {
        $users = $this->getUsers($where);
        foreach($users as $user) {
            $user_id = $user['USER_ID'];
            if ($user_id === '0') $user_id = $user['NAME'];
            $keyboard = $this->buildKeyboard($user, $key);
            $content = array(
                'chat_id' => $user_id,
                'latitude' => $lat,
                'longitude' => $lon,
                'reply_markup' => $keyboard
            );
            if ($inline != "")
                $content['reply_markup'] = $inline;
            $res = $this->telegramBot->sendLocation($content);
            $this->debug($res);
        }
        return $res;
    }
    function sendLocationToUser($user_id, $lat, $lon, $key = NULL, $inline='') {
        return $this->sendLocationTo('(USER_ID="' . DBSafe($user_id) . '" OR NAME LIKE "' . DBSafe($user_id) .  '")', $lat, $lon, $key, $inline);
    }
    function sendLocationToAdmin($lat, $lon, $key = NULL, $inline='') {
        return $this->sendLocationTo("ADMIN=1", $lat, $lon, $key, $inline);
    }
    function sendLocationToAll($lat, $lon, $key = NULL, $inline='') {
        return $this->sendLocationTo("", $lat, $lon, $key, $inline);
    }
    function sendVenue($user_id, $lat, $lon, $title, $address, $keyboard = '', $inline='') {
        $content = array(
            'chat_id' => $user_id,
            'latitude' => $lat,
            'longitude' => $lon,
            'title' => $title,
            'address' => $address,
            'reply_markup' => $keyboard
        );
        if ($inline != "")
            $content['reply_markup'] = $inline;
        $res = $this->telegramBot->sendVenue($content);
        $this->debug($res);
        return $res;
    }
    function sendVenueTo($where, $lat, $lon, $title, $address, array $key = NULL, $inline='') {
        $users = $this->getUsers($where);
        foreach($users as $user) {
            $user_id = $user['USER_ID'];
            if ($user_id === '0') $user_id = $user['NAME'];
            $keyboard = $this->buildKeyboard($user, $key);
            $content = array(
                'chat_id' => $user_id,
                'latitude' => $lat,
                'longitude' => $lon,
                'title' => $title,
                'address' => $address,
                'reply_markup' => $keyboard
            );
            if ($inline != "")
                $content['reply_markup'] = $inline;
            $res = $this->telegramBot->sendVenue($content);
            $this->debug($res);
        }
        return $res;
    }
    function sendVenueToUser($user_id, $lat, $lon, $title, $address, $key = NULL, $inline='') {
        return $this->sendVenueTo('(USER_ID="' . DBSafe($user_id) . '" OR NAME LIKE "' . DBSafe($user_id) .  '")', $lat, $lon, $title, $address, $key, $inline);
    }
    function sendVenueToAdmin($lat, $lon, $title, $address, $key = NULL, $inline='') {
        return $this->sendVenueTo("ADMIN=1", $lat, $lon, $title, $address, $key, $inline);
    }
    function sendVenueToAll($lat, $lon, $title, $address, $key = NULL, $inline='') {
        return $this->sendVenueTo("", $lat, $lon, $title, $address, $key, $inline);
    }
    
    function sendVoice($user_id, $file_path, $caption='', $keyboard = '', $inline='') {
        $file = curl_file_create($file_path);
		$content = array(
			'chat_id' => $user_id,
			'voice' => $file,
			'caption' => $caption,
			'reply_markup' => $keyboard
		);
        if ($inline != "")
            $content['reply_markup'] = $inline;
		$res = $this->telegramBot->sendVoice($content);
		$this->debug($res);
		return $res;
    }
    function sendVoiceTo($where, $file_path, $caption='', array $key = NULL, $inline='') {
        $file = curl_file_create($file_path);
		$users = $this->getUsers($where);
        foreach($users as $user) {
            $user_id = $user['USER_ID'];
            if ($user_id === '0') $user_id = $user['NAME'];
			$keyboard = $this->buildKeyboard($user, $key);
            $content = array(
				'chat_id' => $user_id,
				'voice' => $file,
				'caption' => $caption,
				'reply_markup' => $keyboard
			);
            if ($inline != "")
                $content['reply_markup'] = $inline;
			$res = $this->telegramBot->sendVoice($content);
			$this->debug($res);
		}
        return $res;
    }
	function sendVoiceToUser($user_id, $file_path, $caption='', $key = NULL, $inline='') {
        return $this->sendVoiceTo('(USER_ID="' . DBSafe($user_id) . '" OR NAME LIKE "' . DBSafe($user_id) .  '")', $file_path, $caption, $key, $inline);
    }
    function sendVoiceToAdmin($file_path, $caption='', $key = NULL, $inline='') {
        return $this->sendVoiceTo("ADMIN=1", $file_path, $caption, $key, $inline);
    }
    function sendVoiceToAll($file_path, $caption='', $key = NULL, $inline='') {
        return $this->sendVoiceTo("", $file_path, $caption, $key, $inline);
    }
    
    function sendDice($user_id, $emoji=null) {
        $content = array(
            'chat_id' => $user_id
        );
        if ($emoji!=null)
            $content["emoji"]=$emoji;
        $res = $this->sendContent($content, "sendDice");
        return $res;
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
    
    function getMe() {
        return $this->telegramBot->getMe();
    }
    
    function init() {
        $this->warning("Token bot - " . $this->config['TLG_TOKEN']);
        // create bot
        $me = $this->getMe();
        $this->debug($me);
        if($me)
        {
            $this->warning("Me: @" . $me["result"]["username"] . " (" . $me["result"]["id"] . ")");
            $this->config['TLG_BOT_USERNAME'] = $me["result"]["username"];
            $this->config['TLG_BOT_NAME'] = $me["result"]["first_name"];
            $this->config['TLG_BOT_ID'] = $me["result"]["id"];
            $this->config['TLG_BOT_JOIN_GROUP'] = $me["result"]["can_join_groups"];
            $this->config['TLG_BOT_READ_GROUP'] = $me["result"]["can_read_all_group_messages"];
            $this->config['TLG_BOT_SUPPORT_INLINE'] = $me["result"]["supports_inline_queries"];

            $content = array('chat_id' => $me["result"]["id"]);
            $chat = $this->telegramBot->getChat($content);
            $this->debug($chat);
            if($chat["result"]["photo"]) {
                $image = $chat["result"]["photo"]["big_file_id"];
                $this->debug($image);
                $file = $this->telegramBot->getFile($image);
                $this->debug($file);
                $file_path = ROOT . "cms/cached" . DIRECTORY_SEPARATOR . "telegram" . DIRECTORY_SEPARATOR . $me["result"]["id"] . ".jpg";
                $path_parts = pathinfo($file_path);
                if(!is_dir($path_parts['dirname']))
                    mkdir($path_parts['dirname'], 0777, true);
                $res = $this->telegramBot->downloadFile($file["result"]["file_path"], $file_path);
                $this->debug($res);
            }
            $this->saveConfig();
        }
        else {
            $this->warning("Error connect or invalid token");
            return;
        }
        $this->info("Update user info");
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
            $file_path = ROOT . "cms/cached" . DIRECTORY_SEPARATOR . "telegram" . DIRECTORY_SEPARATOR . $user['USER_ID'] . ".jpg";
            $path_parts = pathinfo($file_path);
            if(!is_dir($path_parts['dirname']))
                mkdir($path_parts['dirname'], 0777, true);
            $res = $this->telegramBot->downloadFile($file["result"]["file_path"], $file_path);
            $this->debug($res);
        }
    }
    
    function processCycle() {
        $this->getConfig();
        if ($this->config['TLG_WEBHOOK'])
            return;
        // Get all the new updates and set the new correct update_id
        if ($this->config['TLG_USEPROXY'])
        {
            $type_proxy = CURLPROXY_SOCKS5;
            if ($this->config['TLG_PROXY_TYPE']==1)
                $type_proxy = CURLPROXY_HTTP;
            if ($this->config['TLG_PROXY_TYPE']==3)
                $type_proxy = CURLPROXY_SOCKS5_HOSTNAME;
            if ($this->config['TLG_PROXY_TYPE']==4)
                $type_proxy = CURLPROXY_HTTPS;
            $this->telegramBot->setProxy($this->config['TLG_PROXY_URL'],$this->config['TLG_PROXY_LOGIN'].':'.$this->config['TLG_PROXY_PASSWORD'], $type_proxy);
        }
        $req = $this->telegramBot->getUpdates($this->last_update_id, 10, $this->config["TLG_TIMEOUT"], false);
        if(isset($req['error_code']))
        {
            if($this->config['TLG_DEBUG'])
                $this->debug($req);
            else
                $this->warning($req['description']);
            return;
        }
        for($i = 0; $i < $this->telegramBot->UpdateCount(); $i++) {
            // You NEED to call serveUpdate before accessing the values of message in Telegram Class
            $this->telegramBot->serveUpdate($i);
            //$this->processMessage();
            $data = $this->telegramBot->getData();
            $this->last_update_id = $data['update_id']+1;
            $url = BASE_URL . '/webhook_telegram.php';
            $data_string = json_encode($data);
            $ch=curl_init($url);
            curl_setopt_array($ch, array(
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $data_string,
                CURLOPT_HEADER => true,
                CURLOPT_HTTPHEADER => array('Content-Type:application/json', 'Content-Length: ' . strlen($data_string)))
            );
            curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, 100);

            $result = curl_exec($ch);
            curl_close($ch);
        }
    }
    function processMessage() {
        $skip = false;
        $data = $this->telegramBot->getData();
        echo $data;
        $this->debug($data);
        $bot_name = $this->config['TLG_BOTNAME'];
        
        $poll_answer = $data['poll_answer'];
        if($poll_answer) {
            $this->info("Pool answer - ID_poll:".$poll_answer['poll_id']."; User: ".$poll_answer['user']['username'].'('.$poll_answer['user']['id'].')');
            
            $chat_id = $poll_answer['user']['id'];
            $username = $poll_answer['user']["username"];
            $fullname = $poll_answer['user']["first_name"].' '.$poll_answer['user']["last_name"];
            $poll_id = $poll_answer['poll_id'];
            $option_ids = $poll_answer['option_ids'];
            
            // get events for callback
            $events = SQLSelect("SELECT * FROM tlg_event WHERE TYPE_EVENT=10 and ENABLE=1;");
            foreach($events as $event) {
                if($event['CODE']) {
                    $this->info("Execute code event " . $event['TITLE']);
                    try {
                        eval($event['CODE']);
                    }
                    catch(Exception $e) {
                        registerError('telegram', sprintf('Exception in "%s" method ' . $e->getMessage(), $text));
                    }
                }
                if($skip) {
                    $this->info("Skip next processing events pool");
                    break;
                }
            }
            return;
        }
        
        $text = $this->telegramBot->Text();
        $callback = $this->telegramBot->Callback_Data();
        if($callback) {
            $chat_id = $data["callback_query"]["message"]["chat"]["id"];
            $username = $data["callback_query"]["message"]["chat"]["username"];
            $fullname = $data["callback_query"]["message"]["chat"]["first_name"].' '.$data["callback_query"]["message"]["chat"]["last_name"];
            $callback_chat_id = $data["callback_query"]["from"]["id"];
            $callback_username = $data["callback_query"]["from"]["username"];
            $callback_fullname = $data["callback_query"]["from"]["first_name"].' '.$data["callback_query"]["from"]["last_name"];
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
        
        if($this->config['TLG_REG_USER'] && ($text == "/start" || $text == "/start@" . $bot_name)) {
            // если нет добавляем
            if(!$user['ID']) {
                $user['USER_ID'] = $chat_id;
                $user['CREATED'] = date('Y/m/d H:i:s');
                $user['ID'] = SQLInsert('tlg_user', $user);
                $this->warning("Added new user: " . $username . " - " . $chat_id);
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
            $this->warning("Unknow user: ".$chat_id."; Message: ".$text);
            return;
        }
        
        if ($user['ADMIN'] != 1 && 
            $user['HISTORY'] != 1 && 
            $user['CMD'] != 1 && 
            $user['PATTERNS'] != 1 && 
            $user['DOWNLOAD'] != 1 && 
            $user['PLAY'] != 1)
        {
            $this->warning("WARNING!!! Permission denied!! User: ".$chat_id."; Message: ".$text);
            $reply = "Обратитесь к администратору для получения доступа к функциям!";
            $content = array(
                'chat_id' => $chat_id,
                'text' => $reply
            );
            $this->sendContent($content);
            return;
        }
        
        
        $document = $data["message"]["document"];
        $audio = $data["message"]["audio"];
        $video = $data["message"]["video"];
        $voice = $data["message"]["voice"];
        $sticker = $data["message"]["sticker"];
        $photo_id = $this->PhotoIdBigSize($data);
        $location = $this->telegramBot->Location();
        if($callback && $user['CMD'] == 1) {
            $cbm = $this->telegramBot->Callback_Message();
            $message_id = $cbm["message_id"];
            $callback_id = $this->telegramBot->Callback_ID();
            // get events for callback
            $events = SQLSelect("SELECT * FROM tlg_event WHERE TYPE_EVENT=9 and ENABLE=1;");
            foreach($events as $event) {
                if($event['CODE']) {
                    $this->info("Execute code event " . $event['TITLE']);
                    try {
                        eval($event['CODE']);
                    }
                    catch(Exception $e) {
                        registerError('telegram', sprintf('Exception in "%s" method ' . $e->getMessage(), $text));
                    }
                }
                if($skip) {
                    $this->info("Skip next processing events callback");
                    break;
                }
            }
            return;
        }
            
        if($location) {
                $latitude = $location["latitude"];
                $longitude = $location["longitude"];
                $this->info("Get location from " . $chat_id . " - " . $latitude . "," . $longitude);
                if($user['MEMBER_ID']) {
                    $sqlQuery = "SELECT * FROM users WHERE ID = '" . $user['MEMBER_ID'] . "'";
                    $userObj = SQLSelectOne($sqlQuery);
                    if($userObj['LINKED_OBJECT']) {
                        $this->info("Update location to user '" . $userObj['LINKED_OBJECT']."'");
                        setGlobal($userObj['LINKED_OBJECT'] . '.Coordinates', $latitude . ',' . $longitude);
                        setGlobal($userObj['LINKED_OBJECT'] . '.CoordinatesUpdated', date('H:i'));
                        setGlobal($userObj['LINKED_OBJECT'] . '.CoordinatesUpdatedTimestamp', time());
                    }
                }
                // get events for location
                $events = SQLSelect("SELECT * FROM tlg_event WHERE TYPE_EVENT=8 and ENABLE=1;");
                foreach($events as $event) {
                    if($event['CODE']) {
                        $this->info("Execute code event " . $event['TITLE']);
                        try {
                            eval($event['CODE']);
                        }
                        catch(Exception $e) {
                            registerError('telegram', sprintf('Exception in "%s" method ' . $e->getMessage(), $text));
                        }
                    }
                    if($skip) {
                        $this->info("Skip next processing events location");
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
                    $this->info("Get photo from " . $chat_id . " - " . $file["result"]["file_path"]);
                    $file_path = $storage . $chat_id . DIRECTORY_SEPARATOR . $file["result"]["file_path"];
                    $type = 2;
                }
                if($document) {
                    $file = $this->telegramBot->getFile($document["file_id"]);
                    $this->debug($file);
                    $this->info("Get document from " . $chat_id . " - " . $document["file_name"]);
                    if(!isset($file['error_code'])) {
                        $file_path = $storage . $chat_id . DIRECTORY_SEPARATOR . "document" . DIRECTORY_SEPARATOR . $document["file_name"];
                        if(file_exists($file_path))
                            $file_path = $storage . $chat_id . DIRECTORY_SEPARATOR . "document" . DIRECTORY_SEPARATOR . $this->telegramBot->UpdateID() . "_" . $document["file_name"];
                    } else {
                        $file_path = "";
                        $this->info($file['description']);
                    }
                    $type = 6;
                }
                if($audio) {
                    $file = $this->telegramBot->getFile($audio["file_id"]);
                    $this->debug($file);
                    $this->info("Get audio from " . $chat_id . " - " . $file["result"]["file_path"]);
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
                    $this->info("Get voice from " . $chat_id . " - " . $file["result"]["file_path"]);
                    $file_path = $storage . $chat_id . DIRECTORY_SEPARATOR . $file["result"]["file_path"];
                    $type = 3;
                }
                if($video) {
                    $file = $this->telegramBot->getFile($video["file_id"]);
                    $this->debug($file);
                    $this->info("Get video from " . $chat_id . " - " . $file["result"]["file_path"]);
                    $file_path = $storage . $chat_id . DIRECTORY_SEPARATOR . $file["result"]["file_path"];
                    $type = 5;
                }
                if($sticker) {
                    $file = $this->telegramBot->getFile($sticker["file_id"]);
                    $this->debug($file);
                    $sticker_set = $sticker["set_name"];
                    $this->info("Get sticker from " . $chat_id . " === Id:" . $sticker["file_id"] ." Set:".$sticker_set);
                    $file_path = $storage.'stickers'.DIRECTORY_SEPARATOR.$file["result"]["file_path"];
                    $sticker_id = $sticker["file_id"];
                    $type = 7;
                }
                if($file_path) {
                    // качаем файл
                    $path_parts = pathinfo($file_path);
                    if(!is_dir($path_parts['dirname']))
                        mkdir($path_parts['dirname'], 0777, true);
                    $res = $this->telegramBot->downloadFile($file["result"]["file_path"], $file_path);
                    $this->debug($res);
                }
                if($voice && $user['PLAY'] == 1) {
                    //проиграть голосовое сообщение
                    $this->info("Play voice from " . $chat_id . " - " . $file_path);
                    @touch($file_path);
                    if ($this->config['TLG_PLAYER'] == 2)
                        playMedia($file_path, 'localhost', true);
                    else
                        playSound($file_path, 1, $level);
                }
                if($file_path || $sticker_id) {
                    // get events
                    $events = SQLSelect("SELECT * FROM tlg_event WHERE TYPE_EVENT=" . $type . " and ENABLE=1;");
                    foreach($events as $event) {
                        if($event['CODE']) {
                            $this->info("Execute code event " . $event['TITLE']);
                            try {
                                eval($event['CODE']);
                            }
                            catch(Exception $e) {
                                registerError('telegram', sprintf('Exception in "%s" method ' . $e->getMessage(), $text));
                            }
                        }
                        if($skip) {
                            $this->info("Skip next processing events type = ".$type);
                            break;
                        }
                    }
                }
                $file_path = "";
        }
        
        $this->info($chat_id . " (" . $username . ", " . $fullname . ")=" . $text);

        if($user['CMD'] == 1) {
        // get events for text message
            $events = SQLSelect("SELECT * FROM tlg_event WHERE TYPE_EVENT=1 and ENABLE=1;");
            foreach($events as $event) {
                if($event['CODE']) {
                    $this->info("Execute code event " . $event['TITLE']);
                    try {
                        eval($event['CODE']);
                    }
                    catch(Exception $e) {
                        registerError('telegram', sprintf('Exception in "%s" method ' . $e->getMessage(), $text));
                    }
                }
                if($skip) {
                    $this->info("Skip next processing events message");
                    break;
                }
            }
            // пропуск дальнейшей обработки если с обработчике событий установили $skip
            if($skip) {
                $this->info("Skip next processing message");
                return;
            }
        }
            
        if($text == "") {
            return;
        }
        
        if($user['ID']) {
            //смотрим разрешения на обработку команд
            if($user['CMD'] == 1) {
                    // поиск полного соответствия команды
                    $sql = "SELECT * FROM tlg_cmd where tlg_cmd.TITLE='" . DBSafe($text) . "' and (ACCESS=3  OR ((select count(*) from tlg_user_cmd where tlg_user_cmd.USER_ID=" . $user['ID'] . " and tlg_cmd.ID=tlg_user_cmd.CMD_ID)>0 and ACCESS>0))";
                    $cmd = SQLSelectOne($sql);
                    if (count($cmd) == 0)
                    {
                        // команда - все что до пробела
                        $command = explode(' ',$text)[0]; 
                        // поиск команд с параметрами
                        $sql = "SELECT * FROM tlg_cmd where LOWER(tlg_cmd.TITLE) = '" . DBSafe(mb_strtolower($command, "UTF-8")) . "' and (ACCESS=3  OR ((select count(*) from tlg_user_cmd where tlg_user_cmd.USER_ID=" . $user['ID'] . " and tlg_cmd.ID=tlg_user_cmd.CMD_ID)>0 and ACCESS>0))";
                        //$this->debug($sql);
                        $cmd = SQLSelectOne($sql);
                    }
                    if($cmd['ID']) {
                        $this->info("Find command - ".$cmd['TITLE']);
                        //нашли команду
                        if($cmd['CODE']) {
                            $this->info("Execute user`s code command");
                            try {
                                $success = eval($cmd['CODE']);
                                $this->info("Command:" . $text . " Result:" . $success);
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
                                    $this->info("Send result to " . $chat_id . ". Command:" . $text . " Result:" . $success);
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
                        $this->info("Command not found");
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
			$this->info("execCommand => Find command");
            if($cmd['CODE']) {
                $this->info("execCommand => Execute user`s code command");
                try {
					$text = $command;
                    $success = eval($cmd['CODE']);
                    $this->info("Command:" . $text . " Result:" . $success);
                    if($success == false) {
                        //нет в выполняемом куске кода return
                    } else {
                        $content = array(
                        'chat_id' => $chat_id,
                        'text' => $success,
                        'parse_mode' => 'HTML'
                        );
                        $this->sendContent($content);
                        $this->info("Send result to " . $chat_id . ". Command:" . $text . " Result:" . $success);
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
        $this->debug("event=" . $event. " details=".json_encode($details));
        if($event == 'SAY') { // || $event=='SAYTO' || $event=='REPLYTO'
            $level = $details['level'];
            $message = $details['message'];
            if($details['destination']) {
                $destination = $details['destination'];
            } elseif($details['source']) {
                $destination = $details['source'];
            }
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
                        $this->info(" Send to " . $user_id . " - " . $reply);
                        $silent = $users[$j]['SILENT'];
                        if ($level >= $users[$j]['HISTORY_SILENT'])
                            $silent = false;
                        else
                            $silent = true;
                        $url=BASE_URL."/ajax/telegram.html?sendMessage=1&user=".$user_id."&text=".urlencode($reply)."&silent=".$silent;
                        getURLBackground($url,0);
                    }
                }
                $this->debug("Sended - " . $reply);
            } else {
                $this->info("No users to send data");
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
 tlg_user: SILENT int(3) unsigned NOT NULL DEFAULT '0' 
 tlg_user: HISTORY int(3) unsigned NOT NULL DEFAULT '0' 
 tlg_user: HISTORY_LEVEL int(3) unsigned NOT NULL DEFAULT '0' 
 tlg_user: HISTORY_SILENT int(3) unsigned NOT NULL DEFAULT '0' 
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
