<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: button.inc.php,v 1.1 2020/10/21 16:51:32 reimy.author_K.mod Exp $

function plugin_button_inline()
{
	$body = implode(func_get_args());
	return "<button type=\"button\" style=\"text-indent:0px;line-height:1em;vertical-align:middle\">{$body}</button>";
}
function plugin_button_convert() {
	return call_user_func_array('plugin_button_inline', func_get_args());
}
?>
