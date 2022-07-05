<?php
/**
* Ukrainian language file for Telegram module
*
*/

$dictionary=array(
/* general */
'ABOUT' => 'Про модуль',
'TLG_HELP' => 'Допомога',
'TLG_TOKEN'=>'Токен бота',
'TLG_STORAGE_PATH'=>'Шлях до сховища',
'TLG_ADMIN'=>'Адміністратор',
'TLG_HISTORY'=>'Історія',
'TLG_HISTORY_LEVEL'=>'Пріоритет історії',
'TLG_COMMANDS'=>'Команди',
'TLG_COMMAND'=>'Команда',
'TLG_PATTERNS'=>'Шаблони',
'TLG_DOWNLOAD'=>'Завантаження',
'TLG_PLAY_VOICE'=>'Програвати голос',
'TLG_DISABLE'=>'Заборонити',
'TLG_ONLY_ADMIN'=>'Тільки для адміністраторів',
'TLG_ALL'=>'Для всіх',
'TLG_ALL_NO_LIMIT' => 'Для всіх (без обмежень)',
'TLG_SHOW_COMMAND'=>'Відображення команди',
'TLG_SHOW'=>'Показати',
'TLG_HIDE'=>'Сховати',
'TLG_CONDITION'=>'Умова',
'TLG_EVENTS'=>'Дії',
'TLG_EVENT'=>'Дія',
'TLG_ENABLE'=>'Ввімкнути',
'TLG_EVENT_TEXT'=>'Текстове повідомлення',
'TLG_EVENT_IMAGE'=>'Зображення',
'TLG_EVENT_VOICE'=>'Голосове повідомлення',
'TLG_EVENT_AUDIO'=>'Аудіо',
'TLG_EVENT_VIDEO'=>'Відео',
'TLG_EVENT_DOCUMENT'=>'Документ',
'TLG_EVENT_STICKER'=>'Стікер',
'TLG_EVENT_LOCATION'=>'Місцезнаходження',
'TLG_COUNT_ROW'=>'Команд в строкі',
'TLG_PLAYER'=>'Проигравач голосу',
'TLG_TIMEOUT'=>'Період long polling (сек)',
'TLG_UPDATE_USER_INFO'=>'Оновити інформацію користувачів',
'TLG_USE_WEBHOOK'=>'Використовувати webhook',
'TLG_WEBHOOK_URL'=>'Webhook URL',
'TLG_PATH_CERT'=>'Шлях до сертифікату',
'TLG_WEBHOOK_SET'=>'Установить webhook',
'TLG_WEBHOOK_CLEAN'=>'Удалить webhook',
'TLG_WEBHOOK_INFO'=>'Статус webhook',
'TLG_USE_PROXY'=>'Використовувати проксі',
'TLG_PROXY_TYPE'=>'Тип проксі',
'TLG_PROXY_URL'=>'Адрес сервера проксі',
'TLG_PROXY_LOGIN'=>'Логін проксі',
'TLG_PROXY_PASSWORD'=>'Пароль проксі',
'TLG_REG_USER'=>'Реєстрація користувачів',
/* about */

/* help */
'HELP_TOKEN'=>'Токен бота отриманого від @BotFather вида \'123456780:AAHeВ7UcDWvEovvcFaMfUrUVPupNORHWD_k\'',
'HELP_STORAGE'=>'Шлях для збереження файлів отриманих від користувачів',
'HELP_TIMEOUT'=>'Період очікування нових повідомлень в секундах',
'HELP_REG_USER'=>'Опція дозволяє відключити автоматичну реєстрацію користувачів (antispam)',
'HELP_USE_PROXY'=>'Настройки прокси для обхода блокировок Роскомнадзора',
'HELP_LOG_LEVEL'=>'Уровень логирования: Debug = писать все, info = основную информацию, warning = только важное',
'HELP_IPRESOLV'=>'Вигляд IP-адреси, яка буде використовуватися для підключення до серверів Telegram. IPv4, IPv6 або Any',
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
