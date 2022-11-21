<?php
if (empty($data)) {
    return;
}
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm"
      crossorigin="anonymous">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js"></script>
<div class="payment-form-wrapper">
    <form action="" method="post" id="payment-form" data-id="<?php echo $data['id']; ?>">
        <div class="form-group">
            <label for="amount">
                <?php _e('Belopp (*obligatoriskt)', 'swish-rp') ?>:
            </label>
            <input type="text"
                   name="amount"
                   id="amount"
                   class="form-control"
                   readonly
                   value="<?php echo $data['amount'] ?>"
                   required>
            <span>
                <?php _e('kr', 'swish-rp') ?>
            </span>
        </div>
        <?php if (!Swish_Repair_Functions::is_mobile()): ?>
            <div class="form-group">
                <label for="phone">
                    <?php _e('Telefonnummer (*obligatoriskt)', 'swish-rp') ?>:
                </label>
                <input type="text"
                       name="phone"
                       id="phone"
                       class="form-control"
                       required
                       placeholder="ex: 46770798170">
            </div>
        <?php endif; ?>
        <div class="form-group">
            <label for="ordernumber">
                <?php _e('Beställningsnummer (*obligatoriskt)', 'swish-rp') ?>:
            </label>
            <input type="text"
                   name="ordernumber"
                   id="ordernumber"
                   value="<?php echo $data['order'] ?>"
                   readonly
                   required
                   class="form-control">
        </div>
        <div class="form-group">
            <label for="note">
                <?php _e('Betalningsanmärkning (valfritt)', 'swish-rp') ?>:
            </label>
            <textarea name="note"
                      id="note"
                      class="form-control"
                      rows="10">Payment for #<?php echo $data['order'] ?>
            </textarea>
        </div>
        <div>
            <input class="btn btn-fill btn-success"
                   type="submit"
                   value="<?php _e('Betala med Swish', 'swish-rp') ?>">
        </div>
    </form>
    <div class="payment-form-message"></div>
</div>