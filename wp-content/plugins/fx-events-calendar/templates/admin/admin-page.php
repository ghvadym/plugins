<?php settings_errors(); ?>
<form method="post" action="options.php">
    <?php
    settings_fields('fx_event_setting');
    do_settings_sections('fx_events_settings_page');
    submit_button();
    ?>
</form>
