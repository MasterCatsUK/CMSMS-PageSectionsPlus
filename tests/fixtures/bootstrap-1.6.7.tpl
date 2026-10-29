{* PageSectionsPlus Bootstrap — for Bootstrap 5.3 websites
   Use with:
   {content_module module="PageSectionsPlus" block="sections" template="PageSectionsPlus Bootstrap"}

   $item.left / $item.right:
     type, bg, text_class, filled, html, image_src, alt, align, align_class, tilt
*}
{if isset($sections) && $sections|@count gt 0}
{foreach from=$sections item=item}
{if $item.left.filled && !$item.right.filled}
<!-- ==================================================
     PAGE SECTION
     ================================================== -->
<section class="psplus-feature-section py-4{if $item.left.bg} {$item.left.bg|escape:'htmlall'}{/if}{if $item.left.text_class} {$item.left.text_class|escape:'htmlall'}{/if}">
  <div class="container">
    <div class="row g-4 align-items-start">
      <div class="col-12 {$item.left.align_class|escape:'htmlall'}">
        {if $item.left.type == 'image'}
          <img
            class="img-fluid rounded-4 shadow-sm psplus-feature-image"
            src="{$item.left.image_src|escape:'htmlall'}"
            {if $item.left.tilt == '1'}style="transform:rotate({if $item.swap == '1' && $item.right.filled}3deg{else}-3deg{/if});transform-origin:center"{/if}
            {if $item.left.alt}alt="{$item.left.alt|escape:'htmlall'}"{/if}
          >
        {elseif $item.left.type == 'gallery'}
          {GMS gallery=$item.left.gallery show_buttons='0'}
        {else}
          <div class="psplus-richtext text-start">{$item.left.html}</div>
        {/if}
      </div>
    </div>
  </div>
</section>

{elseif $item.right.filled && !$item.left.filled}
<!-- ==================================================
     PAGE SECTION
     ================================================== -->
<section class="psplus-feature-section py-4{if $item.right.bg} {$item.right.bg|escape:'htmlall'}{/if}{if $item.right.text_class} {$item.right.text_class|escape:'htmlall'}{/if}">
  <div class="container">
    <div class="row g-4 align-items-start">
      <div class="col-12 {$item.right.align_class|escape:'htmlall'}">
        {if $item.right.type == 'image'}
          <img
            class="img-fluid rounded-4 shadow-sm psplus-feature-image"
            src="{$item.right.image_src|escape:'htmlall'}"
            {if $item.right.tilt == '1'}style="transform:rotate({if $item.swap == '1' && $item.left.filled}-3deg{else}3deg{/if});transform-origin:center"{/if}
            {if $item.right.alt}alt="{$item.right.alt|escape:'htmlall'}"{/if}
          >
        {elseif $item.right.type == 'gallery'}
          {GMS gallery=$item.right.gallery show_buttons='0'}
        {else}
          <div class="psplus-richtext text-start">{$item.right.html}</div>
        {/if}
      </div>
    </div>
  </div>
</section>

{elseif $item.left.filled && $item.right.filled}
<!-- ==================================================
     PAGE SECTION
     ================================================== -->
<section class="psplus-feature-section py-4">
  <div class="container">
    <div class="row g-4 align-items-start{if $item.swap == '1'} flex-lg-row-reverse{/if}">
      <div class="col-12 col-lg-6 order-2 order-lg-1{if $item.left.bg} {$item.left.bg|escape:'htmlall'}{/if}{if $item.left.text_class} {$item.left.text_class|escape:'htmlall'}{/if}">
        <div class="h-100 p-3 p-lg-4 {$item.left.align_class|escape:'htmlall'}">
          {if $item.left.type == 'image'}
            <img
              class="img-fluid rounded-4 shadow-sm psplus-feature-image"
              src="{$item.left.image_src|escape:'htmlall'}"
            {if $item.left.tilt == '1'}style="transform:rotate({if $item.swap == '1' && $item.right.filled}3deg{else}-3deg{/if});transform-origin:center"{/if}
              {if $item.left.alt}alt="{$item.left.alt|escape:'htmlall'}"{/if}
            >
          {elseif $item.left.type == 'gallery'}
            {GMS gallery=$item.left.gallery show_buttons='0'}
          {else}
            <div class="psplus-richtext text-start">{$item.left.html}</div>
          {/if}
        </div>
      </div>

      <div class="col-12 col-lg-6 order-1 order-lg-2{if $item.right.bg} {$item.right.bg|escape:'htmlall'}{/if}{if $item.right.text_class} {$item.right.text_class|escape:'htmlall'}{/if}">
        <div class="h-100 p-3 p-lg-4 {$item.right.align_class|escape:'htmlall'}">
          {if $item.right.type == 'image'}
            <img
              class="img-fluid rounded-4 shadow-sm psplus-feature-image"
              src="{$item.right.image_src|escape:'htmlall'}"
            {if $item.right.tilt == '1'}style="transform:rotate({if $item.swap == '1' && $item.left.filled}-3deg{else}3deg{/if});transform-origin:center"{/if}
              {if $item.right.alt}alt="{$item.right.alt|escape:'htmlall'}"{/if}
            >
          {elseif $item.right.type == 'gallery'}
            {GMS gallery=$item.right.gallery show_buttons='0'}
          {else}
            <div class="psplus-richtext text-start">{$item.right.html}</div>
          {/if}
        </div>
      </div>
    </div>
  </div>
</section>
{/if}
{/foreach}
{/if}
