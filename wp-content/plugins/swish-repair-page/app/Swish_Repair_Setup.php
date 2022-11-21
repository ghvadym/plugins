<?php


class Swish_Repair_Setup
{
    public function __construct()
    {
        self::filters();
        self::actions();
        self::shortcodes();

        Swish_Repair_Cron::cron_init();
        Swish_Repair_Ajax::init();
    }

    static function actions()
    {
        add_action('wp_enqueue_scripts', [self::class, 'enqueue_scripts_call']);
        add_action('init', [self::class, 'init_call']);
    }

    static function filters()
    {
        add_filter('cron_schedules', [self::class, 'cron_add_schedules']);
        add_filter('page_template', [self::class, 'page_template']);
    }

    static function shortcodes()
    {
        add_shortcode('swish_rp_shortcode', [self::class, 'shortcode_manual_link_handle']);
        add_shortcode('swish_rp_payment_link_shortcode', [self::class, 'shortcode_payment_link_handle']);
    }

    static function enqueue_scripts_call()
    {
        wp_register_script('swish_rp_script', SWISH_RP_URL . 'assets/js/swish_rp_js.js', ['jquery'], time(), true);

        //self::bootstrap_scripts();

        wp_enqueue_style('swish_rp_styles', SWISH_RP_URL . 'assets/css/styles.css');
        wp_localize_script('swish_rp_script', 'swishRP', ['ajaxurl' => admin_url('admin-ajax.php')]);
        wp_enqueue_script('swish_rp_script');
    }

    static function bootstrap_scripts()
    {
        $swishPage = get_page_by_title(SWISH_MANUAL_ORDER_PAGE_NAME);
        $pageId = $swishPage->ID ?? '';

        if (is_singular('payment_link') || ($pageId && is_page($pageId))) {
            wp_enqueue_style('bootstrap', '//stackpath.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css');
        }
    }

    static function init_call()
    {
        self::custom_post_types();
        Swish_Repair_Admin::init();
    }

    static function page_template($page_template)
    {
        $swishPage = get_page_by_title(SWISH_MANUAL_ORDER_PAGE_NAME);
        $pageId = $swishPage->ID ?? '';

        if ($pageId && is_page($pageId)) {
            return Swish_Repair_Functions::get_path('manual-payment-page');
        }

        return $page_template;
    }

    static function custom_post_types()
    {
        $supports = [
            'title',
            'editor',
            'revisions',
            'custom-fields',
        ];

        $labels = [
            'name'               => esc_html__('Payment Links', 'swish-rp'),
            'singular_name'      => esc_html__('Payment Link', 'swish-rp'),
            'menu_name'          => esc_html__('Payment Links', 'swish-rp'),
            'parent_item_colon'  => esc_html__('Parent Payment Link', 'swish-rp'),
            'all_items'          => esc_html__('All Payment Links', 'swish-rp'),
            'add_new'            => esc_html__('Add New', 'swish-rp'),
            'add_new_item'       => esc_html__('Add New Payment Link', 'swish-rp'),
            'edit_item'          => esc_html__('Edit Payment Link', 'swish-rp'),
            'new_item'           => esc_html__('New Payment Link', 'swish-rp'),
            'view_item'          => esc_html__('View Payment Link ', 'swish-rp'),
            'search_items'       => esc_html__('Search Payment Link', 'swish-rp'),
            'not_found'          => esc_html__('Not Found', 'swish-rp'),
            'not_found_in_trash' => esc_html__('Not found in Trash', 'swish-rp'),
        ];

        $args = [
            'labels'              => $labels,
            'description'         => esc_html__('Payment Links', 'swish-rp'),
            'public'              => true,
            'publicly_queryable'  => true,
            'exclude_from_search' => false,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'query_var'           => true,
            'show_in_admin_bar'   => true,
            'capability_type'     => 'post',
            'taxonomies'          => [],
            'has_archive'         => true,
            'hierarchical'        => true,
            'menu_position'       => null,
            'menu_icon'           => 'dashicons-list-view',
            'rewrite'             => [
                'slug'       => esc_attr__('payment', 'swish-rp'),
                'with_front' => false,
            ],
            'supports'            => $supports,
        ];

        register_post_type('payment_link', $args);

        if (!get_option("swish_rp_flushed_rewrite_rule", 0)) {
            flush_rewrite_rules();
            update_option("swish_rp_flushed_rewrite_rule", 1);
        }
    }

    static function cron_add_schedules($schedules)
    {
        $schedules['five_min'] = [
            'interval' => 60 * 5,
            'display'  => __('Every 5 minutes', 'swish-rp'),
        ];

        return $schedules;
    }

    static function shortcode_payment_link_handle($atts)
    {
        $post_id = get_the_ID();

        $data = shortcode_atts([
            'amount' => get_post_meta($post_id, 'amount_remaining', true),
            'order'  => get_post_meta($post_id, 'order_id', true),
            'id'     => $post_id
        ], $atts);

        ob_start();
        self::swish_payment_template($data);
        return ob_get_clean();
    }

    static function swish_payment_template(array $data = [])
    {
        if (empty($data)) {
            return;
        }

        Swish_Repair_Ajax::swish_payment_result_process($data['id']);

        $orderId = $_GET['order'] ?? '';
        $swishPayment = $_GET['swish_payment'] ?? '';

        if ($swishPayment === 'success' || $orderId) {
            echo '<div class="swish_payment_message sf-preloader" data-id="'.$data['id'].'"></div>';
            return;
        }

        $paymentStatus = Swish_Repair_Functions::swish_process_status($data['id']);
        if ($paymentStatus) {
            echo '<div class="swish_payment_message">'. $paymentStatus .'</div>';
            return;
        }

        include Swish_Repair_Functions::get_path('payment-form');
    }
}