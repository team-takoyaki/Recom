<?php
require_once('const.php');
require_once('db.php');


$event_data = array(
                'user_id' => $_REQUEST['user_id'],
                'event_id' => $_REQUEST['event_id']
            );

//event_objectのvalidator
if (isset($event_data['user_id']) !== true || isset($event_data['event_id']) !== true) {
    logger('ERROR', 'regist_memo.php validation_error');
    exit;
}
$search_data = array(
                    'user_id' => intval($event_data['user_id']),
                    'event_id' => intval($event_data['event_id'])
                );

$cursor = find_memo($search_data);
$ret = array();
$count = 0;
foreach ($cursor as $val) {
    $count++;
}

if ($count === 0) {
    $ret[] = false;
} else {
    $ret[] = true;
}

echo json_encode($ret);

