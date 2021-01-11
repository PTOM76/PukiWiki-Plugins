<?php
// $Id: alert.inc.php,v 1.0 2020/10/22 16:11:20 K Exp $

function plugin_alert_inline()
{
	$args = func_get_args();
	$arg1 = $args[0];
	$arg2 = $args[1];
    $arg1 = str_replace("\"","&quot;",$arg1);
    $arg1 = str_replace("'","\&#39;",$arg1);
	return "<a href=\"javascript:alert('".$arg1."');\">".$arg2."</a>";
}
function plugin_alert_convert()
{
	return call_user_func_array('plugin_alert_inline', func_get_args());
}
?>
