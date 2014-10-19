<?php
require_once('const.php');
require_once('db.php');

$user_data = array(
                'twitter_id' => $_POST['access_token']
            );

//event_objectのvalidator
if (isset($user_data['twitter_id']) !== true) {
    logger('ERROR', 'regist_memo.php validation_error');
    exit;
}

$insert_data = array(
                    'twitter_id' => $user_data['twitter_id'],
                );

regist_user($insert_data);

//userのデータも返してあげる
$cursor = find_user($insert_data);
$ret = array();
foreach ($cursor as $val) {
    $ret[] = $val;
}

echo json_encode($ret);
