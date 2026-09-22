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

define('KATZEVSN_FILE', __FILE__);
define('KATZEVSN_DIR', plugin_dir_path(KATZEVSN_FILE));
define('KATZEVSN_URL', plugin_dir_url(KATZEVSN_FILE));
define('KATZEVSN_VERSION', '1.0.0');

require_once KATZEVSN_DIR . 'includes/class-katzevsn-collector.php';
require_once KATZEVSN_DIR . 'includes/class-katzevsn-exporter.php';
require_once KATZEVSN_DIR . 'includes/class-katzevsn-admin.php';

final class KATZEVSN_Report {
    public static function init(): void {
        add_action('admin_menu', [__CLASS__, 'register_menu']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue']);
        add_filter('plugin_action_links_' . plugin_basename(KATZEVSN_FILE), [__CLASS__, 'add_plugin_action_links']);

        // CSV export endpoints
        add_action('admin_post_katzevsn_export_version_snapshot', ['KATZEVSN_Exporter', 'export_version_report_csv']);
    }

    public static function register_menu(): void {
        add_management_page(
            __('Version Snapshot', 'katze-version-snapshot'),
            __('Katze Version Snapshot', 'katze-version-snapshot'),
            'manage_options',
            'katze-version-snapshot',
            ['KATZEVSN_Admin', 'render_page']
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
        wp_enqueue_style('katzevsn-admin', KATZEVSN_URL . 'assets/admin.css', [], KATZEVSN_VERSION);
        wp_enqueue_script('katzevsn-admin', KATZEVSN_URL . 'assets/admin.js', [], KATZEVSN_VERSION, true);
    }
}

KATZEVSN_Report::init();
