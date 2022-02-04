{extends file='page.tpl'}

{block name='content'}

   <div class="text-center">
    <h1 class="text-danger display-3"><strong>{$title}</strong></h1>

{if {$promotion.percent >= 1} }
    <div class="display-4">Félicitation!</div>
    <div>Vous avez gagné une réduction de <span class="text-success">{$promotion.percent}%</span></div>
    <div class="pb-1">Votre code de réduction est le: <span class="text-success">{$promotion.code}</span></div>
{else}
   <div class="display-4 pb-1">Désolé, vous avez perdu.</div>
{/if}

 </div>
 
 
{/block}

