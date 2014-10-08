<!-- $Id$ -->
<div class="block-header2">Settings</div>
{if isset($realmoney_mod_message.text)}
<div class="realmoney-mod-msg{if isset($realmoney_mod_message.class)} {$realmoney_mod_message.class}{/if}">
  {$realmoney_mod_message.text}
</div>
{/if}
<form name="usage_currency" method="post">
  <div>
    <div><label for name="plex_price">PLEX price</label></div>
    <input {if isset($realmoney_mod_message.err_price)}class="{$realmoney_mod_message.err_price}"{/if} type="text" name="plex[price]" maxlength="10" size="10" {if isset($realmoney_mod_plex.price)}value="{$realmoney_mod_plex.price}"{/if}/>
    <select {if isset($realmoney_mod_message.err_currency)}class="{$realmoney_mod_message.err_currency}"{/if} name="plex[currency]" >
      <option value=""> - </option>
      {foreach from=$realmoney_mod_rate key=currency item=price}
        <option value="{$currency}" {if $realmoney_mod_plex.currency == $currency}selected{/if}>{$currency}</option>
      {/foreach}
    </select>
    <fieldset><legend>In what currency display prices?</legend>
      {foreach from=$realmoney_mod_rate key=currency item=price}
        <div class="realmoney-checkbox">
          <input type="checkbox" value="{$currency}" name="usage_currency[{$currency}]" {if isset($realmoney_mod_usage_currency.$currency)}checked="checked"{/if}/>
          <label for name="{$currency}">{$currency}</label>
        </div>
      {/foreach}
    </fieldset>
  </div>
  <div><input type="submit" value="Save" name="submit" /></div>
</form>
