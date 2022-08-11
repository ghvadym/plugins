<div class="wrap">
    <h1>
        <?php _e('WP Sync Webspark', 'wcapi') ?>
    </h1>

    <?php if (!empty($data)): ?>
        <table class="form-table">
            <tbody>
                <tr>
                    <th>
                        <?php _e('Posts exists', 'webspark') ?>
                    </th>
                    <td>
                        <?php echo $data['posts_exists'] ?? ''; ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php _e('Posts from API', 'webspark') ?>
                    </th>
                    <td>
                        <?php echo $data['posts_from_api'] ?? ''; ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php _e('Posts inserted', 'webspark') ?>
                    </th>
                    <td>
                        <?php echo $data['posts_inserted'] ?? ''; ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php _e('Posts updated', 'webspark') ?>
                    </th>
                    <td>
                        <?php echo $data['posts_updated'] ?? ''; ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php _e('Posts deleted', 'webspark') ?>
                    </th>
                    <td>
                        <?php echo $data['posts_deleted'] ?? ''; ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php _e('Posts now', 'webspark') ?>
                    </th>
                    <td>
                        <?php echo $data['posts_now'] ?? ''; ?>
                    </td>
                </tr>
            </tbody>
        </table>
    <?php endif; ?>

    <a href="/wp-admin/admin.php?page=<?php echo WW_PLUGIN_SLUG; ?>&ww_update_products=true"
       class="button button-primary">
        <?php _e('Update products', 'webspark') ?>
    </a>
</div>