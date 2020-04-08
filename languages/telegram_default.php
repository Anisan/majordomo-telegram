<?php
/**
* Default language file for Telegram module
*
*/
$dictionary=array(
/* general */
'ABOUT' => 'About',
'TLG_HELP' => 'Help',
'TLG_TOKEN'=>'Token bot',
'TLG_STORAGE_PATH'=>'Path download storage',
'TLG_ADMIN'=>'Administrator',
'TLG_SILENT'=>'Silent mode',
'TLG_HISTORY'=>'History',
'TLG_HISTORY_LEVEL'=>'History level',
'TLG_HISTORY_SILENT'=>'History level not silent',
'TLG_COMMANDS'=>'Commands',
'TLG_COMMAND'=>'Command',
'TLG_PATTERNS'=>'Patterns',
'TLG_DOWNLOAD'=>'Download',
'TLG_PLAY_VOICE'=>'Play',
'TLG_DISABLE'=>'Disable',
'TLG_ONLY_ADMIN'=>'Only administrators',
'TLG_ALL'=>'All',
'TLG_ALL_NO_LIMIT' => 'All (no limit)',
'TLG_SHOW_COMMAND'=>'Show command',
'TLG_SHOW'=>'Show',
'TLG_HIDE'=>'Hide',
'TLG_CONDITION'=>'Condition',
'TLG_EVENTS'=>'Events',
'TLG_EVENT'=>'Event',
'TLG_ENABLE'=>'Enable',
'TLG_EVENT_TEXT'=>'Text message',
'TLG_EVENT_IMAGE'=>'Image',
'TLG_EVENT_VOICE'=>'Voice',
'TLG_EVENT_AUDIO'=>'Audio',
'TLG_EVENT_VIDEO'=>'Video',
'TLG_EVENT_DOCUMENT'=>'Document',
'TLG_EVENT_STICKER'=>'Sticker',
'TLG_EVENT_LOCATION'=>'Location',
'TLG_COUNT_ROW'=>'Count commands on row',
'TLG_PLAYER'=>'Player for voice',
'TLG_TIMEOUT'=>'Timeout long polling (sec)',
'TLG_UPDATE_USER_INFO'=>'Update user info',
'TLG_USE_WEBHOOK'=>'Use webhook',
'TLG_WEBHOOK_URL'=>'Webhook URL',
'TLG_PATH_CERT'=>'Path to certificate',
'TLG_WEBHOOK_SET'=>'Set webhook',
'TLG_WEBHOOK_CLEAN'=>'Clean webhook',
'TLG_WEBHOOK_INFO'=>'Status webhook',
'TLG_USE_PROXY'=>'Use proxy',
'TLG_PROXY_TYPE'=>'Type proxy',
'TLG_PROXY_URL'=>'Server proxy',
'TLG_PROXY_LOGIN'=>'Login proxy',
'TLG_PROXY_PASSWORD'=>'Password proxy',
'TLG_REG_USER'=>'Auto registration users',
/* about */

/* help */
'HELP_TOKEN'=>'Token bot from @BotFather -> \'123456780:AAHeВ7UcDWvEovvcFaMfUrUVPupNORHWD_k\'',
'HELP_STORAGE'=>'Path storage to save files from user',
'HELP_TIMEOUT'=>'Timeout cycle in ms',
'HELP_REG_USER'=>'Enable registration user (anti spam)',
'HELP_USE_PROXY'=>'Enable https proxy (torsocks)',
'HELP_LOG_LEVEL'=>'Debug = all, info = information level, warning = only warning level',
'HELP_USERID'=>'Telegram User ID',
'HELP_NAME'=>'Name user',
'HELP_MEMBER'=>'Link to system user',
'HELP_ADMIN'=>'Administrator',
'HELP_SILENT'=>'Send silent messages',
'HELP_HISTORY'=>'Send history to user',
'HELP_HISTORY_LEVEL'=>'Level history to send(0 - send all history message)',
'HELP_HISTORY_SILENT'=>'Level history to send not silent(0 - send all history message not silent)',
'HELP_COMMANDS'=>'Process command from user',
'HELP_PATTERNS'=>'Process patterns from user',
'HELP_DOWNLOAD'=>'Download files to storage from user',
'HELP_PLAY_VOICE'=>'Play voice from user',
'HELP_TITLE'=>'Title command (view in keyboard telegram client)',
'HELP_DESCRIPTION'=>'Description command',
'HELP_ACCESS_CONTROL'=>'Access control command',
'HELP_COUNTROW'=>'Count commands on row'

/* end module names */
);

foreach ($dictionary as $k=>$v) {
 if (!defined('LANG_'.$k)) {
  define('LANG_'.$k, $v);
 }
}

?>