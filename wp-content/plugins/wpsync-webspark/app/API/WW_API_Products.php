<?php


class WW_API_Products
{
    const API_GET_PRODUCTS = 'https://wp.webspark.dev/wp-api/products';

    static function api_send_response()
    {
        $curl = curl_init();

        $params = [
            CURLOPT_URL            => self::API_GET_PRODUCTS,
            CURLOPT_CUSTOMREQUEST  => 'GET',
            CURLOPT_RETURNTRANSFER => 1
        ];

        curl_setopt_array($curl, $params);

        $response = curl_exec($curl);

        curl_close($curl);

        return json_decode($response);
    }
}