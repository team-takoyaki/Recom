<?php
require_once('const.php');
require_once('db.php');


$event_data = array(
                'user_id' => $_POST['user_id'],
                'event_id' => $_POST['event_id']
            );

//event_objectのvalidator
if (isset($event_data['user_id']) !== true || isset($event_data['event_id']) !== true) {
    logger('ERROR', 'regist_memo.php validation_error');
    exit;
}
$insert_data = array(
                    'user_id' => intval($event_data['user_id']),
                    'event_id' => intval($event_data['event_id'])
                );

regist_memo($insert_data);


