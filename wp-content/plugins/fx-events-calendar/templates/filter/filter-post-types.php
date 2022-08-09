<?php
use Includes\Functions;

$post_types = $data['types'];

if (!empty($post_types)) : ?>
    <div class="fx_filter__cat">
        <h3 class="fx_filter__title">
            <?php _e('Post Types', 'fxevents') ?>
        </h3>
        <div class="fx_filter__list">
            <?php foreach ($post_types as $postType) : ?>
                <label class="fx_filter__item" data-type="<?php echo $postType ?>">
                    <input type="checkbox"
                           value="<?php echo $postType ?>"
                           <?php echo Functions::doChecked($postType, $data['filter_data']['post_types']) ?>
                    >
                    <?php echo $postType ?>
                </label>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>