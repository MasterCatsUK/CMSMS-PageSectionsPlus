<h3>What this does</h3>
<p>Page Sections Plus adds a <code>{content_module}</code> block. On each page, editors can add sections, delete them, and drag them into order. Every section has a left side and a right side.</p>
<p>Each side has:</p>
<ul>
<li>A Bootstrap background class (<code>bg-body</code>, <code>bg-body-secondary</code>, <code>bg-primary</code>, and the other <code>bg-*</code> options).</li>
<li>A content type: <strong>WYSIWYG</strong> (the CMSMS editor) or <strong>Image</strong>. <strong>Gallery (GMS)</strong> is available only when Gallery Made Simple is installed and active.</li>
</ul>
<p>Image sides use the same picture control as Easy Read:</p>
<ul>
<li><strong>Upload image</strong> (or the round + / pencil button, or drop a file on the box) saves a JPG, PNG, GIF or WebP file into <code>uploads/PageSectionsPlus</code>.</li>
<li><strong>Use image link +</strong> then <strong>Import image</strong> downloads a public HTTP(S) image and copies it onto the hosting. Redirects are rejected; use the final image URL. PHP cURL is required for import.</li>
<li><strong>File Picker</strong> opens the native CMSMS image picker.</li>
<li><strong>Tilt picture</strong> turns the tilt on or off for that picture in that section. Existing and new pictures start ticked. Untick and save for a straight picture.</li>
</ul>
<p>The preview appears in the box as soon as the copy succeeds. Apply or Submit still saves the page. Pictures larger than 8 MB are refused.</p>

<h3>How to use it</h3>
<p>Install the module under <strong>Extensions → Modules</strong>, then add this to a page template:</p>
<pre>{content_module module="PageSectionsPlus" block="sections" label="Page Sections"}</pre>
<p>Leave <code>template</code> empty to use the default Basic template, or select either Design Manager template by name:</p>
<pre>{content_module module="PageSectionsPlus" block="sections" template=""}
{content_module module="PageSectionsPlus" block="sections" template="PageSectionsPlus Basic"}
{content_module module="PageSectionsPlus" block="sections" template="PageSectionsPlus Bootstrap"}</pre>
<p><strong>PageSectionsPlus Basic</strong> contains only simple Smarty output without layout markup or a frontend data dump. <strong>PageSectionsPlus Bootstrap</strong> is the complete frontend layout and is focused on websites using Bootstrap 5.3.</p>
<p>Edit a page that uses that template. The <strong>Page sections</strong> field is the editor.</p>
<p><strong>Show/Hide Page Sections</strong> collapses the editor. The choice is remembered separately for each saved page and Page Sections block in that browser. New unsaved pages start open.</p>

<h3>Other module tags</h3>
<p>Put another module's tag directly in a WYSIWYG side. It is processed when the page is displayed:</p>
<pre>{YouTubeCatalog}
{YouTubeCatalog category="featured"}</pre>
<p>Parameters go inside that module's own tag. The module must be installed. If its short tag is not available, use <code>{cms_module module="YouTubeCatalog"}</code>. Wrap CSS or JavaScript that contains curly braces in <code>{literal}...{/literal}</code>.</p>

<h3>Editor permissions</h3>
<p>Assign these under <strong>User Management → Backend Group Permissions</strong>:</p>
<ul>
<li><strong>Page Sections Plus: delete sections</strong></li>
<li><strong>Page Sections Plus: reorder sections and swap sides</strong></li>
<li><strong>Page Sections Plus: use CMSMS File Picker</strong></li>
<li><strong>Page Sections Plus: import an image link</strong></li>
<li><strong>Page Sections Plus: select content type</strong></li>
<li><strong>Page Sections Plus: select background</strong></li>
<li><strong>Page Sections Plus: view help</strong> — without this, the module is hidden from the Extensions menu and its help page is hidden</li>
</ul>
<p>Without these permissions, the related controls are hidden and their saved values are retained. Users can still edit WYSIWYG content and alt text, and can always upload an image using <strong>Upload image</strong> or the pencil button.</p>

<h3>Frontend layout</h3>
<ul>
<li>Both sides filled: two equal columns. <em>Swap left/right on desktop</em> reverses them from the medium breakpoint up.</li>
<li>Only the text side is filled: that text spans the full width of the section.</li>
<li>Only the image side is filled: the section is full width and the image sits left, centre, or right, using the position chosen for that side.</li>
<li>Dark backgrounds (<code>bg-primary</code>, <code>bg-secondary</code>, <code>bg-success</code>, <code>bg-danger</code>, <code>bg-info</code>, <code>bg-dark</code>, <code>bg-black</code>) also get <code>text-white</code>.</li>
</ul>
<p>Imported and uploaded pictures are stored under <code>uploads/PageSectionsPlus</code>. The frontend template prints <code>image_src</code>, which is the public URL.</p>

<h3>Design Manager</h3>
<p>The templates are edited under <strong>Layout → Design Manager → Templates</strong>. <strong>PageSectionsPlus Basic</strong> restores from <code>templates/orig_frontend.tpl</code>. <strong>PageSectionsPlus Bootstrap</strong> restores from <code>templates/orig_bootstrap.tpl</code> and uses <code>$item</code> as its loop variable.</p>
<p>Variables available to both templates:</p>
<ul>
<li><code>{$sections}</code> — list of sections, in editor order.</li>
<li><code>{$section.swap}</code> — <code>1</code> when desktop columns are swapped.</li>
<li><code>{$section.left}</code> and <code>{$section.right}</code> — each has <code>type</code> (<code>wysiwyg</code> or <code>image</code>), <code>bg</code>, <code>text_class</code>, <code>filled</code>, <code>html</code>, <code>image</code>, <code>image_src</code>, <code>alt</code>, <code>align</code> (<code>left</code>, <code>center</code>, <code>right</code>), <code>tilt</code> (<code>1</code> for tilted, <code>0</code> for straight).</li>
<li><code>{$block_name}</code> — the <code>block</code> parameter from the tag.</li>
</ul>

<h3>Upgrade</h3>
<p>Copy the new module files over <code>modules/PageSectionsPlus</code>, then open <strong>Extensions → Modules</strong> and click <strong>Upgrade</strong> next to Page Sections Plus. Upgrading from 1.0 registers the Design Manager template and drops the old module-template copy. Clear the CMSMS cache afterwards (<code>tmp/cache</code> and <code>tmp/templates_c</code>).</p>

<h3>Requirements</h3>
<p>CMS Made Simple 2.2 or newer, with Design Manager and the core file picker (File Manager). The page design should load Bootstrap if you use the <code>bg-*</code> and grid classes from the default template.</p>

<h3>Optional Gallery Made Simple integration</h3>
<p>Gallery controls are hidden when GMS is unavailable. Existing gallery selections are retained, with an editor notice, and are omitted from frontend output until GMS is restored. Text and picture columns keep their layout; the remaining filled column spans the full width.</p>
<p>Version 1.6.8 refreshes only the unchanged factory Bootstrap template. Customised templates are preserved. In custom templates, replace direct GMS short tags with guarded core calls, for example:</p>
<pre>{if $gms_available}{cms_module module='GMS' gallery=$item.left.gallery show_buttons='0'}{/if}</pre>
