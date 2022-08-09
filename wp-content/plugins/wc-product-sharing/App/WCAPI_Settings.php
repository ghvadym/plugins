<?php


class WCAPI_Settings
{
    static function create_table(): void
    {
        global $wpdb;
        $tablePosts = $wpdb->prefix . WCAPI_TABLE_POSTS;

        if (WCAPI_Functions::table_not_exists($tablePosts)) {
            self::wc_api_products_table();
        }
    }

    static function wc_api_products_table(): void
    {
        global $wpdb;
        $table = $wpdb->prefix . WCAPI_TABLE_POSTS;

        $query =
            "CREATE TABLE {$table} (
              `ID` bigint(20) UNSIGNED NOT NULL,
              `wc_product_status` varchar(20) NOT NULL DEFAULT '0',
              `wc_product_id` varchar(20) NOT NULL DEFAULT '0',
              `regular_price` varchar(20) DEFAULT NULL,
              `sale_price` varchar(20) DEFAULT NULL,
              `sku` varchar(20) DEFAULT NULL,
              `post_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `post_content` longtext NOT NULL DEFAULT '',
              `post_title` text NOT NULL,
              `post_excerpt` text NOT NULL DEFAULT '',
              `post_name` varchar(200) NOT NULL DEFAULT ''
            ) ENGINE=InnoDB;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($query);

        $wpdb->query("
            ALTER TABLE {$table}
              ADD PRIMARY KEY (`ID`),
              ADD UNIQUE (`wc_product_id`);
        ");

        $wpdb->query("
            ALTER TABLE {$table}
            MODIFY `ID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
        ");
    }

    static function update_api_products(array $posts = [], bool $demoData = false): array
    {
        global $wpdb;
        $table = $wpdb->prefix . WCAPI_TABLE_POSTS;

        if (WCAPI_Functions::table_not_exists($table)) {
            return [];
        }

        $posts = json_decode(json_encode($posts), true);
        $existsPosts = self::get_wcapi_posts_ids();
        $dbStatus = [];

        if (empty($posts)) {
            self::remove_inaccessible_posts($existsPosts);
        }

        if (!$demoData) {
            $inaccessiblePosts = array_diff($existsPosts, array_column($posts, 'wc_product_id'));
            if (!empty($inaccessiblePosts)) {
                if (self::remove_inaccessible_posts($inaccessiblePosts)) {
                    foreach ($existsPosts as $key => $id) {
                        if (in_array($id, $inaccessiblePosts)) {
                            unset($existsPosts[$key]);
                        }
                    }
                }
            }
        }

        foreach ($posts as $post) {
            if (in_array($post['wc_product_id'], $existsPosts)) {
                $update = $wpdb->update($table, [
                    'wc_product_status' => '0',
                    'regular_price'     => $post['regular_price'],
                    'sale_price'        => $post['sale_price'],
                    'sku'               => $post['sku'],
                    'post_title'        => $post['post_title'],
                    'post_excerpt'      => $post['post_excerpt'],
                    'post_content'      => $post['post_content'],
                    'post_name'         => $post['post_name'],
                ], [
                    'wc_product_id' => $post['wc_product_id'],
                ]);

                if ($update) {
                    $dbStatus[] = $post['wc_product_id'];
                }
            } else {
                $insert = $wpdb->insert($table, [
                    'wc_product_id' => $post['wc_product_id'],
                    'regular_price' => $post['regular_price'],
                    'sale_price'    => $post['sale_price'],
                    'sku'           => $post['sku'],
                    'post_title'    => $post['post_title'],
                    'post_excerpt'  => $post['post_excerpt'],
                    'post_content'  => $post['post_content'],
                    'post_name'     => $post['post_name'],
                ]);

                if ($insert) {
                    $dbStatus[] = $post['wc_product_id'];
                }
            }
        }

        return $dbStatus;
    }

    static function remove_inaccessible_posts(array $posts = []): bool
    {
        global $wpdb;
        $table = $wpdb->prefix . WCAPI_TABLE_POSTS;
        $posts = implode(',', array_map('absint', $posts));

        return !!$wpdb->query("DELETE FROM {$table} WHERE `wc_product_id` IN ({$posts})");
    }

    static function get_wcapi_posts_ids(): array
    {
        global $wpdb;
        $table = $wpdb->prefix . WCAPI_TABLE_POSTS;
        $getResults = $wpdb->get_results("SELECT `wc_product_id` FROM {$table} WHERE `wc_product_id` NOT LIKE 'demo_%'");

        if (!empty($getResults)) {
            $ids = [];

            foreach ($getResults as $id) {
                $ids[] = $id->wc_product_id;
            }

            return $ids;
        } else {
            return [];
        }
    }

    static function wc_api_settings(array $data = []): void
    {
        $options = get_option('wc_api_settings');

        if ($options && is_array($options)) {
            $data = array_merge($options, $data);
        }

        update_option('wc_api_settings', $data);
    }
}