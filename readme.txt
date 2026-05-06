=== Katze Version Snapshot ===
Contributors: studiokatze
Tags: version, report, environment, audit, csv
Requires at least: 5.4
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Domain Path: /languages

View WordPress, PHP, plugin, and theme versions in Markdown or CSV for maintenance checks.

== Description ==
Katze Version Snapshot collects version information for WordPress core, PHP, the database, the active theme (including parent theme), and all installed plugins. Results are displayed as a Markdown table you can copy directly into tickets or documentation, and can also be exported as a CSV file with one click.

Perfect for web agencies and freelancers who manage multiple WordPress sites and need a quick, reliable way to document environment details before and after updates or migrations.

= Features =
* Displays a Markdown-formatted version report under **Tools > Katze Version Snapshot**
* Collects versions for WordPress, PHP, database, active theme, and all installed plugins
* One-click CSV export (environment summary + full plugin list)
* Read-only — no database writes or settings changes
* Access restricted to users with `manage_options` capability (administrators only)
* Translation-ready (English and Japanese included)

== Installation ==
1. Upload the `katze-version-snapshot` directory to `/wp-content/plugins/`.
2. Activate through **Plugins**.
3. Go to **Tools > Katze Version Snapshot** to view the Markdown report or download a CSV.

== Frequently Asked Questions ==
= Who can access this plugin? =
Only users with the `manage_options` capability (typically administrators) can view the report page and export CSV files.

= Does this plugin modify my site? =
No. Katze Version Snapshot is read-only. It does not write to the database or change any settings.

= Where does the page appear? =
The report page is added under **Tools > Katze Version Snapshot** in the WordPress admin. A **Settings** link also appears in the plugin list for quick access.

= What information is included? =
The report includes: WordPress core version, PHP version, database server version, active theme and parent theme (if applicable), and the version and activation status of all installed plugins.

---

= 誰がアクセスできますか？ =
`manage_options` 権限（通常は管理者）のユーザーのみがページを開き、CSV を出力できます。

= サイトに変更は発生しますか？ =
いいえ。データベースへの書き込みや設定変更は行いません。読み取りのみです。

= どこに表示されますか？ =
管理画面の **ツール > Katze Version Snapshot** に追加されます。プラグイン一覧の **設定** リンクからも移動できます。

= どんな情報が含まれますか？ =
WordPress / PHP / データベースのバージョン、有効テーマと親テーマの情報、インストール済みプラグインのバージョンと有効状態が含まれます。

== Changelog ==
= 1.0.0 =
* Initial release.
