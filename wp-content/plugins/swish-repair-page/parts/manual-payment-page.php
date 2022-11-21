<?php
get_header();
Swish_Repair_Functions::swish_manual_payment_process();
?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css"
          integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm"
          crossorigin="anonymous">
    <section id="manual-payment">
        <div class="container">
            <article class="manual-payment-wrapper">
                <h1 class="manual-payment-title">
                    <?php _e('Betala med Swish', 'swish-rp') ?>
                </h1>
                <div class="manual-payment-form">
                    <?php Swish_Repair_Functions::manual_payment_page(); ?>
                </div>
            </article>
        </div>
    </section>

<?php
get_footer();