<?php

use Includes\Functions;

foreach ($data['posts'] as $date => $posts) : ?>
    <div class="fx_calendar__data">
        <div class="fx_calendar__date">
            <?php echo date_i18n('l, d', strtotime($date)); ?>
        </div>
        <div class="fx_calendar__date_posts">
            <?php foreach ($posts as $post) : ?>
                <?php
                $titleLimit = 100;
                $descLimit = 200;
                require Functions::getPath('day-card') ?>
            <?php endforeach; ?>
        </div>
    </div>
<?php endforeach;



