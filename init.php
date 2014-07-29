<?php
$modInfo['realmoney_mod']['name'] = "Real Money Mod";
$modInfo['realmoney_mod']['abstract'] = "Show how much money you really lost ;)";
$modInfo['realmoney_mod']['about'] = "by <a href=\"http://www.back-to-yarrr.de\" target=\"_blank\">Sir Quentin</a>";

event::register("killDetail_assembling", "realmoney::add");

class realmoney {
	function add($page)
	{
		$page->addBehind("itemsLost", "realmoney::show");
	}
  
  function show(){
  	global $smarty;
 		include_once('mods/realmoney_mod/realmoney.php');
  	$html .= $smarty->fetch("../../../mods/realmoney_mod/realmoney.tpl");
    return $html;
  }
}
?>
