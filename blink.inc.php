<?php
// $Id: blink.inc.php,v 1.1 2021/01/23 23:15:50 K Exp $

/** 
* @link http://pkom.ml/?プラグイン/blink.inc.php
* @author K
* @license http://www.gnu.org/licenses/gpl.ja.html GPL
*/

define('PLUGIN_BLINK_USAGE', '#blink(page,url,...)');

define('PLUGIN_BLINK_MAXEXECUTE', 150); // 0 = 無制限

define('THUMB_DIR', "thumbnail/");

define('PLUGIN_BLINK_FAVICON_API', "https://www.google.com/s2/favicons?domain=%URL%");

define('PLUGIN_BLINK_PAGESPEEDONLINE_API', "https://www.googleapis.com/pagespeedonline/v5/runPagespeed?screenshot=true&strategy=desktop&url=%URL%");

function plugin_blink_convert()
{
	global $vars, $head_tags, $plugin_blogplug;
    static $execute = 1;
    if(!$plugin_blogplug == true){
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
	if (func_num_args()){
		$args = func_get_args();
        if(!isset($args[0])){
			return PLUGIN_BLINK_USAGE . '<br />';
		}
	}else{
        return PLUGIN_BLINK_USAGE . '<br />';
    }
    $plugin_blinks = "";
    foreach($args as $page){
        if(PLUGIN_BLINK_MAXEXECUTE == $execute){
            $max_execute = true;
        }
        if($max_execute == true){
            return "最大処理数設定値(PLUGIN_BLINK_MAXEXECUTE)は" . PLUGIN_BLINK_MAXEXECUTE . "です。";
        }
        ++$execute;
        // URL or Page
        if(preg_match("/https?:\/{2}.*/u", $page)){
            // URL
            // ソースコードの取得
            $_source = file_get_contents($page);
            if(preg_match('/<title>(.*?)<\/title>/is', $_source, $matches)){
                // 取得したタイトルをページ名とする。
                $s_page = htmlsc($matches[1]);
            }else{
                // URLをそのままページ名とする。
                $s_page = htmlsc($page);
            }
            // WebAPIによるファビコンのURL
            $_pagefavicon_path = str_replace("%URL%", $page, PLUGIN_BLINK_FAVICON_API);
            // サムネイルの取得
            if(!file_exists(THUMB_DIR . strtoupper(bin2hex($page)) . ".png")){
                $noimage_attach = '&nbsp;<a id="plugin_blink_inlink" class="thumbnail_attach" href="?cmd=blink&do=attach&page=' . $page . '">[サムネイル添付]</a>';
                if(file_exists(THUMB_DIR . "noimage.png")){
                    $_pageimage_path = THUMB_DIR . "noimage.png";
                }else{
                    $_pageimage_path = IMAGE_DIR . "noimage.png";
                }
            }else{
                $_pageimage_path = THUMB_DIR . strtoupper(bin2hex($page)) . ".png";
                $noimage_attach = '';
            }
            // ソースの切り取り
            $_source = plugin_blink_html_drop($_source);
            $_partsource = htmlsc(mb_substr($_source, 0, 140));
            $plugin_blinks .= '<div id="plugin_blink"><a id="plugin_blink_link" href="' . $page . '"></a><div id="plugin_blink_body"><h4>' . $s_page . '</h4><p><img style="width:16px;height:16px;" src="' . $_pagefavicon_path . '" /> <strong>' . $page . '</strong>' . $noimage_attach . '</p><div id="image"><img src="' . $_pageimage_path . '" /></div><p id="short_plugin_blinkcontents">' . $_partsource . '</p></div></div>';
        }else{
            // Page
            $s_page = htmlsc($page);
            if(!is_page($page)){
                if(file_exists(THUMB_DIR . "noimage.png")){
                    $_pageimage_path = THUMB_DIR . "noimage.png";
                }else{
                    $_pageimage_path = IMAGE_DIR . "noimage.png";
                }
                $plugin_blinks .= '<div id="plugin_blink"><a id="plugin_blink_link" href="?cmd=edit&page=' . $page . '"></a><div id="plugin_blink_body"><h4>「' . $s_page . '」は存在しません</h4><p><strong>⧗ --------</strong></p><div id="image"><img src="' . $_pageimage_path . '" /></div><p id="short_plugin_blinkcontents">パネルをクリックすると「' . $s_page . '」というページを新規作成して編集できます。</p></div></div>';
                continue;
            }
            if(!file_exists(THUMB_DIR . strtoupper(bin2hex($page)) . ".png")){
                $noimage_attach = '&nbsp;<a id="plugin_blink_inlink" class="thumbnail_attach" href="?cmd=blink&do=attach&page=' . $page . '">[サムネイル添付]</a>';
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
            $_source = plugin_blink_drop($_source);
            $_partsource = htmlsc(mb_substr(implode($_source),0 ,140));
            $pageurl = get_page_uri($page);
            $date = date("Y-m-d", filemtime(DATA_DIR . strtoupper(bin2hex($page)) . ".txt"));
            $plugin_blinks .= '<div id="plugin_blink"><a id="plugin_blink_link" href="' . $pageurl . '"></a><div id="plugin_blink_body"><h4>' . $s_page . '</h4><p><strong>⧗ ' . $date . '</strong>'. $noimage_attach .'</p><div id="image"><img src="' . $_pageimage_path . '" /></div><p id="short_plugin_blinkcontents">' . $_partsource . '</p></div></div>';
        }
    }
	return '<div id="plugin_blinks">' . $plugin_blinks . "</div>";
}

function plugin_blink_html_drop($source){
    $source = preg_replace('/<title.*?>.*?<\/title>/u', '', $source);
    $source = preg_replace('/<script .*?>.*?<\/script>/u', '', $source);
    $source = preg_replace('/<link .*?>.*?<\/link>/u', '', $source);
    $source = preg_replace('/<meta .*?>.*?<\/meta>/u', '', $source);
    $source = preg_replace('/<style .*?>.*?<\/style>/u', '', $source);
    $source = preg_replace('/<.*?>/u', '', $source);
    return $source;
}

function plugin_blink_drop($source){
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

function plugin_blink_action(){
    global $vars;
    if($vars['do'] == "attach"){
        $msg = "「" . $vars['page'] . "」のサムネイル添付";
        $body = <<<EOD
        <h2>「{$vars['page']}」のサムネイルの添付</h2>
        <form method="post" enctype="multipart/form-data" action="./">
            <input type="hidden" name="plugin" value="blink" />
            <input type="hidden" name="do" value="upload" />
            <input type="hidden" name="page" value="{$vars['page']}" />
            <input type="radio" name="select_mode" id="select_mode1" value="upload" checked /><label for="select_mode1">サムネイル画像:<input type="file" name="uploadfile" id="uploadfile" /></label>
            <br />
            <input type="radio" name="select_mode" id="select_mode2" value="screenshot" /><label for="select_mode2">ページ・サイトのスクリーンショットを生成してサムネイルにする。</label>
            <br /><br />
            管理者パスワード:<input type="password" name="adminpass">&nbsp;
            <input type="submit" value="アップロード" />
        </form>
        EOD;
    }elseif($vars['do'] == "upload"){
        if(!pkwk_login($vars["adminpass"])){
            $body = <<<EOD
            <h2>ログインに失敗しました。</h2>
            <form method="post" enctype="multipart/form-data" action="./">
                <input type="hidden" name="plugin" value="blink" />
                <input type="hidden" name="do" value="upload" />
                <input type="hidden" name="page" value="{$vars['page']}" />
                <input type="radio" name="select_mode" id="select_mode1" value="upload" checked /><label for="select_mode1">サムネイル画像:<input type="file" name="uploadfile" id="uploadfile" /></label>
                <br />
                <input type="radio" name="select_mode" id="select_mode2" value="screenshot" /><label for="select_mode2">ページ・サイトのスクリーンショットを生成してサムネイルにする。</label>
                <br /><br />
                管理者パスワード:<input type="password" name="adminpass">&nbsp;
                <input type="submit" value="アップロード" />
            </form>
            EOD;
            return array('body' => $body, 'msg' => "ログインに失敗しました。");
        }
        if(!file_exists(THUMB_DIR)){
            mkdir(THUMB_DIR);
        }
        if($vars['select_mode'] == "upload"){
            if(is_uploaded_file($_FILES["uploadfile"]["tmp_name"])){
                $pathinfo = pathinfo($_FILES["uploadfile"]["name"]);
                if(strtolower($pathinfo["extension"]) == "png"){
                    move_uploaded_file($_FILES["uploadfile"]["tmp_name"], THUMB_DIR . strtoupper(bin2hex($vars['page'])) . ".png");
                }else{
                    $image = @imagecreatefromstring(file_get_contents($_FILES["uploadfile"]["tmp_name"]));
                    imagepng($image, THUMB_DIR . strtoupper(bin2hex($vars['page'])) . ".png");
                    imagedestroy($image);
                }
            }
        }elseif($vars['select_mode'] == "screenshot"){
            if(preg_match("/https?:\/{2}.*/u", $vars['page'])){
                $pageurl = $vars['page'];
            }else{
                $pageurl = get_script_uri() . "?" . $vars['page'];
            }
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, str_replace("%URL%", $pageurl, PLUGIN_BLINK_PAGESPEEDONLINE_API));
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_TIMEOUT, 300);
            $jsonsource = curl_exec($curl);
            curl_close($curl);
            $json_array = json_decode($jsonsource, true);
            $base64image = $json_array['lighthouseResult']['audits']['final-screenshot']['details']['data'];
            $base64image = preg_replace('/data:image\/(jpeg|png);base64,/', '', $base64image);
            $imagedata = base64_decode($base64image);
            $image = @imagecreatefromstring($imagedata);
            imagepng($image, THUMB_DIR . strtoupper(bin2hex($vars['page'])) . ".png");
            imagedestroy($image);
        }
    }
    return array('body' => $body, 'msg' => $msg);
}