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
'TLG_SILENT'=>'Режим без звука',
'TLG_HISTORY'=>'История',
'TLG_HISTORY_LEVEL'=>'Приоритет истории',
'TLG_HISTORY_SILENT'=>'Приоритет истории сo звуком',
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
'TLG_PLAYER'=>'Проигрыватель голоса',
'TLG_TIMEOUT'=>'Период long polling (сек)',
'TLG_UPDATE_USER_INFO'=>'Обновить информацию пользователей',
'TLG_USE_WEBHOOK'=>'Использовать webhook',
'TLG_WEBHOOK_URL'=>'Webhook URL',
'TLG_PATH_CERT'=>'Путь к сертификату',
'TLG_WEBHOOK_SET'=>'Установить webhook',
'TLG_WEBHOOK_CLEAN'=>'Удалить webhook',
'TLG_WEBHOOK_INFO'=>'Статус webhook',
'TLG_USE_PROXY'=>'Использовать прокси',
'TLG_PROXY_TYPE'=>'Тип прокси',
'TLG_PROXY_URL'=>'Адрес сервера прокси',
'TLG_PROXY_LOGIN'=>'Логин прокси',
'TLG_PROXY_PASSWORD'=>'Пароль прокси',
'TLG_REG_USER'=>'Регистрация пользователей',
/* about */

/* help */
'HELP_TOKEN'=>'Токен бота полученного от @BotFather вида \'123456780:AAHeВ7UcDWvEovvcFaMfUrUVPupNORHWD_k\'',
'HELP_STORAGE'=>'Путь для сохранения файлов полученных от пользователя',
'HELP_TIMEOUT'=>'Период ожидания новых сообщений в секундах',
'HELP_REG_USER'=>'Опция позволяет отключить автоматическую регистрацию пользователей (antispam)',
'HELP_USE_PROXY'=>'Настройки прокси для обхода блокировок Роскомнадзора',
'HELP_LOG_LEVEL'=>'Уровень логирования: Debug = писать все, info = основную информацию, warning = только важное',
'HELP_IPRESOLV'=>'Тип IP адреса, который будет использоваться для подключения к серверам Telegram. IPv4, IPv6 или Any',
'HELP_USERID'=>'Telegram User ID',
'HELP_NAME'=>'Имя пользователя',
'HELP_MEMBER'=>'Связь с пользователем системы',
'HELP_ADMIN'=>'Администратор',
'HELP_SILENT'=>'Сообщения приходят в Telegram клиент без звука',
'HELP_HISTORY'=>'Отправка системной истории пользователю',
'HELP_HISTORY_LEVEL'=>'Уровень важности для отправки (0 - отправка всей истории)',
'HELP_HISTORY_SILENT'=>'Уроверь важности при котором сообщения приходят со звуком(0 - все со звуком)',
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
