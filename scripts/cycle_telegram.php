<?php

chdir(dirname(__FILE__) . '/../');

include_once("./config.php");
include_once("./lib/loader.php");
include_once("./lib/threads.php");

set_time_limit(0);

// connecting to database
$db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME);
 
include_once("./load_settings.php");
include_once(DIR_MODULES . "control_modules/control_modules.class.php");
include_once(DIR_MODULES . 'telegram/telegram.class.php');

set_time_limit(0);

$tlg = new telegram();
$tlg->getConfig();
$bot_id = $tlg->config['TLG_TOKEN'];
echo "Token bot - ".$bot_id."\n";

$rec = SQLSelectOne("SELECT * FROM `shouts` ORDER BY `ID` DESC LIMIT 1"); 
$lastID = $rec['ID'];  
echo "Shouts LastID=".$lastID."\n";

// create bot
//include("Telegram.php");
require("./modules/telegram/Telegram.php");
$telegramBot = new TelegramBot($bot_id);
$me=$telegramBot->getMe();
if ($me)
    echo "Me: @".$me["result"]["username"]." (".$me["result"]["id"].")\n"; 
else
    echo "Error connect, invalid token\n";

echo date("H:i:s") . " running " . basename(__FILE__) . PHP_EOL;
include_once(DIR_MODULES.'patterns/patterns.class.php');
$pt=new patterns();

//== Cycle ===
$previousMillis = 0;
while (true){
//echo "Process\n";
//echo  date("Y-m-d H:i:s u")." Proc start\n";

// отправка истории
$rec=SQLSelect("SELECT * FROM `shouts` where ID > ".$lastID." order by ID;");  
$total=count($rec);
if ($total) {
    // найти кому отправить
    $users=SQLSelect("SELECT * FROM tlg_user WHERE HISTORY=1;"); 
    $c_users=count($users);
    if ($c_users) {
        for($i=0;$i<$total;$i++) {
            $reply = $rec[$i]['MESSAGE'];
            //отправлять всем у кого есть разрешения на получение истории
            for($j=0;$j<$c_users;$j++) {
                $user_id = $users[$j]['USER_ID'];
                //самому себе не отправлять
                if ($users[$j]['MEMBER_ID'] != $rec[$i]['MEMBER_ID'])
                {
                    echo  date("Y-m-d H:i:s ")." Send to ".$user_id." - ".$reply."\n";
                    $keyb = $tlg->getKeyb($users[$j]['ADMIN'],$users[$j]['CMD']);
                    $content = array('chat_id' => $user_id, 'text' => $reply, 'reply_markup' => $keyb);
                    $telegramBot->sendMessage($content);
                }
            }
            echo  date("Y-m-d H:i:s ")." Sended - ".$reply."\n";
            $lastID = $rec[$i]['ID'];
        }
    }
    else
        $lastID = $rec[$total-1]['ID'];
}  

// Get all the new updates and set the new correct update_id
$req = $telegramBot->getUpdates($timeout=5);
for ($i = 0; $i < $telegramBot-> UpdateCount(); $i++) {
    // You NEED to call serveUpdate before accessing the values of message in Telegram Class
    $telegramBot->serveUpdate($i);
    //$data = $telegramBot->getData();
    //print_r($data);
    $text = $telegramBot->Text();
    $chat_id = $telegramBot->ChatID();
    $document = $telegramBot->Document();
    $audio = $telegramBot->Audio();
    $voice = $telegramBot->Voice();
    $photo_id = $telegramBot->PhotoIdBigSize();
    // найти в базе пользователя
    $user=SQLSelectOne("SELECT * FROM tlg_user WHERE USER_ID LIKE '".DBSafe($chat_id)."';"); 
    //permission download file
    if ($user['DOWNLOAD']==1)
    {
        //папку с файлами в настройках
        $storage = $tlg->config['TLG_STORAGE']."/";
        if ($photo_id) 
        {
            $file = $telegramBot->getFile($photo_id);
            echo  date("Y-m-d H:i:s ")." Get photo from ".$chat_id." - ".$file["result"]["file_path"]."\n";
            $file_path = $storage.$chat_id."/".$file["result"]["file_path"];
        }
        if ($document) 
        {
            $file = $telegramBot->getFile($document["file_id"]);
            echo  date("Y-m-d H:i:s ")." Get document from ".$chat_id." - ".$document["file_name"]."\n";
            //print_r($file);
            if(!isset($file['error_code'])) 
            {
                $file_path = $storage.$chat_id."/document/".$document["file_name"];
            }
            else
            {
                $file_path = "";
                echo  date("Y-m-d H:i:s ").$file['description']."\n";
            }
        }
        if ($audio) 
        {
            $file = $telegramBot->getFile($audio["file_id"]);
            //print_r($file);
            echo  date("Y-m-d H:i:s ")." Get audio from ".$chat_id." - ".$file["result"]["file_path"]."\n";
            $path_parts = pathinfo($file["result"]["file_path"]);
            $filename = $path_parts["basename"];
            //use title and performer
            if(isset($audio['title'])) $filename = $audio['title'].".".$path_parts['extension'];
            if(isset($audio['performer'])) $filename = $audio['performer']."-".$filename;
            $file_path = $storage.$chat_id."/audio/".$filename;
        }
        if ($voice) 
        {
            $file = $telegramBot->getFile($voice["file_id"]);
            //print_r($file);
            echo  date("Y-m-d H:i:s ")." Get voice from ".$chat_id." - ".$file["result"]["file_path"]."\n";
            $file_path = $storage.$chat_id."/".$file["result"]["file_path"];
        }
        if ($file_path){ 
            // качаем файл
            $path_parts = pathinfo($file_path);
            if (!is_dir($path_parts['dirname'])) mkdir($path_parts['dirname'], 0777, true);
            $telegramBot->downloadFile($file["result"]["file_path"], $file_path);
        }
        if ($voice && $user['PLAY']==1) 
        {
            //проиграть голосовое сообщение
            echo  date("Y-m-d H:i:s ")." Play voice from ".$chat_id." - ".$file_path."\n";
            @touch($file_path);
            playSound($file_path, 1, $level);
        }
        $file_path = "";
    }    
    if ($text=="") {
        continue;
    }
    echo  date("Y-m-d H:i:s ").$chat_id."=".$text."\n";

    if ($text == "/start") {
        // найти в базе пользователя
        // если нет добавляем
        $user=SQLSelectOne("SELECT * FROM tlg_user WHERE USER_ID LIKE '".DBSafe($chat_id)."';"); 
        if (!$user['ID']) {
            $user['USER_ID']=$chat_id;
            $name = $telegramBot->Username();
            $user['NAME']=$name;
            $user['CREATED'] = date('Y/m/d H:i:s');
            $user['ID']=SQLInsert('tlg_user', $user);
            echo  date("Y-m-d H:i:s ")." Added - ".$name."-".$chat_id."\n";
        } 
        
        $reply = "Вы зарегистрированы! Обратитесь к администратору для получения доступа к функциям.";
        $content = array('chat_id' => $chat_id, 'text' => $reply);
        $telegramBot->sendMessage($content);
        continue;
    }
    
    if ($user['ID']) {
        //смотрим разрешения на обработку команд
        if ($user['ADMIN']==1 || $user['CMD']==1)
        {
            $keyb = $tlg->getKeyb($user['ADMIN'],$user['CMD']);
            $cmd=SQLSelectOne("SELECT * FROM tlg_cmd WHERE TITLE LIKE '".DBSafe($text)."';"); 
            if ($cmd['ID']) {
                //нашли команду
                if ($cmd['CODE'])
                {
                    try {
                        $success = eval($cmd['CODE']);
                        echo  date("Y-m-d H:i:s ")." Result ".$text."-".$success."\n";
                        if ($success == false) {
                            $content = array('chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => "Ошибка выполнения кода команды ".$text);
                            $telegramBot->sendMessage($content);
                        }
                        else
                        {
                            $content = array('chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => $success);
                            $telegramBot->sendMessage($content);
                        }
                        
                    } catch (Exception $e) {
                        registerError('telegram', sprintf('Exception in "%s" method '.$e->getMessage(), $text));
                        $content = array('chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => "Ошибка выполнения кода команды ".$text);
                        $telegramBot->sendMessage($content);
                    }
                    continue;
                }
                // если нет кода, который надо выполнить, то передаем дальше на обработку
            }
            if ($text == "/test") {
                if ($telegramBot->messageFromGroup()) {
                    $reply = "Chat Group";
                } else {
                    $reply = "Private Chat";
                }
                    
                $content = array('chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => $reply);
                $telegramBot->sendMessage($content);
            } 
            else if ($text == "/git") {
                $reply = "Check me on GitHub: https://github.com/Eleirbag89/TelegramBotPHP";
                // Build the reply array
                $content = array('chat_id' => $chat_id, 'text' => $reply);
                $telegramBot->sendMessage($content);
            }
            else
            {
                $rec=array();
                $rec['ROOM_ID']=0;
                $rec['MEMBER_ID']=$user['MEMBER_ID'];
                $rec['MESSAGE']=htmlspecialchars($text);
                $rec['ADDED']=date('Y-m-d H:i:s');
                SQLInsert('shouts', $rec);

                $res=$pt->checkAllPatterns($rec['MEMBER_ID']);
                if (!$res) {
                    processCommand($text);
                } 
            }

        }
    }
}
sleep(1);
	
//echo  date("Y-m-d H:i:s u")." End\n";
}

/**
 * Process message
*/
    

$db->Disconnect(); // closing database connection 
 
DebMes("Unexpected close of cycle: " . basename(__FILE__));
 
?>