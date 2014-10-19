<?php
include_once('const.php');
include_once('db.php');

for ($i = 0; $i < 50; $i++) {
  $insert_data = array('title' => 'タイトルだよ' . $i, 
                       'locale' => '東京',
                       'start_date' => new MongoDate(strtotime('2010-01-30 00:00:00')), 
                       'url' => 'http://google.co.jp/',
                       'tag1' => 'タグ1',
                       'tag2' => 'タグ2',
                       'tag3' => 'タグ3');
  insert_data(EVENT_COLLECTION, $insert_data);
}

/* $find_data = array('title' => 'タイトルだよ'); */
/* $cursor = find_data($table_name, $find_data); */
/* foreach ($cursor as $doc) { */
/*     var_dump($doc); */
/* } */
