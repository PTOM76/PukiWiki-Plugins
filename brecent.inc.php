<?php
// $Id: brecent.inc.php,v 1.0 2021/01/13 16:53:34 K Exp $

/** 
* @link http://pkom.ml/?プラグイン/brecent.inc.php
* @author K
* @license http://www.gnu.org/licenses/gpl.ja.html GPL
*/

define('PLUGIN_BRECENT_DEFAULT_LINES', 15);

// Limit number of executions
define('PLUGIN_BRECENT_EXEC_LIMIT', 2); // N times per one output

// ----

define('PLUGIN_BRECENT_USAGE', '#brecent(number-to-show)');

// Place of the cache of 'RecentChanges'
define('PLUGIN_BRECENT_CACHE', CACHE_DIR . 'recent.dat');

define('THUMB_DIR', "thumbnail/");
define('PLUGIN_BRECENT_HIDE_PAGES', "RecentDeleted,InterWikiName,AutoAliasName,RecentChanges");
function plugin_brecent_convert()
{
	global $vars, $date_format, $head_tags, $plugin_blogplug;
	static $exec_count = 1;
    if (!$plugin_blogplug == true){
        $plugin_blogplug = true;
$head_tags[] = "​<style>
div#plugin_blinks {
    padding:0 10px 0 10px;
    margin:auto;
    display:-webkit-flex;
    display:-ms-flex;
    display:flex;
    flex-wrap:wrap;
    -webkit-flex-wrap:wrap;
    -ms-flex-wrap:wrap;
    width:100%;
    flex:2;
    -webkit-flex:2;
    -ms-flex:2;
}

div#plugin_blink {
    background-color:rgb(250, 250, 250);
    width:320px;
    height:250px;
    min-width:50px;
    border:solid rgb(150, 150, 150) 1px;
    border-radius:5px;
    margin:10px 10px 10px 10px;
    position:relative;
    z-index:1;
    flex-grow:1;
    -webkit-flex-grow:1;
    -ms-flex-grow:1;
    transition-duration:0.3s;
}

div#plugin_blink a#plugin_blink_link {
    opacity:0;
    position:absolute;
    top:0;
    left:0;
    width:100%;
    height:100%;
    text-indent:-999px;
    z-index:2;
}

div#plugin_blink:hover {
    opacity:0.5;
    transform:scale(1.05);
}

div#plugin_blink_body {
    margin:5px 5px 0 5px;
}

div#plugin_blink_body div#image {
    float:left;
    margin:0 15px 15px 0;
}

div#plugin_blink_body img {
    width:150px;
    height:150px;
    max-width:190px;
    max-height:190px;
    min-width:20px;
    min-height:20px;
}

div#plugin_blink_body p#short_plugin_blinkcontents {
    margin-top:-15px;
    line-height:1.6em;
    font-size:12px;
    color:rgb(50, 50, 50);
}

div#plugin_blink a#plugin_blink_inlink {
    position:relative;
    z-index:3;
}
div#plugin_blink a.thumbnail_attach {
    font-size:10px
}
</style>";
    }
	$brecent_lines = PLUGIN_BRECENT_DEFAULT_LINES;
	if (func_num_args()) {
		$args = func_get_args();
		if (! is_numeric($args[0]) || isset($args[1])) {
			return PLUGIN_BRECENT_USAGE . '<br />';
		} else {
			$brecent_lines = $args[0];
		}
	}

	// Show only N times
	if ($exec_count > PLUGIN_BRECENT_EXEC_LIMIT) {
		return '#brecent(): You called me too much' . '<br />' . "\n";
	} else {
		++$exec_count;
	}

	if (! file_exists(PLUGIN_BRECENT_CACHE)) {
		put_lastmodified();
		if (! file_exists(PLUGIN_BRECENT_CACHE)) {
			return '#brecent(): Cache file of RecentChanges not found' . '<br />';
		}
	}

	// Get latest N changes
    $hidepages = explode(",", PLUGIN_BRECENT_HIDE_PAGES);
	$lines = file_head(PLUGIN_BRECENT_CACHE, $brecent_lines + count($hidepages));
	if($lines == FALSE) return '#brecent(): File can not open' . '<br />' . "\n";
	$date = $plugin_brecents = '';
    $recent_count = 0;
	foreach ($lines as $line) {
        $hide_true = false;
		list($time, $page) = explode("\t", rtrim($line));
        foreach($hidepages as $hidepage){
            if($page == $hidepage){
                $hide_true = true;
                break;
            }
        }
        if($hide_true == true){
            continue;
        }else{
            ++$recent_count;
        }
		$_date = get_date($date_format, $time);
		if ($date != $_date) {
			$date = $_date;
		}
		$s_page = htmlsc($page);
        if(!file_exists(THUMB_DIR . strtoupper(bin2hex($page)) . ".png")){
            $noimage_attach = '&nbsp;<a id="plugin_blink_inlink" class="thumbnail_attach" href="?cmd=brecent&do=attach&page=' . $page . '">[サムネイル添付]</a>';
            if(file_exists(THUMB_DIR . "noimage.png")){
                $_pageimage_path = THUMB_DIR . "noimage.png";
            }else{
                $_pageimage_path = IMAGE_DIR . "noimage.png";
            }
        }else{
            $_pageimage_path = THUMB_DIR . strtoupper(bin2hex($page)) . ".png";
            $noimage_attach = '';
        }
        $_source = get_source($page);
        $_source = plugin_brecent_drop($_source);
        $_partsource = mb_substr(implode($_source),0 ,140);
        $pageurl = get_page_uri($page);
        $plugin_brecents .= '<div id="plugin_blink"><a id="plugin_blink_link" href="' . $pageurl . '"></a><div id="plugin_blink_body"><h4>' . $s_page . '</h4><p><strong>⧗ ' . $date . '</strong>'. $noimage_attach .'</p><div id="image"><img src="' . $_pageimage_path . '" /></div><p id="short_plugin_blinkcontents">' . $_partsource . '</p></div></div>';
        if($recent_count == $brecent_lines){
            break;
        }
	}
	return '<div id="plugin_blinks">' . $plugin_brecents . "</div>";
}
function plugin_brecent_drop($source){
    $source = preg_replace('/^\#(.*?)$/u', '', $source);
    $source = preg_replace('/&(.*?)\{(.*?)\};/u', '$2', $source);
    $source = preg_replace('/&(.*?);/u', '', $source);
    $source = preg_replace('/^\|#(.*?)\|$/u', '', $source);
    $source = preg_replace('/^\*\*\*(.*?)$/u', '', $source);
    $source = preg_replace('/^\*\*(.*?)$/u', '', $source);
    $source = preg_replace('/^\*(.*?)$/u', '', $source);
    $source = preg_replace('/^---(.*?)$/u', '$1', $source);
    $source = preg_replace('/^--(.*?)$/u', '$1', $source);
    $source = preg_replace('/^-(.*?)$/u', '$1', $source);
    $source = preg_replace('/\[\[(.*?)>(.*?)\]\]/u', '$1', $source);
    $source = preg_replace('/\[\[(.*?)\]\]/u', '$1', $source);
    $source = preg_replace('/^\|(.*?)\|$/u', '', $source);
    $source = preg_replace('/^\|(.*?)\|h$/u', '', $source);
    $source = preg_replace('/^\|(.*?)\|c$/u', '', $source);
    $source = preg_replace('/^\|(.*?)\|f$/u', '', $source);
    $source = preg_replace('/\'\'\'(.*?)\'\'\'/u', '$1', $source);
    $source = preg_replace('/\'\'(.*?)\'\'/u', '$1', $source);
    $source = preg_replace('/%%%(.*?)%%%/u', '$1', $source);
    $source = preg_replace('/%%(.*?)%%/u', '$1', $source);
    $source = preg_replace('/\/\/(.*?)/u', '', $source);
    return $source;
}

function plugin_brecent_action(){
    global $vars;
    if($vars['do'] == "attach"){
        $msg = "「" . $vars['page'] . "」のサムネイル添付";
        $body = <<<EOD
        <h2>「{$vars['page']}」のサムネイルの添付</h2>
        <form method="post" enctype="multipart/form-data" action="./">
            <input type="hidden" name="plugin" value="brecent">
            <input type="hidden" name="do" value="upload">
            <input type="hidden" name="page" value="{$vars['page']}">
            サムネイル:<input type="file" name="uploadfile"><br /><br />
            管理者パスワード:<input type="password" name="adminpass">&nbsp;
            <input type="submit" value="アップロード" />
        </form>
        EOD;
    }elseif($vars['do'] == "upload"){
        if(!pkwk_login($vars["adminpass"])){
            $body = <<<EOD
            <h2>ログインに失敗しました。</h2>
            <form method="post" enctype="multipart/form-data" action="./">
                <input type="hidden" name="plugin" value="brecent">
                <input type="hidden" name="do" value="upload">
                <input type="hidden" name="page" value="{$vars['page']}">
                サムネイル:<input type="file" name="uploadfile"><br /><br />
                管理者パスワード:<input type="password" name="adminpass">&nbsp;
                <input type="submit" value="アップロード" />
            </form>
            EOD;
            return array('body' => $body, 'msg' => "ログインに失敗しました。");
        }
        if(!file_exists(THUMB_DIR)){
            mkdir(THUMB_DIR);
        }
        if(is_uploaded_file($_FILES["uploadfile"]["tmp_name"])){
            $pathinfo = pathinfo($_FILES["uploadfile"]["name"]);
            if(strtolower($pathinfo["extension"]) == "png"){
                move_uploaded_file($_FILES["uploadfile"]["tmp_name"], THUMB_DIR . strtoupper(bin2hex($vars['page'])) . ".png");
            }else{
                $image = @imagecreatefrompng($_FILES["uploadfile"]["tmp_name"]);
                imagepng($image, THUMB_DIR . strtoupper(bin2hex($vars['page'])) . ".png");
                imagedestroy($image);
            }
        }
    }
    return array('body' => $body, 'msg' => $msg);
}