<?php
require_once('common/admin/admin_menu.php');
$page = new Page('Real Money Mod - Settings');

$version = '1.2'; //Version Update for me, do not change!

$versiondb = config::get('real_money_mod_ver');
if($version != $versiondb)  {
  config::set('real_money_mod_ver', $version);
  $html .= '<br /><b>This Mod got updated, have fun with it! New version set!</b><br /><br />';
}

switch($_GET['step']) {

  default:
    $plexprice = config::get('real_money_mod_plexprice');
    if($plexprice == 0) {
      config::set('real_money_mod_plexprice', '34.99');
      $plexprice = config::get('real_money_mod_plexprice');
    }
    $faktorEuro = config::get('real_money_mod_euro');
    if($faktorEuro == 0) {
      config::set('real_money_mod_euro', '1.3');
      $faktorEuro = config::get('real_money_mod_euro');
    }
    $faktorPfund = config::get('real_money_mod_pfund');
    if($faktorPfund == 0) {
      config::set('real_money_mod_pfund', '1.56');
      $faktorPfund = config::get('real_money_mod_pfund');
    }
    $versionanzeige = config::get('real_money_mod_ver');

    $html .= '
      <form name="add" action="?a=settings_realmoney_mod&amp;step=add" method="post">
      <table width="75%">
        <tr>
          <td>Current GTC price in Dollar:</td>
          <td><input type="text" name="add_plexprice" value="' . $plexprice . '" /></td>
        </tr>
        <tr>
          <td>Exchange ratio Euro &raquo; USD:</td>
          <td><input type="text" name="add_faktorEuro" value="' . $faktorEuro . '" /></td>
        </tr>
        <tr>
          <td>Exchange ratio GBP (Pound) &raquo; USD:</td>
          <td><input type="text" name="add_faktorPfund" value="' . $faktorPfund . '" /></td>
        </tr>
        <tr>
          <td></td>
          <td><br /><input type="submit" value="save" /></td>
        </tr>
      </table>
      </form>';

    $html .= '<br /><br /><hr size="1" /><div align="right"><i><small>Real Money Mod (Version ' . $versionanzeige . ') by <a href="http://www.back-to-yarrr.de" target="_blank">Sir Quentin</a></small></i></div>';

    break;

  case 'add':
    if ($_POST) {
      $rmm_plex = trim($_POST['add_plexprice']);
      $rmm_euro = trim($_POST['add_faktorEuro']);
      $rmm_pfund = $_POST['add_faktorPfund'];

      config::set('real_money_mod_plexprice', $rmm_plex);
      config::set('real_money_mod_euro', $rmm_euro);
      config::set('real_money_mod_pfund', $rmm_pfund);

      $html .= 'Settings updated!';
    }
    break;

}

$page->setContent($html);
$page->addContext($menubox->generate());
$page->generate();
