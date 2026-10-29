<p>PageSectionsPlus Basic provides minimal Smarty output without layout markup. PageSectionsPlus Bootstrap provides the complete layout for Bootstrap 5.3 websites.</p>
<ul>
<li><code>{$sections}</code> — sections in editor order. Skip a section when neither side is <code>filled</code>.</li>
<li><code>{$section.left}</code> / <code>{$section.right}</code> — <code>type</code>, <code>bg</code>, <code>text_class</code>, <code>filled</code>, <code>html</code>, <code>image</code>, <code>image_src</code>, <code>alt</code>, <code>align</code>, <code>tilt</code>.</li>
<li><code>{$section.swap}</code> — <code>1</code> to reverse the two columns on desktop.</li>
<li><code>image_src</code> is the URL to print. <code>image</code> is the stored file-picker path or the original link.</li>
<li>When only one side is <code>filled</code> and its <code>type</code> is <code>wysiwyg</code>, give that text the full width of the section.</li>
<li>When only an image is filled, place it with <code>align</code>: <code>left</code>, <code>center</code>, or <code>right</code>.</li>
<li><code>tilt</code> — <code>1</code> for a tilted picture, <code>0</code> for straight. Each picture has an independent checkbox; new and legacy pictures default to ticked.</li>
</ul>
<p>Factory templates are stored in <code>templates/orig_frontend.tpl</code> and <code>templates/orig_bootstrap.tpl</code>.</p>

<p><code>{$gms_available}</code> indicates whether the optional GMS module is available. Gallery columns retain <code>gallery</code> (the saved ID), but are marked unfilled in frontend data while GMS is absent. Use the core <code>{cms_module module='GMS' ...}</code> tag inside an availability guard, rather than a direct GMS short tag, so templates compile without GMS.</p>
