<?php
if (!defined('CMS_VERSION')) exit;

if (!$this->CheckPermission('Modify Templates')
    && !$this->CheckPermission('Manage All Content')
    && !$this->CheckPermission('Modify Any Page')) {
    return;
}

if (!$this->CanViewHelp()) {
    return;
}

$smarty = $this->GetActionTemplateObject();
$smarty->assign('mod', $this);
echo $this->ProcessTemplate('admin_default.tpl');
