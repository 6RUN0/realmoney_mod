<?php

$modInfo['realmoney_mod']['name'] = 'Real Money Mod';
$modInfo['realmoney_mod']['abstract'] = 'Show how much money you really lost ;)';
$modInfo['realmoney_mod']['about'] = 'by <a href="http://www.back-to-yarrr.de" target="_blank">Sir Quentin</a>';

event::register('killDetail_assembling', 'realmoney::add');

class realmoney {

  function add($page) {
    $page->addBehind('itemsLost', 'realmoney::show');
  }

  function show($page) {
    global $smarty;

    $plexprice = config::get('real_money_mod_plexprice');
    $faktorEuro = config::get('real_money_mod_euro');
    $faktorPfund = config::get('real_money_mod_pfund');
    $versionanzeige = config::get('real_money_mod_ver');

    if($page->kll_id) {
      $kill = new Kill($page->kll_id);
    }
    else {
      $kill = new Kill($page->kll_external_id, TRUE);
    }

    // Get Ship Value
    $ShipValue=$kill->getVictimShip()->getPrice();

    $plex = new Item(29668);
    $plexCost = intval($plex->getAttribute('price'));
    $iskCost = ($plexprice/2)/$plexCost;
    $totaliskloss = $kill->getISKLoss();

    $USDLoss = $totaliskloss*$iskCost;
    $EUROLoss = $USDLoss/$faktorEuro;
    $GBPLoss = $USDLoss/$faktorPfund;

    $smarty->assign('rmm_version', $versionanzeige);
    $smarty->assign('usdLoss', number_format($USDLoss, 2));
    $smarty->assign('euroLoss', number_format($EUROLoss, 2));
    $smarty->assign('gbpLoss', number_format($GBPLoss, 2));

    $html .= $smarty->fetch(get_tpl('./mods/realmoney_mod/realmoney.tpl'));
    return $html;
  }
}
