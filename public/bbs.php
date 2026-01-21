<?php
$dbh = new PDO('mysql:host=mysql;dbname=example_db', 'root', '');
session_start();

// いままで保存してきたものを取得
$select_sth = $dbh->prepare('SELECT * FROM bbs_entries ORDER BY created_at DESC');
$select_sth = $dbh->prepare('SELECT b.*, ' .
    'u.name AS user_name, ' .
    'u.icon_filename AS icon ' .
    'FROM bbs_entries b INNER JOIN users u ON b.user_id = u.id ' .
    'ORDER BY b.created_at DESC'
);
$select_sth->execute();

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
<?php if(empty($_SESSION['login_user_id'])): ?>
  <a href="/login.php">ログイン</a>して自分のタイムラインを閲覧しましょう
<?php else: ?>
  <a href="/timeline.php">タイムラインはこちら</a>
<?php endif; ?>

<hr>

<?php foreach($select_sth as $entry): ?>
  <dl style="margin-bottom: 1em; padding-bottom: 1em; border-bottom: 1px solid #ccc;">
    <dt id="entry<?= htmlspecialchars($entry['id']) ?>">
      番号
    </dt>
    <dd>
      <?= htmlspecialchars($entry['id']) ?>
    </dd>
    <dt>
      投稿者
    </dt>
    <dd>
      <a href="/profile.php?user_id=<?= $entry['user_id'] ?>">
        <?php if(empty($entry['icon'])): ?>
          <img src="/user.png"
            style="height: 3em; width: 3em; border-radius: 50%; object-fit: cover;">
        <?php else: ?>
          <img src="/image/<?= $entry['icon'] ?>"
            style="height: 3em; width: 3em; border-radius: 50%; object-fit: cover;">
        <?php endif; ?>
    </dd>

    <dd>
        <?= htmlspecialchars($entry['user_name']) ?>
        会員ID: <?= htmlspecialchars($entry['user_id']) ?>
      </a>
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

