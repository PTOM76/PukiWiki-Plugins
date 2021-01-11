<?php
// $Id: select_navi.inc.php,v 1.00 2020/10/18 20:48:18 K Exp $
define('SELECT_NAVI_CONFIG_PATH', 'plugin/SelectNavi');
define('SELECT_NAVI', TRUE);//SELECT_NAVIの表示を 有効(TRUE) / 無効(FALSE)

function plugin_select_navi_action(){
    if (isset($_POST['select_navi'])){
        $select_navis = preg_split("/,/",$_POST['select_navi']);
        if ($select_navis[1]=="page"){
            header("Location: ./?".$select_navis[0]);
            exit;
        }
        if ($select_navis[2]=="true"){
            header("Location: ./?".$select_navis[1]."=".$select_navis[0]."&page=".$_POST['page']);
            exit;
        }elseif($select_navis[2]=="false"){
            header("Location: ./?".$select_navis[1]."=".$select_navis[0]);
            exit;
        }elseif($select_navis[2]=="refer"){
            header("Location: ./?".$select_navis[1]."=".$select_navis[0]."&refer=".$_POST['page']);
            exit;
        }
    }
    $msg = "SelectNavi";
    $body = "<h2>SelectNavi</h2>";
    return array('msg'=>$msg, 'body'=>$body);
}

function plugin_select_navi_bar()
{
    if (SELECT_NAVI==TRUE){
    global $vars;
    $page = $vars['page'];
	$config = new Config(SELECT_NAVI_CONFIG_PATH);
    if ($config->read()){

    }else{
        $postdata = "*SelectNavi
-select_navi.inc.php(SelectNaviプラグイン)のセレクトボックスに表示される設定ページです。
-使い方:[[http://k0.22web.org/?%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/select_navi.inc.php]]
**書き方
 //select_navi
 |(プラグイン名)|cmd / plugin|true / false(pageパラメータの有無)|(表示名)|
|~Action(text)|~Type(cmd/plugin)|~Page(true/false/refer)|~Name(text)|
//select_navi
|rename|cmd|false|名前変更|
//select_navi
|template|plugin|refer|複製|
//select_navi
|source|cmd|true|ソースコード|
";
        page_write(PKWK_CONFIG_PREFIX.SELECT_NAVI_CONFIG_PATH,$postdata);
    }
	unset($config);
    $pagefilepath = "./wiki/".strtoupper(bin2hex(PKWK_CONFIG_PREFIX.SELECT_NAVI_CONFIG_PATH)).".txt";
    $pagesource = file_get_contents($pagefilepath);
    $select_navi_option_data = "<option value=\"null\" selected>--------------------</option>\n";
    if (preg_match_all("/\/\/select_navi\n\|(.+?)\|(.+?)\|(.+?)\|(.+?)\|/u",$pagesource,$matchs,PREG_SET_ORDER)){
        foreach ($matchs as $value) {
            $select_navi_option_data .= "<option value=\"".$value[1].",".$value[2].",".$value[3].","."\">".$value[4]."</option>";
        }
    }
    $script = '
    <form action="./?plugin=select_navi" method="post" style="display:inline;">
        <select size="1" name="select_navi" onchange="submit(this.form)">
            '.$select_navi_option_data.'
		</select>
        <input type="hidden" name="page" value="'.$page.'" />
    </form>
    ';
    unset($pagesource);
    }else{
        $script = '無効';
    }
	return $script;
}
?>
