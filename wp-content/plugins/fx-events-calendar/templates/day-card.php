<?php
use Includes\Functions;
?>

<a href="<?php echo $post['link'] ?>" class="fx_post__wrap">
    <div class="fx_post__img">
        <img class="img-cover"
             src="<?php echo $post['image'] ?>"
             alt="<?php echo $post['title'] ?>">
    </div>
    <div class="fx_post__text">
        <h3 class="fx_post__title">
            <?php echo Functions::textLimit($post['title'], $titleLimit) ?>
        </h3>
        <?php if (!empty($post['description'])) : ?>
            <div class="fx_post__desc">
                <?php echo Functions::textLimit($post['description'], $descLimit) ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($post['time'])) : ?>
            <span class="fx_post__time">
                <?php echo __('at ', 'fxevents') . $post['time'] ?>
            </span>
        <?php endif; ?>
    </div>
</a>
