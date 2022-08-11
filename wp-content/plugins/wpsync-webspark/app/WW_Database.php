<?php

class WW_Database
{
    static function add_product($data = []): int
    {
        if (empty($data) || !$data->name) {
            return 0;
        }

        $postData = [
            'post_title'   => $data->name,
            'post_content' => $data->description,
            'post_type'    => 'product',
            'post_status'  => 'publish'
        ];

        $productId = wp_insert_post(wp_slash($postData));

        if (!$productId) {
            return 0;
        }

        self::update_post_meta($productId, $data);

        if (isset($data->picture)) {
            WW_Functions::set_post_thumbnail($data->picture, $productId);
        }

        return $productId;
    }

    static function update_product(int $productId = 0, $data = []): int
    {
        if (!$productId || empty($data)) {
            return 0;
        }

        $postData = [
            'ID'           => $productId,
            'post_title'   => $data->name,
            'post_content' => $data->description
        ];

        $id = wp_update_post(wp_slash($postData));

        if (!$id) {
            return 0;
        }

        self::update_post_meta($id, $data);

        if (isset($data->picture)) {
            WW_Functions::delete_thumbnail($productId);
            WW_Functions::set_post_thumbnail($data->picture, $productId);
        }

        return $id;
    }

    static function remove_product(int $productId = 0): bool
    {
        if (!$productId) {
            return false;
        }

        $thumbnailId = get_post_thumbnail_id($productId);
        $deletePost = wp_delete_post($productId);

        if ($deletePost && $thumbnailId) {
            wp_delete_attachment($thumbnailId, true);
        }

        return !!$deletePost;
    }

    static function update_post_meta(int $productId = 0, $data = []): void
    {
        if (!$productId || empty($data)) {
            return;
        }

        $metadata = [
            '_sku'           => 'sku',
            '_regular_price' => 'price',
            '_stock'         => 'in_stock'
        ];

        foreach ($metadata as $metaName => $value) {
            if (!isset($data->{$value})) {
                continue;
            }

            update_post_meta($productId, $metaName, $data->{$value});
        }
    }
}