<?php


class API_Products_Remove
{
    protected string $token = '';

    protected array $products = [];

    const API_PRODUCTS_URL = 'http://wcapi.loc/wp-json/share-products/v1/products/remove';

    public function __construct($token, $products)
    {
        $this->token = $token;
        $this->products = $products;
    }

    public function api_send_response()
    {
        $curl = curl_init();

        $headers = [
            'token: ' . $this->token,
        ];

        $params = [
            'products' => $this->products
        ];

        $options = WCAPI_Functions::curl_array(
            self::API_PRODUCTS_URL,
            $headers,
            0,
            http_build_query($params)
        );

        curl_setopt_array($curl, $options);

        $response = curl_exec($curl);

        curl_close($curl);

        return json_decode($response);
    }
}