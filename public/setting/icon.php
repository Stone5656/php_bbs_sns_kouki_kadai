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

if(isset($_POST['image_base64'])) {
    // image_base64が送られてきたら
    $image_filename = null;
    if(!empty($_POST['image_base64'])) {
        // 先頭にある無駄な文字列を削除
        $base64 = preg_replace('/^data:.+base64,/', '', $_POST['image_base64']);
        // base64をデコード
        $image_binary = base64_decode($base64);
        // ファイル名が一意になるようにする
        $image_filename = strval(time()) . bin2hex(random_bytes(25)) . '.png';
        $filepath = '/var/www/upload/image/' . $image_filename;
        file_put_contents($filepath, $image_binary, LOCK_EX);
    }
    // ログインユーザーのfilenameを更新
    $update_state = $dbh->prepare("UPDATE users SET icon_filename = :icon_filename WHERE id = :id");
    $update_state->execute([
        ':id' => $user['id'],
        ':icon_filename' => $image_filename,
    ]);
    // リダイレクト
    header("HTTP/1.1 302 Found");
    header("Location: ./icon.php");
    return;
}
?>

<a href="./index.php">設定一覧に戻る</a>
<h1>アイコン画像設定/変更</h1>
<div>
  <?php if(empty($user['icon_filename'])): ?>
  <img src="/user.png"
    style="height: 5em; width: 5em; border-radius: 50%; object-fit: cover;">
  <?php else: ?>
  <img src="/image/<?= $user['icon_filename'] ?>"
    style="height: 5em; width: 5em; border-radius: 50%; object-fit: cover;">
  <?php endif; ?>
</div>
<form method="POST">
  <div style="margin: 1em 0;">
    <input type="file" accept="image/*" name="image" id="imageInput">
  </div>
  <input id="imageBase64Input" type="hidden" name="image_base64"><!-- base64を送る用のinput (非表示) -->
  <canvas id="imageCanvas" style="display: none;"></canvas><!-- 画像縮小に使うcanvas (非表示) -->
  <button type="submit">アップロード</button>
</form>
<hr>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const imageInput = document.getElementById("imageInput");
  imageInput.addEventListener("change", () => {
    if (imageInput.files.length < 1) {
      // 未選択の場合
      return;
    }
    const file = imageInput.files[0];
    if (!file.type.startsWith('image/')){ // 画像でなければスキップ
      return;
    }
    // 画像縮小処理
    const imageBase64Input = document.getElementById("imageBase64Input"); // base64を送るようのinput
    const canvas = document.getElementById("imageCanvas"); // 描画するcanvas
    const reader = new FileReader();
    const image = new Image();
    reader.onload = () => { // ファイルの読み込み完了したら動く処理を指定
      image.onload = () => { // 画像として読み込み完了したら動く処理を指定
        // 元の縦横比を保ったまま縮小するサイズを決めてcanvasの縦横に指定する
        const originalWidth = image.naturalWidth; // 元画像の横幅
        const originalHeight = image.naturalHeight; // 元画像の高さ
        const maxLength = 1000; // 横幅も高さも1000以下に縮小するものとする
        if (originalWidth <= maxLength && originalHeight <= maxLength) { // どちらもmaxLength以下の場合そのまま
            canvas.width = originalWidth;
            canvas.height = originalHeight;
        } else if (originalWidth > originalHeight) { // 横長画像の場合
            canvas.width = maxLength;
            canvas.height = maxLength * originalHeight / originalWidth;
        } else { // 縦長画像の場合
            canvas.width = maxLength * originalWidth / originalHeight;
            canvas.height = maxLength;
        }
        // canvasに実際に画像を描画 (canvasはdisplay:noneで隠れているためわかりにくいが...)
        const context = canvas.getContext("2d");
        context.drawImage(image, 0, 0, canvas.width, canvas.height);
        // canvasの内容をbase64に変換しinputのvalueに設定
        imageBase64Input.value = canvas.toDataURL();
      };
      image.src = reader.result;
    };
    reader.readAsDataURL(file);
  });
});
</script>


