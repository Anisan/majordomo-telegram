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
'TLG_COMMAND'=>'Команда',
'TLG_PATTERNS'=>'Шаблоны',
'TLG_DOWNLOAD'=>'Загрузка',
'TLG_PLAY_VOICE'=>'Играть голос',
'TLG_DISABLE'=>'Запретить',
'TLG_ONLY_ADMIN'=>'Только для администраторов',
'TLG_ALL'=>'Для всех',
'TLG_ALL_NO_LIMIT' => 'Для всех (без ограничений)',
'TLG_SHOW_COMMAND'=>'Отображение команды',
'TLG_SHOW'=>'Показать',
'TLG_HIDE'=>'Скрыть',
'TLG_CONDITION'=>'Условие',
'TLG_EVENTS'=>'События',
'TLG_EVENT'=>'Событие',
'TLG_ENABLE'=>'Включить',
'TLG_EVENT_TEXT'=>'Текстовое сообщение',
'TLG_EVENT_IMAGE'=>'Изображение',
'TLG_EVENT_VOICE'=>'Голосовое сообщение',
'TLG_EVENT_AUDIO'=>'Аудио',
'TLG_EVENT_VIDEO'=>'Видео',
'TLG_EVENT_DOCUMENT'=>'Документ',
'TLG_EVENT_STICKER'=>'Стикер',
'TLG_EVENT_LOCATION'=>'Местоположение',
'TLG_COUNT_ROW'=>'Команд в строке',
'TLG_UPDATE_USER_INFO'=>'Обновить информацию пользователей',
'TLG_USE_WEBHOOK'=>'Использовать webhook',
'TLG_WEBHOOK_URL'=>'Webhook URL',
'TLG_PATH_CERT'=>'Путь к сертификату',
'TLG_WEBHOOK_SET'=>'Установить webhook',
'TLG_WEBHOOK_CLEAN'=>'Удалить webhook',
'TLG_WEBHOOK_INFO'=>'Статус webhook',
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
'HELP_PATTERNS'=>'Обработка сообщения пользователя как шаблона поведения',
'HELP_DOWNLOAD'=>'Сохранение файлов отправляемых пользователем',
'HELP_PLAY_VOICE'=>'Проигрывать голосовые сообщения от пользователя',
'HELP_TITLE'=>'Имя команды (отображается на клавиатуре в Telegram клиенте)',
'HELP_DESCRIPTION'=>'Описание команды',
'HELP_ACCESS_CONTROL'=>'Ограничение доступа к команде',
'HELP_COUNTROW'=>'Количество кнопок команд в одной строке на клавиатуре в Telegram клиенте'

/* end module names */
);

foreach ($dictionary as $k=>$v) {
 if (!defined('LANG_'.$k)) {
  define('LANG_'.$k, $v);
 }
}

?>