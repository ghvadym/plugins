<?php

use Includes\Functions;
use Includes\Calendar;

Calendar::offsetDays($data['offset'], 'fx_calendar__day');
foreach ($data['days'] as $day): ?>
    <?php $currentDate = $data['month'] == date('m') && $day == date('d') ? ' current-date' : ''; ?>
    <div class="fx_calendar__day<?php echo $currentDate ?>">
        <div class="fx_day__numb">
            <?php echo $day ?>
        </div>
        <?php foreach ($data['posts'] as $date => $val) :
            if (date('d', strtotime($date)) == $day) : ?>
                <?php foreach ($val as $item) :
                    echo '• ';
                endforeach; ?>
                <div class="fx_day__body">
                    <div class="fx_day__posts">
                        <?php
                        foreach ($val as $post) :
                            $titleLimit = 50;
                            $descLimit = 100;
                            require Functions::getPath('day-card');
                        endforeach; ?>
                    </div>
                </div>
            <?php endif;
        endforeach; ?>
    </div>
<?php
endforeach;
