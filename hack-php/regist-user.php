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

//regist_user($insert_data);

//userのデータも返してあげる
/*
$cursor = find_user($insert_data);
$ret = array();
foreach ($cursor as $val) {
    $ret[] = $val;
}
*/

$user_data = get_user_data_array($insert_data);

if (count($user_data) === 0) {
    //まだuser登録されていない
    //登録してからデータ取得
    regist_user($insert_data);
    $user_data = get_user_data_array($insert_data);
}

echo json_encode($ret);



//$arr: 検索するときのデータ配列
//return: userのデータの配列
//[{id: 1, access_token: HHHHH}, {.....}]
function get_user_data_array($arr) {
    $cursor = find_user($insert_data);
    $ret = array();
    foreach ($cursor as $val) {
        $ret[] = $val;
    }
    return $ret;
}
