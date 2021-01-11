<?php
// $Id: tex2.inc.php,v 1.00 2020/12/11 17:34:31 K Exp $
/** 
* @link http://pkom.ml/?%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/tex2.inc.php
* @author K
* @license http://www.gnu.org/licenses/gpl.ja.html GPL
*/

define("PLUGIN_TEX2_URL","https://chart.googleapis.com/chart?cht=tx&chf=bg,s,00000000&chl=($1)"); //TeXのURL (「($1)」にURLエンコードされた文字列が挿入される。)
define("PLUGIN_TEX2_INIT","%5Csqrt%7B123%2Bx%7D"); //引数の初期状態
define("PLUGIN_TEX2_CACHE",false); //画像をサーバーへ保存
define("PLUGIN_TEX2_DIR","tex2"); //PLUGIN_TEX2_CACHEがtrueの場合の保存場所

function plugin_tex2_convert()
{
    $args = func_get_args();
	return call_user_func_array('plugin_tex2_inline', $args) . "<br />";
}

function plugin_tex2_inline()
{
    $lastarg = func_get_arg(func_num_args() - 1);
    $lastarg = preg_replace("/(\r|\n|\r\n)/","\\\\\\\\",$lastarg); //改行を改行として扱う
    if($lastarg){
        $texstring = urlencode($lastarg);
    }else{
        $texstring = PLUGIN_TEX2_INIT;
    }
    
    $url = str_replace("($1)",$texstring,PLUGIN_TEX2_URL);
    if(PLUGIN_TEX2_CACHE == true){
        mkdir(PLUGIN_TEX2_DIR, 0777);
        $hex = bin2hex($lastarg);
        $tex2url = PLUGIN_TEX2_DIR . '/'.$hex.'.png';
        if(!file_exists(PLUGIN_TEX2_DIR."/.htaccess")){
            file_put_contents(PLUGIN_TEX2_DIR."/.htaccess",'Require all granted');
        }
        if(!file_exists($tex2url)){
            file_put_contents($tex2url, file_get_contents($url));
        }
        $outhtml = <<<EOD
        <img src="{$tex2url}" />
        EOD;
    }elseif(PLUGIN_TEX2_CACHE == false){
        $outhtml = <<<EOD
        <img src="{$url}" />
        EOD;
    }
    
    return $outhtml;
}