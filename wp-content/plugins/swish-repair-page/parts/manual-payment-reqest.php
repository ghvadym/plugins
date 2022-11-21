<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js"></script>

<div class="payment-form-wrapper">
    <form action="" method="post" id="manual-payment-form">
        <div class="form-group">
            <label for="amount">
                <?php _e('Belopp (*obligatoriskt)', 'swish-rp') ?>:
            </label>
            <input type="number"
                   name="amount"
                   id="amount"
                   class="form-control"
                   placeholder="0.00"
                   min="0.00"
                   step="0.01"
                   required>
            <span>
                <?php _e('kr', 'swish-rp') ?>
            </span>
        </div>
        <div class="form-group">
            <label for="phone">
                <?php _e('Telefonnummer (*obligatoriskt)', 'swish-rp') ?>:
            </label>
            <input type="text"
                   pattern="(46)\d{9}"
                   name="phone"
                   id="phone"
                   class="form-control"
                   required
                   placeholder="ex: 46770798170">
        </div>
        <div class="form-group">
            <label for="ordernumber">
                <?php _e('Beställningsnummer (*obligatoriskt)', 'swish-rp') ?>:
            </label>
            <input type="text"
                   pattern="\d*"
                   name="ordernumber"
                   id="ordernumber"
                   required
                   class="form-control"
                   maxlength="20">
        </div>
        <div class="form-group">
            <label for="note">
                <?php _e('Betalningsanmärkning (valfritt)', 'swish-rp') ?>:
            </label>
            <textarea name="note"
                      id="note"
                      class="form-control"
                      rows="10">
            </textarea>
        </div>
        <div>
            <input class="btn btn-fill btn-success"
                   type="submit"
                   value="<?php _e('Skicka betalning', 'swish-rp') ?>">
        </div>
    </form>
    <div class="payment-form-message"></div>
</div>