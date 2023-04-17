<?php


class Reco_Functions
{
    static function get_path(string $fileName): string
    {
        $pathToFile = RECO_PLUGIN_PATH . "parts/{$fileName}.php";

        if (!file_exists($pathToFile)) {
            return '';
        }

        return $pathToFile;
    }

    static function cut_str(string $text, int $limit = 100)
    {
        $plainText = trim(strip_tags($text));
        $clearText = str_replace('&nbsp;', '', $plainText);
        echo strlen($clearText) > $limit ? mb_substr($clearText, 0, $limit, 'utf-8') . '...' : $clearText;
    }

    static function rating($int = 0)
    {
        $ratingTagPositive = '<span></span>';
        $ratingTagNegative = '<em></em>';

        for ($i = 0; $i < 5; $i++) {
            echo $int > $i ? $ratingTagPositive : $ratingTagNegative;
        }
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
}