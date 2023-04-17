<?php
if (empty($review)) {
    return;
}

$text = $review->text ?? '';
$rating = $review->rating ?? 0;
$reviewer = $review->reviewer->screenName ?? '';
?>
<div class="ireco_item swiper-slide">
    <a href="<?php echo $source_url ?? ''; ?>" class="ireco_item__body" target="_blank">
        <div class="ireco_item__head">
            <div class="ireco_rating">
                <?php Reco_Functions::rating($rating); ?>
            </div>
        </div>
        <?php if ($text): ?>
            <div class="ireco_item__content ireco_text">
                <?php Reco_Functions::cut_str($text, 150); ?>
            </div>
        <?php endif; ?>
        <div class="ireco_item__footer">
            <?php if ($reviewer): ?>
                <div class="ireco_item__author ireco_subtitle">
                    <?php echo $reviewer; ?>
                </div>
            <?php endif; ?>
            <div class="ireco_item__verify">
                Verifierad kund
            </div>
        </div>
    </a>
</div>