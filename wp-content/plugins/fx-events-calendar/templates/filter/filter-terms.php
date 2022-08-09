<?php
use Includes\Functions;

if (!empty($data['terms'])) : ?>
    <div class="fx_filter__cat">
        <h3 class="fx_filter__title">
            <?php _e('Terms', 'fxevents') ?>
        </h3>
        <div class="fx_filter__list terms">
            <?php foreach ($data['terms'] as $termId) :
                $term = get_term($termId); ?>
                <label class="fx_filter__item" data-term="<?php echo $term->slug?>">
                    <input type="checkbox"
                           value="<?php echo $term->term_id ?>"
                           <?php echo Functions::doChecked($term->term_id, $data['filter_data']['terms']) ?>
                    >
                    <?php echo $term->name ?>
                </label>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

