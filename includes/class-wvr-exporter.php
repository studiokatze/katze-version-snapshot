<?php
if (!defined('ABSPATH')) {
    exit;
}

class WVR_Exporter {

    public static function export_version_report_csv(): void {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Forbidden', 'katze-version-snapshot'));
        }
        check_admin_referer('kvs_export_version_snapshot');

        $summary = WVR_Collector::get_summary();
        $plugins = WVR_Collector::get_plugins();

        $domain = (string) wp_parse_url(home_url('/'), PHP_URL_HOST);
        $filename = sanitize_file_name($domain . '-katze-version-snapshot-' . wp_date('Ymd-His') . '.csv');
        self::csv_headers($filename);

        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- Writing CSV to php://output requires a PHP stream handle.
        $out = fopen('php://output', 'w');
        if ($out === false) {
            wp_die(esc_html__('Failed to open CSV output.', 'katze-version-snapshot'));
        }

        self::write_csv($out, $summary, $plugins);

        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Closing the php://output stream handle opened above.
        fclose($out);
        exit;
    }

    /**
     * Test helper for asserting generated CSV content without sending headers or exiting.
     */
    public static function get_csv_content(array $summary, array $plugins): string {
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- Tests use a temporary PHP stream to capture generated CSV.
        $out = fopen('php://temp', 'w+');
        if ($out === false) {
            // Test helper fallback; callers treat an empty string as a failure.
            return '';
        }

        self::write_csv($out, $summary, $plugins);
        rewind($out);
        $csv = stream_get_contents($out);
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Closing the php://temp stream handle opened above.
        fclose($out);

        return $csv === false ? '' : $csv;
    }

    private static function csv_headers(string $filename): void {
        nocache_headers();
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
    }

    /**
     * @param resource $out Writable stream handle.
     */
    private static function write_csv($out, array $summary, array $plugins): void {
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite -- Writing UTF-8 BOM to the active CSV stream.
        fwrite($out, "\xEF\xBB\xBF");

        self::put_csv_row($out, [__('Item', 'katze-version-snapshot'), __('Value', 'katze-version-snapshot')]);
        foreach (WVR_Collector::get_environment_rows($summary) as $row) {
            self::put_csv_row($out, [$row['export_label'], self::sanitize_csv_cell($row['value'])]);
        }

        self::put_csv_row($out, []);
        self::put_csv_row($out, [__('Theme', 'katze-version-snapshot'), '']);
        foreach (WVR_Collector::get_theme_rows($summary) as $row) {
            self::put_csv_row($out, [$row['export_label'], self::sanitize_csv_cell($row['value'])]);
        }

        self::put_csv_row($out, []);
        self::put_csv_row($out, [__('Plugins', 'katze-version-snapshot')]);
        self::put_csv_row($out, [__('Name', 'katze-version-snapshot'), __('Version', 'katze-version-snapshot'), __('Status', 'katze-version-snapshot'), __('Slug', 'katze-version-snapshot')]);
        foreach ($plugins as $p) {
            self::put_csv_row(
                $out,
                [
                    self::sanitize_csv_cell($p['name']),
                    self::sanitize_csv_cell($p['version']),
                    self::sanitize_csv_cell($p['status']),
                    self::sanitize_csv_cell($p['slug']),
                ]
            );
        }
    }

    /**
     * @param resource $out Writable stream handle.
     */
    private static function put_csv_row($out, array $fields): void {
        fputcsv($out, $fields, ',', '"', '\\');
    }

    private static function sanitize_csv_cell(string $value): string {
        $value = preg_replace("/[\r\n]+/", ' ', $value) ?? '';

        if ($value !== '' && preg_match('/^[=+\-@]/', $value)) {
            return "'" . $value;
        }

        return $value;
    }
}
