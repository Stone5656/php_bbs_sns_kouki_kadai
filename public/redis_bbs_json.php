<?php
$redis = new Redis();
$redis->connect('redis', 6379);
$current_file = basename(__FILE__);

$key = 'bbs_write_list_json_key';

$write_list = $redis->exists($key) ? json_decode($redis->get($key)) : [];

if(!empty($_POST['write'])) {
    $write = $_POST['write'];

    array_unshift($write_list, $write);

    $redis->set($key, json_encode($write_list));

    return header("Location: {$current_file}");
}
?>

<form method="POST">
    <textarea name="write"></textarea><br>
    <button type="submit">更新</button>
</form>
<br>
<hr>
<br>
<?php foreach($write_list as $write): ?>
<div>
    <br>
    <?= nl2br(htmlspecialchars($write)) ?>
    <br>
    <hr>
</div>
<?php endforeach; ?>

