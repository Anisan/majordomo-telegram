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
                    $content = array('chat_id' => $user_id, 'text' => $reply);
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
    $text = $telegramBot->Text();
    $chat_id = $telegramBot->ChatID();
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
    
    // найти в базе пользователя
    $user=SQLSelectOne("SELECT * FROM tlg_user WHERE USER_ID LIKE '".DBSafe($chat_id)."';"); 
    if ($user['ID']) {
        //смотрим разрешения на обработку команд
        if ($user['ADMIN']==1 || $user['CMD']==1)
        {
            
            if ($text == "/test") {
                if ($telegramBot->messageFromGroup()) {
                    $reply = "Chat Group";
                } else {
                    $reply = "Private Chat";
                }
                // Create option for the custom keyboard. Array of array string
                $option = array( array("A", "B"), array("C", "D") );
                // Get the keyboard
                $keyb = $telegramBot->buildKeyBoard($option);
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