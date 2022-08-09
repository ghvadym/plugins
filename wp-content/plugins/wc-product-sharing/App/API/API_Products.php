<?php


class API_Products
{
    protected string $token = '';

    const API_PRODUCTS_URL = 'http://wcapi.loc/wp-json/share-products/v1/products';

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function api_send_response()
    {
        $curl = curl_init();

        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'token: ' . $this->token
        ];

        $options = WCAPI_Functions::curl_array(
            self::API_PRODUCTS_URL,
            $headers,
            1
        );

        curl_setopt_array($curl, $options);

        $response = curl_exec($curl);

        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $body = substr($response, $header_size);

        curl_close($curl);

        return json_decode($body);
    }
}