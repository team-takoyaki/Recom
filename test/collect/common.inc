<?php
/***************************************
 * ID : collect/common.inc
 * 共通ライブラリ
 * 
 ***************************************/

// 定義関係
date_default_timezone_set('Asia/Tokyo');

// 
function get_now_ym () {
    return date("Ym");
}

function get_next_ym () {
    return date('Ym', strtotime('+1 month'));
}


function is_ignore ($word) {
    $ignore_list = array(
                         'マス',
                         'コア',
                         'オン',
                         'カソン',
                         'バフ',
                         'パフ',
                         '認定',
                         'ハンズ',
                         '2013',
                         '2012',
                         '2011',
                         '2014',
                         'Nite',
                         'アジャ',
                         'トリ',
                         'ソン',
                         'Users',                                                  
                         'Night',
                         );
    return in_array($word, $ignore_list);
}