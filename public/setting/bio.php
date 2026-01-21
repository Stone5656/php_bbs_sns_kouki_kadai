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

if (isset($_POST['bio'])) {
    $bio = '';
    if(!empty($_POST['bio'])) {
        $bio = $_POST['bio'];
    }
    $update_state = $dbh->prepare("UPDATE users SET bio = :bio WHERE id = :id");
    $update_state->execute([
        ':id' => $user['id'],
        ':bio' => $bio,
    ]);
    header("HTTP/1.1 303 See Other");
	header("Location: ./bio.php");
	return;
}
?>
<a href="./index.php">設定一覧に戻る</a>

<h1>自己紹介　設定/変更</h1>
<form method="POST" accept-charset="UTF-8" autocomplete="off">
  <label for="bio" style="display:block;margin-bottom:6px;">自己紹介<label>
  <textarea id="bio" name="bio" maxlength="1000"
  style="width:100%;min-height:220px;resize:none;padding:10px;border:1px solid #ccc;b
order-radius:8px;font:14px/1.6 ui-monospace,Menlo,Consolas,monospace;"><?=
      htmlspecialchars((!empty($user['bio']) ? $user['bio'] : ''), ENT_QUOTES, 'UTF-8');
  ?></textarea>
  <style>
    #remaining-characters.max {
      color: #dd3535;
    }
  </style>
  <p>残りの文字数：<span id="remaining-characters" style=".max{color: #dd3535;};">50</span></p>
  <div style="margin-top:10px;">
    <button type="submit" style="padding:8px 14px;border:1px solid #ccc;border-radius
:8px;cursor:pointer;">保存</button>
  </div>
</form>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const textarea = document.getElementById("bio");
    const counter  = document.getElementById("remaining-characters");
    const maxLength = parseInt(textarea.getAttribute("maxlength"), 10);

      const updateCount = () => {
          const remaining = maxLength - textarea.value.length;
          counter.textContent = remaining;
          counter.style.color = remaining <= 0 ? "#dd3535" : "#333";
      };

      textarea.addEventListener("input", updateCount);
      updateCount(); // 初期化（ロード時）
});
</script>



