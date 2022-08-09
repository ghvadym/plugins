<?php

namespace Includes;

class Functions
{
    public static function getPath($fileName): string
    {
        $themeFile = THEME_PATH . '/' . PLUGIN_NAME . "/{$fileName}.php";

        if (file_exists($themeFile)) {
            return $themeFile;
        }

        return PLUGIN_PATH . "templates/{$fileName}.php";
    }

    public static function getCurrentLocale(): string
    {
        return substr(get_locale(), 0, 2);
    }

    public static function stringReplace($string): string
    {
        return ucfirst(str_replace("_", " ", $string));
    }

    public static function activeMonth($month): string
    {
        return date('m') === $month ? ' active' : '';
    }

    public static function textLimit(string $text, int $limit = 40): string
    {
        return strlen($text) > $limit ? substr($text, 0, $limit) . '...' : $text;
    }

    public static function doChecked($search, $data): string
    {
        if (empty($data)) {
            return '';
        }
        $checked = in_array($search, $data);
        return $checked ? 'checked' : '';
    }

    public static function sendResponse($file, $data = [])
    {
        ob_start();
        require self::getPath($file);
        $html = ob_get_contents();
        ob_end_clean();

        wp_send_json([
            'result' => $html,
            'posts'  => $data['posts'],
        ]);
    }

    public static function explodeArray($data): array
    {
        return $data ? explode(',', $data) : [];
    }

    public static function preloaderHtml()
    {
        ?>
        <div class="mp-preloader">
            <?php for ($i = 1; $i <= 12; $i++) : ?>
                <div class="bar<?php echo $i ?>"></div>
            <?php endfor; ?>
        </div>
        <?php
    }
}