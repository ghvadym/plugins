<?php


class Reco_API
{
    private static string $api_key = '';
    private static string $env_id = '';
    private static string $url = '';

    private static function register(): bool
    {
        $setting_fields = get_option('reco_reviews_settings', []);

        self::$api_key = !empty($setting_fields['api_key']) ? $setting_fields['api_key'] : '';
        self::$env_id = !empty($setting_fields['env_id']) ? $setting_fields['env_id'] : '';
        self::$url = 'https://api.reco.se/v3/venue/' . self::$env_id . '/';

        return self::$api_key && self::$env_id;
    }

    public static function call(string $route = '')
    {
        if (!self::register()) {
            return [];
        }

        $url = !$route ? self::$url : self::$url . $route;

        $header = [
            'Content-Type: application/json',
            'X-Reco-ApiKey: ' . self::$api_key,
        ];

        $params = [
            CURLOPT_URL            => $url,
            CURLINFO_HEADER_OUT    => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $header
        ];

        $curl = curl_init();

        curl_setopt_array($curl, $params);

        $response = curl_exec($curl);

        curl_close($curl);

        return json_decode($response);
    }
}