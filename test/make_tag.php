<?php
include_once __DIR__.'/const.php';
include_once __DIR__.'/db.php';
include_once __DIR__.'/collect/common.inc';

logger('DEBUG', 'MAKE_TAG START');

// YDN 情報
$app_id = 'dj0zaiZpPXZMaUc3V2loOWZrOSZkPVlXazlPRWhUUWtST05HMG1jR285TUEtLSZzPWNvbnN1bWVyc2VjcmV0Jng9ZGU-';
$skey = '2b52796a41a5e7083e01fbdc43ce2f3d313f012c';

// 形態素解析API情報
$url = 'http://jlp.yahooapis.jp/MAService/V1/parse';

$limit = 50000;

// 条件作成
$now_ymd = date('Ymd', strtotime('-1 day')); //日付は比較演算子が分からなかったので昨日
$now_his = date('His'); // 時間はちょうどはないだろうからこれでOK

// 条件作成
$now_ymd = date('Ymd'); 
$now_his = date('His');
// 現在時刻よりも以降のみ
$find_settig =  array('$where' => "
function() {
    return ((this.start_date > $now_ymd) || (this.start_date==$now_ymd && this.start_time > $now_his));
}
");
// && (this.tag1 == 'null'||this.tag1 == 'NULL'||this.tag1==false||this.tag1==null||!this.tag1)
$db     = connect_database();
$col    = $db->selectCollection(EVENT_COLLECTION);
$cursor = $col->find($find_settig);
$list   = iterator_to_array($cursor);

logger('DEBUG', 'MAKE_TAG db have '.count($list).' no tag events');

// 形態素解析APIでタイトルの名詞を取得
logger('DEBUG', 'MAKE_TAG fetch Yahoo!API start');
$cnt = 0;
$id_word_hash = array();
foreach ($list as $l) {
    if ($cnt >= $limit) {
        logger('ERROR', 'MAKE_TAG Yahoo!API limit error '.$limit);
        break;
    }
    
    // リクエストURL生成 ym以外
    $param = array(
        'appid' => $app_id,
        'sentence'  => $l['title'],
        'results'  => 'ma',
        'count'  => '100',
        'filter' => '9', // 名刺
     );
    $req_url = $url.'?'.http_build_query($param);
    logger('DEBUG', 'MAKE_TAG fetch Yahoo!API '.$req_url);
    
    $ret = simplexml_load_file($req_url);
    $cnt++;    
    if (!$ret) {
        // タイトルで取得できないときは場所でタグ
        $param = array('sentence'  => $l['locate']);
        $req_url = $url.'?'.http_build_query($param);
        logger('DEBUG', 'MAKE_TAG fetch Yahoo!API '.$req_url);
    
        $ret = simplexml_load_file($req_url);
        if (!$ret) {        
            $cnt++;
            logger('ERROR', 'MAKE_TAG Yahoo!API fetch error '.$req_url);
            continue;
        }
    }
    
    $word_list = array();
    foreach ($ret->ma_result->word_list->word as $word) {
        $word_list[] = (string) $word->surface;
    }
    
    $id_word_hash[] = array($l['id'] => $word_list);
}
logger('DEBUG', 'MAKE_TAG fetch Yahoo!API end');


// タグ毎のカウント処理
logger('DEBUG', 'MAKE_TAG tag count start');
$tag_hash = array();
foreach ($id_word_hash as $array => $id) {
    foreach ($id as $array => $words) {
        foreach ($words as $word) {
            
            // フィルター
            if (strlen($word) <= 3) {continue;}
            if (is_ignore ($word))  {continue;}

            if (!isset($tag_hash[$word])) {
                $tag_hash[$word] = 1;
            } else {
                $tag_hash[$word] = ++$tag_hash[$word];
            }
        }
    }
}
// カウント数降順ソート
arsort($tag_hash);
logger('DEBUG', 'MAKE_TAG tag count end '.count($tag_hash).' words exists');


// タグをつけていく処理
logger('DEBUG', 'MAKE_TAG fetch tag start');
$id_tag_hash = array();
foreach ($id_word_hash as $array => $id) {
    foreach ($id as $e_id => $words) {
        $tags = array();
        foreach ($words as $word) {
            foreach ($tag_hash as $tag => $cnt) {
                if ($tag == $word) {$tags[$tag] = $cnt;}
            }
        }
        
        arsort($tags);

        $tag_limit = 0;
        foreach ($tags as $tag => $cnt) {
            if ($tag_limit >= 3) {break;}
            $id_tag_hash [$e_id][$tag_limit] = $tag;
            $tag_limit++;
        }
    }
}
logger('DEBUG', 'MAKE_TAG fetch tag end');


// DB格納開始
logger('DEBUG', 'MAKE_TAG update db start');
foreach ($id_tag_hash as $id => $words) {
    logger('DEBUG', 'MAKE_TAG id '.$id. var_export($words, true));
    $ret = update_data (EVENT_COLLECTION,
                 array('id'=>$id),
                 array(
                       'tag1' => isset($words[0]) ? $words[0] : 'null',
                       'tag2' => isset($words[1]) ? $words[1] : 'null',
                       'tag3' => isset($words[2]) ? $words[2] : 'null',
                       ));
}



logger('DEBUG', 'MAKE_TAG update db END');