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

$find_data = array();
$cursor = get_event_list ($num, $offset);
$json_value = json_encode(iterator_to_array($cursor));    

logger('DEBUG', var_export(iterator_to_array($cursor),true));
logger('DEBUG', count(iterator_to_array($cursor)));

header('Content-Type: text/javascript; charset=utf-8');
echo $json_value;
