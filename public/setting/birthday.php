<?php
session_start();
if(empty($_SESSION['login_user_id'])) {
    header("HTTP/1.1 302 Found");
    header("Location: /login.php");
    return;
}

// DBに接続
$dbh = new PDO('mysql:host=mysql;dbname=example_db', 'root', '');
// ログインIDからログインユーザー情報を取得
$select_state = $dbh->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
$select_state->execute([
    ':id' => $_SESSION['login_user_id'],
]);
$user = $select_state->fetch();

if (isset($_POST['birthday'])) {
    $birthday = '';
    if(!empty($_POST['birthday'])) {
        $birthday = $_POST['birthday'];
    }
    $update_state = $dbh->prepare("UPDATE users SET birthday = :birthday WHERE id = :id");
    $update_state->execute([
        ':id' => $user['id'],
        ':birthday' => $birthday,
    ]);
    header("HTTP/1.1 303 See Other");
	header("Location: ./birthday.php");
	return;
}
?>
<a href="./index.php">設定一覧に戻る</a>

<h1>生年月日　設定/変更</h1>
<form method="POST" accept-charset="UTF-8" autocomplete="off">

  <label for="birthday" style="display: block; margin-bottom: 6px;">
    生年月日（半角数字のみ）
  </label>

  <input
    type="date"
    id="birthday"
    name="birthday"
	min="1900-01-01"
	max="2025-12-31"
	value="<?=
    	htmlspecialchars(
      	!empty($user['birthday'])
        	? (new DateTime($user['birthday']))->format('Y-m-d')
        	: ''
    	, ENT_QUOTES, 'UTF-8')
  	?>"
    style="
      width: 120px;
      min-height: 32px;
      padding: 8px 10px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font: 14px/1.6 ui-monospace, Menlo, Consolas, monospace;
      box-sizing: border-box;
    "
  >

  <div style="margin-top: 12px;">
    <button
      type="submit"
      style="
        padding: 8px 18px;
        border: 1px solid #ccc;
        border-radius: 8px;
        cursor: pointer;
        background: #f8f8f8;
      "
    >
      保存
    </button>
  </div>

</form>
