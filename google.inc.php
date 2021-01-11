<?php
// $Id: google.inc.php,v 1.00 2020/10/20 16:07:21 K Exp $

function plugin_google_convert()
{
    $form = <<<EOD
    <form method="get" action="http://www.google.co.jp/custom" target="_top">
    <a href="http://www.google.com/">
    <img src="https://www.google.com/images/branding/googlelogo/2x/googlelogo_color_150x54dp.png" border="0" alt="Google" align="middle" width="5%" height="5%"></img></a>
        <input type="text" name="q" size="8" maxlength="255" value="" />
        <input type="hidden" name="client" value="pub-0885625580289468" />
        <input type="hidden" name="forid" value="1" />
        <input type="hidden" name="ie" value="UTF-8" />
        <input type="hidden" name="oe" value="UTF-8" />
        <input type="hidden" name="cof" value="GALT:#008000;GL:1;DIV:#336699;VLC:663399;AH:center;BGC:FFFFFF;LBGC:336699;ALC:0000FF;LC:0000FF;T:000000;GFNT:0000FF;GIMP:0000FF;FORID:1;" />
        <input type="hidden" name="hl" value="ja" />
        <input type="submit" value="検索" />
    </form>
    EOD;
	return $form;
}