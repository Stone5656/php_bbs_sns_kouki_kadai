<?php
$session_cookie_name = 'session_id';
$session_id = $_COOKIE[$session_cookie_name] ?? base64_encode(random_bytes(64));
if(!isset($COOKIE[$session_cookie_name])) {
    setcookie($session_cookie_name, $session_id);
}

$redis = new Redis();
$redis->connect('redis', 6379);
$current_file = basename(__FILE__);

$redis_session_key = "session-" . $session_id;

$session_values = $redis->exists($redis_session_key) ? json_decode($redis->get($redis_session_key), true) : [];

$count = isset($session_values["count"]) ? intval($session_values["count"]) : 0;
$count++;

$date = new DateTime(date("Y-m-d H:i:s"));
$formatted_date = $date->format('Y年m月d日 H時i分s秒');

$last_login = isset($session_values["last_login"]) ? $session_values["last_login"] : null;

$session_values["count"] = strval($count);

$redis->set($redis_session_key, json_encode($session_values));

?>

<h2>このセッションでの<?=  strval($count)?>回目のアクセスです！</h2>
<?php
if($last_login != null){
echo "<h4>前回の訪問は{$last_login}でした。</h4>";
}

$session_values["count"] = strval($count);
$session_values["last_login"] = strval($formatted_date);

$redis->set($redis_session_key, json_encode($session_values));
?>

