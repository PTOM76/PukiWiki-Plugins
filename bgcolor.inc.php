<?php
$filedata="<?php
function plugin_bgcolor_convert(){
    \$args = func_get_args();
    \$bg_r = hexdec(substr(\$args[0], 0, 2));
    \$bg_g = hexdec(substr(\$args[0], 2, 2));
    \$bg_b = hexdec(substr(\$args[0], 4, 2));
    \$bg_a = hexdec(substr(\$args[0], 6, 2));
    \$font_r = hexdec(substr(\$args[1], 0, 2));
    \$font_g = hexdec(substr(\$args[1], 2, 2));
    \$font_b = hexdec(substr(\$args[1], 4, 2));
    \$font_a = hexdec(substr(\$args[1], 6, 2));
    if (empty(substr(\$args[0], 6, 2))){
        \$bg_a = \"1\";
    }else{
        \$bg_a = \$bg_a / 255;
    }
    if (empty(substr(\$args[1], 6, 2))){
        \$font_a = \"1\";
    }else{
        \$font_a = \$font_a / 255;
    }
    if (\$args == null){
        \$string = <<<EOD
        <p>使い方が正しくありません。RGBA:&#35;bgcolor(000000ff（背景色）,ffffffff（文字色）) RGB:&#35;bgcolor(000000（背景色）,ffffff（文字色）)</p>
        EOD;
    }else{
    \$string = <<<EOD
    <style>
    body{
        background-color:rgba(\$bg_r, \$bg_g, \$bg_b, \$bg_a);
        color:rgba(\$font_r, \$font_g, \$font_b, \$font_a);
        }
    </style>
    EOD;
    }
  	return \$string;
}
?>";
$temp = tmpfile();
file_put_contents($temp.$filename, $filedata);
header('Content-Type: application/octet-stream');
header("Content-Disposition: attachment; filename=bgcolor.inc.php"); 
readfile($temp.$filename);
unlink($temp.$filename);
?>