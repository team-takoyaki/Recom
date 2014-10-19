<?php
define('DEBUG', true);

// 定義
date_default_timezone_set('Asia/Tokyo');
define('LOG_FILE', __DIR__.'/log/error.log');
define('EVENT_COLLECTION', 'event');
define('EVENT_ID_COLLECTION', 'event_id');
define('MEMO_COLLECTION', 'memo');
define('MEMO_ID_COLLECTION', 'memo_id');
define('USER_COLLECTION', 'user');
define('USER_ID_COLLECTION', 'user_id');



/*********************************
 * ロガー関数
 * logger( ログタイプ, ログ内容)
 ********************************/
function logger ($type='debug', $msg='') {
    // デバッグモードでないときは ERROR のみをロギング
    if (!DEBUG && ($type!='ERROR')) {
        return;
    }
    
    $trace = debug_backtrace();
    $trace[0]['pathinfo'] = pathinfo($trace[0]['file']);
    $body =
        '['.date("D M j G:i:s T Y").'] '.
        '['.$type.'] '.
        $trace[0]['pathinfo']['basename'].'('.$trace[0]['line'].') '.
        $msg."\n";
    // ログ追記
    $fp = fopen(LOG_FILE, "a");
    fwrite($fp, $body);
    fclose($fp);
}
