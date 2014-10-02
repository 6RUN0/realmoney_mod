<!-- $Id$ -->
<div class="kl-detail-shipdetails realmoney-wrap">
  <table class="kb-table">
    <tr class="{cycle name="ccl"} summary totalloss">
      <td class="realmoney-caption">
        <strong>Total real money loss:</strong>
      </td>
      <td class="realmoney-cost">
        {foreach from=$prices item="price"}
          {$price}<br />
        {/foreach}
      </td>
    </tr>
  </table>
</div>
