<?php echo WCAPI_Functions::show_messages(); ?>

<div class="wcapi-main">
    <div class="wcapi-form__wrapper">
        <h2 class="wcapi-form__title">
            <?php _e('Form Registration','wcapi') ?>
        </h2>

        <form method="post" class="wcapi-form">
            <input type="text"
                   name="wcapi_name"
                   class="wcapi-form__row"
                   placeholder="<?php _e('Name','wcapi') ?>"
                   minlength="2"
                   maxlength="30"
                   required
            >
            <input type="email"
                   name="wcapi_email"
                   class="wcapi-form__row"
                   placeholder="<?php _e('Email','wcapi') ?>"
                   minlength="5"
                   maxlength="30"
                   required
            >

            <input type="hidden" name="action" value="wcapi_register">

            <button type="submit"
                    class="wcapi-form__btn button button-primary">
                <?php _e('Submit','wcapi') ?>
            </button>
        </form>
    </div>
</div>
