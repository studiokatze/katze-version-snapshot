<?php
/**
 * Plugin Name: Katze Version Snapshot
 * Description: 保守やバージョンアップ作業前後の確認用として、WordPressやPHP、プラグインなどの各種バージョン情報を一覧化し、Markdown 表示と CSV での出力ができます。
 * Version: 1.0.0
 * Author: studio katze
 * Author URI:  https://studio-katze.jp
 * Requires at least: 5.4
 * Requires PHP: 7.4
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: katze-version-snapshot
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

define('WVR_FILE', __FILE__);
define('WVR_DIR', plugin_dir_path(WVR_FILE));
define('WVR_URL', plugin_dir_url(WVR_FILE));
define('WVR_VERSION', '1.0.0');

require_once WVR_DIR . 'includes/class-wvr-collector.php';
require_once WVR_DIR . 'includes/class-wvr-exporter.php';
require_once WVR_DIR . 'includes/class-wvr-admin.php';

final class WVR_Report {
    public static function init(): void {
        add_action('admin_menu', [__CLASS__, 'register_menu']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue']);
        add_filter('plugin_action_links_' . plugin_basename(WVR_FILE), [__CLASS__, 'add_plugin_action_links']);

        // CSV export endpoints
        add_action('admin_post_kvs_export_version_snapshot', ['WVR_Exporter', 'export_version_report_csv']);
    }

    public static function register_menu(): void {
        add_management_page(
            __('Version Snapshot', 'katze-version-snapshot'),
            __('Katze Version Snapshot', 'katze-version-snapshot'),
            'manage_options',
            'katze-version-snapshot',
            ['WVR_Admin', 'render_page']
        );
    }

    public static function add_plugin_action_links(array $links): array {
        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            esc_url(admin_url('tools.php?page=katze-version-snapshot')),
            esc_html__('設定', 'katze-version-snapshot')
        );

        array_unshift($links, $settings_link);

        return $links;
    }

    public static function enqueue(string $hook): void {
        if ($hook !== 'tools_page_katze-version-snapshot') {
            return;
        }
        wp_enqueue_style('wvr-admin', WVR_URL . 'assets/admin.css', [], WVR_VERSION);
        wp_enqueue_script('wvr-admin', WVR_URL . 'assets/admin.js', [], WVR_VERSION, true);
    }
}

WVR_Report::init();
