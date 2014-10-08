<?php

/**
 * $Id$
 *
 * @category Init
 * @package  Realmoney_Mod
 * @author   Sir Quentin, boris_t <boris@talovikov.ru>
 * @license  http://opensource.org/licenses/MIT MIT
 */

$modInfo['realmoney_mod']['name'] = 'Real Money Mod';
$modInfo['realmoney_mod']['abstract'] = 'Show how much money you really lost ;)<br />Reference rates at <a href="https://www.ecb.europa.eu/">European Central Bank</a>.<br />Currency rates of ' . config::get('realmoney_mod_time') . '.';
$modInfo['realmoney_mod']['about'] = 'Created by <a href="http://www.back-to-yarrr.de" target="_blank">Sir Quentin</a>.<br />Patched by <a href="https://github.com/6RUN0">boris_t</a>.<br /><a href="http://www.evekb.org/forum/viewtopic.php?&t=18397">Get original version</a>.<br /><a href="https://github.com/6RUN0/realmoney_mod">Get patched version</a>.';

event::register('killDetail_assembling', 'RealMoney::add');

/**
 * Provides callback for event::register.
 */
class RealMoney
{

    /**
     * Adds a element in the queue.
     *
     * @param pKillDetail $page object of pKillDetail class
     *
     * @return none
     */
    static function add($page)
    {
        $page->addBehind('itemsLost', 'RealMoney::show');
    }

    /**
     * Render realmoney.tpl
     *
     * @param pKillDetail $page object of pKillDetail class
     *
     * @return none
     */
    static function show($page)
    {
        global $smarty;

        $rate = config::get('realmoney_mod_rate');
        $plex_real = config::get('realmoney_mod_plex');
        $usage_currency = config::get('realmoney_mod_usage_currency');
        $prices = array();

        if ($page->kll_id) {
            $kill = new Kill($page->kll_id);
        } else {
            $kill = new Kill($page->kll_external_id, true);
        }

        $plex = new Item(29668);
        // Loss =  Total ISK / PLEX
        $loss_plex = $kill->calculateISKLoss() / intval($plex->getAttribute('price'));
        $prices[] = self::_format($loss_plex, 3) . ' PLEX';
        if (!empty($plex_real['price']) && !empty($plex_real['currency'])) {
            $loss_money = $loss_plex * $plex_real['price'];
            $prices[] = self::_format($loss_money) . ' ' . $plex_real['currency'];
            if (isset($rate[$plex_real['currency']]) && $rate[$plex_real['currency']] != 0) {
                unset($usage_currency[$plex_real['currency']]);
                foreach ($usage_currency as $currency) {
                    $price_in_currency = $loss_money * $rate[$currency] / $rate[$plex_real['currency']];
                    $prices[] = self::_format($price_in_currency) . ' ' . $currency;
                }
            }
        }

        //var_dump($prices);
        $smarty->assign('prices', $prices);
        $page->page->addHeader('<link rel="stylesheet" type="text/css" href="' . KB_HOST . '/mods/realmoney_mod/css/realmoney.css" />');
        $html .= $smarty->fetch(get_tpl('./mods/realmoney_mod/realmoney'));
        return $html;
    }

    /**
     * Format a number.
     *
     * @param float $num number being formatted
     * @param int   $dec sets the number of decimal points
     *
     * @return string
     */
    private static function _format($num, $dec = 2)
    {
        return number_format($num, $dec, '.', ' ');
    }
}
