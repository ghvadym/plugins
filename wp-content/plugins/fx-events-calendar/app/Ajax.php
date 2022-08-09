<?php

namespace Includes;

class Ajax
{
    static function init()
    {
        add_action('wp_ajax_month_days', [self::class, 'getAjaxCalendar']);
        add_action('wp_ajax_nopriv_month_days', [self::class, 'getAjaxCalendar']);

        add_action('wp_ajax_post_type_hierarchy', [self::class, 'getAjaxFilter']);
        add_action('wp_ajax_nopriv_post_type_hierarchy', [self::class, 'getAjaxFilter']);
    }

    static function getAjaxCalendar(): void
    {
        $data = self::mergeAjax($_POST);
        $filter = self::buildFilterData(
            $data['type'],
            $data['tax'],
            $data['term']
        );

        Calendar::getDaysOfMonth(
            $data['month'],
            $data['output'],
            $data['search'],
            $filter
        );
    }

    static function getAjaxFilter(): void
    {
        $data = self::mergeAjax($_POST);
        $filter = self::buildFilterData(
            $data['type'],
            $data['tax'],
            $data['term']
        );

        Filter::getFilterData(
            $data['month'],
            $data['search'],
            $filter
        );
    }

    static function mergeAjax($data): array
    {
        return array_merge([
            'month'  => date('Y-m'),
            'output' => 'month',
            'search' => '',
            'type'   => '',
            'tax'    => '',
            'term'   => ''
        ], $data);
    }

    static function buildFilterData($type, $tax, $terms): array
    {
        return [
            'post_types' => Functions::explodeArray($type),
            'taxes'      => Functions::explodeArray($tax),
            'terms'      => Functions::explodeArray($terms)
        ];
    }
}