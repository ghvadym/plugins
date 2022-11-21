<?php


class Swish_Repair_Admin
{
    static function init()
    {
        add_action('admin_menu', [self::class, 'crete_settings_page']);
        add_action('admin_init', [self::class, 'register_plugin_settings']);
        add_action('add_meta_boxes', [self::class, 'register_meta_boxes']);

        add_filter('plugin_action_links', [self::class, 'add_plugin_link'], 10, 2);
    }

    static function crete_settings_page()
    {
        add_options_page(
            __('Payment Links Settings'),
            __('Payment Links Settings'),
            'manage_options',
            'swish-rp-setting',
            [self::class, 'settings_page_call']
        );
    }

    static function register_plugin_settings()
    {
        $settings = [
            'fixably_api_key',
            'fixably_domain',
            'fixably_writer_id',
            'swish_sandbox_mode',
            'swish_payee_alias',
            'swish_form_process_message',
            'swish_form_paid_message',
            'fixably_login_password',
            'fixably_manual_order_message'
        ];

        foreach ($settings as $setting) {
            register_setting('swish-rp-settings-group', $setting);
        }
    }

    static function settings_page_call()
    {
        include Swish_Repair_Functions::get_path('admin-setting-page');
    }

    static function add_plugin_link($plugin_actions, $plugin_file)
    {
        $new_actions = [];

        if ('swish-repair-page/swish-repair-page.php' === $plugin_file) {
            $new_actions['cl_settings'] = sprintf(__('<a href="%s">Settings</a>', 'swish-rp'), esc_url(admin_url('options-general.php?page=swish-rp-setting')));
        }

        return array_merge($new_actions, $plugin_actions);
    }

    static function register_meta_boxes()
    {
        add_meta_box(
            'payment-link-logs',
            __('Activity Logs', 'swish-rp'),
            [self::class, 'payment_link_display_call'],
            'payment_link',
            'advanced',
            'high'
        );
    }

    static function payment_link_display_call($post)
    {
        $order_id = get_post_meta($post->ID, "order_id", true);
        if (!$order_id) {
            return;
        }

        $logs = Swish_Repair_Log::get_logs($order_id);
        if (empty($logs)) {
            return;
        }

        foreach ($logs as $log) { ?>
            <div>
                <strong><?php echo esc_html($log->type); ?></strong>
                <span><?php echo esc_html($log->date_created); ?></span>:
                <label for=""><?php echo esc_html($log->log); ?></label>
            </div>
            <?php
        }
    }
}