<?php


class WCAPI_Init
{
    protected static array $options = [];

    protected static string $hash = '';

    protected static string $token = '';

    public function __construct()
    {
        self::$options = get_option('wc_api_settings') ?: [];
        self::$hash = self::$options['hash'] ?: '';
        self::$token = self::$options['token'] ?: '';

        add_action('admin_menu', [self::class, 'admin_menu_page']);
        add_action('admin_enqueue_scripts', [self::class, 'admin_scripts']);

        if (!empty(self::$token)) {
            WCAPI_Cron::cron_init();
        }
    }

    static function admin_scripts(): void
    {
        wp_enqueue_style('wc-products-styles', WCAPI_PLUGIN_URL . '/assets/css/app.css', [], time());
    }

    static function wcapi_default_settings(): void
    {
        if (!empty(self::$options)) {
            return;
        }

        WCAPI_Settings::wc_api_settings([
            'hash'  => self::$hash,
            'token' => self::$token,
        ]);
    }

    static function admin_menu_page(): void
    {
        add_menu_page(
            __('WC API', 'wcapi'),
            __('WC API', 'wcapi'),
            'manage_options',
            WCAPI_PRODUCTS_PAGE,
            [self::class, 'admin_menu_page_init'],
            'dashicons-networking',
            20
        );

        add_submenu_page(
            WCAPI_PRODUCTS_PAGE,
            __('Settings', 'wcapi'),
            __('Settings', 'wcapi'),
            'manage_options',
            WCAPI_SETTINGS_PAGE,
            [self::class, 'admin_settings_page_init']
        );

        add_submenu_page(
            WCAPI_PRODUCTS_PAGE,
            __('Demo products', 'wcapi'),
            __('Demo products', 'wcapi'),
            'manage_options',
            WCAPI_DEMO_PRODUCTS_PAGE,
            [self::class, 'admin_demo_page_init']
        );
    }

    static function admin_menu_page_init(): void
    {
        if (!self::$hash || !self::$token) {
            WCAPI_Functions::redirect(WCAPI_SETTINGS_PAGE);
        } else {
            self::admin_products_page();
        }
    }

    static function admin_settings_page_init()
    {
        if (!self::$hash && !self::$token) {
            self::admin_registration_page();
            return;
        }

        if (self::$hash && !self::$token) {
            self::admin_license_page();
            return;
        }

        self::admin_settings_table();
    }

    static function admin_demo_page_init()
    {
        if (!self::$hash || !self::$token) {
            WCAPI_Functions::redirect(WCAPI_SETTINGS_PAGE);
        } else {
            self::api_demo_data();
        }
    }

    static function admin_license_page(): void
    {
        require WCAPI_Functions::get_path('form-license');

        if (!empty($_GET['wcapi_user_logout'])) {
            WCAPI_Functions::wcapi_user_logout(WCAPI_SETTINGS_PAGE);
        }

        $token = $_POST['wcapi_token'] ?? '';

        self::admin_get_products_by_token($token);
    }

    static function admin_get_products_by_token(string $token = ''): void
    {
        if (!$token) {
            return;
        }

        $api = new API_Products($token);
        $result = $api->api_send_response();

        if (!isset($result->status)) {
            return;
        }

        if ($result->status === 404) {
            WCAPI_Functions::clear_settings();
            return;
        }

        WCAPI_Settings::wc_api_settings([
            'token' => base64_encode($token),
        ]);

        WCAPI_Settings::update_api_products($result->products);

        WCAPI_Functions::redirect();
    }

    static function admin_settings_table()
    {
        $options = get_option('wc_api_settings');
        require WCAPI_Functions::get_path('table-settings');
    }

    static function admin_registration_page(): void
    {
        require WCAPI_Functions::get_path('form-register');

        $name = $_POST['wcapi_name'] ?? '';
        $email = $_POST['wcapi_email'] ?? '';

        self::admin_get_user_data($name, $email);
    }

    static function admin_get_user_data(string $name, string $email): void
    {
        if (!$name || !$email) {
            return;
        }

        $api = new API_Auth($name, $email);
        $result = $api->api_send_response();

        if (!isset($result->hash)) {
            return;
        }

        WCAPI_Settings::wc_api_settings([
            'name'  => $name,
            'email' => $email,
            'hash'  => base64_encode($result->hash),
        ]);

        WCAPI_Functions::redirect();
    }

    static function admin_products_page(): void
    {
        global $wpdb;
        $table = $wpdb->prefix . WCAPI_TABLE_POSTS;

        if (WCAPI_Functions::table_not_exists($table)) {
            return;
        }

        self::api_product_list();
    }

    static function api_product_list()
    {
        if (!class_exists('WP_List_Table')) {
            require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
        }

        require WCAPI_Functions::get_path('table-products');
    }

    static function api_demo_data()
    {
        require WCAPI_Functions::get_path('demo-products');

        $formData = $_POST ?? '';

        if (!$formData) {
            return;
        }

        $posts = [];
        $lastProduct = self::get_last_product();
        $demoId = $lastProduct ? substr($lastProduct, strpos($lastProduct, '_') + 1) + 1 : '1';

        $posts[] = array_merge([
            'wc_product_id' => 'demo_' . $demoId,
            'post_content'  => '',
            'post_excerpt'  => '',
            'post_name'     => '',
            'post_type'     => 'product',
        ], $formData);

        self::database_insert_demo_data($posts);
    }

    static function database_insert_demo_data(array $data = [])
    {
        $insert = WCAPI_Settings::update_api_products($data, true);

        if (empty($insert)) {
            return;
        }

        WCAPI_Functions::redirect();

        self::api_form_message('Successful transfer.');
    }

    static function get_last_product()
    {
        global $wpdb;
        $table = $wpdb->prefix . WCAPI_TABLE_POSTS;

        if (WCAPI_Functions::table_not_exists($table)) {
            return false;
        }

        return $wpdb->get_var("SELECT `wc_product_id` FROM {$table} WHERE `wc_product_id` LIKE 'demo_%' ORDER BY `post_date` DESC LIMIT 1");
    }

    static function api_form_message($result): void
    {
        if (!$result) {
            return;
        }

        require WCAPI_Functions::get_path('form-message');
    }
}