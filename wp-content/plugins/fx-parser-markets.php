<?php

/*
 * Plugin Name: FX Parser Markets
 * Description: Functionality for parsing markets
 * Author: Flexi
 * Version: 1.0
 */

if (!defined('ABSPATH')) exit;

createTables();

require WP_CONTENT_DIR . '/parser/vendor/autoload.php';

use PHPHtmlParser\Dom;

add_action('admin_head', 'markets_scraper_activation');
function markets_scraper_activation()
{
    if (!wp_next_scheduled('markets_scraper_event')) {
        wp_schedule_event(strtotime('05:30:00'), 'daily', 'markets_scraper_event');
    }
}

class DataParser
{
    private static Dom $dom;
    private static array $data = [];

    public static function init(array $params): void
    {
        foreach ($params as $type => $data) {
            self::load($data['url']);
            $func = $data['func'] ?? [self::class, 'getByPath'];
            self::$data[$type] = self::get($func, $data['path'], $data['coff']);
        }
        self::save();
    }

    private static function load(string $url): void
    {
        self::$dom = new Dom;
        self::$dom->loadFromUrl($url);
    }

    private static function get($func, string $path, $coff)
    {
        return $func(self::$dom, $path, $coff);
    }

    private static function time()
    {
        date_default_timezone_set('Europe/Kiev');
        return date("Y-m-d H:i:s", time() - 60 * 60 * 24);
    }

    private static function save()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'markets_values';
        $checkId = self::checkRowExist() ?? null;
        self::$data['time'] = self::time();

        if ($checkId) {
            $wpdb->update($table, self::$data, ['ID' => (int)$checkId]);
        } else {
            $wpdb->insert($table, self::$data);
        }
    }

    private static function checkRowExist()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'markets_values';
        $currentTime = date("Y-m-d", strtotime(self::time()));
        return $wpdb->get_var("SELECT id FROM " . $table . " WHERE DATE_FORMAT(`time`, '%Y-%m-%d') = '" . $currentTime . "' ORDER BY `time`  DESC LIMIT 1");
    }

    public static function getByPath($dom, $path, $coff)
    {
        $array = json_decode($dom->innerHtml);
        $rows = $array->Rows;
        $avgVal = ($rows[1]->Values[0] + $rows[2]->Values[0]) / 2;
        return intval($avgVal * $coff);
    }

    public static function getIoByPath($dom, $path, $coff)
    {
        return intval($dom->find($path)[15]->innerHtml) * $coff;
    }
}

add_action('markets_scraper_event', 'add_markets_event');
function add_markets_event()
{
    if (date('N') == 1 || date('N') == 7) {
        return;
    }

    $lastCoff = getLastCoff();
    $params = [
        'scrap' => [
            'url'  => 'https://www.lme.com/api/trading-data/day-delayed?datasourceId=935e2e7d-00ed-4297-a655-0dd492dedf5a',
            'path' => '.data-set-tabs .data-set-tabs__bottom .data-set-table .data-set-table__table .data-set-table__body .data-set-table__row td',
            'coff' => $lastCoff->scrap,
        ],
        'io'    => [
            'url'  => 'https://www.steelhome.cn/english/tksshpi/shpi_tkspz.php',
            'path' => '.tks_shpi tbody tr td',
            'coff' => $lastCoff->io,
            'func' => [DataParser::class, 'getIoByPath'],
        ],
        'hrc'   => [
            'url'  => 'https://www.lme.com/api/trading-data/day-delayed?datasourceId=8db7b17d-d328-4834-80fe-4e0218324806',
            'path' => '.data-set-tabs .data-set-tabs__bottom .data-set-table .data-set-table__table .data-set-table__body .data-set-table__row td',
            'coff' => $lastCoff->hrc,
        ],
        'rebar' => [
            'url'  => 'https://www.lme.com/api/trading-data/day-delayed?datasourceId=f894524d-cadf-404b-995a-e9a19f49d394',
            'path' => '.data-set-tabs .data-set-tabs__bottom .data-set-table .data-set-table__table .data-set-table__body .data-set-table__row td',
            'coff' => $lastCoff->rebar,
        ],
    ];

    DataParser::init($params);
}

//----------------------------------------------------

function parser_admin_assets()
{
    $path  = content_url('/parser/inc/assets');
    wp_enqueue_style('parser-market-styles', $path . '/css/parser-markets-style.css', [], time());
    wp_enqueue_script('parser-market-script', $path . '/js/parser-markets-script.js', '', time(), true);
    wp_localize_script('parser-market-script', 'myajax', ['ajaxurl' => admin_url('admin-ajax.php')]);
}

add_action('admin_enqueue_scripts', 'parser_admin_assets');

function getLastData()
{
    global $wpdb;
    $table = $wpdb->prefix . 'markets_values';
    return $wpdb->get_row('SELECT * FROM ' . $table . ' ORDER BY `time` DESC LIMIT 1');
}

function parser_markets_callback()
{
    $lastRow = getLastData();
    $lastCoff = getLastCoff();
    ?>

    <form class="parser-markets markets-form-wrap" id="mp-form">
        <div class="markets-form-title">
            <h3 class="title__head">
                <?php _e('Scraper Markets', 'GMK'); ?>
            </h3>
        </div>
        <div class="markets-form-wrap-body">
            <div class="parser-markets__filter">
                <label for="mp-calendar" class="markets-form-row-label">
                    <?php _e('Choose the date', 'GMK'); ?>
                </label>
                <input type="date" name="calendar" id="mp-calendar"
                       value="<?php echo date('Y-m-d', strtotime($lastRow->time)); ?>">
            </div>
            <div class="markets-form-rows-wrap">
                <div class="markets-form-row">
                    <label for="scrap" class="markets-form-row-label">
                        <?php _e('Scrap_exchange', 'GMK'); ?>
                    </label>
                    <input type="text" name="scrap" id="scrap"
                           value="<?php echo $lastRow->scrap ?>">
                </div>
                <div class="markets-form-row">
                    <label for="io" class="markets-form-row-label">
                        <?php _e('IO_exchange', 'GMK'); ?>
                    </label>
                    <input type="text" name="io" id="io"
                           value="<?php echo $lastRow->io ?>">
                </div>
                <div class="markets-form-row">
                    <label for="hrc" class="markets-form-row-label">
                        <?php _e('HRC_exchange', 'GMK'); ?>
                    </label>
                    <input type="text" name="hrc" id="hrc"
                           value="<?php echo $lastRow->hrc ?>">
                </div>
                <div class="markets-form-row">
                    <label for="rebar" class="markets-form-row-label">
                        <?php _e('Rebar_exchange', 'GMK'); ?>
                    </label>
                    <input type="text" name="rebar" id="rebar"
                           value="<?php echo $lastRow->rebar ?>">
                </div>
            </div>
            <div class="parser-markets__btn">
                <button type="submit" class="btn button button-primary epsilon-review-button">
                    <?php _e('Update', 'GMK') ?>
                </button>
            </div>
        </div>
        <?php preloader() ?>
    </form>

    <form class="markets-coff markets-form-wrap" id="form-coff">
        <div class="markets-form-title">
            <h3 class="title__head">
                <?php _e('Market Coefficients', 'GMK'); ?>
            </h3>
        </div>
        <div class="markets-form-wrap-body">
            <div class="markets-form-rows-wrap">
                <div class="markets-form-row">
                    <label for="scrap-coff" class="markets-form-row-label">
                        <?php _e('Scrap_exchange', 'GMK'); ?>
                    </label>
                    <input type="text" name="scrap-coff" id="scrap-coff"
                           value="<?php echo $lastCoff->scrap ?>">
                </div>
                <div class="markets-form-row">
                    <label for="io-coff" class="markets-form-row-label">
                        <?php _e('IO_exchange', 'GMK'); ?>
                    </label>
                    <input type="text" name="io-coff" id="io-coff"
                           value="<?php echo $lastCoff->io ?>">
                </div>
                <div class="markets-form-row">
                    <label for="hrc-coff" class="markets-form-row-label">
                        <?php _e('HRC_exchange', 'GMK'); ?>
                    </label>
                    <input type="text" name="hrc-coff" id="hrc-coff"
                           value="<?php echo $lastCoff->hrc ?>">
                </div>
                <div class="markets-form-row">
                    <label for="rebar-coff" class="markets-form-row-label">
                        <?php _e('Rebar_exchange', 'GMK'); ?>
                    </label>
                    <input type="text" name="rebar-coff" id="rebar-coff"
                           value="<?php echo $lastCoff->rebar ?>">
                </div>
            </div>
            <button type="submit" class="btn button button-primary epsilon-review-button">
                <?php _e('Change', 'GMK') ?>
            </button>
        </div>
        <?php preloader() ?>
    </form>
    <?php
}

function preloader()
{
    ?>
    <div class="mp-preloader">
        <?php for ($i = 1; $i <= 12; $i++) : ?>
            <div class="bar<?php echo $i ?>"></div>
        <?php endfor; ?>
    </div>
    <?php
}

function createTableVal()
{
    global $wpdb;
    $table = $wpdb->prefix . 'markets_values';

    $sql = "CREATE TABLE IF NOT EXISTS " . $table . " (
        `id` BIGINT(120) UNSIGNED NOT NULL AUTO_INCREMENT,
        `scrap` DECIMAL(6,2) UNSIGNED NULL,
        `io` DECIMAL(6,2) UNSIGNED NULL,
        `hrc` DECIMAL(6,2) UNSIGNED NULL,
        `rebar` DECIMAL(6,2) UNSIGNED NULL,
        `time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

function createTableCoff()
{
    global $wpdb;
    $table = $wpdb->prefix . 'markets_coffs';

    $sql =
        "CREATE TABLE IF NOT EXISTS " . $table . " (
        `id` BIGINT(120) UNSIGNED NOT NULL AUTO_INCREMENT,
        `scrap` DECIMAL(6,3) UNSIGNED NULL,
        `io` DECIMAL(6,3) UNSIGNED NULL,
        `hrc` DECIMAL(6,3) UNSIGNED NULL,
        `rebar` DECIMAL(6,3) UNSIGNED NULL,
        `time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    
    INSERT INTO " . $table . " 
    (scrap, io, hrc, rebar) 
    VALUES (0.68, 1, 0.673, 1.083);";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

function createTables()
{
    global $wpdb;
    $tableVal = $wpdb->prefix . 'markets_values';
    $tableCoff = $wpdb->prefix . 'markets_coffs';

    if (!$wpdb->get_var("SHOW TABLES LIKE '" . $tableVal . "'")) createTableVal();
    if (!$wpdb->get_var("SHOW TABLES LIKE '" . $tableCoff . "'")) createTableCoff();
}

add_action('admin_menu', 'parser_markets_options_page', 25);
function parser_markets_options_page()
{
    add_submenu_page(
        'options-general.php',
        'Parser Markets Options',
        'Scraper Markets',
        'manage_options',
        'parser_markets_options',
        'parser_markets_callback'
    );
}

function market_parser_update_value()
{
    if (!$_POST) return;

    $date = $_POST['calendar'] ?? null;
    $scrap = $_POST['scrap'] ?? null;
    $io = $_POST['io'] ?? null;
    $hrc = $_POST['hrc'] ?? null;
    $rebar = $_POST['rebar'] ?? null;

    global $wpdb;
    $table = $wpdb->prefix . 'markets_values';

    $getIdRow = $wpdb->get_row("SELECT id, `time` FROM " . $table . " WHERE DATE_FORMAT(`time`, '%Y-%m-%d') = '" . $date . "'");
    if ($getIdRow) {
        $wpdb->query("UPDATE " . $table . " SET scrap=" . $scrap . ", io=" . $io . ", hrc=" . $hrc . ", rebar=" . $rebar . " WHERE id=" . $getIdRow->id);
    } else {
        $wpdb->query("INSERT INTO `wp_markets_values` (scrap, io, hrc, rebar, `time`) VALUES (" . $scrap . ", " . $io . ", " . $hrc . ", " . $rebar . ", '" . $date . "')");
    }
}

function market_parser_get_row_by_date()
{
    $date = $_POST['calendar'] ?? null;
    global $wpdb;
    $table = $wpdb->prefix . 'markets_values';
    $row = $wpdb->get_row("SELECT * FROM " . $table . " WHERE DATE_FORMAT(`time`, '%Y-%m-%d') = '" . $date . "' LIMIT 1");

    ob_start();
    ?>

    <div class="markets-form-row">
        <label for="scrap" class="markets-form-row-label">
            <?php _e('Scrap_exchange', 'GMK'); ?>
        </label>
        <input type="text" name="scrap" id="scrap"
               value="<?php echo $row->scrap ?>">
    </div>
    <div class="markets-form-row">
        <label for="io" class="markets-form-row-label">
            <?php _e('IO_exchange', 'GMK'); ?>
        </label>
        <input type="text" name="io" id="io"
               value="<?php echo $row->io ?>">
    </div>
    <div class="markets-form-row">
        <label for="hrc" class="markets-form-row-label">
            <?php _e('HRC_exchange', 'GMK'); ?>
        </label>
        <input type="text" name="hrc" id="hrc"
               value="<?php echo $row->hrc ?>">
    </div>
    <div class="markets-form-row">
        <label for="rebar" class="markets-form-row-label">
            <?php _e('Rebar_exchange', 'GMK'); ?>
        </label>
        <input type="text" name="rebar" id="rebar"
               value="<?php echo $row->rebar ?>">
    </div>

    <?php
    $html = ob_get_contents();
    ob_end_clean();

    wp_send_json(['result' => $html]);
}

function getLastCoff()
{
    global $wpdb;
    $table = $wpdb->prefix . 'markets_coffs';
    return $wpdb->get_row('SELECT * FROM ' . $table . ' ORDER BY `time` DESC LIMIT 1');
}

function market_parser_update_coff_values()
{
    if (!$_POST) return;

    $scrap = $_POST['scrap-coff'] ?? null;
    $io = $_POST['io-coff'] ?? null;
    $hrc = $_POST['hrc-coff'] ?? null;
    $rebar = $_POST['rebar-coff'] ?? null;

    global $wpdb;
    $table = $wpdb->prefix . 'markets_coffs';
    $wpdb->query("INSERT INTO " . $table . " (scrap, io, hrc, rebar) VALUES (" . $scrap . ", " . $io . ", " . $hrc . ", " . $rebar . ")");

    updateCoffValues();
}

function updateCoffValues()
{
    $lastCoff = getLastCoff();
    ob_start();
    ?>

    <div class="markets-form-row">
        <label for="scrap-coff" class="markets-form-row-label">
            <?php _e('Scrap_exchange', 'GMK'); ?>
        </label>
        <input type="text" name="scrap-coff" id="scrap-coff"
               value="<?php echo $lastCoff->scrap ?>">
    </div>
    <div class="markets-form-row">
        <label for="io-coff" class="markets-form-row-label">
            <?php _e('IO_exchange', 'GMK'); ?>
        </label>
        <input type="text" name="io-coff" id="io-coff"
               value="<?php echo $lastCoff->io ?>">
    </div>
    <div class="markets-form-row">
        <label for="hrc-coff" class="markets-form-row-label">
            <?php _e('HRC_exchange', 'GMK'); ?>
        </label>
        <input type="text" name="hrc-coff" id="hrc-coff"
               value="<?php echo $lastCoff->hrc ?>">
    </div>
    <div class="markets-form-row">
        <label for="rebar-coff" class="markets-form-row-label">
            <?php _e('Rebar_exchange', 'GMK'); ?>
        </label>
        <input type="text" name="rebar-coff" id="rebar-coff"
               value="<?php echo $lastCoff->rebar ?>">
    </div>

    <?php
    $html = ob_get_contents();
    ob_end_clean();

    wp_send_json(['result' => $html]);
}

add_action('wp_ajax_parser_update_value', 'market_parser_update_value');
add_action('wp_ajax_parser_get_row', 'market_parser_get_row_by_date');
add_action('wp_ajax_parser_coff_update', 'market_parser_update_coff_values');

