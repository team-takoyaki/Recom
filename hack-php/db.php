<?php
include_once __DIR__.'/const.php';

function connect_database() {
    if (DEBUG == false) {
        // Main Database
        $mongo = new Mongo('mongodb://takoyaki:takoyaki0519@ds037997.mongolab.com:37997/hack-db');
    } else {
        // Debug Database
        $mongo = new Mongo('mongodb://takoyaki:takoyaki0519@ds051007.mongolab.com:51007/hack-test');
    }
    return $mongo->selectDB('hack-test');
}

function connect_collection($collection_name) {
    $db = connect_database();
    return $db->selectCollection($collection_name);
}

// MongoCollection Manual
// http://www.php.net/manual/ja/class.mongocollection.php
function insert_data($collection_name, $insert_data) {
    $db = connect_database();
    $col = $db->selectCollection($collection_name);
    return $col->insert($insert_data);
}

function find_data($collection_name, $find_data, $limit, $offset) {
    $db = connect_database();
    $col = $db->selectCollection($collection_name);
    return $col->find($find_data)->skip($offset)->limit($limit);
}

function find_sort_data ($collection_name, $find_data, $limit, $offset, $sort_data) {
    $db = connect_database();
    $col = $db->selectCollection($collection_name);    
    return $col = $col->find($find_data)->sort($sort_data)->skip($offset)->limit($limit);
}

// 更新
function update_data ($collection_name, $find_data, $update_data) {
    $db = connect_database();
    $col = $db->selectCollection($collection_name);
    $col->update($find_data, array( '$set' => $update_data));
    return $col->findOne();
}


//memoに追加する
function regist_memo($data) {
    $db = connect_database();
    $col = $db->selectCollection(MEMO_COLLECTION);

    //すでに登録されているかチェック
    $count = $col->find($data)->count();
    if ($count !== 0) {
        return;
    }

    $id = get_memo_id();
    $insert_data = array(
        'id' => $id,
        'user_id' => $data['user_id'],
        'event_id' => $data['event_id']
    );

    insert_data(MEMO_COLLECTION, $insert_data);
}

//memoから検索する
function find_memo($query, $offset=0, $limit=20) {
    $db = connect_database();
    $col = $db->selectCollection(MEMO_COLLECTION);

    return $col->find($query)->skip($offset)->limit($limit);
}

function get_memo_id() {
    $db = connect_database();
    $col = $db->selectCollection(MEMO_ID_COLLECTION);
    $cursor =  $col->find()->sort(array('id'=>-1))->limit(1);
    $table = iterator_to_array($cursor);
    if (count($table) == 0) {
        $id = 1;
    } else {
        foreach ($table as $row) {
            $id = $row['id'] + 1;
        }
    }
    $col->insert(array('id'=>$id));
    return $id;
}

function remove_memo($data) {
    $db = connect_database();
    $col = $db->selectCollection(MEMO_COLLECTION);
    $col->remove($data, array('justOne' => true));
}

//user情報を追加する
function regist_user($data) {
    $db = connect_database();
    $col = $db->selectCollection(USER_COLLECTION);

    //すでに登録されているかチェック
    $count = $col->find($data)->count();
    if ($count !== 0) {
        return;
    }


    $id = get_user_id();
    $insert_data = array(
        'id' => $id,
        'twitter_id' => $data['twitter_id']
    );

    insert_data(USER_COLLECTION, $insert_data);
}

function get_user_id() {
    $db = connect_database();
    $col = $db->selectCollection(USER_ID_COLLECTION);
    $cursor =  $col->find()->sort(array('id'=>-1))->limit(1);
    $table = iterator_to_array($cursor);
    if (count($table) == 0) {
        $id = 1;
    } else {
        foreach ($table as $row) {
            $id = $row['id'] + 1;
        }
    }
    $col->insert(array('id'=>$id));
    return $id;
}

//user情報を検索する
function find_user($query) {
    $db = connect_database();
    $col = $db->selectCollection(USER_COLLECTION);

    return $col->find($query);
}

// イベント追加時に連番のIDを取得する
function get_event_id () {
    // 連番発行
    $db = connect_database();
    $col = $db->selectCollection(EVENT_ID_COLLECTION);
    $cursor =  $col->find()->sort(array('id'=>-1))->limit(1);
    $table = iterator_to_array($cursor);
    if (count($table) == 0) {
        $id = 1;
    } else {
        foreach ($table as $row) {
            $id = $row['id'] + 1;
        }
    }
    $col->insert(array('id'=>$id));
    return $id;
}

// イベント一覧の情報を取得
function get_event_list ($limit=20, $offset=0) {
    $db = connect_database();
    $col = $db->selectCollection(EVENT_COLLECTION);

    // 条件作成
    $now_ymd = date('Ymd');
    $now_his = date('His');

    // 明日以降か今日でこの時間以降
    $find_settig =  array('$where' => "
function() {
    return this.start_date > $now_ymd || (this.start_date==$now_ymd && this.start_time > $now_his);
}
");

    // 現在日時, 現在時刻の順にに近い順
    $sort_settig = array("start_date" => 1, "start_time" => 1);

    // 取得
    return  $col->find($find_settig)->sort($sort_settig)->skip($offset)->limit($limit);
}

// 指定タグのイベント取得
function get_category_list($tag_array, $limit=20, $offset=0) {
    $db = connect_database();
    $col = $db->selectCollection(EVENT_COLLECTION);

    // 条件作成
    $now_ymd = date('Ymd');
    $now_his = date('His');

    $tag_where_array = array();
    foreach ($tag_array as $tag) {
        $tag_where_array[] = "this.tag1=='$tag' || this.tag2=='$tag' || this.tag3=='$tag'";
    }
    $tag_where = '('.implode('||', $tag_where_array).')';

    // 明日以降か今日でこの時間以降
    $find_settig =  array('$where' => "
return $tag_where && (this.start_date > $now_ymd || (this.start_date==$now_ymd && this.start_time > $now_his));
");
    // 現在日時, 現在時刻の順にに近い順
    $sort_settig = array("start_date" => 1, "start_time" => 1);

    // 取得
    return  $col->find($find_settig)->sort($sort_settig)->skip($offset)->limit($limit);
}


function get_category() {
    $db = connect_database();
    $col = $db->selectCollection(EVENT_COLLECTION);

    // 条件作成
    $now_ymd = date('Ymd');
    $now_his = date('His');

    // 明日以降か今日でこの時間以降
    $find_settig =  array('$where' => "
function() {
    return this.start_date > $now_ymd || (this.start_date==$now_ymd && this.start_time > $now_his);
}
");
    // 取得
    $cursor = $col->find($find_settig);
    $list   = iterator_to_array($cursor);

    // ユニークにする
    $tag_list = array();
    foreach ($list as $e) {
        $cnt = isset($tag_list[$e['tag1']]) ? $tag_list[$e['tag1']] : 0;
        $tag_list[$e['tag1']] = ++$cnt;

        $cnt = isset($tag_list[$e['tag2']]) ? $tag_list[$e['tag2']] : 0;
        $tag_list[$e['tag2']] = ++$cnt;

        $cnt = isset($tag_list[$e['tag3']]) ? $tag_list[$e['tag3']] : 0;
        $tag_list[$e['tag3']] = ++$cnt;
    }
    unset($tag_list['null']);
    unset($tag_list['NULL']);
    unset($tag_list['']);
    unset($tag_list[' ']);
    arsort($tag_list);

    return $tag_list;
}


// include_once('const.php');
// include_once('db.php');
//
// $table_name = 'test';
//
// MongoDate
// http://php.net/manual/ja/class.mongodate.php
// $insert_data = array('title' => 'タイトルだよ', 'start_date' => new MongoDate(strtotime('2010-01-30 00:00:00')), 'url' => 'http://...');
// insert_data($table_name, $insert_data);
//
// $find_data = array('title' => 'タイトルだよ');
// $cursor = find_data($table_name, $find_data);
// foreach ($cursor as $doc) {
//     var_dump($doc);
// }






