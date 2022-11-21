<?php


class Swish_Repair_Fixably_API
{
    public static function get_route(): string
    {
        $base_url = "https://demo.fixably.com/api/v3/";
        $domain = get_option('fixably_domain') ?: '';

        if ($domain) {
            $base_url = 'https://' . $domain . '.fixably.com/api/v3/';
        }

        return $base_url;
    }

    public static function api_request(array $args = [])
    {
        $api_key = get_option('fixably_api_key') ?: '';

        if (!$api_key) {
            return false;
        }

        $href = $args['href'] ?? '';
        $url = $args['url'] ?? '';
        $method = $args['method'] ?? 'GET';
        $data = $args['data'] ?? [];

        $curl_url = self::get_route();
        $curl_url .= $url;

        if ($href) {
            $curl_url = $href;
        }

        $curl = curl_init();

        $header = [
            'Content-Type: application/json',
            'Authorization: ' . $api_key,
        ];

        $params = [
            CURLOPT_URL            => $curl_url,
            CURLINFO_HEADER_OUT    => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $header,
        ];

        if ($method !== "GET") {
            $params[CURLOPT_POSTFIELDS] = json_encode($data);

            if ($method !== "POST") {
                $params[CURLOPT_CUSTOMREQUEST] = 'PATCH';
            } else {
                $params[CURLOPT_CUSTOMREQUEST] = 'POST';
            }
        }

        curl_setopt_array($curl, $params);

        $response = curl_exec($curl);

        curl_close($curl);

        return json_decode($response);
    }

    static function check_orders()
    {
        $ordersLinks = self::get_orders();

        if (empty($ordersLinks)) {
            return;
        }

        foreach ($ordersLinks as $orderLink) {
            $order = self::get_single_order($orderLink->href);

            if (!$order) {
                continue;
            }

            if ((int) $order->totalRemaining <= 0) {
                self::add_order_note($orderLink->href, [
                    [
                        'title' => 'Fel',
                        'text'  => 'Denna order innehåller ingen kostnad. Betallänk kan inte skapas. Vänligen kontrollera ordern.',
                    ]
                ]);

                continue;
            }

            $postId = self::order_post_process($order);

            if (!$postId) {
                continue;
            }

            $postUrl = get_permalink($postId);

            $queue = Swish_Repair_Fixably_API::get_num($order->queue->href);
            $linkCreatedStatus = self::link_created__status($queue);

            update_post_meta($postId, 'status_id', $linkCreatedStatus);
            self::change_order_status($orderLink->href, $linkCreatedStatus);

            $notes = [
                [
                    'title' => "Payment link has been created",
                    'text'  => $postUrl,
                ]
            ];

            $customerEmail = $order->contact->emailAddress ?? '';
            if ($customerEmail) {
                $mail = Swish_Repair_Mail::send_mail(
                    $customerEmail,
                    'Your order have been created. Please go to <a href="' . $postUrl . '" target="_blank">Payment page</a>.'
                );

                if ($mail) {
                    $queue = Swish_Repair_Fixably_API::get_num($order->queue->href);
                    $linkSentStatus = Swish_Repair_Fixably_API::link_sent__status($queue);

                    update_post_meta($postId, 'status_id', $linkSentStatus);
                    self::change_order_status($orderLink->href, $linkSentStatus);

                    $notes[] = [
                        'title' => "Payment link has been sent to $customerEmail",
                        'text'  => $postUrl,
                    ];

                    $status = 'SUCCESS';
                    $message = "Message has been sent to $customerEmail";
                } else {
                    $status = 'ERROR';
                    $message = "Message has not been sent to $customerEmail";
                }
            } else {
                $status = 'ERROR';
                $message = "An order not to contain the payer's mail";
            }

            Swish_Repair_Log::insert_log($order->id, $status, $message);

            self::add_order_note($orderLink->href, $notes);
        }
    }

    static function get_orders()
    {
        $swishStatusIds = [450, 454, 460, 465];
        $ids = implode(',', $swishStatusIds);

        $request = self::api_request([
            'url' => "orders?q=status.id:($ids)!",
        ]);

        return $request->items ?? [];
    }

    static function get_single_order(string $href = '')
    {
        if (!$href) {
            return false;
        }

        $request = self::api_request([
            'href' => $href . '?expand=status',
        ]);

        return isset($request->id) ? $request : false;
    }

    static function change_order_status(string $href = '', int $statusId = 0)
    {
        if (!$href || !$statusId) {
            return false;
        }

        $orderId = self::get_num($href);
        $request = self::api_request([
            'href'   => $href,
            'method' => 'PATCH',
            'data'   => [
                'status' => [
                    'id' => $statusId,
                ]
            ],
        ]);

        if (isset($request->id)) {
            $status = 'SUCCESS';
            $message = "Order status has been changed to #$statusId";
        } else {
            $status = 'ERROR';
            $message = $request->developerMessage ?? "Error - $request->status";
        }

        Swish_Repair_Log::insert_log($orderId, $status, $message);

        return $request->id ?? false;
    }

    static function get_num($href = null)
    {
        if (!$href) {
            return null;
        }

        preg_match("/[^\/]+$/", $href, $matches);
        return $matches[0];
    }

    static function order_post_process($order = null): int
    {
        if (!$order || !$order->totalRemaining) {
            return 0;
        }

        $checkOrder = get_posts([
            'numberposts'    => 1,
            'post_type'      => 'payment_link',
            'meta_key'       => 'order_id',
            'meta_value'     => $order->id,
        ]);

        $paymentPostArgs = [
            "post_content" => "[swish_rp_payment_link_shortcode amount='$order->totalRemaining' order='$order->id']",
            "post_title"   => "Payment URL for #" . $order->id,
            "post_type"    => "payment_link",
            "post_status"  => "publish",
            "meta_input"   => [
                "status_id"        => $order->status->id,
                "order_id"         => $order->id,
                "amount"           => $order->total ?? '',
                "amount_remaining" => $order->totalRemaining,
                "email"            => $order->contact->emailAddress ?? '',
                "phone"            => $order->contact->phoneNumber ?? '',
                "name"             => $order->contact->fullName ?? '',
                "company"          => $order->contact->company ?? '',
                "created_type"     => "auto",
            ],
        ];

        if (!empty($checkOrder)) {
            $paymentPostArgs['ID'] = $checkOrder[0]->ID;
            $postId = wp_update_post(wp_slash($paymentPostArgs));
        } else {
            $postId = wp_insert_post(wp_slash($paymentPostArgs));
        }

        return $postId;
    }

    static function add_order_note(string $href = '', array $data = [])
    {
        if (!$href || empty($data)) {
            return false;
        }

        $writerId = (int) get_option('fixably_writer_id') ?: 107460;
        $notesData = [];
        $notesDefault = [
            'type'      => 'INTERNAL',
            'createdBy' => $writerId
        ];

        foreach ($data as $item) {
            $notesData[] = array_merge($notesDefault, $item);
        }

        return self::api_request([
            'href'   => $href . '/notes',
            'method' => 'POST',
            'data'   => [
                'notes' => $notesData,
            ],
        ]);
    }

    static function link_created__status(int $queue = 0): int
    {
        switch ($queue) {
            case 1:
                return 457;
            case 2:
                return 458;
            case 3:
                return 463;
            case 4:
                return 468;
            default:
                return 0;
        }
    }

    static function link_sent__status(int $queue = 0): int
    {
        switch ($queue) {
            case 1:
                return 451;
            case 2:
                return 455;
            case 3:
                return 461;
            case 4:
                return 466;
            default:
                return 0;
        }
    }

    static function status__paid(int $queue = 0): int
    {
        switch ($queue) {
            case 1:
                return 452;
            case 2:
                return 456;
            case 3:
                return 462;
            case 4:
                return 467;
            default:
                return 0;
        }
    }
}