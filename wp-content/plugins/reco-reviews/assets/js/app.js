document.addEventListener('DOMContentLoaded', function () {
    //var ajax = reco_ajax.ajaxurl;

    var swiper = new Swiper('.ireco_reviews__slider', {
        slidesPerView: 'auto',
        spaceBetween : 10,
        loop         : true,
        speed        : 500,
        pagination   : {
            el       : '.ireco_reviews__pagination',
            clickable: true
        },
        autoplay     : {
            delay: 5000,
        },
    });
});