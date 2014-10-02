#!/usr/bin/php
<?php

/**
 * $Id$
 */

@error_reporting(E_ERROR);

if(function_exists('set_time_limit'))
  @set_time_limit(0);

if (!substr_compare(PHP_OS, 'win', 0, 3, true)) {
  @ini_set('include_path', ini_get('include_path').';.\\common\\includes');
}
else {
  @ini_set('include_path', ini_get('include_path').':./common/includes');
}

$cronStartTime = microtime(true);

if  (file_exists(getcwd().'/cron_update_rates.php')) {
  $KB_HOME = preg_replace('/[\/\\\\]cron$/', '', getcwd());
}
else if (file_exists(__FILE__)) {
  $KB_HOME = preg_replace('/[\/\\\\]cron[\/\\\\]cron_update_rates.php$/', '', __FILE__);
}
else {
  echo 'Set $KB_HOME to the killboard root in cron/cron_update_rates.php.';
  die;
}

chdir($KB_HOME);

require_once('kbconfig.php');
require_once('common/includes/globals.php');

$config = new Config(KB_SITE);

$url = 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';
$http_get = new http_request($url);
$content = $http_get->get_content();
$http_code = $http_get->get_http_code();
$message = '';

switch($http_code) {
  // Fail connect
  case FALSE:
    $message .= $http_get->getError();
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
    }
    else {
      $message .= 'XML error:' . PHP_EOL;
      foreach (libxml_get_errors() as $error) {
        $message .= '  ' . $error->message . PHP_EOL;
      }
    }
    break;
  // Connect and get any HTTP code
  default:
    $message .= "Return HTTP code: ${http_code}." . PHP_EOL;
    $message .= 'HTTP header' . PHP_EOL . $http_get->get_header() . PHP_EOL;

}
if(empty($message)) {
  echo 'Reference rates update' . PHP_EOL;
}
else {
  echo $message . PHP_EOL;
}
