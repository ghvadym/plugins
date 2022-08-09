<div class="wcapi-main">
    <div class="wcapi-form__wrapper">
        <h2 class="wcapi-form__title">
            <?php _e('Set demo product', 'wcapi'); ?>
        </h2>
        <form method="post" class="wcapi-form">
            <input type="text"
                   name="post_title"
                   class="wcapi-form__row"
                   placeholder="<?php _e('Title','wcapi') ?>"
                   minlength="1"
                   maxlength="30"
                   required
            >
            <input type="number"
                   name="regular_price"
                   class="wcapi-form__row"
                   placeholder="<?php _e('Regular price','wcapi') ?>"
                   step="10"
                   min="0"
            >
            <input type="number"
                   name="sale_price"
                   class="wcapi-form__row"
                   placeholder="<?php _e('Sale price','wcapi') ?>"
                   step="10"
                   min="0"
            >
            <input type="number"
                   name="sku"
                   class="wcapi-form__row"
                   placeholder="<?php _e('SKU','wcapi') ?>"
                   step="1"
                   min="0"
            >
            <button type="submit"
                    class="wcapi-form__btn button button-primary">
                <?php _e('Set product','wcapi') ?>
            </button>
        </form>
    </div>
</div>