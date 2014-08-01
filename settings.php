<?php

require_once('common/admin/admin_menu.php');
$page = new Page('Real Money Mod - Settings');

$message = '';

if(isset($_POST['update_rates'])) {
 
  $url = 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';
  $http_get = new http_request($url);
  $content = $http_get->get_content();
  $http_code = $http_get->get_http_code();

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
        $message .= 'XML error:<br />';
        foreach (libxml_get_errors() as $error) {
          $message .= '  ' . $error->message . '<br />';
        }
      }
      break;
    // Connect and get any HTTP code
    default:
      $message .= "Return HTTP code: ${http_code}.<br />";
      $message .= 'HTTP header <br />' . $http_get->get_header();

  }

}

$err_price = '';
$err_currency ='';
if(isset($_POST['save_settings'])) {
  if(empty($_POST['plex_price'])) {
    $message .= 'Please enter PLEX price.<br />';
    $err_price = 'class="realmoney-err-price" ';
  }
  else {
    $plex_price = str_replace(',', '.', htmlspecialchars($_POST['plex_price']));
    if(!is_numeric($plex_price)) {
      $message .= 'PLEX price is the number.<br />';
      $err_price = 'class="realmoney-err-price" ';
    }
    else {
      config::set('realmoney_mod_plex_price', $plex_price);
    }
  }
  if(empty($_POST['plex_currency'])) {
    $message .= 'Please select rate.<br />';
    $err_currency = 'class="realmoney-err-currency" ';
  }
  else {
    config::set('realmoney_mod_plex_currency', $_POST['plex_currency']);
  }
  unset($_POST['plex_price']);
  unset($_POST['plex_currency']);
  unset($_POST['save_settings']);
  config::set('realmoney_mod_usage_currency', $_POST);
}

$update_form = '
  <div class="block-header2">Update reference rates</div>
  <div>
    <form name="update_rates" action="?a=settings_realmoney_mod" method="post">
      <input type="submit" value="Update rates" name="update_rates" />
    </form>
  </div>';

$rate = config::get('realmoney_mod_rate');

if(empty($rate)) {
  $usage_currency = '<span>Please Update reference rates</span>';
}
else {
  $usage_currency = '<div class="block-header2">Settings</div>';
  $usage_currency .= '<form name="usage_currency" action="?a=settings_realmoney_mod" method="post">';
  $checked = config::get('realmoney_mod_usage_currency');
  $plex_price = config::get('realmoney_mod_plex_price');
  $plex_currency = config::get('realmoney_mod_plex_currency');
  $options = '<option value=""> - </option>';
  $checkboxes = '';
  foreach(config::get('realmoney_mod_rate') as $currency => $rate) {
    $chekboxes .= '
      <div class="realmoney-checkbox">
        <input type="checkbox" value="' . $currency . '" name="' . $currency . '" ' . (isset($checked[$currency]) ? 'checked="checked"' : '') . '/>
        <label for name="' . $currency .'">' . $currency . '</label>
      </div>';
    $options .= '<option value="' . $currency . '" ' . (($plex_currency == $currency) ? 'selected="selected" ' : '') . '>' . $currency . '</option>';
  }
  $options = '
    <div>
      <div><label for name="plex_price">PLEX price</label></div>
      <input ' . $err_price . 'type="text" name="plex_price" maxlength="10" size="10" required ' . (isset($plex_price) ? 'value="' . $plex_price . '" ' : '') . '/><select ' . $err_currency . 'name="plex_currency" required >' . $options . '</select>
    </div>';
  $usage_currency .= $options;
  $usage_currency .= '<fieldset><legend>In what currency display prices?</legend>' . $chekboxes . '</fieldset>';
  $usage_currency .= '<br /><div><input type="submit" value="Submit" name="save_settings" /></div></form>';
}

$page->addHeader('<link rel="stylesheet" type="text/css" href="' . KB_HOST . '/mods/realmoney_mod/css/settings.css" />');
$page->setContent("${message}<br />${update_form}<br />${usage_currency}");
$page->addContext($menubox->generate());
$page->generate();
