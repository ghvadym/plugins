<?php
if (empty($data)) {
    return;
}

$reviews = $data['items'] ?? [];
$rating_avg = $data['rating_avg'] ?? '4.9';
$total_items = $data['total_items'] ?? '0';
$source_url = $data['source_url'] ?? '';
$logo_url = $data['logo_url'] ?? '';
$title = $data['title'] ?? '';
?>
<div class="ireco_reviews">
    <div class="ireco_row">
        <div class="ireco_represent">
            <div class="ireco_header">
                <h2 class="ireco_title">
                    <?php echo $title; ?>
                </h2>
            </div>
            <div class="ireco_footer">
                <div class="ireco_footer__col">
                    <div class="ireco_rating__total ireco_rating">
                        <?php Reco_Functions::rating(round($rating_avg)); ?>
                    </div>

                    <div class="ireco_rating__title">
                        <strong>
                            <?php echo round($rating_avg, 1) . ' av 5 stjärnor'; ?>
                        </strong> hos Reco!
                    </div>
                </div>
                <div class="ireco_footer__col">
                    <a href="<?php echo $source_url; ?>" target="_blank">
                        <img class="ireco_logo" src="<?php echo $logo_url; ?>" alt="reco.se">
                    </a>
                </div>
            </div>
        </div>
        <?php if (!empty($reviews)): ?>
            <div class="ireco_items">
                <?php include Reco_Functions::get_path('reco-reviews-items'); ?>
            </div>
        <?php endif; ?>
    </div>
</div>