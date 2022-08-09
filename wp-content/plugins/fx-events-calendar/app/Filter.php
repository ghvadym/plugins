<?php

namespace Includes;

class Filter
{
    public static function getFilterData($month, $search, $filterData): void
    {
        $getPosts = Calendar::getPosts($month, $filterData, $search);
        $getPostsData = self::getPostsData($getPosts);

        $data = [
            'filter_data' => $filterData,
            'terms'       => $getPostsData['terms'] ?? [],
            'taxes'       => $getPostsData['taxes'] ?? [],
            'types'       => $getPostsData['post_types'] ?? [],
        ];

        Functions::sendResponse('filter', $data);
    }

    static function getPostsData($getPosts): array
    {
        if (empty($getPosts)) {
            return [];
        }

        $postTypesData = [
            'post_types' => [],
            'taxes'      => [],
            'terms'      => [],
        ];

        foreach ($getPosts as $post) {
            if (!in_array($post->post_type, $postTypesData['post_types'])) {
                $postTypesData['post_types'][] = $post->post_type;
            }

            $taxes = get_post_taxonomies($post->ID);
            if (empty($taxes)) {
                continue;
            }

            $exclude = ['post_translations', 'language'];

            foreach ($taxes as $tax) {
                if (in_array($tax, $exclude)) {
                    continue;
                }

                if (!in_array($tax, $postTypesData['taxes']) && has_term('', $tax, $post->ID)) {
                    $postTypesData['taxes'][] = $tax;
                }

                $terms = get_the_terms($post->ID, $tax);
                foreach ($terms as $term) {
                    if (!in_array($term->term_id, $postTypesData['terms'])) {
                        $postTypesData['terms'][] = $term->term_id;
                    }
                }
            }
        }

        return apply_filters('fx_events_posts_data_filter', $postTypesData, $getPosts);
    }
}