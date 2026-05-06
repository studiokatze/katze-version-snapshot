<?php
if (!defined('ABSPATH')) {
    exit;
}

class WVR_Collector {
    private static ?array $summary_cache = null;
    private static ?array $plugins_cache = null;

    public static function get_summary(): array {
        if (self::$summary_cache !== null) {
            return self::$summary_cache;
        }

        global $wpdb;

        $wp_version  = get_bloginfo('version');
        $php_version = PHP_VERSION;

        $db_version = $wpdb->db_version();
        $engine = self::detect_db_engine($wpdb);

        $theme   = wp_get_theme();
        $parent   = $theme->parent();
        $is_child = (bool) $parent;

        self::$summary_cache = [
            'wordpress' => ['version' => $wp_version],
            'php'       => ['version' => $php_version],
            'database'  => ['engine' => $engine, 'version' => $db_version],
            'theme'     => [
                'name'           => $theme->get('Name'),
                'version'        => $theme->get('Version'),
                'is_child'       => $is_child,
                'parent_name'    => $is_child ? $parent->get('Name') : '',
                'parent_version' => $is_child ? $parent->get('Version') : '',
            ],
        ];

        return self::$summary_cache;
    }

    private static function detect_db_engine(\wpdb $wpdb): string {
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Core does not expose the DB engine/version; this single read is cached in-memory for the request.
        $version_string = (string) $wpdb->get_var('SELECT VERSION()');

        return stripos($version_string, 'mariadb') !== false ? 'MariaDB' : 'MySQL';
    }

    public static function get_plugins(): array {
        if (self::$plugins_cache !== null) {
            return self::$plugins_cache;
        }

        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $all = get_plugins();
        $items = [];

        foreach ($all as $plugin_file => $data) {
            $name    = $data['Name'] ?? $plugin_file;
            $version = $data['Version'] ?? '';
            $active  = is_plugin_active($plugin_file);
            $network = is_multisite() ? is_plugin_active_for_network($plugin_file) : false;
            $status  = __('Inactive', 'katze-version-snapshot');
            if ($network) {
                $status = __('Network Active', 'katze-version-snapshot');
            } elseif ($active) {
                $status = __('Active', 'katze-version-snapshot');
            }

            $items[] = [
                'name'    => $name,
                'version' => $version,
                'status'  => $status,
                'slug'    => $plugin_file,
            ];
        }

        usort($items, fn($a, $b) => strcasecmp($a['name'], $b['name']) ?: strcasecmp($a['slug'], $b['slug']));

        self::$plugins_cache = $items;

        return self::$plugins_cache;
    }

    public static function build_markdown(array $summary, array $plugins): string {
        $md  = '# ' . self::esc_md(__('サイト環境（最小）', 'katze-version-snapshot')) . "\n\n";

        $md .= '## ' . self::esc_md(__('コア・ランタイム', 'katze-version-snapshot')) . "\n\n";
        $md .= '| ' . self::esc_md(__('項目', 'katze-version-snapshot')) . ' | ' . self::esc_md(__('値', 'katze-version-snapshot')) . " |\n|---|---|\n";
        foreach (self::get_environment_rows($summary) as $row) {
            $md .= self::md_row($row['markdown_label'], $row['value']);
        }

        $md .= "\n## " . self::esc_md(__('テーマ', 'katze-version-snapshot')) . "\n\n";
        $md .= '| ' . self::esc_md(__('項目', 'katze-version-snapshot')) . ' | ' . self::esc_md(__('値', 'katze-version-snapshot')) . " |\n|---|---|\n";
        foreach (self::get_theme_rows($summary) as $row) {
            $md .= self::md_row($row['markdown_label'], $row['value']);
        }

        $md .= "\n## " . self::esc_md(__('プラグイン一覧', 'katze-version-snapshot')) . "\n\n";
        $md .= '| ' . self::esc_md(__('Name', 'katze-version-snapshot')) . ' | ' . self::esc_md(__('Version', 'katze-version-snapshot')) . ' | ' . self::esc_md(__('Status', 'katze-version-snapshot')) . " |\n|---|---|---|\n";
        foreach ($plugins as $p) {
            $md .= self::md_plugin_row($p['name'], $p['version'], $p['status']);
        }

        return $md;
    }

    /**
     * @return array<int, array{markdown_label:string, export_label:string, value:string}>
     */
    public static function get_environment_rows(array $summary): array {
        return [
            [
                'markdown_label' => __('WordPress', 'katze-version-snapshot'),
                'export_label' => __('WordPress', 'katze-version-snapshot'),
                'value' => $summary['wordpress']['version'],
            ],
            [
                'markdown_label' => __('PHP', 'katze-version-snapshot'),
                'export_label' => __('PHP', 'katze-version-snapshot'),
                'value' => $summary['php']['version'],
            ],
            [
                'markdown_label' => __('Database Engine', 'katze-version-snapshot'),
                'export_label' => __('Database Engine', 'katze-version-snapshot'),
                'value' => $summary['database']['engine'],
            ],
            [
                'markdown_label' => __('Database Version', 'katze-version-snapshot'),
                'export_label' => __('Database Version', 'katze-version-snapshot'),
                'value' => $summary['database']['version'],
            ],
        ];
    }

    /**
     * @return array<int, array{markdown_label:string, export_label:string, value:string}>
     */
    public static function get_theme_rows(array $summary): array {
        $rows = [
            [
                'markdown_label' => __('有効テーマ', 'katze-version-snapshot'),
                'export_label' => __('Active Theme', 'katze-version-snapshot'),
                'value' => $summary['theme']['name'],
            ],
            [
                'markdown_label' => __('バージョン', 'katze-version-snapshot'),
                'export_label' => __('Version', 'katze-version-snapshot'),
                'value' => $summary['theme']['version'],
            ],
            [
                'markdown_label' => __('子テーマ？', 'katze-version-snapshot'),
                'export_label' => __('Child Theme?', 'katze-version-snapshot'),
                'value' => $summary['theme']['is_child'] ? __('Yes', 'katze-version-snapshot') : __('No', 'katze-version-snapshot'),
            ],
        ];

        if ($summary['theme']['is_child']) {
            $rows[] = [
                'markdown_label' => __('親テーマ', 'katze-version-snapshot'),
                'export_label' => __('Parent Theme', 'katze-version-snapshot'),
                'value' => $summary['theme']['parent_name'],
            ];
            $rows[] = [
                'markdown_label' => __('親テーマバージョン', 'katze-version-snapshot'),
                'export_label' => __('Parent Version', 'katze-version-snapshot'),
                'value' => $summary['theme']['parent_version'],
            ];
        }

        return $rows;
    }

    private static function md_row(string $key, string $value): string {
        return '| ' . self::esc_md($key) . ' | ' . self::esc_md($value) . " |\n";
    }

    private static function md_plugin_row(string $name, string $version, string $status): string {
        return '| ' . self::esc_md($name) . ' | ' . self::esc_md($version) . ' | ' . self::esc_md($status) . " |\n";
    }

    private static function esc_md(string $s): string {
        $s = str_replace(["\r\n", "\r", "\n"], ' ', $s);
        $s = str_replace(['\\', '|', '`', '*', '_', '[', ']'], ['\\\\', '\|', '\`', '\*', '\_', '\[', '\]'], $s);
        return $s;
    }
}
