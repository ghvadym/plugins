<?php

namespace Includes;

class Calendar
{
    public static function daysOfMonth(string $month): array
    {
        $daysPerMonth = [];
        $getDays = cal_days_in_month(
            CAL_GREGORIAN,
            date('m', strtotime($month)),
            date('Y', strtotime($month))
        );
        for ($i = 1; $i <= $getDays; $i++) {
            $daysPerMonth[] = $i <= 9 ? 0 . $i : $i;
        }

        return apply_filters('fx_events_day_of_months', $daysPerMonth, $month);
    }

    public static function firstDayOfMonth(string $month): string
    {
        return date('w', strtotime($month));
    }

    public static function daysOfWeek(): array
    {
        $locale = Functions::getCurrentLocale();

        $days = [
            'ru' => ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'],
            'ua' => ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Нд'],
            'en' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        ];

        return apply_filters('fx_event_days_of_month', $days[$locale], $days, $locale);
    }

    public static function offsetDays($dayOfWeek, $class)
    {
        $offset = $dayOfWeek ? $dayOfWeek - 1 : 0;
        for ($i = 0; $i < $offset; $i++) { ?>
            <div class="<?php echo $class ?> empty-day"></div>
        <?php }
    }

    public static function getMonthsOfYear(): array
    {
        $locale = Functions::getCurrentLocale();

        $months = [
            'ru' => ['01' => 'Янв', '02' => 'Фев', '03' => 'Мар', '04' => 'Апр', '05' => 'Май', '06' => 'Июн', '07' => 'Июл', '08' => 'Авг', '09' => 'Сент', '10' => 'Окт', '11' => 'Ноя', '12' => 'Дек',],
            'ua' => ['01' => 'Сiч', '02' => 'Лют', '03' => 'Бер', '04' => 'Квi', '05' => 'Тра', '06' => 'Чер', '07' => 'Лип', '08' => 'Сер', '09' => 'Вер', '10' => 'Жов', '11' => 'Лис', '12' => 'Гру',],
            'en' => ['01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'May', '06' => 'Jun', '07' => 'Jul', '08' => 'Aug', '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dec',],
        ];

        return apply_filters('fx_events_months_of_years', $months[$locale], $months, $locale);
    }

    public static function getDaysOfMonth($month, $output, $search, $filterData)
    {
        $getMeta = self::getPosts($month, $filterData, $search);
        $sortPosts = self::sortPostsByDate($getMeta);

        if (empty($sortPosts)) {
            Functions::sendResponse('posts-not-found', ['posts' => null]);
        } else {
            if ($output === 'month') {
                $days = self::daysOfMonth($month);
                $dayOffset = Calendar::firstDayOfMonth($month);
                self::sendResponseDaysOfMonth($days, $sortPosts, $dayOffset, $month);
            } else {
                self::sendResponseDaysList($sortPosts);
            }
        }
    }

    static function sendResponseDaysList($sortPosts)
    {
        $data = [
            'posts' => $sortPosts,
        ];
        Functions::sendResponse('days-list', $data);
    }

    static function sendResponseDaysOfMonth($days, $sortPosts, $daysOffset, $month)
    {
        $data = [
            'days'   => $days,
            'posts'  => $sortPosts,
            'offset' => $daysOffset,
            'month'  => date('m', strtotime($month)),
        ];
        Functions::sendResponse('days-of-month', $data);
    }

    public static function getPosts($month = null, $filterData = [], $search = null): array
    {
        if (!empty($filterData['post_types'])) {
            $post_types = $filterData['post_types'];
        } else {
            $post_types = get_option('fx_events_post_types_option');
        }
        if (empty($post_types)) {
            return [];
        }

        $args = [
            'post_type'      => $post_types,
            'posts_per_page' => -1,
        ];

        if ($search) {
            $args['s'] = $search;
        }

        if ($month) {
            $args['meta_query'] = [
                'date' => [
                    'key'     => 'fx_event__date',
                    'compare' => 'LIKE',
                    'value'   => $month,
                ],
                'time' => [
                    'key'     => 'fx_event__time',
                    'compare' => 'EXISTS',
                ],
            ];

            $args['orderby'] = [
                'date' => 'ASC',
                'time' => 'ASC',
            ];
        }

        if (!empty($filterData['taxes'])) {
            $args['tax_query'] = [
                'relation' => 'OR',
            ];

            foreach ($filterData['taxes'] as $tax) {
                $args['tax_query'][] = [
                    [
                        'taxonomy' => $tax,
                        'operator' => 'EXISTS',
                    ],
                ];
            }
        }

        if (!empty($filterData['terms'])) {
            $args['tax_query'] = [
                'relation' => 'OR',
            ];

            foreach ($filterData['terms'] as $termId) {
                $term = get_term($termId);
                $tax = get_taxonomy($term->taxonomy);
                $args['tax_query'][] = [
                    [
                        'taxonomy' => $tax->name,
                        'field'    => 'id',
                        'terms'    => $filterData['terms'],
                    ],
                ];
            }
        }

        return get_posts($args);
    }

    static function sortPostsByDate($posts): array
    {
        $sortPosts = [];
        foreach ($posts as $post) {
            $meta = get_post_meta($post->ID);
            if (empty($meta['fx_event__date'])) {
                return [];
            }
            $date = $meta['fx_event__date'][0];
            if (!array_key_exists($date, $sortPosts)) {
                $sortPosts[$date] = [];
            }
            $sortPosts[$date][] = [
                'title'       => $post->post_title,
                'content'     => $post->post_content,
                'description' => $post->post_excerpt,
                'image'       => get_the_post_thumbnail_url($post->ID),
                'link'        => get_the_permalink($post->ID),
                'time'        => $meta['fx_event__time'][0],
            ];
        }

        return apply_filters('fx_events_get_posts', $sortPosts, $posts);
    }
}