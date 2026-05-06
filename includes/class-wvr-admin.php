<?php
if (!defined('ABSPATH')) {
    exit;
}

class WVR_Admin {

    public static function render_page(): void {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Forbidden', 'katze-version-snapshot'));
        }

        $summary = WVR_Collector::get_summary();
        $plugins = WVR_Collector::get_plugins();
        $markdown = WVR_Collector::build_markdown($summary, $plugins);

        $version_report_export_url = wp_nonce_url(admin_url('admin-post.php?action=kvs_export_version_snapshot'), 'kvs_export_version_snapshot');

        ?>
        <div class="wrap wvr-report">
            <h1><?php echo esc_html__('Katze Version Snapshot', 'katze-version-snapshot'); ?></h1>
            <p><?php echo esc_html__('単発アップデート時に必要な最小情報を Markdown で表示します。必要に応じて CSV （環境サマリ+プラグイン一覧）をダウンロードしてください。', 'katze-version-snapshot'); ?></p>

            <div class="wvr-actions">
                <a class="button button-primary" href="<?php echo esc_url($version_report_export_url); ?>"><?php echo esc_html__('CSVをダウンロード', 'katze-version-snapshot'); ?></a>
            </div>

            <div class="wvr-heading">
                <h2><?php echo esc_html__('Markdown（コピペ用）', 'katze-version-snapshot'); ?></h2>
                <p class="wvr-copy-notice js-wvr-copy-notice" aria-live="polite"><?php echo esc_html__('コピーしました', 'katze-version-snapshot'); ?></p>
            </div>
            <label class="screen-reader-text" for="wvr-markdown"><?php echo esc_html__('Markdown（コピペ用）', 'katze-version-snapshot'); ?></label>
            <textarea id="wvr-markdown" class="wvr-md" readonly spellcheck="false"><?php echo esc_textarea($markdown); ?></textarea>
            <p class="description"><?php echo esc_html__('上記をそのままコピーして、チケットやドキュメントに貼り付けてください。', 'katze-version-snapshot'); ?></p>
        </div>
        <?php
    }
}
