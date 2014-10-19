<?php
include_once __DIR__.'/common.inc';
include_once __DIR__.'/../const.php';
include_once __DIR__.'/../db.php';

function unix2utc($unix) {
  return (gmdate("Y-m-d", $unix) . "T" . gmdate("H:i:s", $unix) . "Z");
}

function get_event_data_for_month($query, $start_data) {
  $baseurl = 'http://www.google.com/calendar/feeds/fvijvohm91uifvd9hratehf65k%40group.calendar.google.com/public/full';
  // IT 勉強会カレンダー検索
  // https://developers.google.com/gdata/docs/2.0/reference?hl=ja
  $query = array('q' => $query,
                 'orderby' => 'starttime',
                 'sortorder' => 'ascending',
                 'alt' => 'json',
                 'max-results' => 100000,
                 'start-min' => unix2utc($start_data),
                 'start-max' => unix2utc(strtotime('+1 month', $start_data)));
  $url = $baseurl . '?' . http_build_query($query);
  // IT勉強カレンダーからデータ取得
  // https://www.google.com/calendar/embed?src=fvijvohm91uifvd9hratehf65k%40group.calendar.google.com
  return json_decode(file_get_contents($url), true);
}

function get_locate_from_title($title) {
  preg_match('/^\[(.+?)(?:\/(.*?))?]/', $title, $matches);
  $locate = '';
  if ($matches != null && empty($matches) == false) {
    $locate .= $matches[1];
  }
  if ($matches != null && empty($matches[2]) == false) {
    $locate .= $matches[2];
  }
  return $locate;
}

logger('DEBUG', 'CRON GET GOOGLE DATA START ');
$event_data = get_event_data_for_month('', strtotime('now'));
$num = count($event_data['feed']['entry']);
$insert_cnt = 0;
logger('DEBUG', 'CRON INSERT DB GOOGLE DATA START ');

for ($i = 0; $i < $num; $i++) {
    $title = $event_data['feed']['entry'][$i]['title']['$t'];
    $title = preg_replace('/^\[.*?\]/', '', $title);
    $epoch_time = strtotime($event_data['feed']['entry'][$i]['gd$when'][0]['startTime']);
    $start_date = date('Ymd', $epoch_time);
    $start_time = date('His', $epoch_time);
    $url = $event_data['feed']['entry'][$i]['content']['$t'];
    $locate = get_locate_from_title($event_data['feed']['entry'][$i]['title']['$t']);
    $locate_detail = $event_data['feed']['entry'][$i]['gd$where'][0]['valueString'];
    $locate = $locate . '(' . trim($locate_detail) . ')';
    
    // すでに入っているかチェック すでにあるデータは除外
    $check_data = array('title' => $title,
                         'locate' => $locate,
                         'start_date' => $start_date,
                         'start_time' => $start_time,
                         'url' => $url);
    $cursor = find_data(EVENT_COLLECTION,$check_data , 1, 0);
    $chk = iterator_to_array($cursor);
    if (count($chk) != 0) {
        continue;
    }
    
    $insert_data = array('id' => get_event_id (),
                         'title' => $title,
                         'locate' => $locate,
                         'start_date' => $start_date,
                         'start_time' => $start_time,
                         'url' => $url,
                         'src' => 'IT 勉強会カレンダー',
                         'tag1' => null,
                         'tag2' => null,
                         'tag3' => null);
    logger('DEBUG', var_export($insert_data, true));    
    /* print_r($insert_data); */
    insert_data(EVENT_COLLECTION, $insert_data);
    $insert_cnt++;
}

logger('DEBUG', 'CRON INSERT DB GOOGLE DATA END ('.$insert_cnt.')');
logger('DEBUG', 'CRON GET GOOGLE DATA FINISHED');
