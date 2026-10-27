{* PageSectionsPlus Basic
   Minimal Smarty example with no layout markup.
   Use PageSectionsPlus Bootstrap for a Bootstrap 5.3 website.
*}
{if isset($sections) && $sections|@count gt 0}
{foreach from=$sections item=item}
{if $item.left.filled}
{if $item.left.type == 'image'}
{$item.left.image_src|escape:'htmlall'}
{else}
{$item.left.html}
{/if}
{/if}
{if $item.right.filled}
{if $item.right.type == 'image'}
{$item.right.image_src|escape:'htmlall'}
{else}
{$item.right.html}
{/if}
{/if}
{/foreach}

{/if}
