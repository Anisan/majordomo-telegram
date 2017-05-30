<?php
/**
 * Telegram Bot 
    * webhook
    *
 * @package project
 * @author Isupov Andrey <eraser1981@gmail.com>
 * @copyright (c)
 */
//
//
include_once("./config.php");
include_once("./lib/loader.php");
// connecting to database
$db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME);
include_once("./load_settings.php");
include_once(DIR_MODULES . "control_modules/control_modules.class.php");
$ctl = new control_modules();
include_once(DIR_MODULES . 'telegram/telegram.class.php');

$telegram_module = new telegram();
$telegram_module->getConfig();
$telegram_module->processMessage();

$db->Disconnect();
?>