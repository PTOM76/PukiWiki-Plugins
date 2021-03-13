<?php
function plugin_debug_convert(){
	$out = "";
	foreach (glob(PLUGIN_DIR . "*.php") as $filename) {
        $out .= preg_replace('/(.*?)\.inc\.php/', "#$1", basename($filename)) . "<br />";
    }
    return "<pre>" . $out . "</pre>";
}

function plugin_debug_inline(){
	$out = "";
	foreach (glob(PLUGIN_DIR . "*.php") as $filename) {
        $out .= preg_replace('/(.*?)\.inc\.php/', "&$1;", basename($filename)) . "<br />";
    }
    return "<pre>" . $out . "</pre>";
}