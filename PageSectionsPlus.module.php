<?php
#-------------------------------------------------------------------------
# Module: PageSectionsPlus
#-------------------------------------------------------------------------
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#-------------------------------------------------------------------------

class PageSectionsPlus extends CMSModule
{
    const VERSION = '1.6.6';
    const TEMPLATE_TYPE = 'frontend';
    const BASIC_TEMPLATE_NAME = 'PageSectionsPlus Basic';
    const BOOTSTRAP_TEMPLATE_TYPE = 'bootstrap';
    const BOOTSTRAP_TEMPLATE_NAME = 'PageSectionsPlus Bootstrap';
    const UPLOAD_SUBDIR = 'PageSectionsPlus';
    const MAX_UPLOAD_BYTES = 8388608;
    const UPLOAD_ACTION_ID = 'psupload';
    const PERM_DELETE = 'PageSectionsPlus Delete Sections';
    const PERM_REORDER = 'PageSectionsPlus Reorder Sections';
    const PERM_FILEPICKER = 'PageSectionsPlus Use File Picker';
    const PERM_IMAGE_LINK = 'PageSectionsPlus Use Image Link';
    const PERM_CONTENT_TYPE = 'PageSectionsPlus Select Content Type';
    const PERM_BACKGROUND = 'PageSectionsPlus Select Background';
    const PERM_VIEW_HELP = 'PageSectionsPlus View Help';

    private $_psplus_lang = null;
    private $_native_filepicker_enabled = false;

    public function GetName()
    {
        return 'PageSectionsPlus';
    }

    public function GetFriendlyName()
    {
        return $this->Lang('friendlyname');
    }

    public function GetVersion()
    {
        return self::VERSION;
    }

    public function GetAuthor()
    {
        return 'Andrew';
    }

    public function GetAuthorEmail()
    {
        return 'andrew@pagesectionsplus.invalid';
    }

    public function GetDependencies()
    {
        return array();
    }

    public function AdminStyle()
    {
        $fn = dirname(__FILE__).'/css/admin.css';
        if (!is_file($fn)) {
            return '';
        }
        $css = @file_get_contents($fn);
        if (!is_string($css) || $css === '') {
            return '';
        }

        return '<style type="text/css">'.$css.'</style>';
    }

    public function GetChangeLog($lang = 'en_US')
    {
        $fn = dirname(__FILE__).'/doc/changelog.inc';
        if (is_file($fn)) {
            $html = @file_get_contents($fn);
            if (is_string($html) && trim($html) !== '') {
                return $html;
            }
        }

        return $this->Lang('changelog');
    }

    public function CanViewHelp()
    {
        try {
            return $this->CheckPermission(self::PERM_VIEW_HELP);
        } catch (Exception $e) {
            return false;
        }
    }

    public function GetHelp($lang = 'en_US')
    {
        if (!$this->CanViewHelp()) {
            return '';
        }

        $legacy = dirname(__FILE__).'/doc/help.inc';
        if (is_file($legacy)) {
            @unlink($legacy);
        }

        $fn = dirname(__FILE__).'/doc/module_help.inc';
        if (is_file($fn)) {
            $html = @file_get_contents($fn);
            if (is_string($html) && trim($html) !== '') {
                return $html;
            }
        }

        return $this->Lang('help');
    }

    public function Lang()
    {
        $args = func_get_args();
        if (count($args) === 0) {
            return '';
        }

        $parent_value = $this->parent_lang($args);
        if (is_string($parent_value) && strpos($parent_value, 'Missing Languagestring') === false) {
            return $parent_value;
        }

        $bag = $this->language_strings();
        $key = (string)$args[0];
        if (isset($bag[$key])) {
            if (count($args) > 1) {
                $args[0] = $bag[$key];
                return call_user_func_array('sprintf', $args);
            }

            return $bag[$key];
        }

        if (is_string($parent_value)) {
            return $parent_value;
        }

        return '-- Missing Languagestring: '.$key.' --';
    }

    public function IsPluginModule()
    {
        return true;
    }

    public function HasAdmin()
    {
        return true;
    }

    public function GetAdminSection()
    {
        return 'extensions';
    }

    public function GetAdminDescription()
    {
        return $this->Lang('admindescription');
    }

    public function VisibleToAdminUser()
    {
        if (!$this->CanViewHelp()) {
            return false;
        }

        return $this->CheckPermission('Modify Templates')
            || $this->CheckPermission('Manage All Content')
            || $this->CheckPermission('Modify Any Page');
    }

    public function CanDeleteSections()
    {
        return $this->CheckPermission(self::PERM_DELETE);
    }

    public function CanReorderSections()
    {
        return $this->CheckPermission(self::PERM_REORDER);
    }

    public function CanUseFilePicker()
    {
        return $this->CheckPermission(self::PERM_FILEPICKER);
    }

    public function CanUseImageLink()
    {
        return $this->CheckPermission(self::PERM_IMAGE_LINK);
    }

    public function CanSelectContentType()
    {
        return $this->CheckPermission(self::PERM_CONTENT_TYPE);
    }

    public function CanSelectBackground()
    {
        return $this->CheckPermission(self::PERM_BACKGROUND);
    }

    public function GetAdminMenuItems()
    {
        if (!$this->VisibleToAdminUser()) {
            return;
        }

        $item = new CmsAdminMenuItem();
        $item->module = $this->GetName();
        $item->section = 'extensions';
        $item->title = $this->GetFriendlyName();
        $item->action = 'defaultadmin';
        $item->description = $this->GetAdminDescription();

        return array($item);
    }

    public function MinimumCMSVersion()
    {
        return '2.2';
    }

    public function LazyLoadFrontend()
    {
        return true;
    }

    public function LazyLoadAdmin()
    {
        return true;
    }

    public function AllowAutoInstall()
    {
        return false;
    }

    public function AllowAutoUpgrade()
    {
        return false;
    }

    public function InstallPostMessage()
    {
        return $this->Lang('postinstall');
    }

    public function UninstallPostMessage()
    {
        return $this->Lang('postuninstall');
    }

    public function UninstallPreMessage()
    {
        return $this->Lang('preuninstall');
    }

    public function HasCapability($capability, $params = array())
    {
        if (strtolower($capability) === 'contentblocks') {
            return true;
        }

        return false;
    }

    public static function template_lang_callback($str)
    {
        $mod = \cms_utils::get_module('PageSectionsPlus');
        if (is_object($mod)) {
            $val = $mod->Lang('tpltype_'.$str);
            if (is_string($val) && strpos($val, 'Missing Languagestring') === false) {
                return $val;
            }
        }
        if ($str === 'frontend') {
            return 'Basic';
        }
        if ($str === 'bootstrap') {
            return 'Bootstrap';
        }
        if ($str === 'PageSectionsPlus') {
            return 'Page Sections Plus';
        }

        return $str;
    }

    public static function reset_frontend_template($type)
    {
        $fn = dirname(__FILE__).'/templates/orig_frontend.tpl';
        if (is_file($fn)) {
            $data = @file_get_contents($fn);
            if (is_string($data)) {
                return $data;
            }
        }

        return '';
    }

    public static function reset_bootstrap_template($type)
    {
        $fn = dirname(__FILE__).'/templates/orig_bootstrap.tpl';
        if (is_file($fn)) {
            $data = @file_get_contents($fn);
            if (is_string($data)) {
                return $data;
            }
        }

        return '';
    }

    public static function template_help_callback($type)
    {
        $fn = dirname(__FILE__).'/doc/template_help.inc';
        if (is_file($fn)) {
            $data = @file_get_contents($fn);
            if (is_string($data)) {
                return $data;
            }
        }

        return '';
    }

    public function RegisterDesignTemplates()
    {
        if (!class_exists('CmsLayoutTemplateType')) {
            $this->SetTemplate(self::TEMPLATE_TYPE, self::reset_frontend_template(null));
            return;
        }

        $type_key = $this->GetName().'::'.self::TEMPLATE_TYPE;
        $type = null;
        try {
            $type = \CmsLayoutTemplateType::load($type_key);
        } catch (Exception $e) {
            $type = null;
        }

        if (!is_object($type)) {
            $type = new \CmsLayoutTemplateType();
            $type->set_originator($this->GetName());
            $type->set_name(self::TEMPLATE_TYPE);
            $type->set_dflt_flag(true);
            $type->set_lang_callback('PageSectionsPlus::template_lang_callback');
            $type->set_content_callback('PageSectionsPlus::reset_frontend_template');
            if (method_exists($type, 'set_help_callback')) {
                $type->set_help_callback('PageSectionsPlus::template_help_callback');
            }
            if (method_exists($type, 'set_description')) {
                $type->set_description($this->Lang('template_type_description'));
            }
            $type->reset_content_to_factory();
            $type->save();
        }

        $existing = null;
        try {
            $existing = \CmsLayoutTemplate::load_dflt_by_type($type_key);
        } catch (Exception $e) {
            $existing = null;
        }
        if (is_object($existing)) {
            return;
        }

        $uid = 1;
        if (function_exists('get_userid')) {
            $tmp = get_userid(false);
            if ($tmp) {
                $uid = $tmp;
            }
        }

        $contents = '';
        if (method_exists($type, 'get_dflt_contents')) {
            $contents = $type->get_dflt_contents();
        }
        if (!is_string($contents) || trim($contents) === '') {
            $contents = self::reset_frontend_template(null);
        }

        $names = array(self::BASIC_TEMPLATE_NAME);
        foreach ($names as $name) {
            try {
                $tpl = new \CmsLayoutTemplate();
                $tpl->set_name($name);
                $tpl->set_owner($uid);
                $tpl->set_type($type);
                $tpl->set_content($contents);
                $tpl->set_type_dflt(true);
                $tpl->save();
                break;
            } catch (Exception $e) {
                error_log('PageSectionsPlus: unable to create the default frontend template: '.$e->getMessage());
            }
        }
    }

    public function RegisterBootstrapDesignTemplate()
    {
        if (!class_exists('CmsLayoutTemplateType') || !class_exists('CmsLayoutTemplate')) {
            return;
        }

        try {
            $existing = \CmsLayoutTemplate::load(self::BOOTSTRAP_TEMPLATE_NAME);
            if (is_object($existing)) {
                return;
            }
        } catch (Exception $e) {
            error_log('PageSectionsPlus: unable to check for the Bootstrap template: '.$e->getMessage());
        }

        $type_key = $this->GetName().'::'.self::BOOTSTRAP_TEMPLATE_TYPE;
        $type = null;
        try {
            $type = \CmsLayoutTemplateType::load($type_key);
        } catch (Exception $e) {
            $type = null;
        }

        if (!is_object($type)) {
            $type = new \CmsLayoutTemplateType();
            $type->set_originator($this->GetName());
            $type->set_name(self::BOOTSTRAP_TEMPLATE_TYPE);
            $type->set_dflt_flag(true);
            $type->set_lang_callback('PageSectionsPlus::template_lang_callback');
            $type->set_content_callback('PageSectionsPlus::reset_bootstrap_template');
            if (method_exists($type, 'set_help_callback')) {
                $type->set_help_callback('PageSectionsPlus::template_help_callback');
            }
            if (method_exists($type, 'set_description')) {
                $type->set_description($this->Lang('bootstrap_template_type_description'));
            }
            $type->reset_content_to_factory();
            $type->save();
        }

        $uid = 1;
        if (function_exists('get_userid')) {
            $tmp = get_userid(false);
            if ($tmp) {
                $uid = $tmp;
            }
        }

        $contents = self::reset_bootstrap_template(null);
        if (method_exists($type, 'get_dflt_contents')) {
            $factory = $type->get_dflt_contents();
            if (is_string($factory) && trim($factory) !== '') {
                $contents = $factory;
            }
        }

        $tpl = new \CmsLayoutTemplate();
        $tpl->set_name(self::BOOTSTRAP_TEMPLATE_NAME);
        $tpl->set_owner($uid);
        $tpl->set_type($type);
        $tpl->set_content($contents);
        $tpl->set_type_dflt(true);
        $tpl->save();
    }

    public function RemoveDesignTemplates()
    {
        if (!class_exists('CmsLayoutTemplateType')) {
            return;
        }

        try {
            $types = \CmsLayoutTemplateType::load_all_by_originator($this->GetName());
        } catch (Exception $e) {
            return;
        }
        if (!is_array($types)) {
            return;
        }

        foreach ($types as $type) {
            $this->remove_design_template_type($type);
        }
    }

    public function RemoveLegacyDesignTemplateTypes()
    {
        if (!class_exists('CmsLayoutTemplateType')) {
            return;
        }
        try {
            $types = \CmsLayoutTemplateType::load_all_by_originator($this->GetName());
        } catch (Exception $e) {
            error_log('PageSectionsPlus: unable to inspect legacy template types: '.$e->getMessage());
            return;
        }
        if (!is_array($types)) {
            return;
        }

        $keep = array(self::TEMPLATE_TYPE, self::BOOTSTRAP_TEMPLATE_TYPE);
        foreach ($types as $type) {
            if (is_object($type) && !in_array($type->get_name(), $keep, true)) {
                $this->remove_design_template_type($type);
            }
        }
    }

    private function remove_design_template_type($type)
    {
        try {
            $list = $type->get_template_list();
            if (is_array($list)) {
                foreach ($list as $tpl) {
                    $this->remove_design_template($tpl);
                }
            }
        } catch (Exception $e) {
            error_log('PageSectionsPlus: unable to remove Design Manager templates: '.$e->getMessage());
        }

        try {
            if (method_exists($type, 'delete')) {
                $type->delete();
            }
        } catch (Exception $e) {
            error_log('PageSectionsPlus: unable to remove a Design Manager template type: '.$e->getMessage());
        }
    }

    private function remove_design_template($tpl)
    {
        if (is_object($tpl) && method_exists($tpl, 'delete')) {
            $tpl->delete();
        }
    }

    public function SuppressAdminOutput(&$request)
    {
        $action = '';
        if (is_array($request) && isset($request['action'])) {
            $action = (string)$request['action'];
        } else {
            $mact = filter_input(INPUT_GET, 'mact', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $parts = explode(',', is_string($mact) ? $mact : '');
            $action = isset($parts[2]) ? $parts[2] : '';
        }

        return $action === 'upload';
    }

    public function GetContentBlockFieldInput($blockName, $value, $params, $adding, $content_obj)
    {
        $data = $this->decode_value($value);
        $items = $data['items'];
        if (empty($items)) {
            $items = array($this->empty_section());
        }

        $safe_block = preg_replace('/[^A-Za-z0-9_\-]/', '_', (string)$blockName);
        $editor_page_id = (!$adding && is_object($content_obj) && method_exists($content_obj, 'Id'))
            ? (int)$content_obj->Id()
            : 0;
        $config = cms_config::get_instance();
        $uploads = isset($config['uploads_url']) ? rtrim($config['uploads_url'], '/') : '';
        $upload_url = $this->upload_action_url();
        $filepicker_html = '';
        if ($this->CanUseFilePicker()) {
            $filepicker_html = $this->shared_filepicker_control('psplus_picker_'.$safe_block);
        }
        $this->_native_filepicker_enabled = trim($filepicker_html) !== '';

        $html = array();
        $html[] = '<div class="psplus-admin" data-block="'.htmlspecialchars($blockName, ENT_QUOTES, 'UTF-8').'" data-safe="'.htmlspecialchars($safe_block, ENT_QUOTES, 'UTF-8').'" data-page-id="'.$editor_page_id.'" data-show-label="'.htmlspecialchars($this->Lang('show_sections'), ENT_QUOTES, 'UTF-8').'" data-hide-label="'.htmlspecialchars($this->Lang('hide_sections'), ENT_QUOTES, 'UTF-8').'" data-uploads-url="'.htmlspecialchars($uploads, ENT_QUOTES, 'UTF-8').'" data-upload-url="'.htmlspecialchars($upload_url, ENT_QUOTES, 'UTF-8').'">';
        $html[] = '<div class="psplus-editor-actions"><button type="button" class="psplus-toggle-all" aria-expanded="true">'.$this->Lang('hide_sections').'</button></div>';
        $html[] = '<div class="psplus-editor-content">';
        $html[] = '<div class="information"><p>'.$this->Lang('editor_tip').'</p></div>';
        $html[] = '<div class="psplus-sections">';

        foreach ($items as $i => $section) {
            $html[] = $this->render_section_editor($blockName, $safe_block, (int)$i, $section, false);
        }

        $html[] = '</div>';
        $html[] = '<p><button type="button" class="psplus-add cms_btn">'.$this->Lang('add_section').'</button></p>';
        $html[] = '<script type="text/template" class="psplus-template">';
        $html[] = $this->render_section_editor($blockName, $safe_block, 'new', $this->empty_section(), true);
        $html[] = '</script>';
        if ($this->_native_filepicker_enabled) {
            $html[] = '<div class="psplus-shared-filepicker" aria-hidden="true">'.$filepicker_html.'</div>';
        }
        $html[] = '</div>';
        $html[] = $this->admin_asset_tags();
        $html[] = '</div>';

        return array($this->Lang('block_label'), implode("\n", $html));
    }

    public function GetContentBlockFieldValue($blockName, $blockParams, $inputParams, $content_obj)
    {
        $raw = array();
        if (isset($inputParams[$blockName]) && is_array($inputParams[$blockName])) {
            $raw = $inputParams[$blockName];
        }

        $items_in = array();
        if (isset($raw['items']) && is_array($raw['items'])) {
            $items_in = $raw['items'];
        }

        $items = array();
        foreach ($items_in as $row) {
            if (!is_array($row)) {
                continue;
            }
            $items[] = $this->sanitize_section($row);
        }

        return json_encode(array('items' => array_values($items)), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public function ValidateContentBlockFieldValue($blockName, $value, $blockparams, $content_obj)
    {
        return '';
    }

    public function RenderContentBlockField($blockName, $value, $blockparams, $content_obj)
    {
        $data = $this->decode_value($value);
        $smarty = \CmsApp::get_instance()->GetSmarty();
        $smarty->assign('sections', $this->process_section_smarty($data['items']));
        $smarty->assign('block_name', $blockName);
        $smarty->assign('blockparams', $blockparams);
        $smarty->assign('template_name', $this->requested_template_name($blockparams));

        $resource = $this->frontend_template_resource($blockparams);
        try {
            return $smarty->fetch($resource);
        } catch (Exception $e) {
            return $smarty->fetch('string:'.self::reset_frontend_template(null));
        }
    }

    public function bg_options()
    {
        return array(
            '' => $this->Lang('bg_none'),
            'bg-body' => $this->Lang('bg_body'),
            'bg-body-secondary' => $this->Lang('bg_body_secondary'),
            'bg-body-tertiary' => $this->Lang('bg_body_tertiary'),
            'bg-primary' => $this->Lang('bg_primary'),
            'bg-primary-subtle' => $this->Lang('bg_primary_subtle'),
            'bg-secondary' => $this->Lang('bg_secondary'),
            'bg-secondary-subtle' => $this->Lang('bg_secondary_subtle'),
            'bg-success' => $this->Lang('bg_success'),
            'bg-success-subtle' => $this->Lang('bg_success_subtle'),
            'bg-danger' => $this->Lang('bg_danger'),
            'bg-danger-subtle' => $this->Lang('bg_danger_subtle'),
            'bg-warning' => $this->Lang('bg_warning'),
            'bg-warning-subtle' => $this->Lang('bg_warning_subtle'),
            'bg-info' => $this->Lang('bg_info'),
            'bg-info-subtle' => $this->Lang('bg_info_subtle'),
            'bg-light' => $this->Lang('bg_light'),
            'bg-dark' => $this->Lang('bg_dark'),
            'bg-white' => $this->Lang('bg_white'),
            'bg-black' => $this->Lang('bg_black'),
            'bg-transparent' => $this->Lang('bg_transparent'),
        );
    }

    public function type_options()
    {
        return array(
            'wysiwyg' => $this->Lang('type_wysiwyg'),
            'image' => $this->Lang('type_image'),
            'gallery' => $this->Lang('type_gallery'),
        );
    }

    private function gallery_options($current = '0')
    {
        $options = array('0' => $this->Lang('select_gallery'));
        if (class_exists('cms_utils') && is_object(\cms_utils::get_module('GMS'))) {
            try {
                $db = \cmsms()->GetDb();
                $rows = $db->GetArray('SELECT id,title,active FROM '.CMS_DB_PREFIX.'module_gms_galleries WHERE active=1 OR id=? ORDER BY sort_order,title,id', array((int)$current));
                foreach ($rows ?: array() as $row) {
                    $options[(string)$row['id']] = $row['title'].($row['active'] ? '' : ' (inactive)');
                }
            } catch (Exception $e) {
                error_log('PageSectionsPlus: unable to list GMS galleries: '.$e->getMessage());
            }
        }
        if ((int)$current > 0 && !isset($options[(string)$current])) {
            $options[(string)$current] = 'Gallery #'.(int)$current;
        }
        return $options;
    }

    public function align_options()
    {
        return array(
            'left' => $this->Lang('align_left'),
            'center' => $this->Lang('align_center'),
            'right' => $this->Lang('align_right'),
        );
    }

    public function text_class_for_bg($bg)
    {
        $dark = array('bg-primary', 'bg-secondary', 'bg-success', 'bg-danger', 'bg-info', 'bg-dark', 'bg-black');
        if (in_array($bg, $dark, true)) {
            return 'text-white';
        }

        return '';
    }

    public function align_class($align)
    {
        if ($align === 'left') {
            return 'text-start';
        }
        if ($align === 'right') {
            return 'text-end';
        }

        return 'text-center';
    }

    public function image_src($path)
    {
        $path = trim((string)$path);
        if ($path === '') {
            return '';
        }
        if (preg_match('#^(https?:)?//#i', $path) || strpos($path, '/') === 0) {
            return $path;
        }

        $config = cms_config::get_instance();
        $base = isset($config['uploads_url']) ? $config['uploads_url'] : '';
        $parts = explode('/', ltrim($path, '/'));
        $encoded = array();
        foreach ($parts as $part) {
            $encoded[] = rawurlencode($part);
        }

        return rtrim($base, '/').'/'.implode('/', $encoded);
    }

    public function storedImageUrl($image)
    {
        return $this->image_src($image);
    }

    public function uploadDirectory()
    {
        $config = cms_config::get_instance();

        return cms_join_path($config['uploads_path'], self::UPLOAD_SUBDIR);
    }

    public function ensureUploadDirectory()
    {
        $dir = $this->uploadDirectory();
        if (is_dir($dir)) {
            return is_writable($dir);
        }

        return @mkdir($dir, 0775, true) && is_writable($dir);
    }

    public function handleImageUpload($file)
    {
        if (!isset($file['tmp_name'], $file['name'], $file['size'], $file['error'])) {
            return array('error' => 'The picture upload was not valid.');
        }

        $error = (int)$file['error'];
        if ($error !== UPLOAD_ERR_OK) {
            return array('error' => 'The picture could not be uploaded (upload error '.$error.').');
        }

        if ((int)$file['size'] <= 0 || (int)$file['size'] > self::MAX_UPLOAD_BYTES) {
            return array('error' => 'Pictures must be smaller than 8 MB.');
        }

        $tmp = (string)$file['tmp_name'];
        if (!is_uploaded_file($tmp)) {
            return array('error' => 'The uploaded picture could not be verified.');
        }

        $info = @getimagesize($tmp);
        if ($info === false || empty($info['mime'])) {
            return array('error' => 'Please upload a real JPG, PNG, GIF or WebP picture.');
        }

        $mime_map = array(
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
        );
        $mime = strtolower((string)$info['mime']);
        if (!isset($mime_map[$mime])) {
            return array('error' => 'Only JPG, PNG, GIF and WebP pictures are allowed.');
        }

        if (!$this->ensureUploadDirectory()) {
            return array('error' => 'CMSMS cannot write to uploads/'.self::UPLOAD_SUBDIR.'. Check the folder permissions.');
        }

        $original = pathinfo((string)$file['name'], PATHINFO_FILENAME);
        $base = preg_replace('/[^A-Za-z0-9_-]+/', '-', $original);
        $base = trim($base, '-_');
        if ($base === '') {
            $base = 'page-section-image';
        }
        $base = substr($base, 0, 80);

        try {
            $suffix = bin2hex(random_bytes(4));
        } catch (Exception $e) {
            $suffix = substr(md5(uniqid('', true)), 0, 8);
        }

        $filename = $base.'-'.$suffix.'.'.$mime_map[$mime];
        $destination = cms_join_path($this->uploadDirectory(), $filename);
        if (!@move_uploaded_file($tmp, $destination)) {
            return array('error' => 'The picture was valid but could not be saved.');
        }

        @chmod($destination, 0664);

        return array('image' => self::UPLOAD_SUBDIR.'/'.$filename);
    }

    public function cleanStoredImage($image)
    {
        $image = str_replace('\\', '/', trim((string)$image));
        if ($image === '') {
            return '';
        }
        if (preg_match('#^https?://#i', $image)) {
            return $image;
        }
        $image = ltrim($image, '/');
        if (strpos($image, '..') !== false) {
            return '';
        }

        return preg_replace('#[^A-Za-z0-9._/\-]#', '', $image);
    }

    private function upload_action_url()
    {
        $url = html_entity_decode(
            $this->CreateLink(self::UPLOAD_ACTION_ID, 'upload', '', '', array(), '', true),
            ENT_QUOTES,
            'UTF-8'
        );
        if ($url === '') {
            return '';
        }

        return $url.(strpos($url, '?') === false ? '?' : '&').'showtemplate=false';
    }

    private function parent_lang($args)
    {
        $n = count($args);
        if ($n <= 0) {
            return parent::Lang();
        }
        if ($n === 1) {
            return parent::Lang($args[0]);
        }
        if ($n === 2) {
            return parent::Lang($args[0], $args[1]);
        }
        if ($n === 3) {
            return parent::Lang($args[0], $args[1], $args[2]);
        }

        return parent::Lang($args[0], $args[1], $args[2], $args[3]);
    }

    private function language_strings()
    {
        if (is_array($this->_psplus_lang)) {
            return $this->_psplus_lang;
        }

        $lang = array();
        $en = dirname(__FILE__).'/lang/en_US.php';
        if (is_file($en)) {
            include $en;
        }

        $current = '';
        if (class_exists('CmsNlsOperations')) {
            $current = (string)\CmsNlsOperations::get_current_language();
        }
        if (preg_match('/^[A-Za-z]{2}_[A-Za-z]{2}$/', $current) && $current !== 'en_US') {
            $ext = dirname(__FILE__).'/lang/ext/'.$current.'.php';
            if (is_file($ext)) {
                include $ext;
            }
        }

        $this->_psplus_lang = is_array($lang) ? $lang : array();

        return $this->_psplus_lang;
    }

    private function requested_template_name($blockparams)
    {
        if (!is_array($blockparams)) {
            return '';
        }
        if (isset($blockparams['template'])) {
            return trim((string)$blockparams['template']);
        }
        if (isset($blockparams['tpl'])) {
            return trim((string)$blockparams['tpl']);
        }

        return '';
    }

    private function frontend_template_resource($blockparams)
    {
        $wanted = $this->requested_template_name($blockparams);

        if (class_exists('CmsLayoutTemplate')) {
            $tpl = $this->load_layout_template($wanted);
            if (is_object($tpl)) {
                return 'cms_template:'.$tpl->get_name();
            }
        }

        $factory = self::reset_frontend_template(null);
        if (trim($factory) !== '') {
            return 'string:'.$factory;
        }

        return 'string:';
    }

    private function process_section_smarty($items)
    {
        foreach ($items as $index => $item) {
            foreach (array('left', 'right') as $side) {
                if (!isset($item[$side]['type'], $item[$side]['html']) || $item[$side]['type'] !== 'wysiwyg') {
                    continue;
                }
                $items[$index][$side]['html'] = $this->process_embedded_smarty($item[$side]['html']);
            }
        }

        return $items;
    }

    private function process_embedded_smarty($html)
    {
        $html = str_ireplace(
            array('&#123;', '&#x7b;', '&#125;', '&#x7d;'),
            array('{', '{', '}', '}'),
            (string)$html
        );
        if ($html === '' || strpos($html, '{') === false) {
            return $html;
        }

        $rendered = $this->fetch_smarty_string($html);
        if ($rendered !== null) {
            return $rendered;
        }

        $fallback = preg_replace_callback('/\{([A-Za-z][^}]*)\}/', array($this, 'replace_embedded_smarty_tag'), $html);

        return is_string($fallback) ? $fallback : $html;
    }

    private function replace_embedded_smarty_tag($matches)
    {
        $rendered = $this->fetch_smarty_string($matches[0]);

        return $rendered === null ? $matches[0] : $rendered;
    }

    private function fetch_smarty_string($html)
    {
        static $depth = 0;
        if ($depth >= 3) {
            return null;
        }

        $smarty = \CmsApp::get_instance()->GetSmarty();
        $depth++;
        $error = '';
        try {
            foreach (array('string:', 'eval:') as $resource) {
                try {
                    $rendered = $smarty->fetch($resource.$html);
                    if (is_string($rendered)) {
                        return $rendered;
                    }
                } catch (Exception $e) {
                    $error = $e->getMessage();
                }
            }
        } finally {
            $depth--;
        }

        if ($error !== '') {
            error_log('PageSectionsPlus: unable to process embedded Smarty: '.$error);
        }

        return null;
    }

    private function load_layout_template($wanted)
    {
        $type_key = $this->GetName().'::'.self::TEMPLATE_TYPE;
        if ($wanted !== '') {
            try {
                $tpl = \CmsLayoutTemplate::load($wanted);
                if (is_object($tpl)) {
                    return $tpl;
                }
            } catch (Exception $e) {
                error_log('PageSectionsPlus: unable to load template "'.$wanted.'": '.$e->getMessage());
            }
            try {
                $list = \CmsLayoutTemplate::load_all_by_type($type_key);
                if (is_array($list)) {
                    foreach ($list as $tpl) {
                        if (is_object($tpl) && strcasecmp($tpl->get_name(), $wanted) === 0) {
                            return $tpl;
                        }
                    }
                }
            } catch (Exception $e) {
                error_log('PageSectionsPlus: unable to search frontend templates: '.$e->getMessage());
            }

            return null;
        }

        try {
            $tpl = \CmsLayoutTemplate::load_dflt_by_type($type_key);
            if (is_object($tpl)) {
                return $tpl;
            }
        } catch (Exception $e) {
            error_log('PageSectionsPlus: unable to load the default frontend template: '.$e->getMessage());
        }

        return null;
    }

    private function empty_section()
    {
        return array(
            'swap' => '0',
            'left' => $this->empty_column('wysiwyg', 'left'),
            'right' => $this->empty_column('image', 'right'),
        );
    }

    private function empty_column($type = 'wysiwyg', $align = 'center')
    {
        return array(
            'type' => $type,
            'bg' => 'bg-body',
            'image' => '',
            'alt' => '',
            'align' => $align,
            'tilt' => '1',
            'gallery' => '0',
            'html' => '',
        );
    }

    private function decode_value($value)
    {
        if (is_array($value)) {
            $items = isset($value['items']) && is_array($value['items']) ? $value['items'] : array();
            $out = array();
            foreach ($items as $item) {
                if (is_array($item)) {
                    $out[] = $this->sanitize_section($item);
                }
            }

            return array('items' => $out);
        }

        $value = trim((string)$value);
        if ($value === '') {
            return array('items' => array());
        }

        $decoded = json_decode($value, true);
        if (!is_array($decoded)) {
            return array('items' => array());
        }

        $items = isset($decoded['items']) && is_array($decoded['items']) ? $decoded['items'] : array();
        $out = array();
        foreach ($items as $item) {
            if (is_array($item)) {
                $out[] = $this->sanitize_section($item);
            }
        }

        return array('items' => $out);
    }

    private function sanitize_section($row)
    {
        $legacy = isset($row['bg']) ? (string)$row['bg'] : 'bg-body';
        if (!array_key_exists($legacy, $this->bg_options())) {
            $legacy = 'bg-body';
        }

        $left = $this->sanitize_column(isset($row['left']) && is_array($row['left']) ? $row['left'] : array(), $legacy, 'left');
        $right = $this->sanitize_column(isset($row['right']) && is_array($row['right']) ? $row['right'] : array(), $legacy, 'right');

        return array(
            'swap' => (!empty($row['swap']) && $row['swap'] !== '0') ? '1' : '0',
            'left' => $left,
            'right' => $right,
        );
    }

    private function sanitize_column($col, $fallback_bg, $side)
    {
        $type = isset($col['type']) ? (string)$col['type'] : 'wysiwyg';
        if (!in_array($type, array('image', 'gallery'), true)) {
            $type = 'wysiwyg';
        }

        $bg = isset($col['bg']) ? (string)$col['bg'] : $fallback_bg;
        if (!array_key_exists($bg, $this->bg_options())) {
            $bg = $fallback_bg;
        }

        $align = isset($col['align']) ? strtolower((string)$col['align']) : ($side === 'right' ? 'right' : 'left');
        if ($align === 'centre') {
            $align = 'center';
        }
        if (!isset($this->align_options()[$align])) {
            $align = 'center';
        }

        $clean = array(
            'type' => $type,
            'bg' => $bg,
            'text_class' => $this->text_class_for_bg($bg),
            'image' => $this->cleanStoredImage(isset($col['image']) && !is_array($col['image']) ? $col['image'] : ''),
            'alt' => isset($col['alt']) ? trim((string)$col['alt']) : '',
            'align' => $align,
            // Missing values retain the existing tilted appearance. The hidden
            // checkbox input explicitly submits 0 when an editor turns it off.
            'tilt' => !array_key_exists('tilt', $col) || $col['tilt'] === '1' || $col['tilt'] === 1 || $col['tilt'] === true ? '1' : '0',
            'gallery' => isset($col['gallery']) && is_scalar($col['gallery']) && ctype_digit((string)$col['gallery']) ? (string)max(0, (int)$col['gallery']) : '0',
            'html' => isset($col['html']) ? (string)$col['html'] : '',
        );
        $clean['image_src'] = $this->image_src($clean['image']);
        $clean['align_class'] = $this->align_class($align);
        $clean['filled'] = $this->column_is_filled($clean) ? '1' : '0';

        return $clean;
    }

    private function column_is_filled($col)
    {
        if ($col['type'] === 'gallery') {
            return (int)$col['gallery'] > 0;
        }
        if ($col['type'] === 'image') {
            return $col['image'] !== '';
        }

        $text = html_entity_decode(strip_tags($col['html']), ENT_QUOTES, 'UTF-8');
        $text = str_replace("\xc2\xa0", '', $text);
        $text = trim($text);

        return $text !== '';
    }

    private function render_section_editor($blockName, $safe_block, $index, $section, $is_template)
    {
        $idx = (string)$index;
        $name_base = $blockName.'[items]['.$idx.']';
        $id_base = 'psplus_'.$safe_block.'_'.$idx;
        $title_num = $is_template ? '#' : ((string)((int)$index + 1));

        $order = $is_template ? '9999' : (string)(int)$index;
        $html = array();
        $html[] = '<div class="psplus-section" data-index="'.$idx.'" data-order="'.$order.'" style="order:'.$order.'" draggable="false">';
        $html[] = '<div class="psplus-section-head">';
        if ($this->CanReorderSections()) {
            $html[] = '<button type="button" class="psplus-handle" title="'.htmlspecialchars($this->Lang('drag_to_reorder'), ENT_QUOTES, 'UTF-8').'" aria-label="'.htmlspecialchars($this->Lang('drag_to_reorder'), ENT_QUOTES, 'UTF-8').'">&#x2630;</button>';
        }
        $html[] = '<strong class="psplus-section-title">'.$this->Lang('section').' <span class="psplus-num">'.$title_num.'</span></strong>';
        $html[] = '<span class="psplus-toolbar-spacer"></span>';
        if ($this->CanReorderSections()) {
            $html[] = '<button type="button" class="psplus-up" title="'.htmlspecialchars($this->Lang('move_up'), ENT_QUOTES, 'UTF-8').'">'.$this->Lang('move_up').'</button>';
            $html[] = '<button type="button" class="psplus-down" title="'.htmlspecialchars($this->Lang('move_down'), ENT_QUOTES, 'UTF-8').'">'.$this->Lang('move_down').'</button>';
        }
        if ($this->CanDeleteSections()) {
            $html[] = '<button type="button" class="psplus-remove">'.$this->Lang('remove_section').'</button>';
        }
        $html[] = '</div>';

        $checked = $section['swap'] === '1' ? ' checked="checked"' : '';
        if ($this->CanReorderSections()) {
            $html[] = '<div class="psplus-toolbar">';
            $html[] = '<label class="psplus-swap"><input type="checkbox" name="'.htmlspecialchars($name_base.'[swap]', ENT_QUOTES, 'UTF-8').'" value="1"'.$checked.' /> '.$this->Lang('swap_columns').'</label>';
            $html[] = '</div>';
        } else {
            $html[] = $this->hidden_input($name_base.'[swap]', $section['swap']);
        }

        $html[] = '<div class="psplus-columns">';
        $html[] = $this->render_column_editor($name_base, $id_base, 'left', $this->Lang('left'), $section['left'], $is_template);
        $html[] = $this->render_column_editor($name_base, $id_base, 'right', $this->Lang('right'), $section['right'], $is_template);
        $html[] = '</div>';
        $html[] = '</div>';

        return implode("\n", $html);
    }

    private function render_column_editor($name_base, $id_base, $side, $label, $col, $is_template)
    {
        $raw_name = $name_base.'['.$side.']';
        $id = $id_base.'_'.$side;
        $type = $col['type'];

        $html = array();
        $html[] = sprintf(
            '<fieldset class="psplus-col" data-side="%s">',
            htmlspecialchars($side, ENT_QUOTES, 'UTF-8')
        );
        $html[] = '<legend data-default-label="'.htmlspecialchars($label, ENT_QUOTES, 'UTF-8').'">'.$label.'</legend>';
        if ($this->CanSelectContentType()) {
            $html[] = '<label>'.$this->Lang('content_type').' ';
            $html[] = $this->select_html($raw_name.'[type]', 'psplus-type', $this->type_options(), $type);
            $html[] = '</label>';
        } else {
            $html[] = $this->hidden_input($raw_name.'[type]', $type, 'psplus-type');
        }
        if ($this->CanSelectBackground()) {
            $html[] = '<label>'.$this->Lang('bg_color').' ';
            $html[] = $this->select_html($raw_name.'[bg]', 'psplus-bg', $this->bg_options(), $col['bg']);
            $html[] = '</label>';
        } else {
            $html[] = $this->hidden_input($raw_name.'[bg]', $col['bg'], 'psplus-bg');
        }

        $show_image = $type === 'image' ? '' : ' style="display:none"';
        $show_html = $type === 'wysiwyg' ? '' : ' style="display:none"';

        $html[] = '<div class="psplus-image-fields"'.$show_image.'>';
        $html[] = $this->render_picture($raw_name, $id, $col['image'], $is_template);
        $html[] = '<label>'.$this->Lang('image_align').' ';
        $html[] = $this->select_html($raw_name.'[align]', 'psplus-align', $this->align_options(), $col['align']);
        $html[] = '</label>';
        $html[] = $this->hidden_input($raw_name.'[tilt]', '0');
        $tilt_checked = $col['tilt'] === '1' ? ' checked="checked"' : '';
        $html[] = '<label class="psplus-image-tilt"><input type="checkbox" name="'.htmlspecialchars($raw_name.'[tilt]', ENT_QUOTES, 'UTF-8').'" value="1"'.$tilt_checked.' /> '.$this->Lang('image_tilt').'</label>';
        $html[] = '<label>'.$this->Lang('image_alt');
        $html[] = '<input type="text" name="'.htmlspecialchars($raw_name.'[alt]', ENT_QUOTES, 'UTF-8').'" value="'.htmlspecialchars($col['alt'], ENT_QUOTES, 'UTF-8').'" />';
        $html[] = '</label>';
        $html[] = '</div>';

        $show_gallery = $type === 'gallery' ? '' : ' style="display:none"';
        $html[] = '<div class="psplus-gallery-fields"'.$show_gallery.'>';
        $html[] = '<label>'.$this->Lang('gallery_label').' ';
        $html[] = $this->select_html($raw_name.'[gallery]', 'psplus-gallery', $this->gallery_options($col['gallery']), $col['gallery']);
        $html[] = '</label><p>'.$this->Lang('gallery_help').'</p>';
        $html[] = '</div>';

        $html[] = '<div class="psplus-html-fields"'.$show_html.'>';
        $html[] = '<label>'.$this->Lang('wysiwyg_content').'</label>';
        $html[] = $this->wysiwyg_control($raw_name.'[html]', $id.'_html', $col['html'], $is_template);
        $html[] = '</div>';
        $html[] = '</fieldset>';

        return implode("\n", $html);
    }

    private function wysiwyg_control($name, $id, $value, $is_template)
    {
        if (!$is_template && function_exists('create_textarea')) {
            return create_textarea(true, (string)$value, $name, 'cms_textarea psplus-wysiwyg', $id, '', '', '80', '8');
        }

        return '<textarea class="cms_textarea psplus-wysiwyg" data-cms-lang="html" name="'.htmlspecialchars($name, ENT_QUOTES, 'UTF-8').'" id="'.htmlspecialchars($id, ENT_QUOTES, 'UTF-8').'" rows="8" cols="80">'.htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8').'</textarea>';
    }

    private function render_picture($raw_name, $id, $value, $is_template)
    {
        $value = $this->cleanStoredImage($value);
        $src = $this->image_src($value);
        $has = $src !== '';
        $edit_label = $has ? $this->Lang('change_picture') : $this->Lang('add_picture');

        $html = array();
        $html[] = '<div class="psplus-picture">';
        $html[] = '<span class="psplus-picture-label">'.$this->Lang('picture').'</span>';
        $html[] = '<div class="psplus-image-frame" title="'.htmlspecialchars($this->Lang('drop_image_hint'), ENT_QUOTES, 'UTF-8').'">';
        $html[] = '<img class="psplus-preview'.($has ? '' : ' is-hidden').'" src="'.htmlspecialchars($src, ENT_QUOTES, 'UTF-8').'" alt="" />';
        $html[] = '<span class="psplus-image-placeholder'.($has ? ' is-hidden' : '').'">'.$this->Lang('no_picture').'</span>';
        $html[] = '<label class="psplus-image-edit'.($has ? ' has-picture' : '').'" title="'.htmlspecialchars($edit_label, ENT_QUOTES, 'UTF-8').'">';
        $html[] = '<input class="psplus-upload-file" type="file" accept="image/jpeg,image/png,image/webp,image/gif" />';
        $html[] = '<span class="psplus-image-edit-symbol" aria-hidden="true">';
        $html[] = '<svg class="psplus-icon-plus" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 7v10M7 12h10"/></svg>';
        $html[] = '<svg class="psplus-icon-pencil" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 19l3.2-.7L18 8.5 15.5 6 5.7 15.8 5 19z"/><path d="M14.2 7.3l2.5 2.5"/></svg>';
        $html[] = '</span>';
        $html[] = '<span class="psplus-visually-hidden psplus-image-edit-text">'.$edit_label.'</span>';
        $html[] = '</label>';
        $html[] = '<button type="button" class="psplus-remove-image" title="'.htmlspecialchars($this->Lang('remove_picture'), ENT_QUOTES, 'UTF-8').'" aria-label="'.htmlspecialchars($this->Lang('remove_picture'), ENT_QUOTES, 'UTF-8').'"'.($has ? '' : ' disabled="disabled"').'>';
        $html[] = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3m-8 0 1 13h8l1-13M10 11v6m4-6v6"/></svg>';
        $html[] = '</button>';
        $html[] = '</div>';
        $html[] = '<span class="psplus-upload-status" role="status" aria-live="polite"></span>';
        $html[] = '<div class="psplus-source-controls">';
        $html[] = '<button type="button" class="psplus-choose-file">'.$this->Lang('upload_image').'</button> ';
        if ($this->CanUseImageLink()) {
            $html[] = '<button type="button" class="psplus-choose-link" aria-expanded="false">'.$this->Lang('use_image_link').'</button> ';
        }
        if ($this->_native_filepicker_enabled) {
            $html[] = '<button type="button" class="psplus-use-filepicker">'.$this->Lang('file_picker').'</button>';
        }
        if ($this->CanUseImageLink()) {
            $html[] = '<div class="psplus-link-fields" hidden>';
            $html[] = '<label>'.$this->Lang('image_link_label').' <input type="url" class="psplus-image-link" placeholder="https://example.com/picture.png" /></label> ';
            $html[] = '<button type="button" class="psplus-import-link">'.$this->Lang('import_image').'</button>';
            $html[] = '<small>'.$this->Lang('image_link_help').'</small>';
            $html[] = '</div>';
        }
        $html[] = '</div>';
        $html[] = '<input type="hidden" class="psplus-image-value" name="'.htmlspecialchars($raw_name.'[image]', ENT_QUOTES, 'UTF-8').'" id="'.htmlspecialchars($id.'_image', ENT_QUOTES, 'UTF-8').'" value="'.htmlspecialchars($value, ENT_QUOTES, 'UTF-8').'" />';
        $html[] = '</div>';

        return implode("\n", $html);
    }

    private function shared_filepicker_control($name)
    {
        if (!class_exists('\\cms_utils') || !method_exists('\\cms_utils', 'get_filepicker_module')) {
            return '';
        }

        try {
            $picker = \cms_utils::get_filepicker_module();
            if (!is_object($picker) || !method_exists($picker, 'get_html') || !method_exists($picker, 'get_default_profile')) {
                return '';
            }

            $uid = function_exists('get_userid') ? get_userid(false) : null;
            $profile = $picker->get_default_profile('images', $uid);
            if (is_object($profile) && method_exists($profile, 'overrideWith')) {
                $type = 'image';
                if (defined('CMSMS\\FileType::TYPE_IMAGE')) {
                    $type = \CMSMS\FileType::TYPE_IMAGE;
                }
                $custom = $profile->overrideWith(array(
                    'type' => $type,
                    'exclude_prefix' => 'thumb_',
                    'show_thumbs' => true,
                ));
                if (is_object($custom)) {
                    $profile = $custom;
                }
            }

            $html = $picker->get_html($name, '', $profile);

            return is_string($html) ? $html : '';
        } catch (Exception $e) {
            return '';
        }
    }

    private function hidden_input($name, $value, $class = '')
    {
        $class_attr = $class !== ''
            ? ' class="'.htmlspecialchars($class, ENT_QUOTES, 'UTF-8').'"'
            : '';

        return '<input type="hidden"'.$class_attr.' name="'.htmlspecialchars($name, ENT_QUOTES, 'UTF-8').'" value="'.htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8').'" />';
    }

    private function select_html($name, $class, $options, $current)
    {
        $html = '<select name="'.htmlspecialchars($name, ENT_QUOTES, 'UTF-8').'" class="'.htmlspecialchars($class, ENT_QUOTES, 'UTF-8').'">';
        foreach ($options as $val => $label) {
            $sel = ((string)$current === (string)$val) ? ' selected="selected"' : '';
            $html .= '<option value="'.htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8').'"'.$sel.'>'.htmlspecialchars($label, ENT_QUOTES, 'UTF-8').'</option>';
        }
        $html .= '</select>';

        return $html;
    }

    private function admin_asset_tags()
    {
        static $done = false;
        if ($done) {
            return '';
        }
        $done = true;

        $css = '';
        $js = '';
        $css_file = dirname(__FILE__).'/css/admin.css';
        $js_file = dirname(__FILE__).'/js/admin.js';
        if (is_file($css_file)) {
            $css = (string)@file_get_contents($css_file);
        }
        if (is_file($js_file)) {
            $js = (string)@file_get_contents($js_file);
        }
        $js = str_replace('</script>', '<\\/script>', $js);

        return '<style type="text/css">'.$css.'</style>'."\n"
            .'<script type="text/javascript">'.$js.'</script>';
    }
}

$psplus_autoload_help = dirname(__FILE__).'/doc/help.inc';
if (is_file($psplus_autoload_help)) {
    @unlink($psplus_autoload_help);
}
