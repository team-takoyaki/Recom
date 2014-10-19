<?php
require_once('const.php');
require_once('db.php');


$event_data = array(
                'user_id' => $_REQUEST['user_id']
            );
$search_data = array();

$offset = isset($_GET['offset']) ? $_GET['offset'] : 0;
$limit  = isset($_GET['limit'])    ? $_GET['limit'] : 20;

//$_GETのvalidator
if (is_numeric($limit) !== true) {
    $limit = 0;
}

if (is_numeric($offset) !== true) {
    $offset = 20;
}

//event_objectのvalidator
if (isset($event_data['user_id']) === true) {
    $search_data['user_id'] = intval($event_data['user_id']);
}

if (isset($event_data['user_id']) !== true && isset($event_data['event_id']) !== true) {
    logger('ERROR', 'regist_memo.php validation_error');
    return null;
}

//=================
//user_idからuserのevnt_idを取得
$search_data = array(
                    'user_id' => intval($event_data['user_id']),
                );

//userのmemo_data取得
$cursor = find_memo($search_data, $offset, $limit);
$or_query = array();

foreach ($cursor as $val) {
    $or_query[] = array('id' => intval($val['event_id']));
}

if (count($or_query) === 0) {
    return null;
}

//================

//===============
//eventのidからeventの詳細を取得
$ret = array();
$col = connect_collection(EVENT_COLLECTION);
$cursor = $col->find(
                array(
                    '$or' => $or_query
                )
            );

foreach ($cursor as $val) {
    $ret[] = $val;
}

echo json_encode($ret);
