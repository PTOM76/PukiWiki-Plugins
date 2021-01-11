<?php
// $Id: yahoo.inc.php,v 1.00 2020/10/20 16:27:59 K Exp $

function plugin_yahoo_convert()
{
    $form = <<<EOD
    <form method="get" action="https://search.yahoo.com/search" target="_top">
    <a href="http://www.yahoo.com/">
    <img src="https://s.yimg.com/rz/p/yahoo_frontpage_en-US_s_f_p_205x58_frontpage.png" border="0" alt="Yahoo" align="middle" width="5%" height="5%"></img></a>
        <input type="text" name="p" size="8" maxlength="255" value="" />
        <input type="hidden" name="fr" value="top_ga1_sa" />
        <input type="hidden" name="ei" value="UTF-8" />
        <input type="submit" value="検索" />
    </form>
    EOD;
	return $form;
}