<?php


class Reco_Setup
{
    public function __construct()
    {
        self::actions();
        self::filters();
        self::shortcodes();
    }

    public static function actions()
    {
        add_action('wp_enqueue_scripts', [self::class, 'enqueue_scripts_call']);
        add_action('admin_menu', [self::class, 'crete_settings_page']);
        add_action('admin_init', [self::class, 'register_plugin_settings']);
    }

    public static function filters()
    {
        add_filter('acf/format_value/type=text', 'do_shortcode');
        add_filter('plugin_action_links_reco-reviews/reco-reviews.php', [self::class, 'add_plugin_link']);
    }

    public static function shortcodes()
    {
        add_shortcode('reco-reviews', [self::class, 'reco_reviews_slider_callback']);
    }

    public static function enqueue_scripts_call()
    {
        wp_register_script('reco_swiper_scripts', RECO_PLUGIN_URL . 'lib/swiper-slider/swiper-slider.js', [], time(), true);
        wp_register_script('reco_script', RECO_PLUGIN_URL . 'assets/js/app.js', ['jquery', 'reco_swiper_scripts'], time(), true);
        wp_enqueue_script('reco_script');

        wp_enqueue_style('reco_styles', RECO_PLUGIN_URL . 'assets/css/app.css');
        wp_enqueue_style('reco_swiper_styles', RECO_PLUGIN_URL . 'lib/swiper-slider/swiper-slider.css');

        wp_localize_script('reco_script', 'reco_ajax', [
            'ajaxurl' => admin_url('admin-ajax.php'),
        ]);
    }

    static function crete_settings_page()
    {
        add_options_page(
            __('Reco Reviews', 'reco-reviews'),
            __('Reco Reviews', 'reco-reviews'),
            'manage_options',
            'reco-reviews',
            [self::class, 'settings_page_call']
        );
    }

    static function register_plugin_settings()
    {
        register_setting('reco-reviews-settings-group', 'reco_reviews_settings');
    }

    static function settings_page_call()
    {
        include Reco_Functions::get_path('reco-reviews-admin');
    }

    public static function reco_reviews_slider_callback($atts)
    {
        $setting_fields = get_option('reco_reviews_settings', []);
        $data = self::reco_reviews_slider_prepare_data($atts, $setting_fields);

        ob_start();

        do_action('reco_reviews_action_before');
        include Reco_Functions::get_path('reco-reviews');
        do_action('reco_reviews_action_after');

        return ob_get_clean();
    }

    public static function reco_reviews_slider_prepare_data($atts = [], $setting_fields = [])
    {
        $reviews_call = Reco_API::call('reviews');
        $reviews = $reviews_call->reviews ?? [];

        if ($reviews_call->status != 200 || empty($reviews)) {
            return [];
        }

        $total_items = count($reviews);
        $ratings = array_column($reviews, 'rating');
        $rating_avg = array_sum($ratings) / $total_items;
        $setting_fields = get_option('reco_reviews_settings', []);

        if (!empty($atts['count'])) {
            $reviews = array_slice($reviews, 0, $atts['count']);
        } else if (!empty($setting_fields['count'])) {
            $reviews = array_slice($reviews, 0, $setting_fields['count']);
        }

        if (!empty($atts['title'])) {
            $title = $atts['title'];
        } else if (!empty($setting_fields['title'])) {
            $title = $setting_fields['title'];
        } else {
            $title = __('Kunder ger oss toppbetyg!', 'reco-reviews');
        }

        return shortcode_atts([
            'rating_avg'  => $rating_avg,
            'total_items' => $total_items,
            'title'       => $title,
            'source_url'  => !empty($setting_fields['source_url']) ? $setting_fields['source_url'] : 'https://www.reco.se/spolpatrullen-ab/',
            'logo_url'    => !empty($setting_fields['logo']) ? $setting_fields['logo'] : 'https://spolpatrullen.se/wp-content/uploads/2023/01/reco.svg',
            'items'       => $reviews,
        ], $atts);
    }

    static function add_plugin_link($actions)
    {
        if (current_user_can('manage_options')) {
            $actions[] = sprintf(
                '<a href="%1$s">%2$s</a>',
                admin_url('options-general.php?page=reco-reviews'),
                __('Settings', 'reco-reviews')
            );
        }

        return $actions;
    }
}