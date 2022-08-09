<?php

namespace Includes;

class Metabox
{
    public static function init()
    {
        add_action('add_meta_boxes', [self::class, 'add_metabox']);
        add_action('save_post', [self::class, 'save_meta'], 10, 2);
    }

    static function add_metabox()
    {
        add_meta_box(
            'fx-events-metabox',
            __('Events Settings', 'fxevents'),
            [self::class, 'metabox_html'],
            get_option('fx_events_post_types_option')
        );
    }

    public static function metaboxFields() : array
    {
        $fields = [
            'fx_event__title',
            'fx_event__subtitle',
            'fx_event__date',
            'fx_event__time',
            'fx_event__content'
        ];

        return apply_filters('fx_metabox_fields', $fields);
    }

    public static function metabox_html($post)
    {
        $data = self::get_meta($post);
        wp_nonce_field('fx_events_fields', 'fxevents');
        require Functions::getPath('metabox-tab');
    }

    public static function get_meta($post) : array
    {
        $metaFields = self::metaboxFields();
        $fieldsData = [];
        foreach ($metaFields as $field) {
            $meta = get_post_meta($post->ID, $field, true);
            $fieldsData[$field] = $meta;
        }
        return $fieldsData;
    }

    public static function save_meta($post_id, $post)
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return $post_id;
        }

        $metaFields = self::metaboxFields();
        foreach ($metaFields as $field) {
            if (!isset($_POST[$field])) {
                return $post_id;
            }
            update_post_meta(
                $post_id,
                $field,
                sanitize_text_field($_POST[$field])
            );
        }
        return $post_id;
    }
}