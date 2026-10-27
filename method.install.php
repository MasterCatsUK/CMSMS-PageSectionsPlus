<?php
/** SPDX-License-Identifier: GPL-2.0-or-later. See the module docs/LICENSE file. */
if (!defined('CMS_VERSION')) exit;

$this->CreatePermission(PageSectionsPlus::PERM_DELETE, 'Page Sections Plus: delete sections');
$this->CreatePermission(PageSectionsPlus::PERM_REORDER, 'Page Sections Plus: reorder sections and swap sides');
$this->CreatePermission(PageSectionsPlus::PERM_FILEPICKER, 'Page Sections Plus: use CMSMS File Picker');
$this->CreatePermission(PageSectionsPlus::PERM_IMAGE_LINK, 'Page Sections Plus: import an image link');
$this->CreatePermission(PageSectionsPlus::PERM_CONTENT_TYPE, 'Page Sections Plus: select content type');
$this->CreatePermission(PageSectionsPlus::PERM_BACKGROUND, 'Page Sections Plus: select background');
$this->CreatePermission(PageSectionsPlus::PERM_VIEW_HELP, 'Page Sections Plus: view help');

$this->RegisterDesignTemplates();
$this->RegisterBootstrapDesignTemplate();
$this->ensureUploadDirectory();
$this->Audit(0, $this->Lang('friendlyname'), $this->Lang('installed', $this->GetVersion()));
