jQuery(function () {
    var params = parseURLParams(window.location.href);
    if (params !== undefined && params.swish_payment !== undefined && params.order !== undefined) {
        var paymentBlock = jQuery('.swish_payment_message');
        var postId = jQuery(paymentBlock).data('id');
        if (paymentBlock.length && postId) {
            setTimeout(function () {
                jQuery.ajax({
                    type   : 'POST',
                    url    : swishRP.ajaxurl,
                    data   : {
                        postId: postId,
                        action: 'order_status_after_paid'
                    },
                    success: function (response) {
                        if (response) {
                            if (response.message) {
                                console.log(response.message);
                            }

                            if (response.text) {
                                jQuery(paymentBlock).html(response.text);
                                jQuery(paymentBlock).removeClass('sf-preloader');
                            }
                        }
                    },
                    error  : function (err) {
                        console.log("error", err);
                    }
                });
            }, 3000);
        }
    }

    var paymentForm = jQuery('#payment-form');
    if (paymentForm.length) {
        paymentForm.validate({
            rules        : {
                amount: {
                    required: true
                },
                phone : {
                    required: true
                }
            },
            submitHandler: function (form) {
                var amount = jQuery('#amount').val();
                var phone = jQuery('#phone').val();
                var note = jQuery('#note').val();
                var ordernumber = jQuery('#ordernumber').val();
                var postId = jQuery(form).data('id');

                if (!form.classList.contains('sf-preloader')) {
                    form.classList.add('sf-preloader');
                }

                jQuery.ajax({
                    type   : "POST",
                    url    : swishRP.ajaxurl,
                    data   : {
                        amount               : amount,
                        payerAlias           : phone,
                        message              : note,
                        payeePaymentReference: ordernumber,
                        postId               : postId,
                        action               : "request_payment_with_swish"
                    },
                    success: function (response) {
                        if (form.classList.contains('sf-preloader')) {
                            form.classList.remove('sf-preloader');
                        }

                        if (response.url) {
                            window.location.replace(response.url);
                        }

                        if (response.message && response.status) {
                            showFormMessage(response.status, response.message);
                        }
                    },
                    error  : function (err) {
                        if (form.classList.contains('sf-preloader')) {
                            form.classList.remove('sf-preloader');
                        }

                        console.log('error', err);
                    }
                });
            }
        });
    }

    var manualPaymentLogin = jQuery('#manual-payment-login');
    if (manualPaymentLogin.length) {
        manualPaymentLogin.on('submit', function (e) {
            e.preventDefault();
            var pass = jQuery('#fixably-password').val();

            if (!jQuery(manualPaymentLogin).hasClass('sf-preloader')) {
                jQuery(manualPaymentLogin).addClass('sf-preloader');
            }

            jQuery.ajax({
                type   : "POST",
                url    : swishRP.ajaxurl,
                data   : {
                    pass  : pass,
                    action: "manual_payment_login"
                },
                success: function (response) {
                    if (jQuery(manualPaymentLogin).hasClass('sf-preloader')) {
                        jQuery(manualPaymentLogin).removeClass('sf-preloader');
                    }

                    if (response.html) {
                        setCookie('swishauth', '1', 7);
                        jQuery(manualPaymentLogin).parent('.manual-payment-form').html(response.html);
                    }

                    if (response.message && response.status) {
                        showFormMessage(response.status, response.message);
                    }

                    manualPaymentRequest();
                },
                error  : function (err) {
                    if (jQuery(manualPaymentLogin).hasClass('sf-preloader')) {
                        jQuery(manualPaymentLogin).removeClass('sf-preloader');
                    }

                    console.log('error', err);
                }
            });
        });
    }

    function manualPaymentRequest()
    {
        var form = jQuery('#manual-payment-form');
        if (form.length) {
            form.on('submit', function (e) {
                e.preventDefault();

                var amount = jQuery('#amount').val();
                var phone = jQuery('#phone').val();
                var note = jQuery('#note').val();
                var ordernumber = jQuery('#ordernumber').val();

                if (!jQuery(form).hasClass('sf-preloader')) {
                    jQuery(form).addClass('sf-preloader');
                }

                jQuery.ajax({
                    type   : "POST",
                    url    : swishRP.ajaxurl,
                    data   : {
                        amount               : amount,
                        payerAlias           : phone,
                        message              : note,
                        payeePaymentReference: ordernumber,
                        action               : "manual_payment_request"
                    },
                    success: function (response) {
                        if (jQuery(form).hasClass('sf-preloader')) {
                            jQuery(form).removeClass('sf-preloader');
                        }

                        if (response.html) {
                            jQuery(form).parent('.payment-form-wrapper').html(response.html);
                        }

                        if (response.message && response.status) {
                            showFormMessage(response.status, response.message);
                        }
                    },
                    error  : function (err) {
                        if (jQuery(form).hasClass('sf-preloader')) {
                            jQuery(form).removeClass('sf-preloader');
                        }

                        console.log('error', err);
                    }
                });
            });
        }
    }
    manualPaymentRequest();

    function setCookie(name, value, days)
    {
        var expires = "";
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "") + expires + "; path=/";
    }

    function showFormMessage(status, message)
    {
        if (!status || !message) {
            return;
        }

        var item = document.querySelector('.payment-form-message');

        if (!item) {
            return;
        }

        jQuery(item).fadeIn();
        item.innerHTML = message;

        setTimeout(function () {
            jQuery(item).fadeOut();
        }, 10000);
    }

    function parseURLParams(url)
    {
        var queryStart = url.indexOf("?") + 1,
            queryEnd = url.indexOf("#") + 1 || url.length + 1,
            query = url.slice(queryStart, queryEnd - 1),
            pairs = query.replace(/\+/g, " ").split("&"),
            parms = {},
            i,
            n,
            v,
            nv;

        if (query === url || query === "") return;

        for (i = 0; i < pairs.length; i++) {
            nv = pairs[i].split("=", 2);
            n = decodeURIComponent(nv[0]);
            v = decodeURIComponent(nv[1]);

            if (!parms.hasOwnProperty(n)) parms[n] = [];
            parms[n].push(nv.length === 2 ? v : null);
        }
        return parms;
    }
});