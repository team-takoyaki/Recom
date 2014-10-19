<?php
require_once('const.php');
require_once('db.php');

$user_data = array();
$user_data['twitter_id'] = $_REQUEST['twitter_id'];


$search_data = array();


if (isset($user_data['twitter_id']) !== true) {
    logger('ERROR', 'regist_memo.php validation_error');
    return null;
}

$search_data = array(
                    'twitter_id' => $user_data['twitter_id'],
                );

$cursor = find_user($search_data);

$ret = array();

foreach ($cursor as $val) {
    $ret[] = $val;
}

echo json_encode($ret);
