<?php
// Run with PSPLUS_SMARTY_LIB pointing to Smarty 3's libs/Smarty.class.php.
error_reporting(E_ALL & ~E_DEPRECATED);
define('CMS_VERSION', '2.2.24');
define('CMS_DB_PREFIX', 'cms_');
$library = getenv('PSPLUS_SMARTY_LIB');
if (!$library || !is_file($library)) throw new RuntimeException('Set PSPLUS_SMARTY_LIB to Smarty.class.php');
require $library;
class CMSModule {
    public function CheckPermission($key) { return true; }
    public function Lang() { return 'Missing Languagestring'; }
}
class cms_utils {
    public static $installed = false;
    public static $active = false;
    public static function module_available($name) { return self::$installed && self::$active; }
    public static function get_module($name) { return self::$installed ? new stdClass() : null; }
}
class CmsLayoutTemplate {
    public static $templates = array();
    public $content;
    public $name;
    public $saves = 0;
    public function __construct($name, $content) { $this->name=$name; $this->content=$content; }
    public static function load($name) { return self::$templates[$name]; }
    public function get_name() { return $this->name; }
    public function get_content() { return $this->content; }
    public function set_content($content) { $this->content=$content; }
    public function save() { ++$this->saves; }
}
class CmsApp {
    public static $smarty;
    public static function get_instance() { return new self(); }
    public function GetSmarty() { return self::$smarty; }
    public function GetDb() { return new class {
        public function GetArray($sql, $params) { return array(array('id'=>17,'title'=>'Saved gallery','active'=>1)); }
    }; }
}
function cmsms() { return CmsApp::get_instance(); }
require dirname(__DIR__).'/PageSectionsPlus.module.php';
function check($ok,$message) { if (!$ok) throw new RuntimeException($message); }
function invoke_private($module,$method,...$args) {
    $ref=new ReflectionMethod($module,$method);
    return $ref->invoke($module,...$args);
}
$module=new PageSectionsPlus();
$temp=sys_get_temp_dir().'/psplus-optional-gms-'.getmypid();
mkdir($temp);
$smarty=new Smarty();
$smarty->setCompileDir($temp);
$smarty->registerResource('cms_template',array(
    function($name,&$source,$smarty) { $source=CmsLayoutTemplate::load($name)->content; return true; },
    function($name,&$timestamp,$smarty) { $timestamp=1; return true; },
    function() { return false; },
    function() { return false; }
));
$calls=0;
$smarty->registerPlugin('function','cms_module',function($params,$template) use (&$calls) {
    check(cms_utils::module_available('GMS'),'Unavailable GMS must never be called');
    check($params['module']==='GMS' && $params['gallery']==17,'Wrong gallery parameters');
    ++$calls;
    return '<div class="test-gallery">Gallery 17</div>';
});
CmsApp::$smarty=$smarty;
$factory=PageSectionsPlus::reset_bootstrap_template(null);
check(strpos($factory,'{GMS ')===false,'Direct GMS short tags must not require a plugin at compile time');
CmsLayoutTemplate::$templates[PageSectionsPlus::BOOTSTRAP_TEMPLATE_NAME]=new CmsLayoutTemplate(PageSectionsPlus::BOOTSTRAP_TEMPLATE_NAME,$factory);
$columns=array(
    'text'=>array('type'=>'wysiwyg','html'=>'<p>Normal text</p>'),
    'image'=>array('type'=>'image','image'=>'https://example.org/picture.png','alt'=>'Example'),
    'gallery'=>array('type'=>'gallery','gallery'=>'17','html'=>'<p>Old hidden text</p>')
);
foreach (array(array(false,false),array(true,false),array(true,true)) as $state) {
    list(cms_utils::$installed,cms_utils::$active)=$state;
    $available=$state[0] && $state[1];
    check($module->IsGalleryAvailable()===$available,'Installed/active state detection');
    check(isset($module->type_options()['gallery'])===$available,'Gallery option visibility');
    foreach(array(array('text','image'),array('gallery','text'),array('image','gallery'),array('gallery','gallery')) as $types) {
        $input=array('items'=>array(array('left'=>$columns[$types[0]],'right'=>$columns[$types[1]])));
        $before=json_encode($input);
        $countBefore=$calls;
        $html=$module->RenderContentBlockField('sections',$input,array('template'=>PageSectionsPlus::BOOTSTRAP_TEMPLATE_NAME),null);
        check(json_encode($input)===$before,'Rendering must not change stored data');
        if (in_array('text',$types)) check(strpos($html,'Normal text')!==false,'Text remains visible');
        if (in_array('image',$types)) check(strpos($html,'picture.png')!==false,'Picture remains visible');
        check(strpos($html,'Old hidden text')===false,'Hidden gallery text must not leak into output');
        $galleries=count(array_filter($types,function($type){return $type==='gallery';}));
        check($calls-$countBefore===($available?$galleries:0),'GMS dispatch count');
        if (!$available && $galleries===1) check(strpos($html,'col-lg-6')===false && strpos($html,'psplus-feature-section')!==false,'Remaining content keeps full-width Bootstrap layout');
        $clean=invoke_private($module,'decode_value',$input);
        $editor=invoke_private($module,'render_column_editor','sections[items][0]','column','left','Left',$clean['items'][0]['left'],true);
        check((strpos($editor,'class="psplus-gallery-fields"')!==false)===$available,'Gallery selector presence');
        if (!$available && $types[0]==='gallery') {
            check(strpos($editor,'class="psplus-gallery-unavailable"')!==false,'Saved-gallery notice');
            check(strpos($editor,'name="sections[items][0][left][type]" value="gallery"')!==false,'Preserve gallery type');
            check(strpos($editor,'name="sections[items][0][left][gallery]" value="17"')!==false,'Preserve gallery ID');
        }
        $saved=json_decode($module->GetContentBlockFieldValue('sections',array(),array('sections'=>$input),null),true);
        if ($types[0]==='gallery') check($saved['items'][0]['left']['gallery']==='17' && $saved['items'][0]['left']['type']==='gallery','Save retains missing gallery');
    }
}
// Upgrade unchanged LF/CRLF factories, preserve customisations, and remain idempotent.
$oldFactory=file_get_contents(isset($argv[1]) ? $argv[1] : __DIR__.'/fixtures/bootstrap-1.6.7.tpl');
foreach(array($oldFactory,str_replace("\n","\r\n",$oldFactory),$oldFactory."\n<p>Custom layout</p>") as $i=>$content) {
    $tpl=new CmsLayoutTemplate(PageSectionsPlus::BOOTSTRAP_TEMPLATE_NAME,$content);
    CmsLayoutTemplate::$templates[PageSectionsPlus::BOOTSTRAP_TEMPLATE_NAME]=$tpl;
    $oldversion='1.6.7';
    include dirname(__DIR__).'/method.upgrade.php';
    check($tpl->content===($i<2?$factory:$content),'Factory upgrade/custom preservation');
    check($tpl->saves===($i<2?1:0),'Only unchanged factories are saved');
    $oldversion='1.6.8';
    include dirname(__DIR__).'/method.upgrade.php';
    check($tpl->saves===($i<2?1:0),'Repeated upgrade must not rewrite templates');
}
foreach(glob($temp.'/*') as $file) unlink($file);
rmdir($temp);
echo "PASS: GMS absent/inactive/active; editor visibility; saved gallery preservation; real Smarty Bootstrap rendering; guarded gallery calls; factory/custom/CRLF/idempotent upgrades.\n";
