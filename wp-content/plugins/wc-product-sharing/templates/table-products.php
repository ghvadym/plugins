<div class="wrap">
    <h1>
        <?php _e('WC API Products', 'wcapi') ?>
    </h1>

    <form method="get">
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>">

        <?php
        $wcApiControl = new WCAPI_Products;

        $wcApiControl->status_message();

        $wcApiControl->prepare_items();
        $wcApiControl->display();
        ?>
    </form>
</div>