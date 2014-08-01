<?php

$modInfo['realmoney_mod']['name'] = 'Real Money Mod';
$modInfo['realmoney_mod']['abstract'] = 'Show how much money you really lost ;)<br />Reference rates at <a href="https://www.ecb.europa.eu/">European Central Bank</a>.<br />Currency rates of ' . config::get('realmoney_mod_time') . '.';
$modInfo['realmoney_mod']['about'] = 'Created by <a href="http://www.back-to-yarrr.de" target="_blank">Sir Quentin</a>.<br />Patched by <a href="https://github.com/6RUN0">boris_t</a>.<br /><a href="http://www.evekb.org/forum/viewtopic.php?&t=18397">Get original version</a>.<br /><a href="https://github.com/6RUN0/realmoney_mod">Get patched version</a>.';

event::register('killDetail_assembling', 'realmoney::add');

class realmoney {

  function add($page) {
    $page->addBehind('itemsLost', 'realmoney::show');
  }

  function show($page) {

    global $smarty;

    $rate = config::get('realmoney_mod_rate');
    $plex_price = config::get('realmoney_mod_plex_price');
    $plex_currency = config::get('realmoney_mod_plex_currency');
    $usage_currency = config::get('realmoney_mod_usage_currency');
    $prices = array();

    if($page->kll_id) {
      $kill = new Kill($page->kll_id);
    }
    else {
      $kill = new Kill($page->kll_external_id, TRUE);
    }

    $plex = new Item(29668);
    // Loss =  Total ISK / PLEX
    $loss_plex = $kill->calculateISKLoss() / intval($plex->getAttribute('price'));
    $prices[] = number_format($loss_plex, 3, '.', ' ') . ' PLEX';
    if(!empty($plex_price) && !empty($plex_currency)) {
      $loss_money = $loss_plex * $plex_price;
      $prices[] = number_format($loss_money, 2, '.', ' ') . ' ' . $plex_currency;
      if(isset($rate[$plex_currency]) && $rate[$plex_currency] != 0) {
        unset($usage_currency[$plex_currency]);
        foreach($usage_currency as $currency) {
          $price_in_currency = $loss_money * $rate[$currency] / $rate[$plex_currency];
          $prices[] = number_format($price_in_currency, 2, '.', ' ') . ' ' . $currency;
        }
      }
    }

    //var_dump($prices);
    $smarty->assign('prices', $prices);
    $page->page->addHeader('<link rel="stylesheet" type="text/css" href="' . KB_HOST . '/mods/realmoney_mod/css/realmoney.css" />'); 
    $html .= $smarty->fetch(get_tpl('./mods/realmoney_mod/realmoney.tpl'));
    return $html;
  }
}
