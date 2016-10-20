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
class telegram extends module {
    /**
     * blank
     *
     * Module class constructor
     *
     * @access private
     */
    function telegram() {
        $this->name = "telegram";
        $this->title = "Telegram";
        $this->module_category = "<#LANG_SECTION_APPLICATIONS#>";
        $this->checkInstalled();
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
        $this->getConfig();
        global $webhookinfo;
        if ($webhookinfo)
        {
            header("HTTP/1.0: 200 OK\n");
            header('Content-Type: text/html; charset=utf-8');
            require_once("./modules/telegram/Telegram.php");
            $telegramBot = new TelegramBot($this->config['TLG_TOKEN']);
            $webhookInfo = $telegramBot->getWebhookInfo();
            $this->debug($webhookInfo);
            echo print_r($webhookInfo,true);
            exit;
        }
        global $setwebhook;
        if ($setwebhook)
        {
            header("HTTP/1.0: 200 OK\n");
            header('Content-Type: text/html; charset=utf-8');
            require_once("./modules/telegram/Telegram.php");
            $telegramBot = new TelegramBot($this->config['TLG_TOKEN']);
            $webhookRes = $telegramBot->setWebhook($this->config['TLG_WEBHOOK_URL']."/webhook_telegram.php");
            $this->debug($webhookRes);
            echo print_r($webhookRes,true);
            exit;
        }
        global $cleanwebhook;
        if ($cleanwebhook)
        {
            header("HTTP/1.0: 200 OK\n");
            header('Content-Type: text/html; charset=utf-8');
            require_once("./modules/telegram/Telegram.php");
            $telegramBot = new TelegramBot($this->config['TLG_TOKEN']);
            $webhookRes = $telegramBot->setWebhook("");
            $this->debug($webhookRes);
            echo print_r($webhookRes,true);
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
                $this->saveConfig();
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
        global $webhook_set;
        if ($webhook_set) {
            //require_once("./modules/telegram/Telegram.php");
            $telegramBot = new TelegramBot($this->config['TLG_TOKEN']);
            global $tlg_webhook_url;
            $webhookRes = $telegramBot->setWebhook($this->config['TLG_WEBHOOK_URL']."/webhook_telegram.php");
            $this->debug($webhookRes);
            $this->redirect("?");
        }
        global $webhook_clean;
        if ($webhook_clean) {
            //require_once("./modules/telegram/Telegram.php");
            $telegramBot = new TelegramBot($this->config['TLG_TOKEN']);
            $webhookRes = $telegramBot->setWebhook("");
            $this->debug($webhookRes);
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
        if($user['ADMIN'] == 0 && $user['CMD'] == 0) {
            $option = array();
            $visible = false;
        } else {
            //$option = array( array("A", "B"), array("C", "D") );
            $option = array();
            $rec = SQLSelect("SELECT *,(select VALUE from pvalues where Property_name=`LINKED_OBJECT`+'.'+`LINKED_PROPERTY` ORDER BY updated DESC limit 1) as pvalue" . " FROM tlg_cmd INNER JOIN tlg_user_cmd on tlg_cmd.ID=tlg_user_cmd.CMD_ID where tlg_user_cmd.USER_ID=" . $user['ID'] . " and ACCESS>0 order by tlg_cmd.PRIORITY desc, tlg_cmd.TITLE;");
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
        $telegramBot = new TelegramBot("");
        $keyb = $telegramBot->buildKeyBoard($option, $resize = true, $selective = $visible);
        //print_r($keyb);
        return $keyb;
    }
    function buildInlineKeyboardButton($text, $url = "", $callback_data = "", $switch_inline_query = "") {
        $telegramBot = new TelegramBot("");
        return $telegramBot->buildInlineKeyboardButton($text, $url, $callback_data, $switch_inline_query);
    }
    function buildInlineKeyBoard(array $option) {
        $telegramBot = new TelegramBot("");
        return $telegramBot->buildInlineKeyBoard($option);
    }
    function sendContent($content) {
        $this->getConfig();
        include_once("./modules/telegram/Telegram.php");
        $telegramBot = new TelegramBot($this->config['TLG_TOKEN']);
        $this->debug($content);
        $res = $telegramBot->sendMessage($content);
        $this->debug($res);
    }
    function getUsers($where) {
        $query = "SELECT * FROM tlg_user";
        if($where != "")
            $query = $query . " WHERE " . $where;
        $users = SQLSelect($query);
        return $users;
    }
    function editMessage($user_id, $message_id, $message, $keyboard = '') {
        $this->getConfig();
        include_once("./modules/telegram/Telegram.php");
        $telegramBot = new TelegramBot($this->config['TLG_TOKEN']);
        $content = array(
            'chat_id' => $user_id,
            'message_id' => $message_id,
            'text' => $message,
            'reply_markup' => $keyboard
        );
        $res = $telegramBot->editMessageText($content);
        $this->debug($res);
    }
    // Chat Action
    //typing for text messages
    //upload_photo for photos
    //record_video or upload_video for videos
    //record_audio or upload_audio for audio files
    //upload_document for general files
    //find_location for location data
    function sendAction($user_id, $action = 'typing') {
        $this->getConfig();
        include_once("./modules/telegram/Telegram.php");
        $telegramBot = new TelegramBot($this->config['TLG_TOKEN']);
        $content = array(
            'chat_id' => $user_id,
            'action' => $action
        );
        $res = $telegramBot->sendChatAction($content);
        $this->debug($res);
    }
    
    // send message
    function sendMessage($user_id, $message, $keyboard = '', $parse_mode = 'HTML') {
        $this->getConfig();
        include_once("./modules/telegram/Telegram.php");
        $telegramBot = new TelegramBot($this->config['TLG_TOKEN']);
        $content = array(
            'chat_id' => $user_id,
            'text' => $message,
            'reply_markup' => $keyboard,
            'parse_mode' => $parse_mode
        );
        $res = $telegramBot->sendMessage($content);
        $this->debug($res);
    }
    function sendMessageTo($where, $message, array $key = NULL) {
        $this->getConfig();
        include_once("./modules/telegram/Telegram.php");
        $telegramBot = new TelegramBot($this->config['TLG_TOKEN']);
        $users = $this->getUsers($where);
        foreach($users as $user) {
            $user_id = $user['USER_ID'];
            if($key == NULL)
                $keyboard = $this->getKeyb($user);
            else
                $keyboard = $telegramBot->buildKeyBoard($key, $resize = true);
            $content = array(
                'chat_id' => $user_id,
                'text' => $message,
                'reply_markup' => $keyboard,
                'parse_mode' => 'HTML'
            );
            $res = $telegramBot->sendMessage($content);
            $this->debug($res);
        }
    }
    function sendMessageToUser($user_id, $message, $key = NULL) {
        $this->sendMessageTo("USER_ID=" . $user_id, $message, $key);
    }
    function sendMessageToAdmin($message, $key = NULL) {
        $this->sendMessageTo("ADMIN=1", $message, $key);
    }
    function sendMessageToAll($message, $key = NULL) {
        $this->sendMessageTo("", $message, $key);
    }
    ///send image
    function sendImage($user_id, $image_path, $message = '', $keyboard = '') {
        $this->getConfig();
        include_once("./modules/telegram/Telegram.php");
        $telegramBot = new TelegramBot($this->config['TLG_TOKEN']);
        $img = curl_file_create($image_path, 'image/png');
        $content = array(
            'chat_id' => $user_id,
            'photo' => $img,
            'caption' => $message,
            'reply_markup' => $keyboard
        );
        $res = $telegramBot->sendPhoto($content);
        $this->debug($res);
    }
    function sendImageTo($where, $image_path, $message = '', array $key = NULL) {
        $this->getConfig();
        include_once("./modules/telegram/Telegram.php");
        $telegramBot = new TelegramBot($this->config['TLG_TOKEN']);
        $img = curl_file_create($image_path, 'image/png');
        $users = $this->getUsers($where);
        foreach($users as $user) {
            $user_id = $user['USER_ID'];
            if($key == NULL)
                $keyboard = $this->getKeyb($user);
            else
                $keyboard = $telegramBot->buildKeyBoard($key, $resize = true);
            $content = array(
                'chat_id' => $user_id,
                'photo' => $img,
                'caption' => $message,
                'reply_markup' => $keyboard
            );
            $res = $telegramBot->sendPhoto($content);
            $this->debug($res);
        }
    }
    function sendImageToUser($user_id, $image_path, $message = '', $key = NULL) {
        $this->sendImageTo("USER_ID=" . $user_id, $image_path, $message, $key);
    }
    function sendImageToAdmin($image_path, $message = '', $key = NULL) {
        $this->sendImageTo("ADMIN=1", $image_path, $message, $key);
    }
    function sendImageToAll($image_path, $message = '', $key = NULL) {
        $this->sendImageTo("", $image_path, $message, $key);
    }
    function sendFile($user_id, $file_path, $keyboard = '') {
        $this->getConfig();
        include_once("./modules/telegram/Telegram.php");
        $telegramBot = new TelegramBot($this->config['TLG_TOKEN']);
        $file = curl_file_create($file_path);
        $content = array(
            'chat_id' => $user_id,
            'document' => $file,
            'reply_markup' => $keyboard
        );
        $res = $telegramBot->sendDocument($content);
        $this->debug($res);
    }
    function sendFileTo($where, $file_path, array $key = NULL) {
        $this->getConfig();
        include_once("./modules/telegram/Telegram.php");
        $telegramBot = new TelegramBot($this->config['TLG_TOKEN']);
        $file = curl_file_create($file_path);
        $users = $this->getUsers($where);
        foreach($users as $user) {
            $user_id = $user['USER_ID'];
            if($key == NULL)
                $keyboard = $this->getKeyb($user);
            else
                $keyboard = $telegramBot->buildKeyBoard($key, $resize = true);
            $content = array(
                'chat_id' => $user_id,
                'document' => $file,
                'reply_markup' => $keyboard
            );
            $res = $telegramBot->sendDocument($content);
            $this->debug($res);
        }
    }
    function sendFileToUser($user_id, $file_path, $key = NULL) {
        $this->sendFileTo("USER_ID=" . $user_id, $file_path, $key);
    }
    function sendFileToAdmin($file_path, $key = NULL) {
        $this->sendFileTo("ADMIN=1", $file_path, $key);
    }
    function sendFileToAll($file_path, $key = NULL) {
        $this->sendFileTo("", $file_path, $key);
    }
    function sendSticker($user_id, $sticker, $keyboard = '') {
        $this->getConfig();
        include_once("./modules/telegram/Telegram.php");
        $telegramBot = new TelegramBot($this->config['TLG_TOKEN']);
        $content = array(
            'chat_id' => $user_id,
            'sticker' => $sticker,
            'reply_markup' => $keyboard
        );
        $res = $telegramBot->sendSticker($content);
        $this->debug($res);
    }
    function sendStickerTo($where, $sticker, array $key = NULL) {
        $this->getConfig();
        include_once("./modules/telegram/Telegram.php");
        $telegramBot = new TelegramBot($this->config['TLG_TOKEN']);
        $users = $this->getUsers($where);
        foreach($users as $user) {
            $user_id = $user['USER_ID'];
            if($key == NULL)
                $keyboard = $this->getKeyb($user);
            else
                $keyboard = $telegramBot->buildKeyBoard($key, $resize = true);
            $content = array(
                'chat_id' => $user_id,
                'sticker' => $sticker,
                'reply_markup' => $keyboard
            );
            $res = $telegramBot->sendSticker($content);
            $this->debug($res);
        }
    }
    function sendStickerToUser($user_id, $sticker, $key = NULL) {
        $this->sendStickerTo("USER_ID=" . $user_id, $sticker, $key);
    }
    function sendStickerToAdmin($sticker, $key = NULL) {
        $this->sendStickerTo("ADMIN=1", $sticker, $key);
    }
    function sendStickerToAll($sticker, $key = NULL) {
        $this->sendStickerTo("", $sticker, $key);
    }
    function sendLocation($user_id, $lat, $lon, $keyboard = '') {
        $this->getConfig();
        include_once("./modules/telegram/Telegram.php");
        $telegramBot = new TelegramBot($this->config['TLG_TOKEN']);
        $content = array(
            'chat_id' => $user_id,
            'latitude' => $lat,
            'longitude' => $lon,
            'reply_markup' => $keyboard
        );
        $res = $telegramBot->sendLocation($content);
        $this->debug($res);
    }
    function sendLocationTo($where, $lat, $lon, array $key = NULL) {
        $this->getConfig();
        include_once("./modules/telegram/Telegram.php");
        $telegramBot = new TelegramBot($this->config['TLG_TOKEN']);
        $users = $this->getUsers($where);
        foreach($users as $user) {
            $user_id = $user['USER_ID'];
            if($key == NULL)
                $keyboard = $this->getKeyb($user);
            else
                $keyboard = $telegramBot->buildKeyBoard($key, $resize = true);
            $content = array(
                'chat_id' => $user_id,
                'latitude' => $lat,
                'longitude' => $lon,
                'reply_markup' => $keyboard
            );
            $res = $telegramBot->sendLocation($content);
            $this->debug($res);
        }
    }
    function sendLocationToUser($user_id, $lat, $lon, $key = NULL) {
        $this->sendLocationTo("USER_ID=" . $user_id, $lat, $lon, $key);
    }
    function sendLocationToAdmin($lat, $lon, $key = NULL) {
        $this->sendLocationTo("ADMIN=1", $lat, $lon, $key);
    }
    function sendLocationToAll($lat, $lon, $key = NULL) {
        $this->sendLocationTo("", $lat, $lon, $key);
    }
    function sendVenue($user_id, $lat, $lon, $title, $address, $keyboard = '') {
        $this->getConfig();
        include_once("./modules/telegram/Telegram.php");
        $telegramBot = new TelegramBot($this->config['TLG_TOKEN']);
        $content = array(
            'chat_id' => $user_id,
            'latitude' => $lat,
            'longitude' => $lon,
            'title' => $title,
            'address' => $address,
            'reply_markup' => $keyboard
        );
        $res = $telegramBot->sendVenue($content);
        $this->debug($res);
    }
    function sendVenueTo($where, $lat, $lon, $title, $address, array $key = NULL) {
        $this->getConfig();
        include_once("./modules/telegram/Telegram.php");
        $telegramBot = new TelegramBot($this->config['TLG_TOKEN']);
        $users = $this->getUsers($where);
        foreach($users as $user) {
            $user_id = $user['USER_ID'];
            if($key == NULL)
                $keyboard = $this->getKeyb($user);
            else
                $keyboard = $telegramBot->buildKeyBoard($key, $resize = true);
            $content = array(
                'chat_id' => $user_id,
                'latitude' => $lat,
                'longitude' => $lon,
                'title' => $title,
                'address' => $address,
                'reply_markup' => $keyboard
            );
            $res = $telegramBot->sendVenue($content);
            $this->debug($res);
        }
    }
    function sendVenueToUser($user_id, $lat, $lon, $title, $address, $key = NULL) {
        $this->sendVenueTo("USER_ID=" . $user_id, $lat, $lon, $title, $address, $key);
    }
    function sendVenueToAdmin($lat, $lon, $title, $address, $key = NULL) {
        $this->sendVenueTo("ADMIN=1", $lat, $lon, $title, $address, $key);
    }
    function sendVenueToAll($lat, $lon, $title, $address, $key = NULL) {
        $this->sendVenueTo("", $lat, $lon, $title, $address, $key);
    }
    function init() {
        $this->getConfig();
        $this->lastID = 0;
        $this->log("Token bot - " . $this->config['TLG_TOKEN']);
        $rec = SQLSelectOne("SELECT * FROM `shouts` ORDER BY `ID` DESC LIMIT 1");
        if($rec)
            $this->lastID = $rec['ID'];
        $this->log("Shouts LastID=" . $this->lastID);
        // create bot
        require("./modules/telegram/Telegram.php");
        $telegramBot = new TelegramBot($this->config['TLG_TOKEN']);
        $me = $telegramBot->getMe();
        if($me)
        {
            $this->log("Me: @" . $me["result"]["username"] . " (" . $me["result"]["id"] . ")");
            $this->config['TLG_BOTNAME'] = $me["result"]["username"];
            $this->saveConfig();
        }
        else {
            $this->log("Error connect, invalid token");
            return;
        }
        $this->log("Update user info");
        $users = $this->getUsers("");
        foreach($users as $user) {
           $this->updateInfo($telegramBot, $user);
        }
    }
    function updateInfo($telegramBot, $user) {
        $chat = $telegramBot->getChat($user['USER_ID']);
        $this->debug($chat);
        if($user['NAME'] == "") {
            // set name
            if($chat["result"]["type"] == "private")
                $user["NAME"] = $chat["result"]["first_name"] . " " . $chat["result"]["last_name"];
            else
                $user["NAME"] = $chat["result"]["title"];
            SQLUpdate("tlg_user", $user);
        }
        if($chat["result"]["type"] == "private") {
            $content = array(
                'user_id' => $user['USER_ID']
            );
            $image = $telegramBot->getUserProfilePhotos($content);
            //$this->debug($image);
            $file = $telegramBot->getFile($image["result"]["photos"][0][0]["file_id"]);
            $this->debug($file);
            $file_path = ROOT . "cached" . DIRECTORY_SEPARATOR . "telegram" . DIRECTORY_SEPARATOR . $user['USER_ID'] . ".jpg";
            // качаем файл
            $path_parts = pathinfo($file_path);
            if(!is_dir($path_parts['dirname']))
                mkdir($path_parts['dirname'], 0777, true);
            $telegramBot->downloadFile($file["result"]["file_path"], $file_path);
        }
    }
    
    function processCycle() {
        $this->getConfig();
        if ($this->config['TLG_WEBHOOK'])
            return;
        $telegramBot = new TelegramBot($this->config['TLG_TOKEN']);
        
        // Get all the new updates and set the new correct update_id
        $req = $telegramBot->getUpdates($timeout = 5);
        for($i = 0; $i < $telegramBot->UpdateCount(); $i++) {
            // You NEED to call serveUpdate before accessing the values of message in Telegram Class
            $telegramBot->serveUpdate($i);
            $this->processMessage($telegramBot);
        }
    }
    function processMessage($telegramBot) {
        $skip = false;
        $bot_name = $this->config['TLG_BOTNAME'];
        
            $data = $telegramBot->getData();
            $this->debug($data);
            $callback = $telegramBot->Callback_Data();
            if($callback) {
                $chat_id = $telegramBot->Callback_ChatID();
                $cbm = $telegramBot->Callback_Message();
                $message_id = $cbm["message_id"];
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
                }
                return;
            }
            $text = $telegramBot->Text();
            $chat_id = $telegramBot->ChatID();
            $document = $telegramBot->Document();
            $audio = $telegramBot->Audio();
            $video = $telegramBot->Video();
            $voice = $telegramBot->Voice();
            $sticker = $telegramBot->Sticker();
            $photo_id = $telegramBot->PhotoIdBigSize();
            $username = $telegramBot->Username();
            $fullname = $telegramBot->FirstName() . ' ' . $telegramBot->LastName();
            $location = $telegramBot->Location();
            // найти в базе пользователя
            $user = SQLSelectOne("SELECT * FROM tlg_user WHERE USER_ID LIKE '" . DBSafe($chat_id) . "';");
            if($chat_id < 0 && substr($text, 0, strlen('@' . $bot_name)) === '@' . $bot_name) {
                //DebMes("Direct message to bot: ".$bot_name. " ($text)");
                $text = str_replace('@' . $bot_name, '', $text);
                $source_user = SQLSelectOne("SELECT * FROM tlg_user WHERE TRIM(NAME) LIKE '" . DBSafe(trim($username)) . "'");
                if($source_user['ID']) {
                    $user = $source_user;
                    //DebMes("New user check: ".serialize($user));
                } else {
                    //DebMes("Cannot find user: ".$username);
                }
            } else {
                //DebMes("Chatid: ".$chat_id."; Bot-name: ".$bot_name."; Message: ".$text);
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
                }
                return;
            }
            //permission download file
            if($user['DOWNLOAD'] == 1) {
                $type = 0;
                //папку с файлами в настройках
                $storage = $this->config['TLG_STORAGE'] . DIRECTORY_SEPARATOR;
                if($photo_id) {
                    $file = $telegramBot->getFile($photo_id);
                    $this->log("Get photo from " . $chat_id . " - " . $file["result"]["file_path"]);
                    $file_path = $storage . $chat_id . DIRECTORY_SEPARATOR . $file["result"]["file_path"];
                    $type = 2;
                }
                if($document) {
                    $file = $telegramBot->getFile($document["file_id"]);
                    $this->log("Get document from " . $chat_id . " - " . $document["file_name"]);
                    //print_r($file);
                    if(!isset($file['error_code'])) {
                        $file_path = $storage . $chat_id . DIRECTORY_SEPARATOR . "document" . DIRECTORY_SEPARATOR . $document["file_name"];
                        if(file_exists($file_path))
                            $file_path = $storage . $chat_id . DIRECTORY_SEPARATOR . "document" . DIRECTORY_SEPARATOR . $telegramBot->UpdateID() . "_" . $document["file_name"];
                    } else {
                        $file_path = "";
                        $this->log($file['description']);
                    }
                    $type = 6;
                }
                if($audio) {
                    $file = $telegramBot->getFile($audio["file_id"]);
                    //print_r($file);
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
                    $file = $telegramBot->getFile($voice["file_id"]);
                    //print_r($file);
                    $this->log("Get voice from " . $chat_id . " - " . $file["result"]["file_path"]);
                    $file_path = $storage . $chat_id . DIRECTORY_SEPARATOR . $file["result"]["file_path"];
                    $type = 3;
                }
                if($video) {
                    $file = $telegramBot->getFile($video["file_id"]);
                    //print_r($file);
                    $this->log("Get video from " . $chat_id . " - " . $file["result"]["file_path"]);
                    $file_path = $storage . $chat_id . DIRECTORY_SEPARATOR . $file["result"]["file_path"];
                    $type = 5;
                }
                if($sticker) {
                    $file = $telegramBot->getFile($sticker["file_id"]);
                    $this->log("Get sticker from " . $chat_id . " - " . $sticker["file_id"]);
                    //$file_path = $storage.$chat_id.DIRECTORY_SEPARATOR.$file["result"]["file_path"];
                    $sticker_id = $sticker["file_id"];
                    $type = 7;
                }
                if($file_path) {
                    // качаем файл
                    $path_parts = pathinfo($file_path);
                    if(!is_dir($path_parts['dirname']))
                        mkdir($path_parts['dirname'], 0777, true);
                    $telegramBot->downloadFile($file["result"]["file_path"], $file_path);
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
            }
            // пропуск дальнейшей обработки если с обработчике событий установили $skip
            if($skip) {
                $this->log("Skip next processing message");
                return;
            }
            if($text == "/start" || $text == "/start@" . $bot_name) {
                // найти в базе пользователя
                // если нет добавляем
                $user = SQLSelectOne("SELECT * FROM tlg_user WHERE USER_ID LIKE '" . DBSafe($chat_id) . "';");
                if(!$user['ID']) {
                    $user['USER_ID'] = $chat_id;
                    $user['CREATED'] = date('Y/m/d H:i:s');
                    $user['ID'] = SQLInsert('tlg_user', $user);
                    $this->log("Added - " . $name . "-" . $chat_id);
                }
                $reply = "Вы зарегистрированы! Обратитесь к администратору для получения доступа к функциям.";
                $content = array(
                    'chat_id' => $chat_id,
                    'text' => $reply
                );
                $this->sendContent($content);
                $this->updateInfo($telegramBot, $user);
                return;
            }
            if($user['ID']) {
                //смотрим разрешения на обработку команд
                if($user['ADMIN'] == 1 || $user['CMD'] == 1) {
                    $keyb = $this->getKeyb($user);
                    $cmd = SQLSelectOne("SELECT * FROM tlg_cmd INNER JOIN tlg_user_cmd on tlg_cmd.ID=tlg_user_cmd.CMD_ID where tlg_user_cmd.USER_ID=" . $user['ID'] . " and ACCESS>0 and '" . DBSafe($text) . "' LIKE CONCAT(TITLE,'%');");
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
                                    //$telegramBot->sendMessage($content);
                                } else {
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
                    if($text == "/test") {
                        if($telegramBot->messageFromGroup()) {
                            $reply = "Chat Group";
                        } else {
                            $reply = "Private Chat";
                        }
                        $content = array(
                            'chat_id' => $chat_id,
                            'reply_markup' => $keyb,
                            'text' => $reply
                        );
                        $this->sendContent($content);
                    } else {
                        say(htmlspecialchars($text), 0, $user['MEMBER_ID'], 'telegram' . $user['ID']);
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
            $this->getConfig();
            include_once("./modules/telegram/Telegram.php");
            $telegramBot = new TelegramBot($this->config['TLG_TOKEN']);
            $me = $telegramBot->getMe();
            $bot_name = $me["result"]["username"];
            $users = SQLSelect("SELECT * FROM tlg_user WHERE HISTORY=1;");
            $c_users = count($users);
            if($c_users) {
                $reply = $message;
                for($j = 0; $j < $c_users; $j++) {
                    $user_id = $users[$j]['USER_ID'];
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
        subscribeToEvent($this->name, 'SAY');
        subscribeToEvent($this->name, 'SAYTO');
        subscribeToEvent($this->name, 'SAYREPLY');
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
 tlg_user: USER_ID int(10) NOT NULL DEFAULT '0'
 tlg_user: MEMBER_ID int(10) NOT NULL DEFAULT '1'
 tlg_user: CREATED datetime
 tlg_user: ADMIN int(3) unsigned NOT NULL DEFAULT '0' 
 tlg_user: HISTORY int(3) unsigned NOT NULL DEFAULT '0' 
 tlg_user: HISTORY_LEVEL int(3) unsigned NOT NULL DEFAULT '0' 
 tlg_user: CMD int(3) unsigned NOT NULL DEFAULT '0' 
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
