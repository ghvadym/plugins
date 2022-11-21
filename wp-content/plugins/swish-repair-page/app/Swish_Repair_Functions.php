<?php


class Swish_Repair_Functions
{
    static function get_path(string $fileName): string
    {
        $pathToFile = SWISH_RP_DIR . "parts/{$fileName}.php";

        if (!file_exists($pathToFile)) {
            return '';
        }

        return $pathToFile;
    }

    static function is_mobile(): bool
    {
        $useragent = $_SERVER['HTTP_USER_AGENT'];
        return (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',
                $useragent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',
                substr($useragent, 0, 4)));
    }

    static function swish_process_status(int $id = 0): string
    {
        if (!$id) {
            return '';
        }

        $orderStatus = get_post_meta($id, 'status', true);

        if (!$orderStatus) {
            return '';
        }

        if ($orderStatus === 'paid') {
            return get_option('swish_form_paid_message') ?: __('Beställning betald', 'swish-rp');
        }

        if ($orderStatus !== 'process') {
            return '';
        }

        $statusProcessTimeStart = get_post_meta($id, 'status_process_time_start', true);
        $paymentInProcess = $statusProcessTimeStart && (time() - (int)$statusProcessTimeStart) < SWISH_TIME_PAYMENT;

        if (!$paymentInProcess) {
            return '';
        }

        return get_option('swish_form_process_message') ?: __('Beställning under betalning', 'swish-rp');
    }

    static function check_server_ip(): bool
    {
        $ip = self::get_ip();

        if (!$ip) {
            return false;
        }

        if (get_option('swish_sandbox_mode')) {
            $whiteList = [
                '89.46.83',
                '103.57.74',
            ];
        } else {
            $whiteList = [
                '213.132.115',
                '35.228.51',
                '34.140.166',
            ];
        }

        foreach ($whiteList as $ipItem) {
            if (strpos($ip, $ipItem) !== false) {
                return true;
            }
        }

        return false;
    }

    static function get_ip()
    {
        $user_ip = $_SERVER['REMOTE_ADDR'];

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $user_ip = $_SERVER['HTTP_CLIENT_IP'];
        } else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $user_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }

        return $user_ip;
    }

    static function get_swish_response(): array
    {
        $input = file_get_contents('php://input');

        $inputArray = json_decode($input, true);

        file_put_contents(SWISH_RP_DIR . 'swish_payment.log', date('Y-m-d H:i:s') . ' ' . self::get_ip() . ' ' . $input . PHP_EOL, FILE_APPEND);

        return $inputArray ?: [];
    }

    static function manual_payment_page()
    {
        $userAuth = $_COOKIE['swishauth'] ?? '';
        $fileName = 'manual-payment-reqest';

        if (empty($userAuth)) {
            $fileName = 'manual-payment-login';
        }

        include Swish_Repair_Functions::get_path($fileName);
    }

    static function swish_manual_payment_process()
    {
        $orderId = $_GET['order'] ?? '';
        $swishPayment = $_GET['swish_payment'] ?? '';

        if ($swishPayment !== 'success' || !$orderId) {
            return;
        }

        $swishResponse = self::get_swish_response();

        if (empty($swishResponse)) {
            return;
        }

        if (!self::check_server_ip()) {
            return;
        }

        if (empty($swishResponse['id'])) {
            return;
        }

        self::manual_table_insert_log($swishResponse);

        $swishOrderStatus = $swishResponse['status'] ?? '';
        if (strtolower($swishOrderStatus) !== 'paid') {
            return;
        }

        $getUrl = Swish_Repair_Fixably_API::get_route();
        $orderHref = $getUrl . "orders/$orderId";
        $order = Swish_Repair_Fixably_API::get_single_order($orderHref);

        if ($order && !in_array($order->status->id, Swish_Repair_API::paid_statuses())) {
            $queue = Swish_Repair_Fixably_API::get_num($order->queue->href);

            if ($queue) {
                $statusPaid = Swish_Repair_Fixably_API::status__paid($queue);
                $changeStatus = Swish_Repair_Fixably_API::change_order_status($orderHref, $statusPaid);

                if ($changeStatus) {
                    Swish_Repair_Fixably_API::add_order_note($orderHref, [
                        [
                            'title' => 'PAID',
                            'text'  => 'Paid with manual payment'
                        ],
                    ]);
                }

                $post = get_posts([
                    'post_type'   => 'payment_link',
                    'numberposts' => 1,
                    'meta_key'    => 'order_id',
                    'meta_value'  => $orderId
                ]);

                if (!empty($post)) {
                    $postId = $post[0]->ID;
                    update_post_meta($postId, 'status_id', $statusPaid);
                    update_post_meta($postId, 'status', 'paid');

                    Swish_Repair_Log::insert_log($orderId, 'SUCCESS', "Order has been paid with manual payment");
                }
            }
        }

        $customerEmail = $order->contact->emailAddress ?? '';
        if ($customerEmail) {
            Swish_Repair_Mail::send_mail(
                $customerEmail,
                "Order #$orderId has been paid."
            );
        }
    }

    static function manual_table_insert_log(array $data = []): int
    {
        if (empty($data)) {
            return 0;
        }

        global $wpdb;
        $table = $wpdb->prefix . SWISH_DB_MANUAL_PAYMENT_TABLE_LOG;

        return $wpdb->insert($table, [
            'order_id'    => $data['payeePaymentReference'],
            'amount'      => $data['amount'],
            'payment_id'  => $data['id'],
            'payer_alias' => $data['payerAlias'],
            'status'      => $data['status'],
            'message'     => $data['message']
        ]);
    }
}