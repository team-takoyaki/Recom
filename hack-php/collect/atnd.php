<?php
/***************************************
 * ID : collect/atnd.php
 * ATNDからイベント情報を取得する
 *
 * ATND API memo (http://atnd.org/doc/api.html)
 * URL
 * - http://api.atnd.org/eventatnd/event
 * PARAM
 * - ym : 201302 (複数パラメータOK なので常に今月と翌月取得
 * - format : json
 * - start : 索の開始位置検索結果の何件目から出力するか 初期値：1
 * - order : 検索結果のソート順1: 更新日順（デフォルト）2:開催日順 3:新着順
 * - count : 取得件数検索結果の最大出力データ数 max 100
 * 
 * RESPONSE
 * - results_returned : 含まれる検索結果の件数
 * - results_available : 利用可能な検索結果の総件数
 * - results_start : 検索の開始位置
 * 
 * - event_id : イベントID ex)803/0
 * - title : タイトル
 * - started_atイベント開催日時2012-03-09T18:00:00+09:00
 * 
 * - address : 開催場所 ex)東京都港区
 * - place : 開催会場 ex)中華飯店
 * - lat : 開催会場の緯度 ex)35.6866072
 * - lon : 開催会場の経度 ex)139.7605287
 * - event_url : ATNDのURL ex)http://atnd.org/event/E0000805/0
 **************************************/

// 共通ライブラリ読み込み
include_once __DIR__.'/common.inc';
include_once __DIR__.'/../const.php';
include_once __DIR__.'/../db.php';

// ATND url
define("ATND_REQ_URL", 'http://api.atnd.org/eventatnd/event/');

/***************************************
 * スケルトン
 ***************************************/
logger('DEBUG', 'CRON GET ATND DATA START ');

// イベント取得
$events = get_atnd_for_month();

// 取得失敗
if (!$events) {
    logger('ERROR', 'FAILED CRON GET ATND DATA');
    exit;
}
logger('DEBUG', 'CRON GET ATND DATA END ');

// DB格納
logger('DEBUG', 'CRON INSERT DB ATND DATA START ');
$insert_cnt = 0;
foreach ($events as $e) {
    // 時刻チェック 現在時刻よりも以前のものはスキップ
    if ( date('Y-m-d H:i:s') > date("Y-m-d H:i:s", strtotime($e['started_at'])) ) {
        continue;
    }

    // すでに入っているかチェック すでにあるデータは除外
    $check_data = array('title' => $e['title'],
                        'locate' => $e['address'].' ('.$e['place'].')',
                        'start_date' => date("Ymd", strtotime($e['started_at'])),
                        'start_time' => date("His", strtotime($e['started_at'])),
                        'url' => $e['event_url'],
                        );
    $cursor = find_data(EVENT_COLLECTION,$check_data , 1, 0);
    $chk = iterator_to_array($cursor);
    if (count($chk) != 0) {
        continue;
    }
        
    $insert_data = array('id'    => get_event_id (),
                         'title' => $e['title'],
                         'locate' => $e['address'].' ('.$e['place'].')',
                         'start_date' => date("Ymd", strtotime($e['started_at'])),
                         'start_time' => date("His", strtotime($e['started_at'])),
                         'url' => $e['event_url'],
                         'src' => 'ATND',                         
                         'tag1' => 'null',
                         'tag2' => 'null',
                         'tag3' => 'null');        
    logger('DEBUG', var_export($insert_data, true));
    insert_data(EVENT_COLLECTION, $insert_data);
    $insert_cnt++;
}
logger('DEBUG', 'CRON INSERT DB ATND DATA END ('.$insert_cnt.')');
logger('DEBUG', 'CRON GET ATND DATA FINISHED');
exit;

/***************************************
 * 直近の1ヶ月のデータ(期間長めで)を取得
 * 実質今月と来月取得
 ***************************************/
function get_atnd_for_month ($start='1', $events = array()) {
    // リクエストURL生成 ym以外
    $param = array(
        'format' => 'json',
        'start'  => $start,
        'order'  => '2',
        'count'  => '100',
     );
    // ym も足して
    $url = ATND_REQ_URL.'?'.http_build_query($param).'&'
        .'ym='.get_now_ym().'&'
        .'ym='.get_next_ym();

    // データ取得
    logger('DEBUG', 'get atnd data '.$url);

    logger('DEBUG', 'atnd get start');    
    $ret = json_decode (file_get_contents($url), true);
    
    // 取得失敗
    if (!$ret) {
        logger('ERROR', 'faild get atnd data '.$url , true);

        // 一つでのイベント取得できているときは返却
        if (count($events) != 0) {
            return $events;
        } else {
            return false;
        }
    }
    
    // 取得成功時
    logger('DEBUG', 'atnd get success');
    // 現在取得完了の位置
    $done_cnt = $ret['results_start']+$ret['results_returned'];

    // イベント情報の追記
    $events = array_merge($events, $ret['events'][0]['event']);
    logger('DEBUG', count($events));    
    
    // 取得の終了条件確認
    // 取得スタートと取得件数が利用可能な検索結果を超えるまで
    if ($done_cnt < $ret['results_available']) {
        logger('DEBUG', 're get antd data now='.$done_cnt.' end='.$ret['results_available']);
        return get_atnd_for_month ($done_cnt, $events);
    } else {
        // 全取得完了時
        logger('DEBUG', 'finish get atnd data');
        // イベントリストを返却
        return $events;
    }
}


