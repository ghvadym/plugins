<?php
$active = get_option('fx_events_setting_option')['output_format'];
$formatItems = [
    'month' => __('month', 'fxevents'),
    'list'  => __('list', 'fxevents'),
];

foreach ($formatItems as $item => $name): ?>
    <div class="fx_format__item<?php echo $active == $item ? ' active' : '' ?>" data-format="<?php echo $item ?>">
        <?php echo $name ?>
    </div>
<?php
endforeach;