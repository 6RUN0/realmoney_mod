<?php

/**
 * $Id$
 *
 * @category Settings
 * @package  Realmoney_Mod
 * @author   boris_t <boris@talovikov.ru>
 * @license  http://opensource.org/licenses/MIT MIT
 */
class pRealMoneyModSettings extends pageAssembly
{
    public $page;
    private $_plex;
    private $_msg = array();

    /**
     * Constructor methods for this classes.
     */
    function __construct()
    {
        parent::__construct();
        $this->queue('start');
        $this->queue('submitFetchForm');
        $this->queue('fetchForm');
        $this->queue('submitSettingsForm');
        $this->queue('settingsForm');
    }

    /**
     * Preparation of the page.
     *
     * @return none
     */
    function start()
    {
        $this->page = new Page();
        $this->page->setTitle('Settings - Real Money Mod');
        $this->page->addHeader('<link rel="stylesheet" type="text/css" href="' . KB_HOST . '/mods/realmoney_mod/css/settings.css" />');
    }

    /**
     * Submitted fetch form.
     *
     * @return none
     */
    function submitFetchForm()
    {
        if (isset($_POST['submit']) && $_POST['submit'] == 'Update rates') {
            $this->_fetch();
        }
    }

    /**
     * Render fetch form.
     *
     * @return string html
     */
    function fetchForm()
    {
        global $smarty;
        $smarty->assign('realmoney_mod_message', $this->_msg);
        return $smarty->fetch(get_tpl('./mods/realmoney_mod/realmoney_mod_fetch'));
    }

    /**
     * Submitted setting form.
     *
     * @return none
     */
    function submitSettingsForm()
    {
        $this->_msg = array();
        if (isset($_POST['submit']) && $_POST['submit'] == 'Save') {
            if (isset($_POST['plex'])) {
                $this->_plex = array(
                    'price' => '',
                    'currency' => '',
                );
                $price = $_POST['plex']['price'];
                $price = $this->_is_price($price);
                if ($price) {
                    $this->_plex['price'] = $price;
                } else {
                    $this->_msg['text'] .= 'Please check price<br />';
                    $this->_msg['class'] = 'realmoney-mod-err';
                    $this->_msg['err_price'] = 'realmoney-err-price';
                }
                if (!empty($_POST['plex']['currency'])) {
                    $this->_plex['currency'] = $_POST['plex']['currency'];
                } else {
                    $this->_msg['text'] .= 'Please set currency<br />';
                    $this->_msg['class'] = 'realmoney-mod-err';
                    $this->_msg['err_currency'] = 'realmoney-err-currency';
                }
                config::set('realmoney_mod_plex', $this->_plex);
            }
            if (isset($_POST['usage_currency'])) {
                config::set('realmoney_mod_usage_currency', $_POST['usage_currency']);
            }
        }
    }

    /**
     * Render setting form.
     *
     * @return string html
     */
    function settingsForm()
    {
        global $smarty;
        $rate = config::get('realmoney_mod_rate');

        if (empty($rate)) {
            $this->_msg['text'] = 'Please Update reference rates';
            $this->_msg['class'] = 'realmoney-mod-warn';
        } else {
            $usage_currency = config::get('realmoney_mod_usage_currency');
            $plex = config::get('realmoney_mod_plex');
            $rate = config::get('realmoney_mod_rate');
            $smarty->assign('realmoney_mod_usage_currency', $usage_currency);
            $smarty->assign('realmoney_mod_plex', $plex);
            $smarty->assign('realmoney_mod_rate', $rate);
        }
        $smarty->assign('realmoney_mod_message', $this->_msg);
        $tpl = get_tpl('./mods/realmoney_mod/realmoney_mod_settings');
        return $smarty->fetch($tpl);
    }

    /**
     * Build context.
     *
     * @return none
     */
    function context()
    {
        parent::__construct();
        $this->queue('menu');
    }

    /**
     * Render of admin menu.
     *
     * @return string html
     */
    function menu()
    {
        include 'common/admin/admin_menu.php';
        return $menubox->generate();
    }

    /**
     * Fetch currency rate.
     *
     * @return none
     */
    private function _fetch()
    {
        $url = 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';
        $http_get = new http_request($url);
        $content = $http_get->get_content();
        $http_code = $http_get->get_http_code();
        switch($http_code) {
        // Fail connect
        case false:
            $this->_msg['text'] = $http_get->getError();
            $this->_msg['class'] = 'realmoney-mod-err';
            break;
        // Connect and get HTTP 200 OK
        case 200:
            libxml_use_internal_errors(true);
            $sxml = simplexml_load_string($content);
            if ($sxml) {
                $time = (string) $sxml->Cube->Cube['time'];
                config::set('realmoney_mod_time', $time);
                $rate = array();
                $rate['EUR'] = '1';
                foreach ($sxml->Cube->Cube->Cube as $item) {
                    $key = (string) $item['currency'];
                    $val = (string) $item['rate'];
                    $rate[$key] = $val;
                }
                config::set('realmoney_mod_rate', $rate);
                $this->_msg['text'] = 'Update date ' . $time . '<br />';
                $this->_msg['class'] = 'realmoney-mod-ok';
            } else {
                $this->_msg['text'] = 'XML error:<br />';
                foreach (libxml_get_errors() as $error) {
                    $this->_msg['text'] .= '  ' . $error->message . '<br />';
                }
                $this->_msg['class'] = 'realmoney-mod-err';
            }
            break;
        // Connect and get any HTTP code
        default:
            $this->_msg['text'] = "Return code: ${http_code}.<br />";
            $header = $http_get->get_header();
            if (!empty($header)) {
                $this->_msg['text'] .= 'HTTP header <br />' . $http_get->get_header();
            }
            $this->_msg['class'] = 'realmoney-mod-err';
        }
    }

    /**
     * Find whether the type of a variable is price.
     *
     * @param mixed $num numeric
     *
     * @return float|false
     */
    private function _is_price($num) {
        $num = floatval($num);
        if (is_float($num) && $num > 0) {
            return $num;
        }
        return false;
    }
}

$pageAssembly = new pRealMoneyModSettings();
event::call('pRealMoneyModSettings_assembling', $pageAssembly);
$html = $pageAssembly->assemble();
$pageAssembly->page->setContent($html);

$pageAssembly->context();
event::call('pRealMoneyModSettings_context_assembling', $pageAssembly);
$context = $pageAssembly->assemble();
$pageAssembly->page->addContext($context);

$pageAssembly->page->generate();
