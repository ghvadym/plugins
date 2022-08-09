<?php

namespace Includes;

class MainSettings
{
    public static function init()
    {
        add_action('admin_menu', [self::class, 'admin_options_page']);
        add_action('admin_init', [self::class, 'admin_settings_init']);
    }

    static function admin_options_page()
    {
        add_menu_page(
            __('FX Events', 'fxevents'),
            __('FX Events', 'fxevents'),
            'manage_options',
            'fx_events_settings_page',
            [self::class, 'create_admin_page'],
            'dashicons-calendar-alt',
            20
        );
    }

    static function create_admin_page()
    {
        require_once Functions::getPath('admin/admin-page');
    }

    static function admin_settings_init()
    {
        register_setting('fx_event_setting', 'fx_events_setting_option');
        register_setting('fx_event_setting', 'fx_events_post_types_option');

        add_settings_section(
            'fx_event_setting_section',
            __('Flexi Events Settings', 'fxevents'),
            [self::class, 'fx_event_section_body'],
            'fx_events_settings_page'
        );

        self::addFields();
        self::registerCheckboxesFields();
    }

    static function fx_event_section_body()
    {
        echo "Insert shortcode to some field <br><strong>[fx_event_calendar]</strong>";
    }

    static function addFields()
    {
        self::registerField('default_output_format', [
            'type'    => 'radio',
            'name'    => 'output_format',
            'options' => ['month', 'list'],
        ]);
    }

    static function registerField(string $slug, array $args)
    {
        add_settings_field(
            'fx_event_' . $slug,
            Functions::stringReplace($slug),
            [self::class, 'fieldBody'],
            'fx_events_settings_page',
            'fx_event_setting_section',
            $args
        );
    }

    static function registerCheckboxesFields()
    {
        add_settings_field(
            'fx_events_post_types',
            'Select post types',
            [self::class, 'addPostTypeFields'],
            'fx_events_settings_page',
            'fx_event_setting_section'
        );
    }

    static function fieldBody(array $args)
    {
        $fields = get_option('fx_events_setting_option');

        switch ($args['type']) {
            case 'checkbox':
                self::fieldTypeCheckbox($fields, $args);
                break;
            case 'radio':
                self::fieldTypeRadio($fields, $args);
                break;
            default:
                self::fieldTypeText($fields, $args);
        }
    }

    static function fieldTypeText($fields, $args)
    {
        $value = $fields[$args['name']] ?? null;
        ?>

        <input type="<?php echo $args['type'] ?>"
               name="fx_events_setting_option[<?php echo $args['name'] ?>]"
               id="<?php echo $args['name'] ?>"
               value="<?php echo $value ?? '' ?>"
        >

        <?php
    }

    static function fieldTypeCheckbox($fields, $args)
    {
        $value = $fields[$args['name']] ?? null;
        ?>

        <input type="<?php echo $args['type'] ?>"
               name="fx_events_setting_option[<?php echo $args['name'] ?>]"
               id="<?php echo $args['name'] ?>"
            <?php checked(in_array($value, $fields)) ?>
        >

        <?php
    }

    static function fieldTypeRadio($fields, $args)
    {
        if (empty($args['options'])) {
            return;
        }

        $i = 0;
        foreach ($args['options'] as $name) {
            $checked = $fields[$args['name']] ? $fields[$args['name']] == $name : $i == 0; ?>

            <div>
                <input type="<?php echo $args['type'] ?>"
                       name="fx_events_setting_option[<?php echo $args['name'] ?>]"
                       id="<?php echo 'fx_event_choice_' . $name ?>"
                       value="<?php echo $name ?>"
                       <?php checked($checked); ?>
                >
                <label for="<?php echo 'fx_event_choice_' . $name ?>">
                    <?php echo esc_html($name); ?>
                </label>
            </div>

        <?php $i++;
        }
    }

    static function addPostTypeFields()
    {
        $args = [
            'public'   => true,
            '_builtin' => false,
        ];
        $post_types = get_post_types($args);

        if (empty($post_types)) {
            echo '<p>' . __('Public custom post types not found.') . '</p>';
            return;
        }

        foreach ($post_types as $post_type) {
            self::checkboxField($post_type);
        }
    }

    static function checkboxField($name)
    {
        $checkedFields = get_option('fx_events_post_types_option');
        ?>

        <div>
            <input type="checkbox"
                   id="<?php echo esc_attr('post_type_' . $name); ?>"
                   name="fx_events_post_types_option[]"
                   value="<?php echo esc_attr($name); ?>"
                <?php checked(in_array($name, $checkedFields)); ?>
            />
            <label for="<?php echo esc_attr('post_type_' . $name); ?>">
                <?php echo esc_html($name); ?>
            </label>
        </div>

        <?php
    }
}