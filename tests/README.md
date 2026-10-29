# Optional GMS regression check

Run `PSPLUS_SMARTY_LIB=/absolute/path/to/smarty/libs/Smarty.class.php php tests/optional_gms.php` with Smarty 3.1 installed separately. The test uses real Smarty compilation/rendering and small CMSMS API stubs; it does not connect to a CMSMS database or establish live-install compatibility. It covers absent, inactive and active GMS, gallery-control visibility, retained gallery IDs, Bootstrap rendering and guarded dispatch, plus factory/custom/CRLF/repeated upgrades.
