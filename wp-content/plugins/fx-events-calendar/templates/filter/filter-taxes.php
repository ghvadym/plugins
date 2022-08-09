<?php
use Includes\Functions;

if (!empty($data['taxes'])) : ?>
    <div class="fx_filter__cat">
        <h3 class="fx_filter__title">
            <?php _e('Taxes', 'fxevents') ?>
        </h3>
        <div class="fx_filter__list taxonomies">
            <?php foreach ($data['taxes'] as $tax) : ?>
                <label class="fx_filter__item" data-tax="<?php echo $tax ?>">
                    <input type="checkbox"
                           value="<?php echo $tax ?>"
                           <?php echo Functions::doChecked($tax, $data['filter_data']['taxes']) ?>
                    >
                    <?php echo str_replace("-", " ", $tax) ?>
                </label>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>