<?php


class API_Unauth
{
    protected string $hash = '';

    const API_USER_URL = 'http://wcapi.loc/wp-json/share-products/v1/user/unauth';

    public function __construct($hash)
    {
        $this->hash = $hash;
    }

    public function api_send_response()
    {
        $params = [
            'hash'  => $this->hash
        ];

        $curl = curl_init();

        $options = WCAPI_Functions::curl_array(self::API_USER_URL, [], 0, $params);

        curl_setopt_array($curl, $options);

        $response = curl_exec($curl);

        curl_close($curl);

        return json_decode($response);
    }
}