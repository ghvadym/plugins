<?php


class WW_Functions
{
    static function get_path(string $fileName): string
    {
        $pathToFile = WW_PLUGIN_PATH . "templates/{$fileName}.php";

        if (!file_exists($pathToFile)) {
            return '';
        }

        return $pathToFile;
    }

    static function update_products_from_api($productsArray = []): array
    {
        if (empty($productsArray)) {
            return [];
        }

        $products = [
            'inserted' => [],
            'updated'  => [],
            'deleted'  => []
        ];

        if (!empty($productsWithSku = WW_Functions::get_products_sku())) {
            $existsProductSku = array_column(WW_Functions::get_products_sku(), 'sku');
            $apiProductsSku = array_column((array) $productsArray, 'sku');
            $productsDiff = array_diff($existsProductSku, $apiProductsSku);
        }

        foreach ($productsArray as $product) {
            if (!$product->sku) {
                continue;
            }

            $productId = WW_Functions::get_product_id_by_sku($product->sku);

            if (!$productId) {
                if ($id = WW_Database::add_product($product)) {
                    $products['inserted'][] = $id;
                }
            } else {
                if ($id = WW_Database::update_product($productId, $product)) {
                    $products['updated'][] = $id;
                }
            }
        }

        if (!empty($productsDiff)) {
            foreach ($productsDiff as $sku) {
                $productId = WW_Functions::get_product_id_by_sku($sku);
                if (!$productId) {
                    continue;
                }

                if (WW_Database::remove_product($productId)) {
                    $products['deleted'][] = $productId;
                }
            }
        }

        return [
            'posts_from_api' => count((array) $productsArray),
            'posts_exists'   => count($productsWithSku),
            'posts_inserted' => count($products['inserted']),
            'posts_updated'  => count($products['updated']),
            'posts_deleted'  => count($products['deleted']),
            'posts_now'      => count(WW_Functions::get_products_sku())
        ];
    }

    static function set_post_thumbnail($imageUrl, $postId): int
    {
        $path = self::save_image($imageUrl);

        if (!$path) {
            return 0;
        }

        $attachId = self::upload_image($path);

        set_post_thumbnail($postId, $attachId);

        return $attachId;
    }

    static function save_image($imageUrl): string
    {
        $uploadDir = wp_upload_dir();
        $getImage = file_get_contents($imageUrl);
        $filename = self::generate_string() . '.png';
        $path = $uploadDir['path'] . '/' . $filename;

        if (!file_put_contents($path, $getImage)) {
            return 0;
        }

        return $path;
    }

    static function upload_image($path): int
    {
        if (!$path) {
            return 0;
        }

        $attachment = [
            'guid'           => $path,
            'post_mime_type' => 'image/png',
            'post_title'     => sanitize_file_name(basename($path)),
            'post_content'   => '',
            'post_status'    => 'inherit'
        ];

        $attachId = wp_insert_attachment($attachment, $path);

        require_once(ABSPATH . 'wp-admin/includes/image.php');

        $generateAttachData = wp_generate_attachment_metadata($attachId, $path);
        $updateAttachMeta = wp_update_attachment_metadata($attachId, $generateAttachData);

        return $attachId;
    }

    static function delete_thumbnail($postId): bool
    {
        if (!$postId) {
            return false;
        }
        $thumbnailId = get_post_thumbnail_id($postId);
        delete_post_thumbnail($postId);
        return !!wp_delete_attachment($thumbnailId, true);
    }

    static function get_product_id_by_sku($sku = ''): int
    {
        if (!$sku) {
            return 0;
        }

        global $wpdb;

        return (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT meta.post_id FROM `{$wpdb->postmeta}` as meta 
                INNER JOIN `{$wpdb->posts}` as posts
                ON meta.post_id = posts.id
                WHERE meta.meta_key = '_sku'
                AND meta.meta_value = '%s';",
                $sku
            )
        );
    }

    static function get_products_sku(): array
    {
        global $wpdb;
        return $wpdb->get_results("
            SELECT meta.post_id as id, meta.meta_value as sku FROM `{$wpdb->postmeta}` as meta 
            INNER JOIN `{$wpdb->posts}` posts
            ON meta.post_id = posts.id
            WHERE meta.meta_key = '_sku'
            AND meta.meta_value IS NOT NULL",
        );
    }

    static function generate_string($length = 10): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }
}