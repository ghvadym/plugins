<?php


class Swish_Repair_Database
{
    static function table_log_install()
    {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table = $wpdb->prefix . SWISH_DB_TABLE_LOG;

        $create_table = "CREATE TABLE IF NOT EXISTS {$table} (
            `id` int NOT NULL AUTO_INCREMENT,
            `order_id` int NOT NULL,
            `type` varchar(500) NULL,
            `log` TEXT NULL,
            `date_created` timestamp NOT NULL  DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) {$charset_collate} ";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($create_table);
    }

    static function manual_table_log_install()
    {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table = $wpdb->prefix . SWISH_DB_MANUAL_PAYMENT_TABLE_LOG;

        $create_table = "CREATE TABLE IF NOT EXISTS {$table} (
            `id` int NOT NULL AUTO_INCREMENT,
            `order_id` varchar(255) NULL,
            `amount` DECIMAL(8,2) UNSIGNED NULL,
            `payment_id` varchar(255) NULL,
            `payer_alias` varchar(255) NULL,
            `status` varchar(255) NULL,
            `message` text NULL,
            `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) {$charset_collate} ";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($create_table);
    }

    static function table_log_uninstall()
    {
        self::drop_table(SWISH_DB_TABLE_LOG);
    }

    static function table_manual_payment_log_uninstall()
    {
        self::drop_table(SWISH_DB_MANUAL_PAYMENT_TABLE_LOG);
    }

    static function drop_table(string $tableWithoutPrefix = '')
    {
        if (!$tableWithoutPrefix) {
            return;
        }

        global $wpdb;
        $table = $wpdb->prefix . $tableWithoutPrefix;

        $query = "DROP TABLE IF EXISTS {$table}";

        $wpdb->query($query);
    }

    static function add_manual_payment_page()
    {
        $swishPage = get_page_by_title(SWISH_MANUAL_ORDER_PAGE_NAME);

        if (!empty($swishPage)) {
            return;
        }

        $args = [
            'post_title'   => SWISH_MANUAL_ORDER_PAGE_NAME,
            'post_content' => '',
            'post_status'  => 'publish',
            'post_type'    => 'page'
        ];

        wp_insert_post(wp_slash($args));
    }

    static function remove_manual_payment_page()
    {
        $swishPage = get_page_by_title(SWISH_MANUAL_ORDER_PAGE_NAME);
        $pageId = $swishPage->ID ?? '';

        if (!$pageId) {
            return;
        }

        wp_delete_post($pageId, true);
    }

    static function remove_options()
    {
        $options = [
            'fixably_api_key',
            'fixably_domain',
            'swish_sandbox_mode',
            'swish_form_process_message',
            'swish_form_paid_message',
            'fixably_login_password',
            'fixably_manual_order_message'
        ];

        foreach ($options as $option) {
            delete_option($option);
        }
    }

    static function remove_payment_links()
    {
        $posts = get_posts([
            'post_type'   => 'payment_link',
            'numberposts' => -1
        ]);

        if (empty($posts)) {
            return;
        }

        foreach ($posts as $post) {
            wp_delete_post($post->ID, true);
        }
    }

    static function clear_after_uninstall()
    {
        self::table_log_uninstall();
        self::table_manual_payment_log_uninstall();
        self::remove_options();
        self::remove_payment_links();
    }
}