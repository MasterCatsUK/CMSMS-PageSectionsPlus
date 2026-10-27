<div class="pagecontainer">
  <div class="pageheader">{$mod->Lang('friendlyname')}</div>
  <div class="pageoverflow">
    <p>{$mod->Lang('admin_module_intro')}</p>
    <pre>{literal}{content_module module="PageSectionsPlus" block="sections" label="Page Sections"}{/literal}</pre>
    <p>The default is the minimal Smarty inspection template:</p>
    <pre>{literal}{content_module module="PageSectionsPlus" block="sections" label="Page Sections" template="PageSectionsPlus Basic"}{/literal}</pre>
    <p>For websites using Bootstrap 5.3:</p>
    <pre>{literal}{content_module module="PageSectionsPlus" block="sections" label="Page Sections" template="PageSectionsPlus Bootstrap"}{/literal}</pre>
  </div>
  <div class="pageoverflow">
    <p>{$mod->Lang('admin_design_intro')}</p>
  </div>
</div>
