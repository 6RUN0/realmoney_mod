<?php

/**
 * $Id$
 */

class pRealMoneyModSettings extends pageAssembly {

  public $page;
  private $plex;
  private $rate;
  private $usage_currency;
  private $message = array();

  function __construct() {
    parent::__construct();
    $this->queue('start');
    $this->queue('submitFetchForm');
    $this->queue('fetchForm');
    $this->queue('submitSettingsForm');
    $this->queue('settingsForm');
  }

  function start() {
    $this->page = new Page();
    $this->page->setTitle('Settings - Real Money Mod');
    $this->page->addHeader('<link rel="stylesheet" type="text/css" href="' . KB_HOST . '/mods/realmoney_mod/css/settings.css" />');
  }

  function submitFetchForm() {
    if(isset($_POST['submit']) && $_POST['submit'] == 'Update rates') {
      $this->fetch();
    }
  }

  function fetchForm() {
    global $smarty;
    $smarty->assign('realmoney_mod_message', $this->message);
    return $smarty->fetch(get_tpl('./mods/realmoney_mod/realmoney_mod_fetch'));
  }

  function submitSettingsForm() {
    $this->message = array();
    if(isset($_POST['submit']) && $_POST['submit'] == 'Save') {
      if(isset($_POST['plex'])) {
        $this->plex = array(
          'price' => '',
          'currency' => '',
        );
        $price = $_POST['plex']['price'];
        $price = $this->is_price($price);
        if($price) {
          $this->plex['price'] = $price;
        }
        else {
          $this->message['text'] .= 'Please check price<br />';
          $this->message['class'] = 'realmoney-mod-err';
          $this->message['err_price'] = 'realmoney-err-price';
        }
        if(!empty($_POST['plex']['currency'])) {
          $this->plex['currency'] = $_POST['plex']['currency'];
        }
        else {
          $this->message['text'] .= 'Please set currency<br />';
          $this->message['class'] = 'realmoney-mod-err';
          $this->message['err_currency'] = 'realmoney-err-currency';
        }
        config::set('realmoney_mod_plex', $this->plex);
      }
      if(isset($_POST['usage_currency'])) {
        config::set('realmoney_mod_usage_currency', $_POST['usage_currency']);
      }
    }
  }

  function settingsForm() {
    global $smarty;
    $rate = config::get('realmoney_mod_rate');

    if(empty($rate)) {
      $this->message['text'] = 'Please Update reference rates';
      $this->message['class'] = 'realmoney-mod-warn';
    }
    else {
      $usage_currency = config::get('realmoney_mod_usage_currency');
      $plex = config::get('realmoney_mod_plex');
      $rate = config::get('realmoney_mod_rate');
      $smarty->assign('realmoney_mod_usage_currency', $usage_currency);
      $smarty->assign('realmoney_mod_plex', $plex);
      $smarty->assign('realmoney_mod_rate', $rate);
    }
    $smarty->assign('realmoney_mod_message', $this->message);
    return $smarty->fetch(get_tpl('./mods/realmoney_mod/realmoney_mod_settings'));
  }

  function context() {
    parent::__construct();
    $this->queue('menu');
  }

  function menu() {
    require_once('common/admin/admin_menu.php');
    return $menubox->generate();
  }

  private function fetch() {
    $url = 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';
    $http_get = new http_request($url);
    $content = $http_get->get_content();
    $http_code = $http_get->get_http_code();
    switch($http_code) {
      // Fail connect
      case FALSE:
        $this->message['text'] = $http_get->getError();
        $this->message['class'] = 'realmoney-mod-err';
        break;
      // Connect and get HTTP 200 OK
      case 200:
        libxml_use_internal_errors(true);
        $sxml = simplexml_load_string($content);
        if($sxml) {
          $time = (string) $sxml->Cube->Cube['time'];
          config::set('realmoney_mod_time', $time);
          $rate = array();
          $rate['EUR'] = '1';
          foreach($sxml->Cube->Cube->Cube as $item) {
            $key = (string) $item['currency'];
            $val = (string) $item['rate'];
            $rate[$key] = $val;
          }
          config::set('realmoney_mod_rate', $rate);
          $this->message['text'] = 'Update date ' . $time . '<br />';
          $this->message['class'] = 'realmoney-mod-ok';
        }
        else {
          $this->message['text'] = 'XML error:<br />';
          foreach (libxml_get_errors() as $error) {
            $this->message['text'] .= '  ' . $error->message . '<br />';
          }
          $this->message['class'] = 'realmoney-mod-err';
        }
        break;
      // Connect and get any HTTP code
      default:
        $this->message['text'] = "Return code: ${http_code}.<br />";
        $header = $http_get->get_header();
        if(!empty($header)) {
          $this->message['text'] .= 'HTTP header <br />' . $http_get->get_header();
        }
        $this->message['class'] = 'realmoney-mod-err';
    }
  }

  private function is_price($num) {
    $num = floatval($num);
    if(is_float($num) && $num > 0) {
      return $num;
    }
    return FALSE;
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
