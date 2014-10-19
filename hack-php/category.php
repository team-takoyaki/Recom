<?php
/***************************************
 * ID : category.php
 * タグ一覧と、指定タグの情報を提供
 *
 * param :
 *  tag - タグ名前を指定
 * 
 **************************************/

include_once('const.php');
include_once('db.php');

// パラメータ取得
$tag    = isset($_GET["tag"]) ? $_GET["tag"] : false;
$num    = isset($_GET["num"])    ? $_GET["num"] : 20;
$offset = isset($_GET["offset"]) ? $_GET["offset"] : 0;

// パラメータバリテーション
if (!is_numeric($num)) {
    $num = 0;
}
if (!is_numeric($offset)) {
    $offset = 20;
}

// tagの複数処理
$tag_array = explode('<>', $tag);

if (count($tag_array) == 0 || !$tag) {
    // カテゴリ一覧
    $cache_file = './log/category.cache';
    $cache_time = 60*60*6; // 4分の1日

    $time = file_exists($cache_file) ? filemtime($cache_file) : 0;
    if (file_exists($cache_file) && ($time + $cache_time) > time() ) {
        logger('DEBUG', "request  all tag list, load ".$cache_file.'('.$cache_time.')');
        $tag_list = json_decode(file_get_contents($cache_file), true);
    }
    if ($tag_list == null){
        $tag_list = array();
        $_tag_list = get_category();        
        foreach ($_tag_list as $tag => $cnt) {
            $tag_list[] = array('tag' => $tag, 'cnt' => $cnt);
        }
        // ファイル書き込み失敗時
        if (!file_put_contents($cache_file, json_encode($tag_list))) {
            logger('ERORR', "cant save tag list");
            header('Content-Type: text/javascript; charset=utf-8');
            echo '[]';
            exit;
        }
        logger('DEBUG', "request  all tag list, save ".$cache_file.'('.$cache_time.')');
    }

    $list_count = count($tag_list);
    logger('DEBUG', "request  all tag list, returned ($list_count) list");
    header('Content-Type: text/javascript; charset=utf-8');
    echo json_encode($tag_list);    
    exit;
}

// 指定カテゴリ表示
$cursor    = get_category_list($tag_array , $num, $offset);
$list      = iterator_to_array($cursor);

// 取得できなかった時
$list_count = count($list);
if ($list_count <= 0) {
    header('Content-Type: text/javascript; charset=utf-8');
    echo '[]';
    exit;
}

logger('DEBUG', "request tag list ($tag, $num, $offset), returned ($list_count) list");

header('Content-Type: text/javascript; charset=utf-8');
echo json_encode(array_values($list));
exit;
