<?php


class API_Auth
{
    protected string $name = '';
    protected string $email = '';

    const API_USER_URL = 'http://wcapi.loc/wp-json/share-products/v1/user/auth';

    public function __construct($name, $email)
    {
        $this->name = $name;
        $this->email = $email;
    }

    public function api_send_response()
    {
        $params = [
            'name'  => $this->name,
            'email' => $this->email,
        ];

        $curl = curl_init();

        $options = WCAPI_Functions::curl_array(self::API_USER_URL, [], 0, $params);

        curl_setopt_array($curl, $options);

        $response = curl_exec($curl);

        curl_close($curl);

        return json_decode($response);
    }
}