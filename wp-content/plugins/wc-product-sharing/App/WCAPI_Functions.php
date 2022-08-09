<?php


class WCAPI_Functions
{
    static function get_path(string $fileName): string
    {
        $pathToFile = WCAPI_PLUGIN_PATH . "templates/{$fileName}.php";

        if (!file_exists($pathToFile)) {
            return '';
        }

        return $pathToFile;
    }

    static function redirect(string $page = '', string $status = ''): void
    {
        if (!$page) {
            $page = WCAPI_PRODUCTS_PAGE;
        }

        $path = 'admin.php?page=' . $page;

        if ($status) {
            $path .= '&status=' . $status;
        }

        wp_redirect(admin_url($path));
    }

    static function table_truncate(string $tableName = '', bool $removeDemo = true): void
    {
        global $wpdb;
        $table = $wpdb->prefix . WCAPI_TABLE_POSTS;

        if ($tableName) {
            $table = $wpdb->prefix . $tableName;
        }

        if (self::table_not_exists($table)) {
            return;
        }

        $query = "DELETE FROM {$table}";

        if (!$removeDemo) {
            $query .= " WHERE `wc_product_id` NOT LIKE 'demo_%'";
        }

        $wpdb->query($query);
    }

    static function get_token(): string
    {
        $options = get_option('wc_api_settings');

        if (!$options) {
            return '';
        }

        $token = $options['token'] ?? '';

        if (!$token) {
            return '';
        }

        return base64_decode($token);
    }

    static function get_hash()
    {
        $options = get_option('wc_api_settings');

        if (!$options) {
            return '';
        }

        $hash = $options['hash'] ?? '';

        if (!$hash) {
            return '';
        }

        return base64_decode($hash);
    }

    static function table_not_exists(string $table = ''): bool
    {
        global $wpdb;

        if (!$table) {
            $table = $wpdb->prefix . WCAPI_TABLE_POSTS;
        }

        return !$wpdb->get_var("SHOW TABLES LIKE '" . $table . "'");
    }

    static function show_messages()
    {
        $action = $_REQUEST['action'] ?? '';

        if (!$action) {
            return false;
        }

        $text = '<div id="message" class="updated notice is-dismissible"><p>%s</p></div>';

        switch ($_REQUEST['action']) {
            case 'wcapi_register':
                return sprintf($text, __('Something wrong with registration. Try to type another values.'));
            case 'wcapi_license':
                return sprintf($text, __('Token is not a valid.'));
        }

        return false;
    }

    static function clear_settings(string $pageRedirect = '')
    {
        self::table_truncate();

        WCAPI_Settings::wc_api_settings([
            'hash'  => '',
            'token' => '',
            'email' => '',
            'name'  => '',
        ]);

        self::redirect($pageRedirect);
    }

    static function wcapi_user_logout(string $page = '')
    {
        $hash = self::get_hash();

        if (!$hash) {
            return;
        }

        $unauth = new API_Unauth($hash);
        $result = $unauth->api_send_response();

        if (!isset($result->status)) {
            return;
        }

        self::clear_settings($page);
    }

    static function curl_array($url, $headers = [], $header = 0, $postFields = []): array
    {
        if (!$url) {
            return [];
        }

        $params = [
            CURLOPT_URL            => $url,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_RETURNTRANSFER => 1
        ];

        if (!empty($headers)) {
            $params += [
                CURLOPT_HTTPHEADER => $headers
            ];
        }

        if ($header) {
            $params += [
                CURLOPT_HEADER => 1
            ];
        }

        if (!empty($postFields)) {
            $params += [
                CURLOPT_POSTFIELDS => $postFields
            ];
        }

        return $params;
    }
}