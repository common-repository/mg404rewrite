<?php
/*
Plugin Name: mg404Rewrite
Plugin URI: http://mgsimon.de/mg404rewrite/
Description: Workaround to use permalinks without mod_rewrite. If you have any posts to permalinks, you can try to activate PostProxy to resolve this issue (configurations / misc).
Version: 0.9
Author: Michael Gustav Simon
Author URI: http://mgsimon.de
*/

/*
History
=======
0.1 HTTP-Status 200 OK
0.2 Modify HTTP-Header not for WordPress-404
0.3 Add GPL (http://www.gnu.org/copyleft/gpl.html) license information.
    Plugin URI changed.
0.4 Overwrite .htaccess-Rules to use ErrorDocument and protect for any mod_rewrite-Rule
0.5 Trackback-Requests will be ignored, because WordPress set the HTTP-Status to 302.
0.6 HTTP-Post will not deliverd by Apache 404 Errorhandling. 
    Trackback-URL have to be used without permalinkstructure.
    Use prefix mg404_ for all functions.
0.7 HTTP-Post workaround: mg404rewrite/index.php = Post-Proxy
    1. Determine any form with method POST.
    2. Determine action URL.
    3. Do nothing for non-permalink.
    4. Change action URL to .../mg404rewrite/index.php.
    5. Insert hidden formfield with original action URL to handle request in .../mg404Rewrite/index.php.
    6. .../mg404Rewrite/index.php: Overwrite requested URL with original Post-URL and include WordPress index.php.
0.8 Add Tested up to: 2.7.1 in readme.txt
0.9 Change Tested up to: 2.8.6, Stable tag: 0.9 and any URL from blog.mgsimon.de to mgsimon.de in readme.txt
    Change any URL from blog.mgsimon.de to mgsimon.de

*/

/*  Copyright 2007  Michael Gustav Simon  (email : mgsimon@mgsimon.de)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

function mg404_postproxy_url_callback() {
	$default = "wp-content/plugins/mg404rewrite/";
	if (!get_option('mg404_postproxy_url'))
		add_option('mg404_postproxy_url',$default);
	echo "<input name='mg404_postproxy_url' id='mg404_postproxy_url' value='" . attribute_escape(get_option('mg404_postproxy_url')) . "' class='regular-text code' type='text'><span class='setting-description'>Relative URL (without http://mydomain/mywordpress/) to the PostProxy script (Default: " . attribute_escape($default) . " ).</span>";
}

function mg404_postproxy_linkregex_callback() {
	$default = ".*\.php$";
	if (!get_option('mg404_postproxy_linkregex'))
		add_option('mg404_postproxy_linkregex',$default);
	echo "<input name='mg404_postproxy_linkregex' id='mg404_postproxy_linkregex' value='" . attribute_escape(get_option('mg404_postproxy_linkregex')) . "' class='regular-text code' type='text'><span class='setting-description'>Regular expression to check non permalink URL (Default: " . attribute_escape($default) . ").</span>";
}

function mg404_postproxy_urlregex_callback() {
	$default = ".*action=[\"'](.*?)[\"' >]";
	if (!get_option('mg404_postproxy_urlregex'))
		add_option('mg404_postproxy_urlregex',$default);
	echo "<input name='mg404_postproxy_urlregex' id='mg404_postproxy_urlregex' value='" . attribute_escape(get_option('mg404_postproxy_urlregex')) . "' class='regular-text code' type='text'><span class='setting-description'>Regular expression to extract URL in first back reference () (Default: " . attribute_escape($default) . ").</span>";
}

function mg404_postproxy_formregex_callback() {
	$default = "<form.+method=[\"']?post.*?>";
	if (!get_option('mg404_postproxy_formregex'))
		add_option('mg404_postproxy_formregex',$default);
	echo "<input name='mg404_postproxy_formregex' id='mg404_postproxy_formregex' value='" . attribute_escape(get_option('mg404_postproxy_formregex')) . "' class='regular-text code' type='text'><span class='setting-description'>Regular expression to macht any form tag with the method post (Default: " . attribute_escape($default) . ").</span>";
}

function mg404_postproxy_enable_callback() {
	$checked = get_option('mg404_postproxy_enable');
	if ($checked) 
		$checked = " checked='checked' ";
	echo "<input {$checked} name='mg404_postproxy_enable' id='mg404_postproxy_enable' type='checkbox' value='1' class='regular-text code'>";
}

function mg404_setting_section_callback() {
	echo "<p>If you have any posts to permalinks, you can try to activate PostProxy to resolve this issue.</p>";
}

function mg404_settings_api_init() {
	add_settings_section('mg404_setting_section', 'mg404rewrite', 'mg404_setting_section_callback', 'misc');
	add_settings_field('mg404_postproxy_enable', 'Activate PostProxy', 'mg404_postproxy_enable_callback', 'misc', 'mg404_setting_section');
	register_setting('misc', 'mg404_postproxy_enable');
	add_settings_field('mg404_postproxy_formregex', 'RegEx Form', 'mg404_postproxy_formregex_callback', 'misc', 'mg404_setting_section');
	register_setting('misc', 'mg404_postproxy_formregex');
	add_settings_field('mg404_postproxy_urlregex', 'RegEx URL', 'mg404_postproxy_urlregex_callback', 'misc', 'mg404_setting_section');
	register_setting('misc', 'mg404_postproxy_urlregex');
	add_settings_field('mg404_postproxy_linkregex', 'RegEx Non-Permalink', 'mg404_postproxy_linkregex_callback', 'misc', 'mg404_setting_section');
	register_setting('misc', 'mg404_postproxy_linkregex');
	add_settings_field('mg404_postproxy_url', 'PostProxy URL', 'mg404_postproxy_url_callback', 'misc', 'mg404_setting_section');
	register_setting('misc', 'mg404_postproxy_url');
}

add_action('admin_init', 'mg404_settings_api_init');

function mg404_FormProcessor($match) {
	$formtag = $match[0];
	$urlregex = "/" . get_option('mg404_postproxy_urlregex') . "/i";
	preg_match($urlregex,$formtag,$actionurl);
	$linkregex = "/" . get_option('mg404_postproxy_linkregex') . "/i";
	if (preg_match($linkregex,$actionurl[1]))
		return $formtag;
	$postproxyurl = get_option('siteurl') . "/" . get_option('mg404_postproxy_url');
	$formtag = str_replace($actionurl[1],$postproxyurl,$formtag);
	$formtag .= "<input type='hidden' name='mg404_posturl' value='" . $actionurl[1] . "'/>";
	$formtag .= "<input type='hidden' name='mg404_level' value='" . substr_count(get_option('mg404_postproxy_url'),'/') . "'/>";
	return $formtag;
}

function mg404_PostProxy($content) {
	$formregex = "/" . get_option('mg404_postproxy_formregex') . "/i";
	$content = preg_replace_callback($formregex,'mg404_FormProcessor',$content);
	return $content;
}

function mg404_ob_start() {
	if (get_option('mg404_postproxy_enable') && !strpos($_SERVER['REQUEST_URI'], 'wp-admin'))
		ob_start('mg404_PostProxy');
}

add_action('template_redirect', 'mg404_ob_start');

function mg404_RewriteRule ($ruleSet) {
	$parsed = parse_url(get_option('home'));
	$path = '';
	if (is_array($parsed))
		$path = $parsed['path'];
	$ruleSet = 'ErrorDocument 404 ' . $path . '/index.php';
	return $ruleSet;
}

add_filter('mod_rewrite_rules','mg404_RewriteRule');

function mg404_trackback_url() {
	global $id;
	return get_option('siteurl') . '/wp-trackback.php?p=' . $id;
}

add_filter( 'trackback_url', 'mg404_trackback_url' );

function mg404_sendHTTPCode () {
	if (!is_404() && !is_trackback())
			header("Status: 200 OK");
}

add_action('template_redirect', 'mg404_sendHTTPCode');

?>
