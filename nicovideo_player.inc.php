<?php
$filedata="<?php
function plugin_nicovideo_player_convert()
{
    if (func_num_args() < 1) return \"使い方:#nicovideo_player([VideoID(sm~~~~~~~~)],[横],[高さ])\";
    \$args = func_get_args();
    \$id = trim(\$args[0]);
    \$width = trim(\$args[1]).\"px\";
    \$height = trim(\$args[2]).\"px\";
    \$width_2 = trim(\$args[1]);
    \$height_2 = trim(\$args[2]);
    if (\$width_2 == null) \$width_2 = \"640\";
    if (\$height_2 == null) \$height_2 = \"360\";
    if (\$width == \"px\") \$width = \"100%\";
    if (\$height == \"px\") \$height = \"0\";
    \$str = <<<EOD
    <div style=\"width: 100%; max-width: 560px;\">
        <div style=\"position: relative; padding-bottom: 56.25%; height: \$height; width: \$width;\">
            <script type=\"application/javascript\" src=\"https&#58;//embed.nicovideo.jp/watch/\$id/script?w=\$width_2&h=\$height_2\"></script><noscript><p>JavaScriptが無効です。</p><a href=\"https&#58;//www.nicovideo.jp/watch/\$id\">https&#58;//www.nicovideo.jp/watch/\$id</a></noscript>
        </div>
    </div>
    <br><br><br>
    EOD;
    return \$str;
}
function plugin_nicovideo_player_inline() {
    \$args = func_get_args();
	return call_user_func_array('plugin_nicovideo_player_convert', \$args);
}
?>";
$temp = tmpfile();
file_put_contents($temp.$filename, $filedata);
header('Content-Type: application/octet-stream');
header("Content-Disposition: attachment; filename=nicovideo_player.inc.php"); 
readfile($temp.$filename);
unlink($temp.$filename);
?>