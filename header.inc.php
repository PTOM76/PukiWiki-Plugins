<?php
/**
 * @author     K
 * @license    http://www.gnu.org/licenses/gpl.html GPL v3
 * @link       http://pkom.ml/?%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/header.inc.php
 * @version    $Id: header.inc.php,v 1.0 2020/10/24 10:54:20 K $
 */

// Use SubHeader if true
define('HEADER_ENABLE', FALSE);

// Name of Header
define('HEADER_NAME', ':Header');

function plugin_header_convert()
{
	global $vars;
	static $menu = NULL;

	$num = func_num_args();
	if ($num > 0) {
		if ($num > 1) {
			return '#header(): Zero or One argument needed';
		}
		if ($menu !== NULL) {
			return '#header(): Already set: ' . htmlsc($menu);
		}
		$args = func_get_args();
		if (! is_page($args[0])) {
			return '#header(): No such page: ' . htmlsc($args[0]);
		} else {
			$menu = $args[0]; // Set
			return '';
		}
	}
	$page = ($menu === NULL) ? HEADER_NAME : $menu;
	if (HEADER_ENABLE) {
		$path = explode('/', strip_bracket($vars['page']));
		while(! empty($path)) {
			$_page = join('/', $path) . '/' . HEADER_NAME;
			if (is_page($_page)) {
				$page = $_page;
				break;
			}
			array_pop($path);
		}
	}
	if (! is_page($page)) {
		return '';
	} else if ($vars['page'] === $page) {
		return '<!-- #header(): You already view ' . htmlsc($page) . ' -->';
	} else if (!is_page_readable($page)) {
		return '#header(): ' . htmlsc($page) . ' is not readable';
	} else {
		$headertext = preg_replace('/^(\*{1,3}.*)\[#[A-Za-z][\w-]+\](.*)$/m', '$1$2', get_source($page));
		return convert_html($headertext);
	}
}
