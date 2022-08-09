<?php echo WCAPI_Functions::show_messages(); ?>

<div class="wcapi-main">
    <p>
        <a href="/wp-admin/admin.php?page=<?php echo WCAPI_SETTINGS_PAGE; ?>&wcapi_user_logout=logout">
            <?php _e('Back to previous step','wcapi'); ?>
        </a>
    </p>

    <div class="wcapi-form__wrapper">
        <h2 class="wcapi-form__title">
            <?php _e('Get License','wcapi') ?>
        </h2>
        <p>
            <?php _e('Your request in process. Please, check your mailbox later.','wcapi') ?>
        </p>
        <form method="post" class="wcapi-form">
            <input type="text"
                   name="wcapi_token"
                   class="wcapi-form__row"
                   placeholder="<?php _e('API Key','wcapi') ?>"
                   minlength="10"
                   maxlength="50"
                   required
            >

            <input type="hidden" name="action" value="wcapi_license">

            <button type="submit"
                    class="wcapi-form__btn button button-primary">
                <?php _e('Submit','wcapi') ?>
            </button>
        </form>
    </div>
</div>
