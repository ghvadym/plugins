<?php


class WCAPI_Products extends WP_List_Table
{
    public function get_columns()
    {
        return [
            'cb'            => '<input type="checkbox" />',
            'id'            => __('ID', 'wcapi'),
            'title'         => __('Title', 'wcapi'),
            'regular_price' => __('Regular price', 'wcapi'),
            'sale_price'    => __('Sale price', 'wcapi'),
            'sku'           => __('SKU', 'wcapi'),
            'date'          => __('Date', 'wcapi'),
        ];
    }

    public function prepare_items()
    {
        $products = $this->tableData() ?: [];

        if (empty($products)) {
            return;
        }

        $this->add_custom_actions();

        $perPage = 10;
        $currentPage = $this->get_pagenum();
        $totalItems = count($products);

        usort($products, [&$this, 'sort_data']);

        $this->set_pagination_args([
            'total_items' => $totalItems,
            'per_page'    => $perPage,
            'total_pages' => ceil($totalItems / $perPage),
        ]);

        $products = array_slice($products, (($currentPage - 1) * $perPage), $perPage);

        $columns = $this->get_columns();
        $hidden = [];
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = [$columns, $hidden, $sortable];

        $this->items = $products;
    }

    public function tableData()
    {
        global $wpdb;

        $table = $wpdb->prefix . WCAPI_TABLE_POSTS;

        return $wpdb->get_results("
            SELECT `wc_product_id` as id, `regular_price`, `sale_price`, `sku`, `post_title` as title, DATE_FORMAT(post_date, '%d.%m.%Y | %H:%i:%s') as `date` FROM {$table}
            WHERE `wc_product_status` = '0'
        ", ARRAY_A);
    }

    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'id':
            case 'title':
            case 'sku':
            case 'regular_price':
            case 'sale_price':
            case 'date':
                return $item[$column_name];
            default:
                return print_r($item, true);
        }
    }

    public function get_sortable_columns()
    {
        return [
            'date' => ['date', false],
        ];
    }

    function column_title($item)
    {
        $actions = [
            'delete' => sprintf('<a href="?page=%s&action=%s&product_id=%s">Delete</a>', $_REQUEST['page'], 'delete_product', $item['id']),
            'add'    => sprintf('<a href="?page=%s&action=%s&product_id=%s">Add</a>', $_REQUEST['page'], 'insert_product', $item['id']),
        ];

        return sprintf('%1$s %2$s', $item['title'], $this->row_actions($actions));
    }

    function get_bulk_actions()
    {
        return [
            'delete_product' => 'Delete',
            'insert_product' => 'Insert products',
        ];
    }

    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="product_id[]" value="%s" />', $item['id']
        );
    }

    public function no_items()
    {
        $this->add_custom_actions(); ?>

        <p>
            <?php _e('There are no posts attached to you in the system yet.', 'wcapi'); ?>
        </p>
        <p>
            <?php _e('Add demo products on your own', 'wcapi'); ?> =>
            <a href="/wp-admin/admin.php?page=<?php echo WCAPI_DEMO_PRODUCTS_PAGE; ?>">
                <strong>
                    <?php _e('Add', 'wcapi'); ?>
                </strong>
            </a>
        </p>
        <p style="margin: 0">
            <?php _e('Update your attached API products', 'wcapi'); ?> =>
            <a href="/wp-admin/admin.php?page=<?php echo WCAPI_PRODUCTS_PAGE; ?>&get_api_products=update">
                <strong>
                    <?php _e('Update', 'wcapi'); ?>
                </strong>
            </a>
        </p>

        <?php
    }

    public function add_custom_actions()
    {
        $action = $this->current_action();
        $id = $_GET['product_id'] ?? '';
        $update = $_GET['get_api_products'] ?? '';
        $logout = $_GET['wcapi_user_logout'] ?? '';

        if (!empty($action) && $id) {
            if ($action === 'delete_product') {
                $this->deleteProduct($id);
            }

            if ($action === 'insert_product') {
                $this->addProduct($id);
            }
        }

        if ($update) {
            $this->get_api_products();
        }

        if ($logout) {
            WCAPI_Functions::wcapi_user_logout();
        }
    }

    protected function get_api_products()
    {
        $token = WCAPI_Functions::get_token();

        if (!$token) {
            return;
        }

        WCAPI_Init::admin_get_products_by_token($token);
    }

    public function addProduct($userId)
    {
        if (is_string($userId)) {
            $userId = explode(',', $userId);
        }

        if (!$this->removeProductsFromApi($userId)) {
            return;
        }

        $userId = array_map('strval', $userId);

        global $wpdb;
        $table = $wpdb->prefix . WCAPI_TABLE_POSTS;

        $query = "
            SELECT `wc_product_id`, `regular_price`, `sale_price`, `sku`, `post_title`, `post_excerpt`, `post_content`, `post_name`
            FROM {$table} WHERE wc_product_id %s";

        $userId = implode("','", $userId);

        $posts = $wpdb->get_results(sprintf($query, "IN ('{$userId}')"), ARRAY_A);

        $metaData = [
            'sku',
            'regular_price',
            'sale_price',
        ];

        foreach ($posts as $post) {
            $postId = wp_insert_post([
                'post_title'   => $post['post_title'],
                'post_excerpt' => $post['post_excerpt'],
                'post_content' => $post['post_content'],
                'post_name'    => $post['post_name'],
                'post_type'    => 'product',
            ]);

            if (!$postId) {
                continue;
            }

            foreach ($metaData as $metaItem) {
                if (empty($post[$metaItem])) {
                    continue;
                }

                update_post_meta($postId, '_' . $metaItem, $post[$metaItem]);
            }

            if (!empty($post['regular_price'])) {
                if (empty($post['sale_price'])) {
                    update_post_meta($postId, '_price', $post['regular_price']);
                } else {
                    update_post_meta($postId, '_price', $post['sale_price']);
                }
            }

            $wpdb->update($table,
                ['wc_product_status' => '1'],
                ['wc_product_id' => $post['wc_product_id']]
            );
        }

        WCAPI_Functions::redirect(WCAPI_PRODUCTS_PAGE, 'insert_product');
    }

    public function deleteProduct($productIds)
    {
        self::sortProductsForDeleting($productIds);
    }

    protected function sortProductsForDeleting($productIds)
    {
        if (is_string($productIds)) {
            if (str_contains($productIds, 'demo_')) {
                self::deleteDemoProducts($productIds);
            } else {
                self::deleteSimpleProducts($productIds);
            }

            return;
        }

        $simpleProducts = [];
        $demoProducts = [];

        foreach ($productIds as $productId) {
            if (str_contains($productId, 'demo_')) {
                $demoProducts[] = $productId;
            } else {
                $simpleProducts[] = $productId;
            }
        }

        self::deleteSimpleProducts($simpleProducts);
        self::deleteDemoProducts($demoProducts);
    }

    protected function deleteDemoProducts($productIds)
    {
        global $wpdb;
        $table = $wpdb->prefix . WCAPI_TABLE_POSTS;

        if (is_array($productIds)) {
            $productIds = implode(',', $productIds);
        }

        $deleteProduct = $wpdb->query("DELETE FROM {$table} WHERE `wc_product_id` IN ('{$productIds}')");

        if (!$deleteProduct) {
            return;
        }

        WCAPI_Functions::redirect(WCAPI_PRODUCTS_PAGE, 'delete_product');
    }

    protected function deleteSimpleProducts($productIds)
    {
        if (is_string($productIds)) {
            $productIds = explode(',', $productIds);
        }

        if (!$this->removeProductsFromApi($productIds)) {
            return;
        }

        global $wpdb;
        $table = $wpdb->prefix . WCAPI_TABLE_POSTS;

        $productsString = implode(',', $productIds);

        $deleteProduct = $wpdb->query("DELETE FROM {$table} WHERE `wc_product_id` IN ({$productsString})");

        if (!$deleteProduct) {
            return;
        }

        WCAPI_Functions::redirect(WCAPI_PRODUCTS_PAGE, 'delete_product');
    }

    protected function removeProductsFromApi($productIds): bool
    {
        $token = WCAPI_Functions::get_token();

        if (!$token) {
            return false;
        }

        $productsWithoutDemo = [];

        foreach ($productIds as $productId) {
            if (str_contains($productId, 'demo_')) {
                continue;
            }

            $productsWithoutDemo[] = $productId;
        }

        if (empty($productsWithoutDemo)) {
            return false;
        }

        $api = new API_Products_Remove($token, $productIds);
        $result = $api->api_send_response();

        if (!isset($result->status) || $result->status === 404) {
            return false;
        }

        return true;
    }

    public function status_message()
    {
        if (!isset($_GET['status']) || !$_GET['status']) {
            return;
        }

        if ($_GET['status'] === 'insert_product') {
            $message = 'Product has been added successfully.';
        }

        if ($_GET['status'] === 'delete_product') {
            $message = 'Product has been deleted.';
        }

        if (!isset($message)) {
            return;
        }

        require WCAPI_Functions::get_path('message');
    }

    protected function extra_tablenav($which)
    {
        $products = $this->tableData() ?: [];
        if (empty($products)) {
            return;
        }

        $dataActions = [
            'get_api_products' => __('Update', 'wcapi'),
        ];

        foreach ($dataActions as $key => $val) {
            submit_button($val, '', $key, false);
        }
    }

    private function sort_data($a, $b)
    {
        $orderby = 'date';
        $order = 'desc';

        if (!empty($_GET['orderby'])) {
            $orderby = $_GET['orderby'];
        }

        if (!empty($_GET['order'])) {
            $order = $_GET['order'];
        }

        $result = strcmp($a[$orderby], $b[$orderby]);

        if ($order === 'asc') {
            return $result;
        }

        return -$result;
    }

    protected function display_tablenav($which)
    {
        ?>
        <div class="tablenav <?php echo esc_attr($which); ?>">

            <?php if ($this->has_items()) : ?>
                <div class="alignleft actions bulkactions">
                    <?php $this->bulk_actions($which); ?>
                </div>
            <?php
            endif;
            $this->extra_tablenav($which);
            $this->pagination($which);
            ?>

            <br class="clear"/>
        </div>
        <?php
    }
}