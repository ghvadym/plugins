<?php

use Includes\Functions;

$hasPostTypes = get_option('fx_events_post_types_option');

if ($hasPostTypes) : ?>
    <div class="flexi-calendar">
        <div class="fx_calendar__row">
            <div class="fx_calendar__filter">
                <div class="fx_filter__categories">
                    <?php require Functions::getPath('filter') ?>
                </div>
                <div class="fx-btn" id="events-filter-reset">
                    <?php _e('Reset Filter', 'fxevents') ?>
                </div>
            </div>
            <div class="fx_calendar__body">
                <div class="fx_calendar__head">
                    <div class="fx_calendar__datepicker">
                        <div class="fx_datepicker__head"
                             id="fx_datepicker"
                             data-date="<?php echo date('Y-m') ?>"
                             data-startdate="<?php echo date_i18n('F Y') ?>">
                            <?php echo date_i18n('F Y') ?>
                        </div>
                        <div class="fx_datepicker__wrapper">
                            <div class="fx_datepicker__year">
                                <span class="fx_year-prev fx_year-arrow"></span>
                                <span class="fx_year-current">
                                    <?php echo date_i18n('Y') ?>
                                </span>
                                <span class="fx_year-next fx_year-arrow"></span>
                            </div>
                            <div class="fx_datepicker__body">
                                <?php require Functions::getPath('datepicker') ?>
                            </div>
                        </div>
                        <i id="fx_datepicker_close" class="fx_icon-close abs-center-y"></i>
                    </div>
                    <div class="fx_calendar__search">
                        <label for="events-search" class="fx_search__label">
                            <?php _e('Search event') ?>
                        </label>
                        <input name="events-search" id="events-search" class="fx_search__field">
                        <i id="events-submit" class="fx_search__btn"></i>
                        <i id="events-reset" class="fx_search__reset fx_icon-close abs-center-y"></i>
                    </div>
                    <div class="fx_calendar__format">
                        <?php require Functions::getPath('output-format') ?>
                    </div>
                </div>
                <div class="fx_calendar__content">
                    <div class="fx_calendar__week">
                        <?php require Functions::getPath('days-of-week') ?>
                    </div>
                    <div class="fx_calendar__loop"></div>
                    <div id="calendar-posts" class="fx_calendar__popup fx_scrollbar">
                        <div class="fx_calendar__popup-body"></div>
                        <i id="fx-popup-close" class="fx_calendar__popup-close fx_icon-close"></i>
                    </div>
                    <?php Functions::preloaderHtml(); ?>
                </div>
            </div>
        </div>
    </div>
<?php endif;