<?php
// $Id: youtube_sr.inc.php,v 1.00 2020/10/20 16:27:59 K Exp $

function plugin_youtube_sr_convert()
{
    $form = <<<EOD
    <form method="get" action="https://www.youtube.com/results" target="_top">
    <a href="https://www.youtube.com/">
    <img src="https://www.youtube.com/yts/img/marketing/browsers/yt_logo_rgb_light-vflc4oMnY.png" border="0" alt="Yahoo" align="middle" width="5%" height="5%"></img></a>
        <input type="text" name="search_query" size="8" value="" />
        <input type="submit" value="検索" />
    </form>
    EOD;
	return $form;
}