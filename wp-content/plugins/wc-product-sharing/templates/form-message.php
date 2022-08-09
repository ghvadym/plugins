<?php
if (!isset($result)) {
    return;
}
?>

<div id="message" class="wcapi-message updated notice is-dismissible">
    <p>
        <?php echo $result; ?>
    </p>
</div>