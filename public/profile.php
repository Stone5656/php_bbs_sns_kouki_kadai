<?php
$user = null;

if(!empty($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
    // DBに接続
    $dbh = new PDO('mysql:host=mysql;dbname=example_db', 'root', '');
    // ユーザー情報を取得
    $select_state = $dbh->prepare("SELECT * FROM users WHERE id=:id LIMIT 1");
    $select_state->execute([
        ':id' => $user_id,
    ]);
    $user = $select_state->fetch();
}

if (empty($user)) {
    header("HTTP/1.1 404 Not Found");
    print("そのようなユーザーは存在しません");
    return;
}

// いままで保存してきたものを取得
$select_sth = $dbh->prepare('SELECT * FROM bbs_entries WHERE user_id = :user ORDER BY created_at DESC');
$select_sth->execute([
    ':user' => $user['id'],
]);

// フォロー状態を取得
$follow_relationship = null;
session_start();
if (!empty($_SESSION['login_user_id'])) { // ログインしている場合
  // フォロー状態をDBから取得
  $select_sth = $dbh->prepare(
    "SELECT * FROM user_relationships"
    . " WHERE follower_user_id = :follower_user_id AND followee_user_id = :followee_user_id"
  );

  $select_sth->execute([
    ':followee_user_id' => $user['id'], // フォローされる側は閲覧しようとしているプロフィールの会員
    ':follower_user_id' => $_SESSION['login_user_id'], // フォローする側はログインしている会員
  ]);
  $follow_relationship = $select_sth->fetch();
}

// フォロワーを取得
$follower_relationship = null;
if (!empty($_SESSION['login_user_id'])) { // ログインしている場合
  // フォロー状態をDBから取得
  $select_sth = $dbh->prepare(
    "SELECT * FROM user_relationships"
    . " WHERE follower_user_id = :follower_user_id AND followee_user_id = :followee_user_id"
  );

  $select_sth->execute([
    ':followee_user_id' => $_SESSION['login_user_id'], // フォローしている側は閲覧しようとしているプロフィールの会員
    ':follower_user_id' => $user['id'], // フォローされるる側はログインしている会員
  ]);
  $follower_relationship = $select_sth->fetch();
}

// bodyのHTMLを出力するための関数を用意
function bodyFilter (string $body): string
{
  $body = htmlspecialchars($body); // エスケープ処理
  $body = nl2br($body); // 改行文字を<br>要素に変換
  // >>1 といった文字列を該当番号の投稿へのページ内リンクとする (レスアンカー機能)
  // 「>」(半角の大なり記号)は htmlspecialchars() でエスケープされているため注意
  $body = preg_replace('/&gt;&gt;(\d+)/', '<a href="#entry$1">&gt;&gt;$1</a>', $body);
  return $body;
}

?>
<style>
html, body {
  margin: 0;
  padding: 0;
}
</style>
<a href="/timeline.php">掲示板に戻る</a>
<div style="position: relative; margin-bottom: 3em;">
  <?php if (empty($user['cover_filename'])): ?>
    <div
      style="
        width: 100vw;
        height: 240px;
        background: linear-gradient(
          135deg,
          #0f172a 0%,
          #0ea5e9 50%,
          #22c55e 100%
        );
        background-size: cover;
        background-position: center;
      "
    ></div>
  <?php else: ?>
    <img
      src="/image/<?= htmlspecialchars($user['cover_filename'], ENT_QUOTES) ?>"
      style="
        width: 100vw;
        height: 240px;
        object-fit: cover;
      "
    >
  <?php endif; ?>

  <?php if (empty($user['icon_filename'])): ?>
    <img
      src="/user.png"
      style="
        position: absolute;
        bottom: -2.5em;
        width: 5em;
        height: 5em;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid white;
        background: #fff;
        margin-left: 1em;
      "
    >
  <?php else: ?>
    <img
      src="/image/<?= $user['icon_filename'] ?>"
      style="
        position: absolute;
        left: 0;
        bottom: -2.5em;
        width: 5em;
        height: 5em;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid white;
        background: #fff;
        margin-left: 1em;
      "
    >
  <?php endif; ?>
</div>
<div 
    style="
        margin-left: 1em;
        font-size: 1.5em;
    "
><?= htmlspecialchars($user['name']) ?></div>
<div
    style="
        margin-left: 1.5em;
        font-size: 1em;
        color: #7f7f7f;
    "
>
    <?php if (empty($user['birthday'])): ?>
        年齢不詳
    <?php else: ?>
		<?php
		  $birthday = DateTime::createFromFormat('Y-m-d', $user['birthday']);
 		  $today = new DateTime('now');
		?>
        <?= $today->diff($birthday)->y ?>歳
    <?php endif ?>
</div>
<?php if(isset($_SESSION['login_user_id'])): ?>
<?php if($user['id'] === $_SESSION['login_user_id']): ?>
<div style="margin: 1em 0;">
  これはあなたです！<br>
  <a href="/setting/index.php">設定画面はこちら</a>
</div>
<?php else: // 他人の場合 ?>
<div style="margin: 1em 0;">
  <?php if(empty($relationship)): // フォローしていない場合 ?>
  <div>
    <a href="./follow.php?followee_user_id=<?= $user['id'] ?>">フォローする</a>
  </div>
  <?php else: // フォローしている場合 ?>
  <div>
    <?= $relationship['created_at'] ?> にフォローしました。
  </div>
  <?php endif; ?>
  <?php if(!empty($follower_relationship)): // フォローされている場合 ?>
  <div>
    フォローされています。
  </div>
  <?php endif; ?>
</div>
<?php endif; ?>
<?php else: //ログインしていない場合> ?>
<?php endif; ?>
<div
    style="
      padding: 12px;
      white-space: pre-wrap;
      overflow-wrap: break-word;
      margin-right: 1em;
      margin-left: 1em;
    "
>
  <?= htmlspecialchars($user['bio'] ?? '', ENT_QUOTES, 'UTF-8') ?>
</div>
<?php foreach($select_sth as $entry): ?>
  <dl style="margin-bottom: 1em; padding-bottom: 1em; border-bottom: 1px solid #ccc; margin-right: 1em; margin-left: 1em;">
    <hr>
    <dt>
      投稿者
    </dt>
    <dd>
        <?php if(empty($user['icon_filename'])): ?>
          <img src="/user.png"
            style="height: 3em; width: 3em; border-radius: 50%; object-fit: cover;">
        <?php else: ?>
          <img src="/image/<?= $user['icon_filename'] ?>"
            style="height: 3em; width: 3em; border-radius: 50%; object-fit: cover;">
        <?php endif; ?>
    </dd>

    <dd>
        <?= htmlspecialchars($user['name']) ?>
        会員ID: <?= htmlspecialchars($user['id']) ?>
    </dd>
    <dt>日時</dt>
    <dd><?= $entry['created_at'] ?></dd>
    <dt>内容</dt>
    <dd>
      <?= bodyFilter($entry['body']) ?>
      <?php if(!empty($entry['image_filename'])): ?>
      <div>
        <img src="/image/<?= $entry['image_filename'] ?>" style="max-height: 10em;">
      </div>
      <?php endif; ?>
    </dd>
  </dl>
<?php endforeach ?>

