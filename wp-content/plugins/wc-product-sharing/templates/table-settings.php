<div class="wrap">
    <?php echo WCAPI_Functions::show_messages(); ?>

    <h1>
        <?php _e('WC API Settings', 'wcapi') ?>
    </h1>
    <table class="form-table">
        <tbody>
        <?php if (!empty($options['name'])): ?>
            <tr>
                <th>
                    <?php _e('Username', 'fxevents') ?>
                </th>
                <td>
                    <?php echo $options['name']; ?>
                </td>
            </tr>
        <?php endif; ?>

        <?php if (!empty($options['email'])): ?>
            <tr>
                <th>
                    <?php _e('Email', 'fxevents') ?>
                </th>
                <td>
                    <?php echo $options['email']; ?>
                </td>
            </tr>
        <?php endif; ?>

        <tr>
            <th>
                <?php _e('Update products', 'fxevents') ?>
            </th>
            <td>
                <a href="/wp-admin/admin.php?page=<?php echo WCAPI_PRODUCTS_PAGE; ?>&get_api_products=update">
                    <?php _e('Update', 'wcapi') ?>
                </a>
            </td>
        </tr>

        <tr>
            <th>
                <?php _e('Logout', 'fxevents') ?>
            </th>
            <td>
                <a href="/wp-admin/admin.php?page=<?php echo WCAPI_PRODUCTS_PAGE; ?>&wcapi_user_logout=logout">
                    <?php _e('Logout', 'wcapi') ?>
                </a>
            </td>
        </tr>

        </tbody>
    </table>
</div>