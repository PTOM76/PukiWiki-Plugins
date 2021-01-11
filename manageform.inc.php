<?php
// $Id: manageform.inc.php,v 0.2 2020/11/29 12:00:00 K Exp $
 
/** 
* @link http://pkom.ml/?%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/manageform.inc.php
* @author K
* @license http://www.gnu.org/licenses/gpl.ja.html GPL
*/
 
function plugin_manageform_convert()
{
    $html = <<<EOD
    EOD;
    return $html;
}
function plugin_manageform_inline() {
    return call_user_func_array('plugin_manageform_convert', func_get_args());
}
function plugin_manageform_action()
{
    global $vars;
    session_start();
    $title = "PukiWikiManageForm";
    if (isset($vars['manageform_loginpass'])){
        if (pkwk_login($vars['manageform_loginpass'])){
            $_SESSION['manageform_password'] = $vars['manageform_loginpass'];
        }else{
            $title = "ログインに失敗しました。";
        }
    }
    if (!pkwk_login($_SESSION['manageform_password'])){
$html = <<<EOD
<h2>管理画面へログイン</h2>
<form method="post" action="?plugin=manageform">
    管理者パスワード:<input type="password" name="manageform_loginpass"></input> <input type="submit" value="ログイン" />
</form>
EOD;
        return array('msg'=>$title, 'body'=>$html);
    }
    if ($vars['do'] == "logout"){
        //管理画面からログアウト
        unset($_SESSION['manageform_password']);
        header("Location: ./?plugin=manageform");
    }
    if ($vars['do'] == "upload_plugin"){
	    foreach ($_FILES['upload_file']['tmp_name'] as $no => $tmp_name) {
    		$filename = './plugin/'.$_FILES['upload_file']['name'][$no];
            if (strtolower(pathinfo($filename, PATHINFO_EXTENSION)) == "php"){
                if (move_uploaded_file($tmp_name, $filename)) {
                   exit;
		        }else{
                    echo "アップロードに失敗しました。";
                }
            }
	    	
	    }
    }
    if ($vars['do'] == "design_setting_save"){
        file_put_contents("./".SKIN_DIR.$vars['css_filename'],$vars['pukiwiki_css']);
    }
    if ($vars['do'] == "save_skin_settings"){
        $filedata = file_get_contents("./".SKIN_DIR."pukiwiki.skin.php");
        if (isset($filedata)){
            if (isset($vars['skin_css_set'])){
                $filedata = preg_replace('/^(?!.*\/\/).*<link rel="stylesheet" type="text\/css" href="<\?php echo SKIN_DIR \?>(.*?)" \/>/um', ' <link rel="stylesheet" type="text/css" href="<?php echo SKIN_DIR ?>'.$_POST['skin_css_set'].'" />', $filedata);
            }
            if (isset($vars['SKIN_DEFAULT_DISABLE_TOPICPATH'])){
                $filedata = preg_replace('/define\(\s*?\'SKIN_DEFAULT_DISABLE_TOPICPATH\'\s*?,\s*?.*?\s*?\);/u', "	define('SKIN_DEFAULT_DISABLE_TOPICPATH', ".$vars['SKIN_DEFAULT_DISABLE_TOPICPATH'].");", $filedata);
            }
            if (isset($vars['PKWK_SKIN_SHOW_TOOLBAR'])){
                $filedata = preg_replace('/define\(\s*?\'PKWK_SKIN_SHOW_TOOLBAR\'\s*?,\s*?.*?\s*?\);/u', "	define('PKWK_SKIN_SHOW_TOOLBAR', ".$vars['PKWK_SKIN_SHOW_TOOLBAR'].");", $filedata);
            }
            if (isset($vars['PKWK_SKIN_SHOW_NAVBAR'])){
                $filedata = preg_replace('/define\(\s*?\'PKWK_SKIN_SHOW_NAVBAR\'\s*?,\s*?.*?\s*?\);/u', "	define('PKWK_SKIN_SHOW_NAVBAR', ".$vars['PKWK_SKIN_SHOW_NAVBAR'].");", $filedata);
            }
            file_put_contents("./".SKIN_DIR."pukiwiki.skin.php",$filedata);
        }
    }
    if ($vars['do'] == "save_settings"){
        $filedata = file_get_contents("./pukiwiki.ini.php");
        if (isset($filedata)){
            if (isset($vars['wiki_title'])){
                $filedata = preg_replace('/^(?!.*\/\/).*\$page_title(\s*?)=(\s*?)\'(.*?)\';/um', preg_quote("$")."page_title = '".htmlsc($vars['wiki_title'])."';", $filedata);
            }
            if (isset($vars['LANG'])){
                $filedata = preg_replace('/define\((\s*?)\'LANG\'(\s*?),(\s*?)\'(.*?)\'(\s*?)\);/u', "define('LANG', '".$vars['LANG']."');", $filedata);
            }
            if (isset($vars['PKWK_OPTIMISE'])){
                $filedata = preg_replace('/define\((\s*?)\'PKWK_OPTIMISE\'(\s*?),(\s*?)(.*?)(\s*?)\);/u', "define('PKWK_OPTIMISE', ".$vars['PKWK_OPTIMISE'].");", $filedata);
            }
            if (isset($vars['PKWK_READONLY'])){
                $filedata = preg_replace('/define\((\s*?)\'PKWK_READONLY\'(\s*?),(\s*?)(.*?)(\s*?)\);/u', "define('PKWK_READONLY', ".$vars['PKWK_READONLY'].");", $filedata);
            }
            if (isset($vars['PKWK_SAFE_MODE'])){
                $filedata = preg_replace('/define\((\s*?)\'PKWK_SAFE_MODE\'(\s*?),(\s*?)(.*?)(\s*?)\);/u', "define('PKWK_SAFE_MODE', ".$vars['PKWK_SAFE_MODE'].");", $filedata);
            }
            if (isset($vars['PKWK_DISABLE_INLINE_IMAGE_FROM_URI'])){
                $filedata = preg_replace('/define\((\s*?)\'PKWK_DISABLE_INLINE_IMAGE_FROM_URI\'(\s*?),(\s*?)(.*?)(\s*?)\);/u', "define('PKWK_DISABLE_INLINE_IMAGE_FROM_URI', ".$vars['PKWK_DISABLE_INLINE_IMAGE_FROM_URI'].");", $filedata);
            }
            if (isset($vars['PKWK_QUERY_STRING_MAX'])){
                $filedata = preg_replace('/define\((\s*?)\'PKWK_QUERY_STRING_MAX\'(\s*?),(\s*?)(.*?)(\s*?)\);/u', "define('PKWK_QUERY_STRING_MAX', ".$vars['PKWK_QUERY_STRING_MAX'].");", $filedata);
            }
            if (isset($vars['PKWKEXP_DISABLE_MULTILINE_PLUGIN_HACK'])){
                $filedata = preg_replace('/define\((\s*?)\'PKWK_QUERY_PKWKEXP_DISABLE_MULTILINE_PLUGIN_HACKSTRING_MAX\'(\s*?),(\s*?)(.*?)(\s*?)\);/u', "define('PKWKEXP_DISABLE_MULTILINE_PLUGIN_HACK', ".$vars['PKWKEXP_DISABLE_MULTILINE_PLUGIN_HACK'].");", $filedata);
            }
            if (isset($vars['PKWK_ALLOW_JAVASCRIPT'])){
                $filedata = preg_replace('/define\((\s*?)\'PKWK_ALLOW_JAVASCRIPT\'(\s*?),(\s*?)(.*?)(\s*?)\);/u', "define('PKWK_ALLOW_JAVASCRIPT', ".$vars['PKWK_ALLOW_JAVASCRIPT'].");", $filedata);
            }
            if (isset($vars['nofollow'])){
                $filedata = preg_replace('/\$nofollow(\s*?)=(\s*?)(.*?);/u', preg_quote("$")."nofollow = ".htmlsc($vars['nofollow']).";", $filedata);
            }
            if (isset($vars['nowikiname'])){
                $filedata = preg_replace('/\$nowikiname(\s*?)=(\s*?)(.*?);/u', preg_quote("$")."nowikiname = ".htmlsc($vars['nowikiname']).";", $filedata);
            }
            if (isset($vars['autolink'])){
                $filedata = preg_replace('/\$autolink(\s*?)=(\s*?)(.*?);/u', preg_quote("$")."autolink = ".htmlsc($vars['autolink']).";", $filedata);
            }
            if (isset($vars['autoalias'])){
                $filedata = preg_replace('/\$autoalias(\s*?)=(\s*?)(.*?);/u', preg_quote("$")."autoalias = ".htmlsc($vars['autoalias']).";", $filedata);
            }
            if (isset($vars['autoalias_max_words'])){
                $filedata = preg_replace('/\$autoalias_max_words(\s*?)=(\s*?)(.*?);/u', preg_quote("$")."autoalias_max_words = ".htmlsc($vars['autoalias_max_words']).";", $filedata);
            }
            if (isset($vars['function_freeze'])){
                $filedata = preg_replace('/\$function_freeze(\s*?)=(\s*?)(.*?);/u', preg_quote("$")."function_freeze = ".htmlsc($vars['function_freeze']).";", $filedata);
            }
            if (isset($vars['notimeupdate'])){
                $filedata = preg_replace('/\$notimeupdate(\s*?)=(\s*?)(.*?);/u', preg_quote("$")."notimeupdate = ".htmlsc($vars['notimeupdate']).";", $filedata);
            }
            if (isset($vars['line_break'])){
                $filedata = preg_replace('/\$line_break(\s*?)=(\s*?)(.*?);/u', preg_quote("$")."line_break = ".htmlsc($vars['line_break']).";", $filedata);
            }
            if (isset($vars['modifier'])){
                $filedata = preg_replace('/\$modifier(\s*?)=(\s*?)\'(.*?)\';/u', preg_quote("$")."modifier = '".htmlsc($vars['modifier'])."';", $filedata);
            }
            if (isset($vars['modifierlink'])){
                $filedata = preg_replace('/\$modifierlink(\s*?)=(\s*?)\'(.*?)\';/u', preg_quote("$")."modifierlink = '".htmlsc($vars['modifierlink'])."';", $filedata);
            }
            if (isset($vars['defaultpage'])){
                $filedata = preg_replace('/\$defaultpage(\s*?)=(\s*?)\'(.*?)\';/u', preg_quote("$")."defaultpage  = '".htmlsc($vars['defaultpage'])."';", $filedata);
            }
            if (isset($vars['whatsnew'])){
                $filedata = preg_replace('/\$whatsnew(\s*?)=(\s*?)\'(.*?)\';/u', preg_quote("$")."whatsnew     = '".htmlsc($vars['whatsnew'])."';", $filedata);
            }
            if (isset($vars['whatsdeleted'])){
                $filedata = preg_replace('/\$whatsdeleted(\s*?)=(\s*?)\'(.*?)\';/u', preg_quote("$")."whatsdeleted = '".htmlsc($vars['whatsdeleted'])."';", $filedata);
            }
            if (isset($vars['interwiki'])){
                $filedata = preg_replace('/\$interwiki(\s*?)=(\s*?)\'(.*?)\';/u', preg_quote("$")."interwiki    = '".htmlsc($vars['interwiki'])."';", $filedata);
            }
            if (isset($vars['aliaspage'])){
                $filedata = preg_replace('/\$aliaspage(\s*?)=(\s*?)\'(.*?)\';/u', preg_quote("$")."aliaspage    = '".htmlsc($vars['aliaspage'])."';", $filedata);
            }
            if (isset($vars['menubar'])){
                $filedata = preg_replace('/\$menubar(\s*?)=(\s*?)\'(.*?)\';/u', preg_quote("$")."menubar      = '".htmlsc($vars['menubar'])."';", $filedata);
            }
            if (isset($vars['rightbar_name'])){
                $filedata = preg_replace('/\$rightbar_name(\s*?)=(\s*?)\'(.*?)\';/um', preg_quote("$")."rightbar_name = '".htmlsc($vars['rightbar_name'])."';", $filedata);
            }
            if ((isset($vars['newadminpass'])) && (isset($vars['check_newadminpass'])) && (isset($vars['adminpass'])) && (isset($vars['adminpass_type']))){
                if (($vars['newadminpass'] == $vars['check_newadminpass']) && (pkwk_login($vars["adminpass"]) == true)){
                    $newadminpass = $vars['newadminpass'];
                    if($vars['adminpass_type'] == 'x-php-md5'){
                        $newadminpass = md5($newadminpass);
                    }elseif($vars['adminpass_type'] == 'x-php-sha256'){
                        $newadminpass = hash('sha256', $newadminpass);
                    }elseif($vars['adminpass_type'] == 'x-php-sha1'){
                        $newadminpass = sha1($newadminpass);
                    }elseif($vars['adminpass_type'] == 'x-php-crypt'){
                        $newadminpass = crypt($newadminpass);
                    }elseif($vars['adminpass_type'] == 'x-php-sha384'){
                        $newadminpass = hash('sha384', $newadminpass);
                    }elseif($vars['adminpass_type'] == 'x-php-sha512'){
                        $newadminpass = hash('sha512', $newadminpass);
                    }elseif($vars['adminpass_type'] == 'MD5'){
                        $newadminpass = base64_encode(pkwk_hex2bin(md5($newadminpass)));
                    }elseif($vars['adminpass_type'] == 'SMD5'){
                        $newadminpass = base64_encode(pkwk_hex2bin(md5($newadminpass . substr(base64_decode($newadminpass), 16))) . substr(base64_decode($newadminpass), 16));
                    }elseif($vars['adminpass_type'] == 'SHA'){
                        $newadminpass = base64_encode(pkwk_hex2bin(sha1($newadminpass)));
                    }elseif($vars['adminpass_type'] == 'SSHA'){
                        $newadminpass = base64_encode(pkwk_hex2bin(sha1($newadminpass . substr(base64_decode($newadminpass), 16))) . substr(base64_decode($newadminpass), 16));
                    }elseif($vars['adminpass_type'] == 'CRYPT'){
                        $newadminpass = crypt($newadminpass);
                    }elseif($vars['adminpass_type'] == 'SSHA256'){
                        $newadminpass = base64_encode(hash('sha256', $newadminpass . substr(base64_decode($newadminpass), 32)) . substr(base64_decode($newadminpass), 32));
                    }elseif($vars['adminpass_type'] == 'SSHA384'){
                        $newadminpass = base64_encode(hash('sha384', $newadminpass . substr(base64_decode($newadminpass), 48)) . substr(base64_decode($newadminpass), 48));
                    }elseif($vars['adminpass_type'] == 'SHA256'){
                        $newadminpass = base64_encode(hash('sha256', $newadminpass));
                    }elseif($vars['adminpass_type'] == 'SHA384'){
                        $newadminpass = base64_encode(hash('sha384', $newadminpass));
                    }elseif($vars['adminpass_type'] == 'SHA512'){
                        $newadminpass = base64_encode(hash('sha512', $newadminpass));
                    }elseif($vars['adminpass_type'] == 'SSHA512'){
                        $newadminpass = base64_encode(hash('sha512', $newadminpass . substr(base64_decode($newadminpass), 64)) . substr(base64_decode($newadminpass), 64));
                    }
                    $filedata = preg_replace('/^(?!.*\/\/).*\$adminpass(\s*?)=(\s*?)\'(.*?)\';/u', preg_quote("$")."adminpass = '"."{".htmlsc($vars['adminpass_type'])."}".htmlsc($newadminpass)."';", $filedata);
                }
            }
            if (isset($vars['user_auth_username'])){
                preg_match('/\$auth_users\s*?=\s*?array\((.+?)\);/su',$filedata,$matches);
                $users = "";
                $count = 0;
                foreach ($_POST['user_auth_username'] as $value1) {
                    if ($value1 != ""){
                        $value2 = $_POST['user_auth_password'][$count];
                        if ($value2 != ""){
                            $value1 = htmlsc($value1);
                            $value1 = plugin_manageform_phpsecialchars($value1);
                            $value2 = htmlsc($value2);
                            $value2 = plugin_manageform_phpsecialchars($value2);
                            $users .= '\''.$value1.'\'=>\''.$value2.'\','."\n";
                        }
                    }
                    $count = $count + 1;
                }
                $filedata = str_replace($matches[0],'$auth_users = array('."\n".$users.');',$filedata);
            }
            if (isset($vars['read_auth'])){
                $filedata = preg_replace('/\$read_auth\s*?=\s*?(.*?);/u', preg_quote("$")."read_auth = ".htmlsc($vars['read_auth']).";", $filedata);
            }
            if (isset($vars['edit_auth'])){
                $filedata = preg_replace('/\$edit_auth\s*?=\s*?(.*?);/u', preg_quote("$")."edit_auth = ".htmlsc($vars['edit_auth']).";", $filedata);
            }
            if (isset($vars['search_auth'])){
                $filedata = preg_replace('/\$search_auth\s*?=\s*?(.*?);/u', preg_quote("$")."search_auth = ".htmlsc($vars['search_auth']).";", $filedata);
            }
            if (isset($vars['read_auth_username'])){
                preg_match('/\$read_auth_pages\s*?=\s*?array\((.+?)\);/su',$filedata,$matches);
                $pages = "";
                $count = 0;
                foreach ($_POST['read_auth_username'] as $value1) {
                    if ($value1 != ""){
                        $value2 = $_POST['read_auth_pages'][$count];
                        if ($value2 != ""){
                            $value1 = htmlsc($value1);
                            $value1 = plugin_manageform_phpsecialchars($value1);
                            $value2 = htmlsc($value2);
                            $value2 = plugin_manageform_phpsecialchars($value2);
                            $pages .= '\''.$value2.'\'=>\''.$value1.'\','."\n";
                        }
                    }
                    $count = $count + 1;
                }
                $filedata = str_replace($matches[0],'$read_auth_pages = array('."\n".$pages.');',$filedata);
            }
            if (isset($vars['edit_auth_username'])){
                preg_match('/\$edit_auth_pages\s*?=\s*?array\((.+?)\);/su',$filedata,$matches);
                $pages = "";
                $count = 0;
                foreach ($_POST['edit_auth_username'] as $value1) {
                    if ($value1 != ""){
                        $value2 = $_POST['edit_auth_pages'][$count];
                        if ($value2 != ""){
                            $value1 = htmlsc($value1);
                            $value1 = plugin_manageform_phpsecialchars($value1);
                            $value2 = htmlsc($value2);
                            $value2 = plugin_manageform_phpsecialchars($value2);
                            $pages .= '\''.$value2.'\'=>\''.$value1.'\','."\n";
                        }
                    }
                    $count = $count + 1;
                }
                $filedata = str_replace($matches[0],'$edit_auth_pages = array('."\n".$pages.');',$filedata);
            }
            file_put_contents("./pukiwiki.ini.php",$filedata);
        }
        exit;
    }
    global $page_title,$defaultpage,$menubar,$rightbar_name,$whatsdeleted,$whatsnew,$interwiki,$aliaspage,$modifier,$modifierlink,$nofollow,$nowikiname,$autolink,$autoalias,$autoalias_max_words,$function_freeze,$notimeupdate,$line_break,$read_auth,$read_auth_pages,$edit_auth,$edit_auth_pages,$search_auth,$auth_users,$auth_groups;
    header("X-Frame-Options: SAMEORIGIN"); 
//言語ファイル取得
$LANG_SELECT = "";
foreach(glob('./{*.lng.php}',GLOB_BRACE) as $file){
    if(is_file($file)){
        preg_match('/(.*?).lng.php/u', basename($file), $matches);
        if (LANG == $matches[1]){
            $LANG_SELECT .= '<option value="'.htmlsc($matches[1]).'" selected>'.htmlsc($matches[1]).'</option>';
        }else{
            $LANG_SELECT .= '<option value="'.htmlsc($matches[1]).'">'.htmlsc($matches[1]).'</option>';
        }
    }
}
$html_general_setting = <<<EOD
    <h3>基本設定</h3>
    <form class="manageform_form" action="" method="post" target="sendForm">
        <input type="hidden" value="manageform" name="plugin"></input>
        <input type="hidden" value="save_settings" name="do"></input>
        <table class="style_table" cellspacing="1" border="0" width="75%">
            <tbody>
                <tr>
                    <td class="style_td" width="20%">タイトル</td>
                    <td class="style_td" width="80%"><input type="text" value="{$page_title}" placeholder="〇〇〇攻略WIKI" name="wiki_title"></input></td>
                </tr>
                <tr>
                    <td class="style_td" width="20%">管理者の名前</td>
                    <td class="style_td" width="80%"><input type="text" value="{$modifier}" placeholder="anonymous" name="modifier"></input></td>
                </tr>
                <tr>
                    <td class="style_td" width="20%">管理者のサイト</td>
                    <td class="style_td" width="80%"><input type="text" value="{$modifierlink}" placeholder="https://www.example.com/" name="modifierlink"></input></td>
                </tr>
                <tr>
                    <td class="style_td" width="20%">言語</td>
                    <td class="style_td" width="80%">
                    <select name="LANG" size="1">
                        {$LANG_SELECT}
                    </select>
                    </td>
                </tr>
                <tr>
                    <td style="text-align:center" colspan="2" class="style_td">
                        <input class="form_button" type="submit" value="設定を保存する" name="general_setting_save" id="general_setting_save"></input>
                    </td>
                </tr>
            </tbody>
        </table>
    </form>
    <h3>管理者パスワードの変更</h3>
    <form class="manageform_form" action="" method="post" target="sendForm">
        <input type="hidden" value="manageform" name="plugin"></input>
        <input type="hidden" value="save_settings" name="do"></input>
        <table class="style_table" cellspacing="1" border="0" width="75%">
            <tbody>
                <tr>
                    <td class="style_td" width="20%">現在の管理者パスワード</td>
                    <td class="style_td" width="80%"><input type="password" value="" placeholder="現在のパスワード" name="adminpass"></input></td>
                </tr>
                <tr>
                    <td class="style_td" width="20%">新しい管理者パスワード</td>
                    <td class="style_td" width="80%"><input type="password" value="" placeholder="新しいパスワード" name="newadminpass"></input></td>
                </tr>
                <tr>
                    <td class="style_td" width="20%">新しい管理者パスワード(確認)</td>
                    <td class="style_td" width="80%"><input type="password" value="" placeholder="新しいパスワード" name="check_newadminpass"></input></td>
                </tr>
                <tr>
                    <td class="style_td" width="20%">暗号化の種類 <a href="https://pukiwiki.osdn.jp/dev/?BugTrack/709">[参照]</a></td>
                    <td class="style_td" width="80%">
                        <select name="adminpass_type" size="1">
                            <option value="CLEARTEXT">CLEARTEXT</option>
                            <option value="x-php-md5" selected>x-php-md5</option>
                            <option value="x-php-sha1">x-php-sha1</option>
                            <option value="x-php-sha256">x-php-sha256</option>
                            <option value="x-php-sha384">x-php-sha384</option>
                            <option value="x-php-sha512">x-php-sha512</option>
                            <option value="x-php-crypt">x-php-crypt</option>
                            <option value="CRYPT">CRYPT</option>
                            <option value="MD5">MD5</option>
                            <option value="SMD5">SMD5</option>
                            <option value="SHA">SHA</option>
                            <option value="SSHA">SSHA</option>
                            <option value="SHA256">SHA256</option>
                            <option value="SSHA256">SSHA256</option>
                            <option value="SHA384">SHA384</option>
                            <option value="SSHA384">SSHA384</option>
                            <option value="SHA512">SHA512</option>
                            <option value="SSHA512">SSHA512</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td style="text-align:center" colspan="2" class="style_td">
                        <input class="form_button" type="submit" value="パスワードを変更する" name="adminpass_setting_save" id="adminpass_setting_save"></input>
                    </td>
                </tr>
            </tbody>
        </table>
    </form>
EOD;
$PKWK_OPTIMISE_TRUE_SELECT = "";$PKWK_OPTIMISE_FALSE_SELECT = "";if (PKWK_OPTIMISE == 0){$PKWK_OPTIMISE_FALSE_SELECT = "selected";}elseif (PKWK_OPTIMISE == 1){$PKWK_OPTIMISE_TRUE_SELECT = "selected";}
$PKWK_READONLY_TRUE_SELECT = "";$PKWK_READONLY_FALSE_SELECT = "";if (PKWK_READONLY == 0){$PKWK_READONLY_FALSE_SELECT = "selected";}elseif (PKWK_READONLY == 1){$PKWK_READONLY_TRUE_SELECT = "selected";}
$PKWK_SAFE_MODE_TRUE_SELECT = "";$PKWK_SAFE_MODE_FALSE_SELECT = "";if (PKWK_SAFE_MODE == 0){$PKWK_SAFE_MODE_FALSE_SELECT = "selected";}elseif (PKWK_SAFE_MODE == 1){$PKWK_SAFE_MODE_TRUE_SELECT = "selected";}
$PKWK_DISABLE_INLINE_IMAGE_FROM_URI_TRUE_SELECT = "";$PKWK_DISABLE_INLINE_IMAGE_FROM_URI_FALSE_SELECT = "";if (PKWK_DISABLE_INLINE_IMAGE_FROM_URI == 1){$PKWK_DISABLE_INLINE_IMAGE_FROM_URI_FALSE_SELECT = "selected";}elseif (PKWK_DISABLE_INLINE_IMAGE_FROM_URI == 0){$PKWK_DISABLE_INLINE_IMAGE_FROM_URI_TRUE_SELECT = "selected";}
$PKWKEXP_DISABLE_MULTILINE_PLUGIN_HACK_TRUE_SELECT = "";$PKWKEXP_DISABLE_MULTILINE_PLUGIN_HACK_FALSE_SELECT = "";if (PKWKEXP_DISABLE_MULTILINE_PLUGIN_HACK == 1){$PKWKEXP_DISABLE_MULTILINE_PLUGIN_HACK_FALSE_SELECT = "selected";}elseif (PKWKEXP_DISABLE_MULTILINE_PLUGIN_HACK == 0){$PKWKEXP_DISABLE_MULTILINE_PLUGIN_HACK_TRUE_SELECT = "selected";}
$PKWK_ALLOW_JAVASCRIPT_TRUE_SELECT = "";$PKWK_ALLOW_JAVASCRIPT_FALSE_SELECT = "";if (PKWK_ALLOW_JAVASCRIPT == 0){$PKWK_ALLOW_JAVASCRIPT_FALSE_SELECT = "selected";}elseif (PKWK_ALLOW_JAVASCRIPT == 1){$PKWK_ALLOW_JAVASCRIPT_TRUE_SELECT = "selected";}
$nofollow_true_select = "";$nofollow_false_select = "";if ($nofollow == 1){$nofollow_false_select = "selected";}elseif ($nofollow == 0){$nofollow_true_select = "selected";}
$nowikiname_true_select = "";$nowikiname_false_select = "";if ($nowikiname == 1){$nowikiname_false_select = "selected";}elseif ($nowikiname == 0){$nowikiname_true_select = "selected";}
$function_freeze_true_select = "";$function_freeze_false_select = "";if ($function_freeze == 0){$function_freeze_false_select = "selected";}elseif ($function_freeze == 1){$function_freeze_true_select = "selected";}
$notimeupdate_disable_select = "";$notimeupdate_everyone_select = "";$notimeupdate_admin_select = "";
if ($notimeupdate == 0){$notimeupdate_disable_select = "selected";}elseif ($notimeupdate == 1){$notimeupdate_everyone_select = "selected";}elseif ($notimeupdate == 2){$notimeupdate_admin_select = "selected";}
$PKWK_QUERY_STRING_MAX = PKWK_QUERY_STRING_MAX;
$line_break_true_select = "";$line_break_false_select = "";if ($line_break == 0){$line_break_false_select = "selected";}elseif ($line_break == 1){$line_break_true_select = "selected";}

//1.5.3~ (AutoAlias)
if(isset($autoalias)){
    $autoaliasname_table = <<<EOD
                <tr>
                    <td class="style_td" width="20%">AutoAliasName(AutoAliasName)</td>
                    <td class="style_td" width="80%"><input type="text" value="{$aliaspage}" placeholder="AutoAliasName" name="aliaspage"></input></td>
                </tr>
    EOD;
    $autoalias_table = <<<EOD
                <tr>
                    <th style="text-align:center;height:20px;" colspan="2" class="style_th">AutoAlias</th>
                </tr>
                <tr>
                    <td class="style_td" width="20%">置換する単語の最小文字数(\$autoalias) (0でAutoAlias無効)</td>
                    <td class="style_td" width="80%"><input type="number" value="{$autoalias}" placeholder="0" name="autoalias"></input></td>
                </tr>
                <tr>
                    <td class="style_td" width="20%">置換する最大単語数(\$autoalias_max_words)</td>
                    <td class="style_td" width="80%"><input type="number" value="{$autoalias_max_words}" placeholder="50" name="autoalias_max_words"></input></td>
                </tr>
    EOD;
}else{
    $autoalias_table = "";
    $autoaliasname_table = "";
}
//----
$html_details_setting = <<<EOD
    <h3>詳細設定</h3>
    <form class="manageform_form" action="" method="post" target="sendForm">
        <input type="hidden" value="manageform" name="plugin"></input>
        <input type="hidden" value="save_settings" name="do"></input>
        <table class="style_table" cellspacing="1" border="0" width="75%">
            <tbody>
                <tr>
                    <th style="text-align:center;height:20px;" colspan="2" class="style_th">機能設定</th>
                </tr>
                <tr>
                    <td class="style_td" width="20%">最適化モード(PKWK_OPTIMISE)</td>
                    <td class="style_td" width="80%">
                    <select name="PKWK_OPTIMISE" size="1">
                        <option value="1" {$PKWK_OPTIMISE_TRUE_SELECT}>有効</option>
                        <option value="0" {$PKWK_OPTIMISE_FALSE_SELECT}>無効</option>
                    </select>
                    </td>
                </tr>
                <tr>
                    <th style="text-align:center;height:20px;" colspan="2" class="style_th">セキュリティ設定</th>
                </tr>
                <tr>
                    <td class="style_td" width="20%">読み取り専用(PKWK_READONLY)</td>
                    <td class="style_td" width="80%">
                    <select name="PKWK_READONLY" size="1">
                        <option value="1" {$PKWK_READONLY_TRUE_SELECT}>有効</option>
                        <option value="0" {$PKWK_READONLY_FALSE_SELECT}>無効</option>
                    </select>
                    </td>
                </tr>
                <tr>
                    <td class="style_td" width="20%">セーフモード(PKWK_SAFE_MODE)</td>
                    <td class="style_td" width="80%">
                    <select name="PKWK_SAFE_MODE" size="1">
                        <option value="1" {$PKWK_SAFE_MODE_TRUE_SELECT}>有効</option>
                        <option value="0" {$PKWK_SAFE_MODE_FALSE_SELECT}>無効</option>
                    </select>
                    </td>
                </tr>
                <tr>
                    <td class="style_td" width="20%">外部サイトの画像(PKWK_DISABLE_INLINE_IMAGE_FROM_URI)</td>
                    <td class="style_td" width="80%">
                    <select name="PKWK_DISABLE_INLINE_IMAGE_FROM_URI" size="1">
                        <option value="0" {$PKWK_DISABLE_INLINE_IMAGE_FROM_URI_TRUE_SELECT}>許可</option>
                        <option value="1" {$PKWK_DISABLE_INLINE_IMAGE_FROM_URI_FALSE_SELECT}>拒否</option>
                    </select>
                    </td>
                </tr>
                <tr>
                    <td class="style_td" width="20%">JavaScriptの実行(PKWK_ALLOW_JAVASCRIPT)</td>
                    <td class="style_td" width="80%">
                    <select name="PKWK_ALLOW_JAVASCRIPT" size="1">
                        <option value="1" {$PKWK_ALLOW_JAVASCRIPT_TRUE_SELECT}>許可</option>
                        <option value="0" {$PKWK_ALLOW_JAVASCRIPT_FALSE_SELECT}>拒否</option>
                    </select>
                    </td>
                </tr>
                <tr>
                    <td class="style_td" width="20%">凍結機能(\$function_freeze)</td>
                    <td class="style_td" width="80%">
                        <select name="function_freeze" size="1">
                            <option value="1" {$function_freeze_true_select}>有効</option>
                            <option value="0" {$function_freeze_false_select}>無効</option>
                    </select>
                    </td>
                </tr>
                <tr>
                    <td class="style_td" width="20%">「タイムスタンプを変更しない」の設定(\$notimeupdate)</td>
                    <td class="style_td" width="80%">
                        <select name="notimeupdate" size="1">
                            <option value="0" {$notimeupdate_disable_select}>無効(利用不可)</option>
                            <option value="1" {$notimeupdate_everyone_select}>誰でも利用可能</option>
                            <option value="2" {$notimeupdate_admin_select}>管理者のみ利用可能</option>
                    </select>
                    </td>
                </tr>
                <tr>
                    <td class="style_td" width="20%">クエリ文字列の文字数制限(PKWK_QUERY_STRING_MAX)</td>
                    <td class="style_td" width="80%"><input type="number" value="{$PKWK_QUERY_STRING_MAX}" placeholder="640" name="PKWK_QUERY_STRING_MAX"></input></td>
                </tr>    
                <tr>
                    <th style="text-align:center;height:20px;" colspan="2" class="style_th">その他設定</th>
                </tr>
                <tr>
                    <td class="style_td" width="20%">プラグインの複数行(PKWKEXP_DISABLE_MULTILINE_PLUGIN_HACK)</td>
                    <td class="style_td" width="80%">
                        <select name="PKWKEXP_DISABLE_MULTILINE_PLUGIN_HACK" size="1">
                            <option value="0" {$PKWKEXP_DISABLE_MULTILINE_PLUGIN_HACK_TRUE_SELECT}>許可</option>
                            <option value="1" {$PKWKEXP_DISABLE_MULTILINE_PLUGIN_HACK_FALSE_SELECT}>拒否</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="style_td" width="20%">検索エンジンのインデックス化(nofollow)</td>
                    <td class="style_td" width="80%">
                        <select name="nofollow" size="1">
                            <option value="0" {$nofollow_true_select}>許可</option>
                            <option value="1" {$nofollow_false_select}>拒否</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="style_td" width="20%">WikiNameの自動リンク(nowikiname)</td>
                    <td class="style_td" width="80%">
                    <select name="nowikiname" size="1">
                        <option value="0" {$nowikiname_true_select}>有効</option>
                        <option value="1" {$nowikiname_false_select}>無効</option>
                    </select>
                    </td>
                </tr>
                <tr>
                    <td class="style_td" width="20%">AutoLinkのページ名の最小文字数(\$autolink) (0で無効)</td>
                    <td class="style_td" width="80%"><input type="number" value="{$autolink}" placeholder="0" name="autolink"></input></td>
                </tr>
                <tr>
                    <td class="style_td" width="20%">自動で改行(\$line_break)</td>
                    <td class="style_td" width="80%">
                        <select name="line_break" size="1">
                            <option value="1" {$line_break_true_select}>有効</option>
                            <option value="0" {$line_break_false_select}>無効</option>
                        </select>
                    </td>
                </tr>
                {$autoalias_table}
                <tr>
                    <td style="text-align:center" colspan="2" class="style_td">
                        <input class="form_button" type="submit" value="設定を保存する" name="details_setting_save" id="details_setting_save"></input>
                    </td>
                </tr>
            </tbody>
        </table>
    </form>
EOD;
//1.5.3~ (RightBar)
if(exist_plugin("rightbar")){
    $rightbar = <<<EOD
                <tr>
                    <td class="style_td" width="20%">右メニューバー(RightBar)</td>
                    <td class="style_td" width="80%"><input type="text" value="{$rightbar_name}" placeholder="RightBar" name="rightbar_name"></input></td>
                </tr>
    EOD;
}else{
    $rightbar = "";
}
//----
$html_page_setting = <<<EOD
    <h3>標準ページ設定</h3>
    <form class="manageform_form" action="" method="post" target="sendForm">
        <input type="hidden" value="manageform" name="plugin"></input>
        <input type="hidden" value="save_settings" name="do"></input>
        <table class="style_table" cellspacing="1" border="0" width="75%">
            <tbody>
                <tr>
                    <td class="style_td" width="20%">トップページ(Top / DefaultPage)</td>
                    <td class="style_td" width="80%"><input type="text" value="{$defaultpage}" placeholder="FrontPage" name="defaultpage"></input></td>
                </tr>
                <tr>
                    <td class="style_td" width="20%">メニューバー(MenuBar)</td>
                    <td class="style_td" width="80%"><input type="text" value="{$menubar}" placeholder="MenuBar" name="menubar"></input></td>
                </tr>
                {$rightbar}
                <tr>
                    <td class="style_td" width="20%">更新されたページ一覧(RecentChanges)</td>
                    <td class="style_td" width="80%"><input type="text" value="{$whatsnew}" placeholder="RecentChanges" name="whatsnew"></input></td>
                </tr>
                <tr>
                    <td class="style_td" width="20%">削除されたページ一覧(RecentDeleted)</td>
                    <td class="style_td" width="80%"><input type="text" value="{$whatsdeleted}" placeholder="RecentDeleted" name="whatsdeleted"></input></td>
                </tr>
                <tr>
                    <td class="style_td" width="20%">InterWikiName(InterWikiName)</td>
                    <td class="style_td" width="80%"><input type="text" value="{$interwiki}" placeholder="InterWikiName" name="interwiki"></input></td>
                </tr>
                {$autoaliasname_table}
                <tr>
                    <td style="text-align:center" colspan="2" class="style_td">
                        <input class="form_button" type="submit" value="設定を保存する" name="page_setting_save" id="page_setting_save"></input>
                    </td>
                </tr>
        </tbody>
    </table>
    </form>
EOD;

if (file_exists(SKIN_DIR."pukiwiki.skin.php")){
    $pukiwiki_skin_php = file_get_contents("./".SKIN_DIR."pukiwiki.skin.php");
    if (isset($pukiwiki_skin_php)){
        preg_match('/<link rel="stylesheet" type="text\/css" href="<\?php echo SKIN_DIR \?>(.*?)" \/>/um', $pukiwiki_skin_php,$matches);
        $design_file = $matches[1];
        preg_match("/define\(\s*?'SKIN_DEFAULT_DISABLE_TOPICPATH'\s*?,\s*?(.*?)\s*?\);/u", $pukiwiki_skin_php,$matches);
        $PKWK_SKIN_SHOW_TOPICPATH_INT = (int) $matches[1];
        preg_match("/define\(\s*?'PKWK_SKIN_SHOW_NAVBAR'\s*?,\s*?(.*?)\s*?\);/u", $pukiwiki_skin_php,$matches);
        $PKWK_SKIN_SHOW_NAVBAR_INT = (int) $matches[1];
        preg_match("/define\(\s*?'PKWK_SKIN_SHOW_TOOLBAR'\s*?,\s*?(.*?)\s*?\);/u", $pukiwiki_skin_php,$matches);
        $PKWK_SKIN_SHOW_TOOLBAR_INT = (int) $matches[1];
    }
}
if (file_exists(SKIN_DIR."pukiwiki.skin.php")){
    $design_css = file_get_contents(SKIN_DIR.$design_file);
    $design_css_name = $design_file;
}elseif (file_exists(SKIN_DIR."pukiwiki.css")){
    $design_css = file_get_contents(SKIN_DIR."pukiwiki.css");
    $design_css_name = "pukiwiki.css";
}elseif (file_exists(SKIN_DIR."pukiwiki.css.php")){
    $design_css = file_get_contents(SKIN_DIR."pukiwiki.css.php");
    $design_css_name = "pukiwiki.css.php";
}
$SKIN_DEFAULT_DISABLE_TOPICPATH_URL_SELECT = "";$SKIN_DEFAULT_DISABLE_TOPICPATH_PATH_SELECT = "";if ($PKWK_SKIN_SHOW_TOPICPATH_INT == 1){$SKIN_DEFAULT_DISABLE_TOPICPATH_URL_SELECT = "selected";}elseif ($PKWK_SKIN_SHOW_TOPICPATH_INT == 0){$SKIN_DEFAULT_DISABLE_TOPICPATH_PATH_SELECT = "selected";}
$PKWK_SKIN_SHOW_NAVBAR_TRUE_SELECT = "";$PKWK_SKIN_SHOW_NAVBAR_FALSE_SELECT = "";if ($PKWK_SKIN_SHOW_NAVBAR_INT == 1){$PKWK_SKIN_SHOW_NAVBAR_TRUE_SELECT = "selected";}elseif ($PKWK_SKIN_SHOW_NAVBAR_INT == 0){$PKWK_SKIN_SHOW_NAVBAR_FALSE_SELECT = "selected";}
$PKWK_SKIN_SHOW_TOOLBAR_TRUE_SELECT = "";$PKWK_SKIN_SHOW_TOOLBAR_FALSE_SELECT = "";if ($PKWK_SKIN_SHOW_TOOLBAR_INT == 1){$PKWK_SKIN_SHOW_TOOLBAR_TRUE_SELECT = "selected";}elseif ($PKWK_SKIN_SHOW_TOOLBAR_INT == 0){$PKWK_SKIN_SHOW_TOOLBAR_FALSE_SELECT = "selected";}
$html_design_setting = <<<EOD
    <h3>デザイン設定</h3>
    <form class="manageform_form" action="" method="post" target="sendForm">
        <input type="hidden" value="manageform" name="plugin"></input>
        <input type="hidden" value="save_skin_settings" name="do"></input>
        <table class="style_table" cellspacing="1" border="0" width="100%">
            <tbody>
                <tr>
                    <th style="text-align:center;height:20px;" colspan="2" class="style_th">スキン設定</th>
                </tr>
                <tr>
                    <td class="style_td" width="20%">CSSカスタマイズ</td>
                    <td class="style_td" width="80%"><input type="text" value="{$design_css_name}" placeholder="pukiwiki.css" name="skin_css_set"></input></td>
                </tr>
                <tr>
                    <td class="style_td" width="20%">タイトル下のテキスト(SKIN_DEFAULT_DISABLE_TOPICPATH)</td>
                    <td class="style_td" width="80%">
                        <select name="SKIN_DEFAULT_DISABLE_TOPICPATH" size="1">
                            <option value="0" {$SKIN_DEFAULT_DISABLE_TOPICPATH_PATH_SELECT}>ページのパス</option>
                            <option value="1" {$SKIN_DEFAULT_DISABLE_TOPICPATH_URL_SELECT}>ページのURL</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="style_td" width="20%">ナビゲーションバー(PKWK_SKIN_SHOW_NAVBAR)</td>
                    <td class="style_td" width="80%">
                        <select name="PKWK_SKIN_SHOW_NAVBAR" size="1">
                            <option value="1" {$PKWK_SKIN_SHOW_NAVBAR_TRUE_SELECT}>有効</option>
                            <option value="0" {$PKWK_SKIN_SHOW_NAVBAR_FALSE_SELECT}>無効</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="style_td" width="20%">ツールバー(PKWK_SKIN_SHOW_TOOLBAR)</td>
                    <td class="style_td" width="80%">
                        <select name="PKWK_SKIN_SHOW_TOOLBAR" size="1">
                            <option value="1" {$PKWK_SKIN_SHOW_TOOLBAR_TRUE_SELECT}>有効</option>
                            <option value="0" {$PKWK_SKIN_SHOW_TOOLBAR_FALSE_SELECT}>無効</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td style="text-align:CENTER" colspan="2" class="style_td">
                        <input class="form_button" type="submit" value="設定を保存する" name="design_setting_save" id="design_setting_save"></input>
                    </td>
                </tr>
            </tbody>
        </table>
    </form>
    <form class="manageform_form" action="" method="post" target="sendForm">
        <input type="hidden" value="manageform" name="plugin"></input>
        <input type="hidden" value="design_setting_save" name="do"></input>
        <table class="style_table" cellspacing="1" border="0" width="100%">
            <tbody>
                <tr>
                    <th style="text-align:center;height:20px;" colspan="2" class="style_th">{$design_css_name}</th>
                </tr>
                <tr>
                    <td style="text-align:center" colspan="2" class="style_td">
                        <textarea style="height:750px;width:100%;" name="pukiwiki_css" id="pukiwiki_css">{$design_css}</textarea>
                    </td>
                </tr>
                <tr>
                    <td style="text-align:LEFT" colspan="2" class="style_td">
                        ファイル名:<input class="form_textbox" type="text" value="{$design_css_name}" name="css_filename" id="css_filename" />
                        <input class="form_button" type="submit" value="保存する" name="design_setting_save" id="design_setting_save" />
                        <input class="form_button" type="submit" value="読み込む"onClick="loadCssFile();"  name="design_load" id="design_load" />
                    </td>
                </tr>
            </tbody>
        </table>
    </form>
    <script>
    function loadCssFile()
    {
        var xhr = new XMLHttpRequest();
        xhr.open("GET","skin/" + document.getElementById("css_filename").value,true);
        xhr.send(null);
        xhr.onload = function(){
            document.getElementById("pukiwiki_css").value = xhr.responseText;
        }
    }
    </script>
EOD;

$read_auth_true_select = "";$read_auth_false_select = "";if ($read_auth == 1){$read_auth_true_select = "selected";}elseif ($read_auth == 0){$read_auth_false_select = "selected";}
$edit_auth_true_select = "";$edit_auth_false_select = "";if ($edit_auth == 1){$edit_auth_true_select = "selected";}elseif ($edit_auth == 0){$edit_auth_false_select = "selected";}
$search_auth_true_select = "";$search_auth_false_select = "";if ($search_auth == 1){$search_auth_true_select = "selected";}elseif ($search_auth == 0){$search_auth_false_select = "selected";}

$user_count = 0;
$readauthpages_count = 0;
$editauthpages_count = 0;

$filedata = file_get_contents('./pukiwiki.ini.php');
if (preg_match('/\$auth_users\s*?=\s*?array\((.*?)\);/us',$filedata,$matches)){
    $auth_users_html = "";
    preg_match_all('/\'(.+?)\'\s*?=>\s*?\'(.+?)\'\s*?,/u',$matches[1],$matches2,PREG_SET_ORDER);
    foreach($matches2 as $value1)
    {
        $user_count = $user_count + 1;
        $auth_users_html .= '<ele id="user_auth_ele'.$user_count.'"><input type="text" name="user_auth_username[]" id="user_auth_username'.$user_count.'" value="'.$value1[1].'" size="20" placeholder="ユーザー名"> <input type="text" name="user_auth_password[]" id="user_auth_password'.$user_count.'" value="'.$value1[2].'" size="40" placeholder="パスワード"> <input type="button" name="user_auth_delete'.$user_count.'" id="user_auth_delete'.$user_count.'" onclick="RemoveUserAuthOfUser('.$user_count.');" value="削除"><br></ele>';
    }
}
if (preg_match('/\$read_auth_pages\s*?=\s*?array\((.*?)\);/us',$filedata,$matches)){
    $read_auth_pages_html = "";
    preg_match_all('/\'(.+?)\'\s*?=>\s*?\'(.+?)\'\s*?,/u',$matches[1],$matches2,PREG_SET_ORDER);
    foreach($matches2 as $value1)
    {
        $readauthpages_count = $readauthpages_count + 1;
        $read_auth_pages_html .= '<ele id="read_auth_ele'.$readauthpages_count.'"><input type="text" name="read_auth_pages[]" id="read_auth_pages'.$readauthpages_count.'" value="'.$value1[1].'" size="40" placeholder="ページ"> <input type="text" name="read_auth_username[]" id="read_auth_username'.$readauthpages_count.'" value="'.$value1[2].'" size="20" placeholder="ユーザー"> <input type="button" name="read_auth_delete'.$readauthpages_count.'" id="read_auth_delete'.$readauthpages_count.'" onclick="RemoveReadAuthPage('.$readauthpages_count.');" value="削除"><br></ele>';
    }
}
if (preg_match('/\$edit_auth_pages\s*?=\s*?array\((.*?)\);/us',$filedata,$matches)){
    $edit_auth_pages_html = "";
    preg_match_all('/\'(.+?)\'\s*?=>\s*?\'(.+?)\'\s*?,/u',$matches[1],$matches2,PREG_SET_ORDER);
    foreach($matches2 as $value1)
    {
        $editauthpages_count = $editauthpages_count + 1;
        $edit_auth_pages_html .= '<ele id="edit_auth_ele'.$editauthpages_count.'"><input type="text" name="edit_auth_pages[]" id="edit_auth_pages'.$editauthpages_count.'" value="'.$value1[1].'" size="40" placeholder="ページ"> <input type="text" name="edit_auth_username[]" id="edit_auth_username'.$editauthpages_count.'" value="'.$value1[2].'" size="20" placeholder="ユーザー"> <input type="button" name="edit_auth_delete'.$editauthpages_count.'" id="edit_auth_delete'.$editauthpages_count.'" onclick="RemoveEditAuthPage('.$editauthpages_count.');" value="削除"><br></ele>';
    }
}

$html_user_setting = <<<EOD
    <h3>ユーザー設定</h3>
    <form class="manageform_form" action="" method="post" target="sendForm">
        <input type="hidden" value="manageform" name="plugin"></input>
        <input type="hidden" value="save_settings" name="do"></input>
        <table class="style_table" cellspacing="1" border="0" width="100%">
            <tbody>
                <tr>
                    <th style="text-align:center;height:20px;" colspan="2" class="style_th">ユーザー設定</th>
                </tr>
                <tr>
                    <td class="style_td" width="20%">ユーザー</td>
                    <td class="style_td" width="80%">
                        暗号化は「{x-php-md5}」などを利用してください。<br />
                        <span id="userauthofuserarea">
                        {$auth_users_html}
                        </span>
                        <input type="button" value="追加" onClick="AddUserAuthOfUser();" />
                    </td>
                </tr>
                <tr>
                    <td class="style_td" width="20%">閲覧制限(ユーザー認証)</td>
                    <td class="style_td" width="80%">
                        <select name="read_auth" size="1">
                            <option value="1" {$read_auth_true_select}>有効</option>
                            <option value="0" {$read_auth_false_select}>無効</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="style_td" width="20%">編集制限(ユーザー認証)</td>
                    <td class="style_td" width="80%">
                        <select name="edit_auth" size="1">
                            <option value="1" {$edit_auth_true_select}>有効</option>
                            <option value="0" {$edit_auth_false_select}>無効</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="style_td" width="20%">検索制限(ユーザー認証)</td>
                    <td class="style_td" width="80%">
                        <select name="search_auth" size="1">
                            <option value="1" {$search_auth_true_select}>有効</option>
                            <option value="0" {$search_auth_false_select}>無効</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="style_td" width="20%">閲覧制限ページ</td>
                    <td class="style_td" width="80%">
                        <span id="readpageauthsarea">
                        {$read_auth_pages_html}
                        </span>
                        <input type="button" value="追加" onClick="AddReadAuthPage();" />
                    </td>
                </tr>
                <tr>
                    <td class="style_td" width="20%">編集制限ページ</td>
                    <td class="style_td" width="80%">
                        <span id="editpageauthsarea">
                        {$edit_auth_pages_html}
                        </span>
                        <input type="button" value="追加" onClick="AddEditAuthPage();" />
                    </td>
                </tr>
                <tr>
                    <td style="text-align:CENTER" colspan="2" class="style_td">
                        <input class="form_button" type="submit" value="設定を保存する" name="design_setting_save" id="design_setting_save"></input>
                    </td>
                </tr>
            </tbody>
        </table>
    </form>
<script>
var user_count = {$user_count};
var userinput1 = document.getElementById('userauthofuserarea');
function AddUserAuthOfUser(){
	user_count++;
	userinput1.insertAdjacentHTML('beforeend','<ele id="user_auth_ele' + user_count + '"><input type="text" name="user_auth_username[]" id="user_auth_username' + user_count + '" value="" size="20" placeholder="ユーザー名" /> <input type="text" name="user_auth_password[]" id="user_auth_password' + user_count + '" value="" size="40" placeholder="パスワード"/> <input type="button" name="user_auth_delete' + user_count + '" id="user_auth_delete' + user_count + '" onClick="RemoveUserAuthOfUser(' + user_count + ');" value="削除" /><br /></ele>');
}
function RemoveUserAuthOfUser(userauthareaid){
    document.getElementById('user_auth_ele' + userauthareaid).remove();
}

var readauthpages_count = {$readauthpages_count};
var readauthpages_input1 = document.getElementById('readpageauthsarea');
function AddReadAuthPage(){
	readauthpages_count++;
	readauthpages_input1.insertAdjacentHTML('beforeend','<ele id="read_auth_ele' + readauthpages_count + '"><input type="text" name="read_auth_pages[]" id="read_auth_pages' + readauthpages_count + '" value="" size="40" placeholder="ページ" /> <input type="text" name="read_auth_username[]" id="read_auth_username' + readauthpages_count + '" value="" size="20" placeholder="ユーザー"/> <input type="button" name="read_auth_delete' + readauthpages_count + '" id="read_auth_delete' + readauthpages_count + '" onClick="RemoveReadAuthPage(' + readauthpages_count + ');" value="削除" /><br /></ele>');
}
function RemoveReadAuthPage(userauthareaid){
    document.getElementById('read_auth_ele' + userauthareaid).remove();
}

var editauthpages_count = {$editauthpages_count};
var editauthpages_input1 = document.getElementById('editpageauthsarea');
function AddEditAuthPage(){
	editauthpages_count++;
	editauthpages_input1.insertAdjacentHTML('beforeend','<ele id="edit_auth_ele' + editauthpages_count + '"><input type="text" name="edit_auth_pages[]" id="edit_auth_pages' + editauthpages_count + '" value="" size="40" placeholder="ページ" /> <input type="text" name="edit_auth_username[]" id="edit_auth_username' + editauthpages_count + '" value="" size="20" placeholder="ユーザー"/> <input type="button" name="edit_auth_delete' + editauthpages_count + '" id="edit_auth_delete' + editauthpages_count + '" onClick="RemoveEditAuthPage(' + editauthpages_count + ');" value="削除" /><br /></ele>');
}
function RemoveEditAuthPage(userauthareaid){
    document.getElementById('edit_auth_ele' + userauthareaid).remove();
}
</script>
EOD;

$html = <<<EOD
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <style> 
.form_tab {
  list-style: none;
  margin: 0;
  padding: 0;
}

.form_button {
  display:inline-block;
  background-color: #D2E2F2;
  border:1px solid #AAAAAA;
  padding:5px 10px;
  border-radius : 3px;
}

.form_button:hover {
  background-color: #C0D0E0;
}

.form_tab li {
  display: inline-block;
  color: #AAAAAA;
  background-color: #DDEEFF;
  padding: 10px 10px;
  cursor: pointer;
}
 
.form_tab li:hover {
  color: #000;
  background-color: #D2E2F2;
}
 
.form_tab li.form_active {
  color: #000;
  background-color: #D2E2F2;
}
 
.tabContent {
  display: none;
  padding: 15px;
  border: 1px solid #D2E2F2;
}
 
.form_active {
  display: block;
}
    </style>
    <script>
    \$(function() {
        \$(".form_tab li").click(function() {
            var num = \$(".form_tab li").index(this);
            \$(".tabContent").removeClass('form_active');      
            \$(".tabContent").eq(num).addClass('form_active');
            \$(".form_tab li").removeClass('form_active');        
            \$(this).addClass('form_active');
        });
    });
    </script>
    <iframe name="sendForm" style="width:0px;height:0px;border:0px;display:none;"></iframe>
    <h2>管理画面</h2>
    <span id="form_result"></span>
    <ul class="form_tab">
        <li class="form_active">基本設定</li>
        <li>詳細設定</li>
        <li>標準ページ設定</li>
        <li>デザイン設定</li>
        <li>ユーザー設定</li>
        <li>プラグイン設定</li>
        <li>PukiWikiManageForm</li>
    </ul>
    <div class="tabContent form_active">
{$html_general_setting}
    </div>
    <div class="tabContent">
{$html_details_setting}
    </div>
    <div class="tabContent">
{$html_page_setting}
    </div>
    <div class="tabContent">
{$html_design_setting}
    </div>
    <div class="tabContent">
{$html_user_setting}
    </div>
    <div class="tabContent">
        <h3>プラグイン設定</h3>
        <h4>プラグインのアップロード</h4>
        phpファイルのみアップロードできます。<br />
        それ以外のファイルは転送して導入して下さい。
        <form enctype="multipart/form-data" action="./" method="post">
            <input type="hidden" value="manageform" name="plugin"></input>
            <input type="hidden" value="upload_plugin" name="do"></input>
            <input name="upload_file[]" type="file" multiple="multiple" />
            <input type="submit" value="アップロード" />
        </form>
    </div>
    <div class="tabContent">
        <a href="?plugin=manageform&do=logout"><input type="submit" value="ログアウト" /></a>
    </div>
    <script>
    \$('.form_button').click(function() {
        document.getElementById("form_result").innerHTML = "設定を保存しました。(" + new Date()+")";
    })
    </script>
EOD;
    return array('msg'=>$title, 'body'=>$html);
}

function plugin_manageform_phpsecialchars($chars){
    $chars = str_replace("'","\\'",$chars);
    $chars = str_replace("\"","\\\"",$chars);
    return $chars;
}
?>