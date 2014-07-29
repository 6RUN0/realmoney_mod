<?php
$plexprice = config::get('real_money_mod_plexprice');
$faktorEuro = config::get('real_money_mod_euro');
$faktorPfund = config::get('real_money_mod_pfund');
$versionanzeige = config::get('real_money_mod_ver');

$kll_id = (int) edkURI::getArg('kll_id', 1);

$kill = new Kill($kll_id);

// Get Ship Value
$ShipValue=$kill->getVictimShip()->getPrice();

$qry = new DBQuery(true);
$qry->execute("SELECT * FROM kb3_item_price WHERE typeID=29668");
$row = $qry->getRow();

$plexCost = intval($row['price']);
$iskCost = ($plexprice/2)/$plexCost;
$totaliskloss = $kill->getISKLoss();

$USDLoss = $totaliskloss*$iskCost;
$EUROLoss = $USDLoss/$faktorEuro;
$GBPLoss = $USDLoss/$faktorPfund;

$smarty->assign("rmm_version", $versionanzeige);
$smarty->assign("usdLoss", number_format($USDLoss, 2));
$smarty->assign("euroLoss", number_format($EUROLoss, 2));
$smarty->assign("gbpLoss", number_format($GBPLoss, 2));
?>
