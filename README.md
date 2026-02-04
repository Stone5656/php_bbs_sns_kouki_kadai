# サービス構築手順書

Git/Dockerのセットアップ、アプリケーションの起動までの手順を記したものです。

## 1. AWS EC2インスタンスの接続
新規作成したまっさらなEC2インスタンスに接続してください

## 2. Git/Dockerでの環境構築

### 2.1 このリポジトリをクローンする

1. Gitをインストール
```bash
sudo yum install git -y
```

2. リポジトリをクローンする
```bash
git clone https://github.com/Stone5656/php_bbs_sns_kouki_kadai.git
```

### 2.2 サービスを起動する

1. Dockerをインストールする
```bash
sudo yum install -y docker
sudo systemctl start docker
sudo systemctl enable docker
```

2. ユーザーをdockerグループに追加
```bash
sudo usermod -a -G docker ec2-user
```
sshの再接続が必要なので、一度ログアウトしてください
```bash
exit
```

3. Docker Composeをインストールする
```bash
sudo mkdir -p /usr/local/lib/docker/cli-plugins/
sudo curl -SL https://github.com/docker/compose/releases/download/v2.36.0/docker-compose-linux-x86_64 -o /usr/local/lib/docker/cli-plugins/docker-compose
sudo chmod +x /usr/local/lib/docker/cli-plugins/docker-compose
```

4. ルートフォルダに移動
```bash
cd php_bbs_sns_kouki_kadai/
```

5. サービスを起動する
```bash
docker compose up -d
```

6. テーブルを作成する
Mysql用のコンテナに接続します。
```bash
docker compose exec mysql mysql example_db
```

下記コマンドでテーブルを作成できます。
```mysql
CREATE TABLE `users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `icon_filename` text COLLATE utf8mb4_unicode_ci,
  `bio` text COLLATE utf8mb4_unicode_ci,
  `cover_filename` text COLLATE utf8mb4_unicode_ci,
  `birthday` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `user_relationships` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `followee_user_id` int unsigned NOT NULL,
  `follower_user_id` int unsigned NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `bbs_entries` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_filename` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

