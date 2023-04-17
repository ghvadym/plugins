<?php
if (empty($reviews)) {
    return;
}
?>

<div class="ireco_items__body ireco_reviews__slider">
    <div class="ireco_items__list swiper-wrapper">
        <?php foreach ($reviews as $review):
            include Reco_Functions::get_path('reco-reviews-item');
        endforeach; ?>
    </div>
    <div class="ireco_reviews__pagination"></div>
</div>
