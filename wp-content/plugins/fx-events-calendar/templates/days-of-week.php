<?php
use Includes\Calendar;
$days = Calendar::daysOfWeek();
?>

<?php if (!empty($days)): ?>
    <?php foreach ($days as $day): ?>
        <div class="fx_calendar__week_day">
            <?php echo $day; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>