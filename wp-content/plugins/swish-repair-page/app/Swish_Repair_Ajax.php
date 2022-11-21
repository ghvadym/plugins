<?php


class Swish_Repair_Ajax
{
    static function init()
    {
        add_action('wp_ajax_nopriv_request_payment_with_swish', [self::class, 'swish_payment_request']);
        add_action('wp_ajax_request_payment_with_swish', [self::class, 'swish_payment_request']);

        add_action('wp_ajax_nopriv_order_status_after_paid', [self::class, 'order_status_after_paid']);
        add_action('wp_ajax_order_status_after_paid', [self::class, 'order_status_after_paid']);

        add_action('wp_ajax_nopriv_manual_payment_login', [self::class, 'manual_payment_login']);
        add_action('wp_ajax_manual_payment_login', [self::class, 'manual_payment_login']);

        add_action('wp_ajax_nopriv_manual_payment_request', [self::class, 'manual_payment_request']);
        add_action('wp_ajax_manual_payment_request', [self::class, 'manual_payment_request']);
    }

    static function manual_payment_login()
    {
        $pass = $_POST['pass'] ?? '';

        if (!$pass) {
            return;
        }

        $realPass = get_option('fixably_login_password');

        if (!$realPass) {
            wp_send_json([
                'status'  => 'error',
                'message' => __('Rätt lösenord är inte inställt. Kontakta administratören', 'swish-rp'),
            ]);

            return;
        }

        if ($pass !== $realPass) {
            wp_send_json([
                'status'  => 'error',
                'message' => __('Lösenordet är inte korrekt', 'swish-rp'),
            ]);

            return;
        }

        ob_start();
        include Swish_Repair_Functions::get_path('manual-payment-reqest');
        $html = ob_get_contents();
        ob_end_clean();

        wp_send_json([
            'status' => 'success',
            'html'   => $html,
        ]);
    }

    static function order_status_after_paid()
    {
        $action = $_POST['action'] ?? '';
        $postId = $_POST['postId'] ?? '';

        if (!$action || !$postId) {
            return;
        }

        $paymentStatus = Swish_Repair_Functions::swish_process_status($postId);

        if (!$paymentStatus) {
            wp_send_json([
                'status'  => 'error',
                'message' => __('Process message not found', 'swish-rp'),
            ]);
        }

        wp_send_json([
            'status' => 'success',
            'text'   => $paymentStatus,
        ]);
    }

    static function manual_payment_request()
    {
        $postData = $_POST ?? [];

        if (empty($postData)) {
            return;
        }

        $orderId = $postData['payeePaymentReference'] ?? '';
        if (!$orderId) {
            return;
        }

        $request = Swish_Repair_Fixably_API::api_request([
            'url' => "orders/$orderId?expand=status",
        ]);

        if (empty($request->id)) {
            wp_send_json([
                'status'  => 'error',
                'message' => __('Angivet ordernummer existerar inte', 'swish-rp'),
            ]);

            return;
        }


        if (isset($request->status->id) && in_array($request->status->id, Swish_Repair_API::paid_statuses())) {
            wp_send_json([
                'status'  => 'error',
                'message' => "Beställ #$orderId i status \"Betald med swish\" redan"
            ]);

            return;
        }

        $sandboxMode = get_option('swish_sandbox_mode');
        $data = [];
        $paymentDataKeys = [
            'amount',
            'payeePaymentReference',
            'payerAlias',
        ];

        if (!$sandboxMode) {
            $paymentDataKeys[] = 'message';
        }

        foreach ($paymentDataKeys as $key) {
            $postItem = $postData[$key];

            if (empty($postItem)) {
                continue;
            }

            if ($key === 'amount') {
                $data[$key] = (int)$postItem;
            } else {
                $data[$key] = (string)$postItem;
            }
        }

        $swishPage = get_page_by_title(SWISH_MANUAL_ORDER_PAGE_NAME);
        $swishPageId = $swishPage->ID;
        $orderNumber = $data['payeePaymentReference'] ?? '';

        $pageUrl = get_the_permalink($swishPageId);
        $siteUrl = sprintf(
            '%s?swish_payment=success&order=%s',
            $pageUrl,
            (string)$orderNumber
        );
        $data['callbackUrl'] = $siteUrl;

        $paymentRequest = Swish_Repair_API::api_request($data);
        $location = $paymentRequest['location'] ?? '';

        if (empty($location)) {
            wp_send_json([
                'status'  => 'error',
                'message' => __('Dåligt svar', 'swish-rp'),
            ]);
        }

        $getSuccessMessage = get_option('fixably_manual_order_message') ?: "<h2>Betalningsförfrågan för order #[order_number] har skickats.</h2>";
        $successMessage = str_replace('[order_number]', $orderId, $getSuccessMessage);

        wp_send_json([
            'html' => $successMessage
        ]);
    }

    static function swish_payment_request()
    {
        $postData = $_POST ?? [];
        if (empty($postData)) {
            return;
        }

        $sandboxMode = get_option('swish_sandbox_mode');
        $data = [];
        $paymentDataKeys = [
            'amount',
            'payeePaymentReference',
        ];

        if (!Swish_Repair_Functions::is_mobile()) {
            $paymentDataKeys[] = 'payerAlias';
        }

        if (!$sandboxMode) {
            $paymentDataKeys[] = 'message';
        }

        foreach ($paymentDataKeys as $key) {
            $postItem = $postData[$key];

            if (empty($postItem)) {
                continue;
            }

            if ($key === 'amount') {
                $data[$key] = (int)$postItem;
            } else {
                $data[$key] = (string)$postItem;
            }
        }

        $postId = $_POST['postId'] ?? '';
        $orderNumber = $data['payeePaymentReference'] ?? '';

        $postUrl = get_the_permalink($postId);
        $siteUrl = sprintf(
            '%s?swish_payment=success&order=%s',
            $postUrl,
            (string)$orderNumber
        );
        $data['callbackUrl'] = $siteUrl;

        $paymentRequest = Swish_Repair_API::api_request($data);

        $token = $paymentRequest['paymentrequesttoken'] ?? '';
        $location = $paymentRequest['location'] ?? '';

        if (empty($location)) {
            wp_send_json([
                'status'  => 'error',
                'message' => __('Dåligt svar', 'swish-rp'),
            ]);
        }

        update_post_meta($postId, 'status', 'process');
        update_post_meta($postId, 'status_process_time_start', time());

        if (Swish_Repair_Functions::is_mobile()) {
            if (empty($token)) {
                wp_send_json([
                    'status'  => 'error',
                    'device'  => 'mobile',
                    'message' => __('Dåligt svar', 'swish-rp'),
                ]);
            } else {
                wp_send_json([
                    'status' => 'success',
                    'device' => 'mobile',
                    'url'    => sprintf(
                        'swish://paymentrequest?token=%s&callbackurl=%s',
                        (string)$token,
                        urlencode($siteUrl)
                    ),
                ]);
            }
        } else {
            wp_send_json([
                'status'  => 'success',
                'device'  => 'desktop',
                'url'     => $postUrl,
                'process' => true,
            ]);
        }
    }

    static function swish_payment_result_process(int $postId = 0): bool
    {
        $orderId = $_GET['order'] ?? '';
        $swishPayment = $_GET['swish_payment'] ?? '';

        if (!$swishPayment || $swishPayment !== 'success' || !$orderId) {
            return false;
        }

        $swishResponse = Swish_Repair_Functions::get_swish_response();

        if (empty($swishResponse)) {
            return false;
        }

        $payeePaymentReference = $swishResponse['payeePaymentReference'] ?? '';
        $swishOrderStatus = $swishResponse['status'] ?? '';
        $errorMessage = $swishResponse['errorMessage'] ?? '';

        if (!Swish_Repair_Functions::check_server_ip()) {
            Swish_Repair_Log::insert_log($orderId, 'ERROR', "Request IP is not allowed");
            return false;
        }

        if (!$swishOrderStatus || !$payeePaymentReference) {
            Swish_Repair_Log::insert_log($orderId, 'ERROR', 'No data from SWISH API');
            return false;
        }

        if (strtolower($swishOrderStatus) !== 'paid') {
            $message = !empty($errorMessage) ? $errorMessage : sprintf('Order - %s', $swishOrderStatus);
            Swish_Repair_Log::insert_log($orderId, 'ERROR', $message);
            return false;
        }

        if ($payeePaymentReference != $orderId) {
            Swish_Repair_Log::insert_log($orderId, 'ERROR', 'Order ID does not match SWISH payment ID');
            return false;
        }

        $getUrl = Swish_Repair_Fixably_API::get_route();
        $orderHref = $getUrl . "orders/$orderId";
        $order = Swish_Repair_Fixably_API::get_single_order($orderHref);

        if (in_array($order->status->id, Swish_Repair_API::paid_statuses())) {
            return false;
        }

        $notes = [
            [
                'title' => "PAID",
                'text'  => "Order #$orderId",
            ],
        ];

        Swish_Repair_Log::insert_log($orderId, 'SUCCESS', "Order #$orderId has been paid");

        Swish_Repair_Fixably_API::add_order_note($orderHref, $notes);

        $customerEmail = $order->contact->emailAddress ?? '';
        if ($customerEmail) {
            Swish_Repair_Mail::send_mail(
                $customerEmail,
                "Order #$orderId has been paid."
            );
        }

        if (!$order) {
            Swish_Repair_Log::insert_log($orderId, 'ERROR', "Paid order #$orderId has not been found in Fixably API");
        }

        $queue = Swish_Repair_Fixably_API::get_num($order->queue->href);
        $statusPaid = Swish_Repair_Fixably_API::status__paid($queue);

        if ($postId) {
            update_post_meta($postId, 'status_id', $statusPaid);
            update_post_meta($postId, 'status', 'paid');
        }

        Swish_Repair_Fixably_API::change_order_status($orderHref, $statusPaid);


        return true;
    }
}