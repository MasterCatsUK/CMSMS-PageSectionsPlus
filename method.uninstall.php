<?php
if (!defined('CMS_VERSION')) exit;

$this->RemovePermission(PageSectionsPlus::PERM_DELETE);
$this->RemovePermission(PageSectionsPlus::PERM_REORDER);
$this->RemovePermission(PageSectionsPlus::PERM_FILEPICKER);
$this->RemovePermission(PageSectionsPlus::PERM_IMAGE_LINK);
$this->RemovePermission(PageSectionsPlus::PERM_CONTENT_TYPE);
$this->RemovePermission(PageSectionsPlus::PERM_BACKGROUND);
$this->RemovePermission(PageSectionsPlus::PERM_VIEW_HELP);

$this->RemoveDesignTemplates();
$this->DeleteTemplate();
$this->RemovePreference();
$this->Audit(0, $this->Lang('friendlyname'), $this->Lang('uninstalled'));
