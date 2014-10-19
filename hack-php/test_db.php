<?php
include_once('const.php');
include_once('db.php');

$table_name = 'test';
$insert_data = array('title' => 'タイトルだよ', 'start_date' => new MongoDate(strtotime('2010-01-30 00:00:00')), 'url' => 'http://...');                                                   
insert_data($table_name, $insert_data);

$find_data = array('title' => 'タイトルだよ');
$cursor = find_data($table_name, $find_data);
foreach ($cursor as $doc) {
    var_dump($doc);
}