<?php
/***************************************
 * ID : list.php
 * 取得したイベントのリストをJSONで返却
 *
 * param :
 *  num - 取得する件数
 *  offset - 取得するスタート位置
 * 
 **************************************/

include_once('const.php');
include_once('db.php');

// パラメータ取得
$num    = isset($_GET["num"])    ? $_GET["num"] : 20;
$offset = isset($_GET["offset"]) ? $_GET["offset"] : 0;

// パラメータバリテーション
if (!is_numeric($num)) {
    $num = 0;
}
if (!is_numeric($offset)) {
    $offset = 20;
}

// データ取得
$find_data = array();
$cursor    = get_event_list ($num, $offset);
$list      = iterator_to_array($cursor);

// 取得できなかった時
$list_count = count($list);
if ($list_count <= 0) {
    header('Content-Type: text/javascript; charset=utf-8');
    echo '[]';
    exit;
}

logger('DEBUG', "request event list ($num,$offset), returned ($list_count) list");

header('Content-Type: text/javascript; charset=utf-8');
echo json_encode(array_values($list));
