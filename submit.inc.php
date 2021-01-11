<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: submit.inc.php,v 1.0 2020/10/21 17:08:12 K Exp $

function plugin_submit_inline()
{
	$body = implode(func_get_args());
	return "<input type=\"submit\" style=\"text-indent:0px;line-height:1em;vertical-align:middle\" value=\"{$body}\"></input>";
    if ($body==""){
        $body = "送信";
    }
}
function plugin_submit_convert() {
	return call_user_func_array('plugin_submit_inline', func_get_args());
}
?>
