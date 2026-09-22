# WordPress.org 公開手順

## 前提

- WordPress.org の SVN 権限が有効化されていること
- `svn` と `rsync` が使えること
- このリポジトリの公開対象ファイルが最新であること

## 1. SVN 作業コピーを作る

```bash
mkdir -p .build
svn checkout https://plugins.svn.wordpress.org/katze-version-snapshot .build/wordpress-org-svn
```

初回は認証を求められるので、WordPress.org の `studiokatze` と SVN パスワードを使います。

## 2. リリース内容を `trunk` / `tags` に反映する

```bash
sh scripts/stage-wordpress-org-release.sh .build/wordpress-org-svn
```

このスクリプトは以下を行います。

- プラグイン本体の `Version:` からリリース版を取得
- このリポジトリの公開対象を SVN の `trunk/` に同期
- 同じ内容を `tags/<version>/` に同期
- `wordpress-org-assets/` が存在すれば `assets/` に同期

除外対象:

- `.git/`
- `.DS_Store`
- `.build/`
- `scripts/`
- `README.md`
- `katze-version-snapshot.zip`
- `katze-version-snapshot/`（ローカル配布用の複製）

## 3. 差分確認と追加登録

```bash
svn status .build/wordpress-org-svn
svn add --force .build/wordpress-org-svn/trunk .build/wordpress-org-svn/tags .build/wordpress-org-svn/assets
svn status .build/wordpress-org-svn
```

不要ファイルが含まれていないことを確認してください。

## 4. コミット

```bash
svn commit -m "Release 1.0.0" .build/wordpress-org-svn
```

`1.0.0` は、実際のプラグインバージョンに合わせてください。

## 補足

- WordPress.org の表示更新には時間がかかることがあります。
- バナーやアイコンを追加する場合は、リポジトリ直下に `wordpress-org-assets/` を作って管理する想定です。
