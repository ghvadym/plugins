<form method="post" id="manual-payment-login">
    <div class="form-group">
        <div class="form-notes">
            <?php the_content(); ?>
        </div>
        <label for="fixably-password">
            <?php _e('Lösenord', 'swish-rp'); ?>:
        </label>
        <input type="password"
               name="fixably-password"
               id="fixably-password"
               class="form-control"
               value=""
               required
               minlength="5"
               maxlength="10">
    </div>
    <div class="form-group">
        <input class="btn btn-fill btn-success" type="submit" value="<?php _e('Logga in', 'swish-rp') ?>">
    </div>
</form>
<div class="payment-form-message"></div>