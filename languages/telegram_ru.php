<?php
/**
* Russian language file for Telegram module
*
*/

$dictionary=array(
/* general */
'ABOUT' => 'О модуле',
'TLG_HELP' => 'Помощь',
'TLG_TOKEN'=>'Токен бота',
'TLG_STORAGE_PATH'=>'Путь к хранилищу',
'TLG_ADMIN'=>'Администратор',
'TLG_HISTORY'=>'История',
'TLG_HISTORY_LEVEL'=>'Приоритет истории',
'TLG_COMMANDS'=>'Команды',
'TLG_DOWNLOAD'=>'Загрузка',
'TLG_PLAY_VOICE'=>'Играть голос',
'TLG_DISABLE'=>'Запретить',
'TLG_ONLY_ADMIN'=>'Только для администраторов',
'TLG_ALL'=>'Для всех',
/* about */

/* help */
'HELP_TOKEN'=>'Токен бота полученного от @BotFather вида \'123456780:AAHeВ7UcDWvEovvcFaMfUrUVPupNORHWD_k\'',
'HELP_STORAGE'=>'Путь для сохранения файлов полученных от пользователя',
'HELP_USERID'=>'Telegram User ID',
'HELP_NAME'=>'Имя пользователя',
'HELP_MEMBER'=>'Связь с пользователем системы',
'HELP_ADMIN'=>'Администратор',
'HELP_HISTORY'=>'Отправка системной истории пользователю',
'HELP_HISTORY_LEVEL'=>'Уровень важности для отправки (0 - отправка всей истории)',
'HELP_COMMANDS'=>'Обработка команд полученных от пользователя',
'HELP_DOWNLOAD'=>'Сохранение файлов отправляемых пользователем',
'HELP_PLAY_VOICE'=>'Проигрывать голосовые сообщения от пользователя',
'HELP_TITLE'=>'Имя команды (отображается на клавиатуре в Telegram клиенте)',
'HELP_DESCRIPTION'=>'Описание команды',
'HELP_ACCESS_CONTROL'=>'Ограничение доступа к команде'

/* end module names */
);

foreach ($dictionary as $k=>$v) {
 if (!defined('LANG_'.$k)) {
  define('LANG_'.$k, $v);
 }
}

?>