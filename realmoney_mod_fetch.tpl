<!-- $Id$ -->
<div class="block-header2">Update reference rates</div>
{if isset($realmoney_mod_message.class)}
  <div class="realmoney-mod-msg {$realmoney_mod_message.class}">
    {if isset($realmoney_mod_message.text)}
      {$realmoney_mod_message.text}
    {/if}
  </div>
{/if}
<div>
  <form name="update_rates" method="post">
    <input type="submit" value="Update rates" name="submit" />
  </form>
</div>
