document.addEventListener('DOMContentLoaded', function () {
    var ajax = fx_theme_ajax.ajaxurl;
    var formatItems = document.querySelectorAll('.fx_calendar__format .fx_format__item');
    var dateHead = document.getElementById('fx_datepicker');
    var dateWrapper = document.querySelector('.fx_datepicker__wrapper');
    var arrowsYear = document.querySelectorAll('.fx_datepicker__year > .fx_year-arrow');
    var monthsCalendar = document.querySelectorAll('.fx_datepicker__list > .fx_datepicker__item');
    var wrapper = document.querySelector('.flexi-calendar');
    var filterData = {
        'type': [],
        'tax' : [],
        'term': [],
    }

    sendResponseForCalendar(filterData);
    outputFormat(filterData);
    datePicker(filterData);
    detectInputSearch();
    search(filterData);
    clearFilter();

    function search(filterData)
    {
        var searchBtn = document.getElementById('events-submit');
        var searchField = document.getElementById('events-search');
        var searchReset = document.getElementById('events-reset');
        if (!searchBtn) {
            return;
        }

        searchBtn.onclick = () => {
            if (!searchField.value) {
                return;
            }

            sendResponseForCalendar(filterData);
        }

        searchReset.onclick = () => {
            var searchField = document.getElementById('events-search');
            if (!searchField.value) {
                return;
            }
            searchField.value = '';
            searchField.parentNode.classList.remove('input-focus');
            sendResponseForCalendar(filterData);
        }

        searchField.addEventListener('keyup', function(e) {
            sendResponseOnClick(e, filterData)
        }, false);
    }

    function detectInputSearch()
    {
        var searchInput = document.getElementById('events-search');
        if (!searchInput) {
            return;
        }

        if (searchInput.value.length) {
            searchInput.parentNode.classList.add('input-focus');
        }

        searchInput.onfocus = () => {
            searchInput.parentNode.classList.add('input-focus');
        }
        searchInput.onblur = () => {
            if (searchInput.value) {
                return;
            }
            searchInput.parentNode.classList.remove('input-focus');
        }
    }

    function playFilter(filterData) {
        var inputs = document.querySelectorAll('.flexi-calendar .fx_filter__item > input');
        if (!inputs) {
            return;
        }

        inputs.forEach((input) => {
            input.onchange = async () => {
                inputs.forEach((checkbox) => {
                    var filterCat = Object.keys(checkbox.parentNode.dataset)[0];
                    if (!filterCat) {
                        return;
                    }

                    if (checkbox.checked && !filterData[filterCat].includes(checkbox.value)) {
                        filterData[filterCat].push(checkbox.value);
                    }

                    if (!checkbox.checked && filterData[filterCat].includes(checkbox.value)) {
                        var index = filterData[filterCat].indexOf(checkbox.value);
                        filterData[filterCat].splice(index);
                    }
                })
                sendResponseForCalendar(filterData);
            }
        })
    }

    function clearFilter()
    {
        var inputs = document.querySelectorAll('.flexi-calendar .fx_filter__item > input');
        var resetBtn = document.getElementById('events-filter-reset');
        if (!inputs) {
            return;
        }

        resetBtn.onclick = () => {
            inputs.forEach((input) => {
                input.checked = false;
            })

            var filterData = {
                'type': [],
                'tax' : [],
                'term': [],
            }

            sendResponseForCalendar(filterData);
            outputFormat(filterData);
            datePicker(filterData);
            search(filterData);
        }
    }

    async function getDaysOfMonth(month, output, filterData, search)
    {
        var data = new FormData();
        data.append('action', 'month_days');
        data.append('month', month);
        data.append('output', output);
        data.append('search', search);
        data.append('type', filterData.type);
        data.append('tax', filterData.tax);
        data.append('term', filterData.term);

        var response = await fetch(ajax, {
            method: 'POST',
            body  : data
        });

        var html = await response.json();

        actionsAfterResponse(html, output);
    }

    async function getDataFilter(month, output, filterData, search)
    {
        var data = new FormData();
        var filterWrap = document.querySelector('.fx_filter__categories');
        data.append('action', 'post_type_hierarchy');
        data.append('month', month);
        data.append('search', search);
        data.append('type', filterData.type);
        data.append('tax', filterData.tax);
        data.append('term', filterData.term);

        var response = await fetch(ajax, {
            method: 'POST',
            body  : data
        });

        var html = await response.json();
        if (!html) {
            return;
        }

        filterWrap.innerHTML = html.result;
        playFilter(filterData);
    }

    function outputFormat(filterData)
    {
        if (!formatItems) {
            return;
        }

        formatItems.forEach((item) => {
            item.onclick = function () {
                formatItems.forEach((i) => {
                    i.classList.remove('active');
                });
                this.classList.add('active');
                sendResponseForCalendar(filterData);
            }
        })
    }

    function sendResponseForCalendar(filterData)
    {
        var month = document.getElementById('fx_datepicker');
        var outputFormat = document.querySelector('.fx_format__item.active');
        var searchField = document.getElementById('events-search');
        var wrapper = document.querySelector('.fx_calendar__content');
        if (!month || !outputFormat || !searchField || !wrapper) {
            return;
        }
        getDaysOfMonth(month.dataset.date, outputFormat.dataset.format, filterData, searchField.value);
        getDataFilter(month.dataset.date, outputFormat.dataset.format, filterData, searchField.value);
        wrapper.classList.add('loading');
    }

    function actionsAfterResponse(html, output)
    {
        var loopDaysWrap = document.querySelector('.fx_calendar__loop')
        var daysOfWeek = document.querySelector('.fx_calendar__week');

        if (output === 'list') {
            daysOfWeek.classList.add('d-none');
        } else {
            daysOfWeek.classList.remove('d-none');
        }

        loopDaysWrap.innerHTML = html.result;

        if (html.posts == null) {
            wrapper.classList.add('fx_posts-not-found');
        } else {
            wrapper.classList.remove('fx_posts-not-found');
        }

        var postsWrap = document.querySelector('.fx_calendar__content');
        postsWrap.classList.remove('loading');

        showPostsOfDay();
    }

    function datePickerAction(e) {
        if (!dateWrapper.contains(e.target) && !dateHead.contains(e.target) && dateWrapper.classList.contains('active')) {
            dateWrapper.classList.remove('active');
        }
    }

    function datePicker(filterData)
    {
        var dpWrapper = document.querySelector('.fx_calendar__datepicker');
        var dpClose = document.getElementById('fx_datepicker_close');

        dateHead.onclick = () => {
            dateWrapper.classList.toggle('active');
            window.addEventListener('click', datePickerAction, false);
        }

        window.removeEventListener('click', datePickerAction, false);

        dpClose.onclick = () => {
            restartDatePicker();
            clearActiveDate();
            sendResponseForCalendar(filterData);
        }

        arrowsYear.forEach((arrow) => {
            arrow.onclick = () => {
                var currentYear = document.querySelector('.fx_datepicker__year .fx_year-current');
                if (arrow.classList.contains('fx_year-prev')) {
                    currentYear.innerHTML = parseInt(currentYear.innerHTML) - 1;
                }
                if (arrow.classList.contains('fx_year-next')) {
                    currentYear.innerHTML = parseInt(currentYear.innerHTML) + 1;
                }
                clearActiveDate();
                dpWrapper.classList.add('dp-changed');
            }
        })

        monthsCalendar.forEach((month) => {
            month.onclick = () => {
                if (month.classList.contains('active')) {
                    return;
                }

                var dateWrapper = document.querySelector('.fx_datepicker__wrapper');
                var currentYear = document.querySelector('.fx_datepicker__year .fx_year-current').innerHTML;
                var dateHead = document.getElementById('fx_datepicker');
                var getMonth = month.dataset.month;
                var selectedDate = currentYear + '-' + getMonth;
                var clearDate = selectedDate.replace(/\s/g, '');
                var currentLocale = document.getElementsByTagName('html')[0].getAttribute('lang').substring(0,2);

                switch (currentLocale) {
                    case 'ru':
                        var monthNames = ["Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"];
                        break;
                    case 'uk' || 'ua':
                        var monthNames = ["Сiчень", "Лютий", "Березень", "Квiтень", "Травень", "Червень", "Липень", "Серпень", "Вересень", "Жовтень", "Листопад", "Грудень"];
                        break;
                    default:
                        var monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
                        break;
                }

                var date = new Date(clearDate);

                dateHead.dataset.date = clearDate;
                dateHead.innerHTML = monthNames[date.getMonth()] + ' ' + date.getFullYear();
                dateWrapper.classList.remove('active');
                clearActiveDate();
                month.classList.add('active');
                dpWrapper.classList.add('dp-changed');

                sendResponseForCalendar(filterData);
            }
        })
    }

    function clearActiveDate()
    {
        var monthsCalendar = document.querySelectorAll('.fx_datepicker__list > .fx_datepicker__item');
        monthsCalendar.forEach((monthItem) => {
            if (!monthItem.classList.contains('active')) {
                return;
            }
            monthItem.classList.remove('active');
        })
    }

    function restartDatePicker()
    {
        var date = new Date();
        var currentYear = document.querySelector('.fx_datepicker__year .fx_year-current');
        dateHead.innerHTML = dateHead.dataset.startdate;
        dateHead.dataset.date = date.getFullYear() + '-' + date.getMonth() + 1;
        dateHead.parentNode.classList.remove('dp-changed');
        currentYear.innerHTML = date.getFullYear();
    }

    function showPostsAction(e) {
        var calendar = document.querySelector('.fx_calendar__loop');
        var popup = document.getElementById('calendar-posts');
        if (!popup.contains(e.target) && !calendar.contains(e.target) && popup.classList.contains('active')) {
            popup.classList.remove('active');
        }
    }

    function sendResponseOnClick(e, filterData)
    {
        if (e.keyCode !== 13) {
            return false;
        }

        sendResponseForCalendar(filterData);
    }

    function showPostsOfDay()
    {
        var cards = document.querySelectorAll('.fx_calendar__loop .fx_calendar__day');
        if (!cards) {
            return;
        }

        var popup = document.getElementById('calendar-posts');
        cards.forEach((card) => {
            card.onclick = () => {
                var posts = card.querySelector('.fx_day__body');
                if (!posts) {
                    return;
                }

                popup.classList.add('active');
                popup.querySelector('.fx_calendar__popup-body').innerHTML = posts.outerHTML;
                window.addEventListener('click', showPostsAction, false);
            }
        })

        var popupClose = document.getElementById('fx-popup-close');
        if (popupClose) {
            popupClose.onclick = () => {
                popup.classList.remove('active');
            }
        }

        window.removeEventListener('click', showPostsAction, false);
    }
});