<?php
$filedata="<?php
function plugin_youtube_convert()
{
    if (func_num_args() < 1) return \"使い方:#youtube([VideoID],[横],[高さ])\";
    \$args = func_get_args();
    \$id = trim(\$args[0]);
    \$width = trim(\$args[1]).\"px\";
    \$height = trim(\$args[2]).\"px\";
    if (\$width == \"px\") \$width = \"100%\";
    if (\$height == \"px\") \$height = \"0\";
    \$str = <<<EOD
    <div style=\"width: 100%; max-width: 560px;\">
        <div style=\"position: relative; padding-bottom: 56.25%; height: \$height; width: \$width;\">
            <iframe style=\"position: absolute;  width: 100%;  height: 100%;  left: 0;  right: 0;  top: 0;  bottom: 0;\" width=\"1280\" height=\"720\" src=\"https&#58;//www.youtube.com/embed/\$id\" frameborder=\"0\" allowfullscreen></iframe>
        </div>
    </div>
    EOD;
    return \$str;
}
function plugin_youtube_inline() {
    \$args = func_get_args();
	return call_user_func_array('plugin_youtube_convert', \$args);
}
?>";
$temp = tmpfile();
file_put_contents($temp.$filename, $filedata);
header('Content-Type: application/octet-stream');
header("Content-Disposition: attachment; filename=youtube.inc.php"); 
readfile($temp.$filename);
unlink($temp.$filename);
?>