<?php
/** SPDX-License-Identifier: GPL-2.0-or-later. See the module docs/LICENSE file. */
if (!defined('CMS_VERSION')) exit;

if (!isset($oldversion)) {
    $oldversion = '0';
}

if (version_compare($oldversion, '1.1.0', '<')) {
    $legacy = $this->GetTemplate('frontend');
    $this->RegisterDesignTemplates();

    if (is_string($legacy) && trim($legacy) !== '' && class_exists('CmsLayoutTemplate')) {
        try {
            $tpl = \CmsLayoutTemplate::load_dflt_by_type('PageSectionsPlus::frontend');
            $factory = PageSectionsPlus::reset_frontend_template(null);
            if (is_object($tpl) && trim($tpl->get_content()) === trim($factory) && trim($legacy) !== trim($factory)) {
                $tpl->set_content($legacy);
                $tpl->save();
            }
        } catch (Exception $e) {
            error_log('PageSectionsPlus upgrade: unable to migrate the frontend template: '.$e->getMessage());
        }
    }

    $this->DeleteTemplate();
}

if (version_compare($oldversion, '1.2.0', '<')) {
    $this->RegisterDesignTemplates();
    if (class_exists('CmsLayoutTemplate')) {
        try {
            $tpl = \CmsLayoutTemplate::load_dflt_by_type('PageSectionsPlus::frontend');
            if (is_object($tpl)) {
                $tpl->set_content(PageSectionsPlus::reset_frontend_template(null));
                $tpl->save();
            }
            $type = \CmsLayoutTemplateType::load('PageSectionsPlus::frontend');
            if (is_object($type) && method_exists($type, 'reset_content_to_factory')) {
                $type->reset_content_to_factory();
                $type->save();
            }
        } catch (Exception $e) {
            error_log('PageSectionsPlus upgrade: unable to refresh the frontend template: '.$e->getMessage());
        }
    }
}

if (version_compare($oldversion, '1.3.0', '<')) {
    $this->ensureUploadDirectory();
}

if (version_compare($oldversion, '1.5.0', '<')) {
    $this->CreatePermission(PageSectionsPlus::PERM_DELETE, 'Page Sections Plus: delete sections');
    $this->CreatePermission(PageSectionsPlus::PERM_REORDER, 'Page Sections Plus: reorder sections and swap sides');
    $this->CreatePermission(PageSectionsPlus::PERM_FILEPICKER, 'Page Sections Plus: use CMSMS File Picker');
    $this->CreatePermission(PageSectionsPlus::PERM_IMAGE_LINK, 'Page Sections Plus: import an image link');
    $this->CreatePermission(PageSectionsPlus::PERM_CONTENT_TYPE, 'Page Sections Plus: select content type');
    $this->CreatePermission(PageSectionsPlus::PERM_BACKGROUND, 'Page Sections Plus: select background');
}

if (version_compare($oldversion, '1.6.0', '<')) {
    $this->RegisterDesignTemplates();
    if (class_exists('CmsLayoutTemplate')) {
        try {
            $tpl = \CmsLayoutTemplate::load_dflt_by_type('PageSectionsPlus::frontend');
            if (is_object($tpl)) {
                $tpl->set_name(PageSectionsPlus::BASIC_TEMPLATE_NAME);
                $tpl->set_content(PageSectionsPlus::reset_frontend_template(null));
                $tpl->save();
            }
            $type = \CmsLayoutTemplateType::load('PageSectionsPlus::frontend');
            if (is_object($type) && method_exists($type, 'reset_content_to_factory')) {
                $type->reset_content_to_factory();
                $type->save();
            }
        } catch (Exception $e) {
            error_log('PageSectionsPlus upgrade: unable to install the Basic template: '.$e->getMessage());
        }
    }
    $this->RegisterBootstrapDesignTemplate();
    $this->RemoveLegacyDesignTemplateTypes();
}

if (version_compare($oldversion, '1.6.2', '<')) {
    $this->CreatePermission(PageSectionsPlus::PERM_VIEW_HELP, 'Page Sections Plus: view help');
}

// Only refresh the unmodified factory Bootstrap template. Custom designs stay intact.
if (version_compare($oldversion, '1.6.4', '<') && class_exists('CmsLayoutTemplate')) {
    try {
        $tpl = \CmsLayoutTemplate::load(PageSectionsPlus::BOOTSTRAP_TEMPLATE_NAME);
        if (is_object($tpl) && hash('sha256', trim(str_replace("\r\n", "\n", $tpl->get_content()))) === 'ce8ed2f9986b081e4c2875a8c92fc10d23fd593c2737ad0e693e8c97fc6ba6d7') {
            $tpl->set_content(PageSectionsPlus::reset_bootstrap_template(null));
            $tpl->save();
        }
    } catch (Exception $e) {
        error_log('PageSectionsPlus upgrade: unable to refresh the factory Bootstrap template: '.$e->getMessage());
    }
}

// Gallery support refreshes only the previous, unmodified factory layout.
if (version_compare($oldversion, '1.6.5', '<') && class_exists('CmsLayoutTemplate')) {
    try {
        $tpl = \CmsLayoutTemplate::load(PageSectionsPlus::BOOTSTRAP_TEMPLATE_NAME);
        if (is_object($tpl) && hash('sha256', trim(str_replace("\r\n", "\n", $tpl->get_content()))) === 'c9b8e320068065ea9e6ca74df21871fc1c6718da948a7a18f39114ee5095f45d') {
            $tpl->set_content(PageSectionsPlus::reset_bootstrap_template(null));
            $tpl->save();
        }
    } catch (Exception $e) {
        error_log('PageSectionsPlus upgrade: unable to refresh the gallery factory template: '.$e->getMessage());
    }
}

// Remove the old inspection output only from an unchanged factory Basic template.
if (version_compare($oldversion, '1.6.7', '<') && class_exists('CmsLayoutTemplate')) {
    try {
        $tpl = \CmsLayoutTemplate::load(PageSectionsPlus::BASIC_TEMPLATE_NAME);
        if (is_object($tpl) && hash('sha256', trim(str_replace("\r\n", "\n", $tpl->get_content()))) === 'bd4cfe3f0e01dd2757863e911fa4f2264452346daee21b1965441581707c97eb') {
            $tpl->set_content(PageSectionsPlus::reset_frontend_template(null));
            $tpl->save();
        }
    } catch (Exception $e) {
        error_log('PageSectionsPlus upgrade: unable to refresh the factory Basic template: '.$e->getMessage());
    }
}
