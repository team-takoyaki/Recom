<?php
/***************************************
 * ID : recomend.php
 * ユーザにとっておすすめのイベントリストを返却
 * ユーザの持っているイベント
 *
 * param :
 *  id - ユーザID
 *  num - 取得する件数
 *  offset - 取得するスタート位置
 * 
 **************************************/

include_once('const.php');
include_once('db.php');

// パラメータ取得
$id     = isset($_GET["id"])     ? $_GET["id"] : false;
$num    = isset($_GET["num"])    ? $_GET["num"] : 20;
$offset = isset($_GET["offset"]) ? $_GET["offset"] : 0;

$id = (int) $id;
$id = 11;
// パラメータバリテーション
if (!$id || !is_numeric($num)) {
    logger('DEBUG', 'no vaid id='.$id);        
    header('Content-Type: text/javascript; charset=utf-8');
    echo '[]';
    exit;
}
if (!is_numeric($num)) {
    $num = 20;
}
if (!is_numeric($offset)) {
    $offset = 0;
}

// メモリストを取得
$cursor = find_data(MEMO_COLLECTION, array('user_id'=>$id), 100, 0);
$list   = iterator_to_array($cursor);
$memo_event_list = array();
foreach ($list as $memo) {
    $memo_event_list[] = $memo['event_id'];
}

// メモリストが0件の時終了
if (count($memo_event_list) < 1) {
    logger('DEBUG', 'memo not found id='.$id);
    header('Content-Type: text/javascript; charset=utf-8');
    echo '[]';
    exit;
}
logger('DEBUG', 'memo '.count($memo_event_list).' found id='.$id);



// 同じ指向の人のイベント取得
// 方針
// 同じ度が高い順の上位60%からイベント取得
$db = connect_database();
$col = $db->selectCollection(MEMO_COLLECTION);
$cursor = $col->find(array('event_id'=> array('$in' => $memo_event_list)));

$near_user_list = array();
// $near_user_list[$user_id] = count
while ($cursor->hasNext()) {
    $document = $cursor->getNext();
    if ($document['user_id'] == $id ) {continue;} // 自分以外ね
    
    $near_user_list[$document['user_id']] =
        isset($near_user_list[$document['user_id']]) ? ++$near_user_list[$document['user_id']] : 1;    
}

// 要らない人切り捨てのリミット取得
$near_user_limit = round (count($near_user_list) * 0.6, 0);

arsort($near_user_list);
$near_user_limit_cnt = 0;
$near_user_limit_where_array = array();
foreach ($near_user_list as $near_user_id => $cnt) {
    if ($near_user_limit_cnt > $near_user_limit) {continue;}    
    $near_user_limit_where_array[] = "this.user_id==$near_user_id";
    $near_user_limit_cnt++;
}

$near_user_limit_where = implode('||', $near_user_limit_where_array);
// 
$cursor = $col->find(array('$where' => "function () { return $near_user_limit_where; }"));
$list = iterator_to_array($cursor);

// 似ている人のイベント格納
foreach ($list as $memo) {
    if (in_array($memo['event_id'], $memo_event_list)) {continue;}
    $memo_event_list[] = $memo['event_id'];
}


// イベントを取得
$tag_where_array = array();
$cursor = find_data(EVENT_COLLECTION, array('id'=> array('$in' => $memo_event_list)), 100, 0);
$list   = iterator_to_array($cursor);

// イベントからタグ一覧を抽出
$tag_hash = array();
foreach ($list as $e) {
    $tag_hash[] = $e['tag1'];
    $tag_hash[] = $e['tag2'];
    $tag_hash[] = $e['tag3'];    
}

$tag_where_array = array();
foreach ($tag_hash as $tag) {
    $tag_where_array[] = "this.tag1=='$tag' || this.tag2=='$tag' || this.tag3=='$tag'";
}
$tag_where = '('.implode('||', $tag_where_array).')';

$now_ymd = date('Ymd');
$now_his = date('His');

// メモに入っているの除外分作成
$memo_where_array = array();
foreach ($memo_event_list as $memo_id) {
    $memo_where_array[] = "this.id != $memo_id";
}
$memo_where = '('.implode('||', $memo_where_array).')';

// 明日以降か今日でこの時間以降
$find_settig =  array('$where' => "
return $memo_where && $tag_where && (this.start_date > $now_ymd || (this.start_date==$now_ymd && this.start_time > $now_his));
");

// 現在日時, 現在時刻の順にに近い順
$sort_settig = array("start_date" => 1, "start_time" => 1);

$cursor = find_sort_data (EVENT_COLLECTION, $find_settig, $num, $offset, $sort_settig);
$list = iterator_to_array($cursor);

/*
logger('INFO', var_export(count($list), true));
// すでにメモしているイベントは除外
foreach ($list as $_id => $e) {
    foreach ($memo_event_list as $_e) {
        if ($e['id'] == $_e) {
            logger('DEBUG', "request recommend event list ".$e['title']." is already memo");
            unset($list[$_id]);
            continue;
        }
    }
}
logger('INFO', var_export(count($list), true));
*/

// イベントが無いとき
$list_count = count($list);
if ($list_count <= 0) {
    logger('DEBUG', "request recommend event list not found recommend evennt id=".$id);    
    header('Content-Type: text/javascript; charset=utf-8');
    echo '[]';
    exit;
}

logger('DEBUG', "request recommend event list ($id, $num, $offset), returned ($list_count) list");

header('Content-Type: text/javascript; charset=utf-8');
echo json_encode(array_values($list));
