<?php


class Swish_Repair_Log
{
    public static function insert_log(int $order_id = 0, string $status = '', string $log = '')
    {
        if (!$order_id || !$status || !$log) {
            return false;
        }

        global $wpdb;
        $table = $wpdb->prefix . SWISH_DB_TABLE_LOG;

        return $wpdb->insert(
            $table,
            [
                'order_id' => $order_id,
                'type'     => $status,
                'log'      => $log
            ],
            ['%d', '%s', '%s']
        );
    }

    public static function get_logs($order_id)
    {
        if (!$order_id) {
            return false;
        }

        global $wpdb;
        $table = $wpdb->prefix . SWISH_DB_TABLE_LOG;

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * from " . $table . " WHERE order_id = %d ORDER BY date_created DESC;",
                $order_id
            )
        );
    }
}