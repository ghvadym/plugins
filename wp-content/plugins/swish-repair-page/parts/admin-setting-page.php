<div class="wrap">
    <h1><?php _e('Settings', 'swish-rp') ?></h1>
    <small><?php _e('You need Fixable API Key to plugin to work', 'swish-rp') ?></small>

    <form method="post" action="options.php">
        <?php settings_fields('swish-rp-settings-group'); ?>
        <?php do_settings_sections('swish-rp-settings-group'); ?>
        <h2><?php _e('Fixably API', 'swish-rp') ?></h2>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="fixably_api_key">
                        <?php _e('API KEY', 'swish-rp') ?>
                    </label>
                </th>
                <td>
                    <input type="password"
                           class="regular-text"
                           name="fixably_api_key"
                           id="fixably_api_key"
                           value="<?php echo esc_attr(get_option('fixably_api_key')); ?>">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="fixably_domain">
                        <?php _e('Domain', 'swish-rp') ?>
                    </label>
                </th>
                <td>
                    <input type="text"
                           class="regular-text"
                           name="fixably_domain"
                           id="fixably_domain"
                           value="<?php echo esc_attr(get_option('fixably_domain')); ?>">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="fixably_writer_id">
                        <?php _e('Writer ID', 'swish-rp') ?>
                    </label>
                </th>
                <td>
                    <input type="number"
                           class="regular-text"
                           name="fixably_writer_id"
                           id="fixably_writer_id"
                           min="0"
                           value="<?php echo esc_attr(get_option('fixably_writer_id')); ?>">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="fixably_login_password">
                        <?php _e('Manual order login password', 'swish-rp') ?>
                    </label>
                </th>
                <td>
                    <input type="password"
                           class="regular-text"
                           name="fixably_login_password"
                           id="fixably_login_password"
                           value="<?php echo get_option('fixably_login_password'); ?>">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="fixably_manual_order_message"></label>
                    <?php _e('Manual order message', 'swish-rp') ?>
                </th>
                <td>
                    <?php
                    wp_editor(
                        get_option('fixably_manual_order_message'),
                        'fixably_manual_order_message',
                        [
                            'textarea_name' => 'fixably_manual_order_message',
                            'textarea_rows' => 8,
                            'media_buttons' => false
                        ]
                    ); ?>
                </td>
            </tr>
        </table>


        <h2><?php _e('SWISH API', 'swish-rp') ?></h2>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="swish_payee_alias">
                        <?php _e('Payee alias', 'swish-rp') ?>
                    </label>
                </th>
                <td>
                    <input type="text"
                           class="regular-text"
                           name="swish_payee_alias"
                           id="swish_payee_alias"
                           value="<?php echo esc_attr(get_option('swish_payee_alias')); ?>">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="swish_sandbox_mode">
                        <?php _e('Sandbox Mode', 'swish-rp') ?>
                    </label>
                </th>
                <td>
                    <input type="checkbox"
                           class="regular-text"
                           name="swish_sandbox_mode"
                           id="swish_sandbox_mode"
                           value="1"
                        <?= (get_option('swish_sandbox_mode') == 1 ? "checked" : ""); ?>>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="swish_form_process_message"></label>
                    <?php _e('Form process message', 'swish-rp') ?>
                </th>
                <td>
                    <?php
                    wp_editor(
                        get_option('swish_form_process_message'),
                        'swish_form_process_message',
                        [
                            'textarea_name' => 'swish_form_process_message',
                            'textarea_rows' => 8,
                            'media_buttons' => false
                        ]
                    ); ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="swish_form_paid_message"></label>
                    <?php _e('Form paid message', 'swish-rp') ?>
                </th>
                <td>
                    <?php
                    wp_editor(
                        get_option('swish_form_paid_message'),
                        'swish_form_paid_message',
                        [
                            'textarea_name' => 'swish_form_paid_message',
                            'textarea_rows' => 8,
                            'media_buttons' => false
                        ]
                    ); ?>
                </td>
            </tr>
        </table>
        <?php submit_button(); ?>
    </form>
</div>