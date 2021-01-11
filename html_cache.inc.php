<?php
// $Id: html_cache.inc.php,v 1.00 2020/11/07 22:45:00 K Exp $

/** 
* @link http://pkom.ml/?%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/html_cache.inc.php
* @author K
* @license http://www.gnu.org/licenses/gpl.ja.html GPL
*/

define("PLUGIN_HTML_CACHE_PLUGIN_FALSE","article,comment,include,memo,recent,versionlist,vote");//指定されたプラグインがページに使用されている場合はキャッシュしない。「,」で区切る
define("PLUGIN_HTML_CACHE_INLINE_PLUGIN_FALSE","");//指定されたインラインプラグインがページに使用されている場合はキャッシュしない。「,」で区切る
define("PLUGIN_HTML_CACHE_FALSE_PAGES","");//キャッシュしないページを指定「,」で区切る
define("PLUGIN_HTML_CACHE_FALSE_AUTO_LINK",true);//存在しないページのリンクがあればキャッシュしない。(trueで有効,falseで無効)
define("WIKI_DIR","wiki/");//wikiのディレクトリ

function plugin_html_cache_convert()
{
    $args_num = func_num_args();
    $args = func_get_args();
    if ($args_num >= 0 ){
        if ($args[0]=="false"){
            return "";
        }
    }
    return "";
}

function plugin_html_cache_action(){
    global $vars;
    plugin_html_cache_page_cache($vars['page']);
    header("Location: ./?".$vars['page']);
    exit;
}

function plugin_html_cache_page_cache($page)
{
    $page_source = get_source($page);
    $dir = "./".CACHE_DIR."html_cache";
    $html_cache_enable = true;
    $html_cache_enable2 = false;
    if (preg_match('/,'.preg_quote($page).',/u',",".PLUGIN_HTML_CACHE_FALSE_PAGES.",",$matches)){
        if (isset($matches)){
            $html_cache_enable = false;
        }
    }
    $false_plugins = array();
    $false_inlineplugins = array();
    $false_plugins = explode(',',PLUGIN_HTML_CACHE_PLUGIN_FALSE);
    $false_inlineplugins = explode(',',PLUGIN_HTML_CACHE_INLINE_PLUGIN_FALSE);
    foreach ($page_source as $v){
        if (preg_match('/^\#html_cache\(true(.*)\)/u',$v,$matches)){
            $html_cache_enable = true;
            $html_cache_enable2 = true;
        }
        if (preg_match('/^\#html_cache\(false(.*)\)/u',$v,$matches)){
            $html_cache_enable = false;
        }
        if ($false_plugins[0] != ""){
            foreach ($false_plugins as $v2){
                if (preg_match('/^\#'.$v2.'(.*)/u',$v,$matches)){
                    $html_cache_enable = false;
                }
            }
        }
        if ($false_inlineplugins[0] != ""){
            foreach ($false_inlineplugins as $v2){
                if (preg_match('/&'.$v2.'(.*);/u',$v,$matches)){
                    $html_cache_enable = false;
                }
            }
        }
        if (PLUGIN_HTML_CACHE_FALSE_AUTO_LINK == true){
            if (preg_match('/\[\[(.*?)>(.*?)\]\]/u',$v,$matches)){
                if (!preg_match('/https?:\/\/(.*)/u',$matches[2],$matches2)){
                    if (!file_exists(WIKI_DIR.strtoupper(bin2hex($matches[2])).".txt")){
                        $html_cache_enable = false;
                    }
                }
            }
            if (preg_match('/\[\[([^>]+)\]\]/u',$v,$matches)){
                if (!preg_match('/https?:\/\/(.*)/u',$matches[1],$matches2)){
                    if (!file_exists(WIKI_DIR.strtoupper(bin2hex($matches[1])).".txt")){
                        $html_cache_enable = false;
                    }
                }
            }
        }
    }
    if (($html_cache_enable == false)&&($html_cache_enable2 == false)){
        unlink($dir."/".strtoupper(bin2hex($page)).".html");
        return;
        exit;
    }
    $html = convert_html($page_source);
    if (!file_exists($dir)){
        mkdir($dir, 0777);
    }
    file_put_contents($dir."/".strtoupper(bin2hex($page)).".html",$html);
    return;
    exit;
}
?>
