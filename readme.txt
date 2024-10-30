=== mg404Rewrite ===
Contributors: mgsimon
Donate link: http://mgsimon.de/mg404rewrite/
Tags: permalink, 404, rewrite
Requires at least: 2.7
Tested up to: 2.8.6
Stable tag: 0.9

This module is a workaround to use pretty permalinks without any rewrite rule.

== Description ==

Pretty Permalinks requires outofthebox the module mod_rewrite. This module is a workaround to use pretty permalinks without any rewrite rule. The errorhandling - customized errorpage - will be used to resolve pretty permalinks. mg404Rewrite-Modul sends HTTP-Header-Code depending on the request. 
If you have any posts to permalinks, you can try to activate PostProxy to resolve this issue (configurations / misc).

== Installation ==

1. Unpack mg404rewrite.0.7.zip.
2. Copy mg404rewrite into your wp-content/plugins directory.
3. Activate the plugin at the plugin administration page.
4. (Re-)Change permalink settings to write .htaccess (configurations / permalinks).
5. If you have any posts to permalinks, you can try to activate PostProxy to resolve this issue (configurations / misc).

== Licence ==

GPL (http://www.gnu.org/copyleft/gpl.html)
