<?php
$fields = get_option('reco_reviews_settings', []);
?>

<div class="wrap">
    <form method="post" action="options.php">
        <?php settings_fields('reco-reviews-settings-group'); ?>
        <h2>
            <?php _e('Reco Reviews Settings', 'reco-reviews'); ?>
        </h2>
        <p>
            <?php _e('Use shortcode', 'reco-reviews'); ?>
            : [reco-reviews]
        </p>
        <p>
            <?php _e('Supported params in shortcode', 'reco-reviews'); ?>
            : count, title
        </p>
        <small>
            <?php _e('Example', 'reco-reviews'); ?>
            : [reco-reviews count="2" title="Title of section"]
        </small>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="reco_api_key">
                        <?php _e('Reco API Key', 'reco-reviews') ?>
                    </label>
                </th>
                <td>
                    <input type="password"
                           class="regular-text"
                           name="reco_reviews_settings[api_key]"
                           id="reco_api_key"
                           value="<?php echo $fields['api_key']; ?>">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="reco_env_id">
                        <?php _e('Reco Venue ID', 'reco-reviews') ?>
                    </label>
                </th>
                <td>
                    <input type="text"
                           class="regular-text"
                           name="reco_reviews_settings[env_id]"
                           id="reco_env_id"
                           value="<?php echo $fields['env_id']; ?>">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="reco_title">
                        <?php _e('Title', 'reco-reviews') ?>
                    </label>
                </th>
                <td>
                    <textarea class="regular-text"
                              name="reco_reviews_settings[title]"
                              id="reco_title"
                              rows="2"><?php echo $fields['title']; ?></textarea>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="reco_count">
                        <?php _e('Count of reviews', 'reco-reviews') ?>
                    </label>
                </th>
                <td>
                    <input type="number"
                           class="regular-text"
                           name="reco_reviews_settings[count]"
                           id="reco_count"
                           value="<?php echo $fields['count']; ?>"
                           min="0"
                           step="1">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="reco_source_url">
                        <?php _e('Source URL', 'reco-reviews') ?>
                    </label>
                </th>
                <td>
                    <input type="text"
                           class="regular-text"
                           name="reco_reviews_settings[source_url]"
                           id="reco_source_url"
                           value="<?php echo $fields['source_url']; ?>">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="reco_logo_url">
                        <?php _e('Logo URL', 'reco-reviews') ?>
                    </label>
                </th>
                <td>
                    <input type="text"
                           class="regular-text"
                           name="reco_reviews_settings[logo]"
                           id="reco_logo_url"
                           value="<?php echo $fields['logo']; ?>">
                </td>
            </tr>
        </table>
        <?php submit_button(); ?>
    </form>
</div>