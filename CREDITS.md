# Credits — PageSections Plus

This page records the software, integrations, documentation and creative
resources evidenced in this project's source and development records. A listed
integration is not necessarily bundled in the repository. Existing copyright
and licence notices remain in force; this acknowledgement does not relicense
third-party work or imply endorsement.

## Platform and shared components

| Project / contributors | Contribution and source | How it is used |
|---|---|---|
| CMS Made Simple development team and contributors | [CMSMS source](https://github.com/cmsmadesimple/cmsmadesimple), [developer handbook](https://github.com/cmsmadesimple/cmsms-developer-handbook), [API documentation](https://apidoc.cmsmadesimple.org/) | Host module framework, permissions, content/module APIs and administration. CMSMS is installed separately and retains its own [licence](https://github.com/cmsmadesimple/cmsmadesimple/blob/master/LICENCE). |
| Smarty project and contributors | [Smarty](https://www.smarty.net/), [source and licence](https://github.com/smarty-php/smarty) | Template rendering supplied by CMSMS; Smarty source is not bundled here. |
| PHP Group and PHP contributors | [PHP](https://www.php.net/), [manual](https://www.php.net/manual/en/) | Server language and standard extensions; supplied by the site's hosting environment. |
| ADOdb project and contributors | [ADOdb](https://adodb.org/), [source](https://github.com/ADOdb/ADOdb) | Database abstraction used through CMSMS's database API; supplied by CMSMS. |
| Bootstrap team and contributors | [Bootstrap 5 documentation](https://getbootstrap.com/docs/5.3/), [MIT licence](https://github.com/twbs/bootstrap/blob/main/LICENSE) | The supplied frontend markup uses Bootstrap classes. The site theme supplies Bootstrap; its CSS/JavaScript is not copied into this module. |
| jQuery team / OpenJS Foundation and contributors | [jQuery](https://jquery.com/), [MIT licence](https://github.com/jquery/jquery/blob/main/LICENSE.txt) | Integration with the CMSMS admin's jQuery events and editor controls; jQuery is host-provided. |
| TinyMCE / Tiny Technologies and contributors | [TinyMCE](https://www.tiny.cloud/), [licensing](https://www.tiny.cloud/legal/) | The content editor integrates with the CMSMS-configured WYSIWYG editor, including TinyMCE where installed. No TinyMCE distribution is bundled; licensing depends on the installed version and edition. |
| curl/libcurl project and contributors | [curl](https://curl.se/), [licence](https://curl.se/docs/copyright.html) | HTTP requests through the hosting environment's PHP cURL extension; libcurl is not bundled. |
| Gallery Made Simple (GMS) module contributors | [CMSMS Forge project](https://dev.cmsmadesimple.org/projects/gms) | Optional gallery rendering through the `{GMS}` tag when that module is installed. GMS code and gallery pictures are not bundled here; they retain their own licences. |

## Implementation references

The CMSMS content-block, Design Manager, File Picker, permission and WYSIWYG
APIs informed the integration. The CMSMS core and handbook linked above are the
authoritative references. The picture-upload/import workflow shares this
maintainer's [Easy Read module](https://github.com/MasterCatsUK/CMSMS-EasyRead)
approach; it is not an imported third-party upload widget.

## Development assistance

- Maintained and published by [MasterCatsUK](https://github.com/MasterCatsUK).
- [OpenAI Codex](https://openai.com/codex/) was used for AI-assisted implementation,
  troubleshooting, documentation and release preparation. This records tool
  provenance; it does not replace the credits or licences of upstream projects.
- **ModuleChecker 1.2.0**, credited in its source to **Pixel Solutions**, was
  used for local static preflight with a CMS metadata shim. See the
  [official ModuleChecker documentation](https://developer.cmsmadesimple.org/docs/developer-tools/module-checker)
  and [CMSMS scanner rules project](https://github.com/cmsmadesimple/cmsms-mc-rules).
  The scanner is a development tool, not a bundled runtime dependency; the
  preflight does not establish live CMSMS compatibility.

## Project artwork

The promotional module banner was generated for this project with **OpenAI
ImageGen** on 27 October 2026, with direction and selection by the maintainer.
It is an illustration of the module's purpose, not a screenshot of its interface.
Standard and retina exports are in `assets/`. No third-party photo library or
external stock image is represented as the source of this generated banner.

The module's own GPL-2.0-or-later notice is retained in [docs/LICENSE](docs/LICENSE),
with the complete GPL text in [docs/COPYING](docs/COPYING).

Attribution inventory reviewed: 29 October 2026.
