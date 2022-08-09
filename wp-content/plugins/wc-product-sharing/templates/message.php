<?php
if (empty($message)) {
    return;
}
?>

<div id="message" class="updated notice is-dismissible">
    <p>
        <?php echo $message; ?>
    </p>
</div>