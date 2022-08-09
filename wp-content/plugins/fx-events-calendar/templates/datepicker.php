<?php
use Includes\Calendar;
use Includes\Functions;
$months = Calendar::getMonthsOfYear();
?>

<div class="fx_datepicker__list">
    <?php foreach ($months as $int => $month): ?>
        <div class="fx_datepicker__item<?php echo Functions::activeMonth($int) ?>"
             data-month="<?php echo $int ?>">
             <?php echo $month ?>
        </div>
    <?php endforeach; ?>
</div>
