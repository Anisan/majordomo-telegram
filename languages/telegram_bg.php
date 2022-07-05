<?php
/*
Bulgarian language file for Telegram module
*/

$dictionary=array(
/* general */
'ABOUT' => 'За модула',
'TLG_HELP' => 'Помощ',
'TLG_TOKEN'=>'Тоукън на бота',
'TLG_STORAGE_PATH'=>'Път до хранилището',
'TLG_ADMIN'=>'Администратор',
'TLG_HISTORY'=>'История',
'TLG_HISTORY_LEVEL'=>'Ниво на лога',
'TLG_COMMANDS'=>'Команди',
'TLG_COMMAND'=>'Команда',
'TLG_PATTERNS'=>'Шаблони',
'TLG_DOWNLOAD'=>'Зареждане',
'TLG_PLAY_VOICE'=>'Възпроизвеждане на глас',
'TLG_DISABLE'=>'Забраняване',
'TLG_ONLY_ADMIN'=>'Само за администратори',
'TLG_ALL'=>'За всички',
'TLG_ALL_NO_LIMIT' => 'За всички (без ограничения)',
'TLG_SHOW_COMMAND'=>'Показване на команди',
'TLG_SHOW'=>'Показване',
'TLG_HIDE'=>'Скриване',
'TLG_CONDITION'=>'Условие',
'TLG_EVENTS'=>'Събития',
'TLG_EVENT'=>'Събитие',
'TLG_ENABLE'=>'Включване',
'TLG_EVENT_TEXT'=>'Текстово съобщение',
'TLG_EVENT_IMAGE'=>'Изображение',
'TLG_EVENT_VOICE'=>'Голосово съобщение',
'TLG_EVENT_AUDIO'=>'Аудио',
'TLG_EVENT_VIDEO'=>'Видео',
'TLG_EVENT_DOCUMENT'=>'Документ',
'TLG_EVENT_STICKER'=>'Стикер',
'TLG_EVENT_LOCATION'=>'Местоположение',
'TLG_COUNT_ROW'=>'Команди на ред',
'TLG_PLAYER'=>'Гласов плейър',
'TLG_TIMEOUT'=>'Период long polling (сек)',
'TLG_UPDATE_USER_INFO'=>'Актуализация на информацията за потребителя',
'TLG_USE_WEBHOOK'=>'Използване на webhook',
'TLG_WEBHOOK_URL'=>'Webhook URL',
'TLG_PATH_CERT'=>'Път към сертификат',
'TLG_WEBHOOK_SET'=>'Задаване на webhook',
'TLG_WEBHOOK_CLEAN'=>'Изтриване на webhook',
'TLG_WEBHOOK_INFO'=>'Статус на webhook',
'TLG_USE_PROXY'=>'Исползване на прокси',
'TLG_PROXY_TYPE'=>'Тип прокси',
'TLG_PROXY_URL'=>'Адрес на прокси сервъра ',
'TLG_PROXY_LOGIN'=>'Потребителско име',
'TLG_PROXY_PASSWORD'=>'Парола',
'TLG_REG_USER'=>'Регистрация на потребител',
/* about */

/* help */
'HELP_TOKEN'=>'Тоукън на бота получен от @BotFather във вид \'123456780:AAHeВ7UcDWvEovvcFaMfUrUVPupNORHWD_k\'',
'HELP_STORAGE'=>'Път за запазване на файлове, получени от потребителя',
'HELP_TIMEOUT'=>'Периодът на чакане за нови съобщения в секунди',
'HELP_REG_USER'=>'Опцията Ви позволява да деактивирате автоматичната регистрация на потребителите (антиспам)',
'HELP_USE_PROXY'=>'Активиране на https прокси (torsocks)',
'HELP_LOG_LEVEL'=>'Ниво на запис на логовете: Debug = записва всичко, info = основна информация, warning = само важното',
'HELP_IPRESOLV'=>'Типът IP адрес, който ще се използва за свързване към сървърите на Telegram. IPv4, IPv6 или Any',
'HELP_USERID'=>'Telegram User ID',
'HELP_NAME'=>'Потребителско име',
'HELP_MEMBER'=>'Комуникация с потребителя на системата',
'HELP_ADMIN'=>'Администратор',
'HELP_HISTORY'=>'Изпращане на системни логове на потребителя',
'HELP_HISTORY_LEVEL'=>'Ниво на важност за изпращане (0 - изпращане на свички логове)',
'HELP_COMMANDS'=>'Обработка на команди, получени от потребителя',
'HELP_PATTERNS'=>'Обработка на потребителско съобщение като шаблон за поведение',
'HELP_DOWNLOAD'=>'Запазване на файлове, изпратени от потребителя',
'HELP_PLAY_VOICE'=>'Възпроизвеждане на гласови съобщения от потребителя',
'HELP_TITLE'=>'Име на командата (показва се на клавиатурата на клиента на Telegram)',
'HELP_DESCRIPTION'=>'Описание на командата',
'HELP_ACCESS_CONTROL'=>'Ограничаване на достъпа до команда',
'HELP_COUNTROW'=>'Броя на командните бутони в един ред на клавиатурата на клиента на Telegram'

/* end module names */
);

foreach ($dictionary as $k=>$v) {
if (!defined('LANG'.$k)) {
define('LANG'.$k, $v);
}
}

?>
