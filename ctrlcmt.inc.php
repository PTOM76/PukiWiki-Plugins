<?php
// $Id: ctrlcmt.inc.php,v 1.00 2020/12/25 16:58:19 K Exp $

/** 
 * @link http://example.com/
 * @author K
 * @license http://www.gnu.org/licenses/gpl.ja.html GPL
 */

define('PLUGIN_CTRLCMT_NGWORD', "ちん(こ|ちん),死ね"); // NGワードの設定 (正規表現) 「,」で複数指定可
// PLUGIN_CTRLCMT_NGWORD_MODE
// false = 機能の無効化
// hide = NGワードをPLUGIN_CTRLCMT_NGWORD_MASKの文字に置き換える。
// stop = NGワードが存在する場合、エラー画面を表示する。
define('PLUGIN_CTRLCMT_NGWORD_MODE', 'stop');
define('PLUGIN_CTRLCMT_NGWORD_MASK', '＊');

define('PLUGIN_CTRLCMT_IPCRY', false); // 2ch風ID
define('PLUGIN_CTRLCMT_SAVEIP', true); // IPアドレス/コメントを保存する
define('PLUGIN_CTRLCMT_DIR', 'ctrlcmt'); // CTRLCMT用ディレクトリ
define('PLUGIN_CTRLCMT_MULTILINE', false); // 複数行コメント
define('PLUGIN_CTRLCMT_ROWS', '3'); // 行数

define('PLUGIN_CTRLCMT_NONAME', "名無しさん"); // 名無し時の名前
//----
define('PLUGIN_CTRLCMT_DIRECTION_DEFAULT', '1'); // 1: above 0: below
define('PLUGIN_CTRLCMT_SIZE_MSG', 70);
define('PLUGIN_CTRLCMT_SIZE_NAME', 15);
define('PLUGIN_CTRLCMT_BLACKIPLIST', '');
// ----
define('PLUGIN_CTRLCMT_FORMAT_MSG', '$msg');
define('PLUGIN_CTRLCMT_FORMAT_NAME', '[[$name]]');
define('PLUGIN_CTRLCMT_FORMAT_NOW', '&new{$now};');
define('PLUGIN_CTRLCMT_FORMAT_STRING', "\$MSG\$ -- \$NAME\$\$IPCRY\$ \$NOW\$");

function plugin_ctrlcmt_convert()
{
    return plugin_ctrlcmt_inline(func_get_args());
}

function plugin_ctrlcmt_action()
{
    global $vars;
    
    if ($vars['do'] == "comment")
    {
        global $now, $_title_updated, $_no_name;
        global $_msg_comment_collided, $_title_comment_collided;
        global $_comment_plugin_fail_msg;
        if (!$password)
        {
            $password = "abcdef";
        }
        if (PKWK_READONLY) die_message('PKWK_READONLY prohibits editing');
        if (PLUGIN_CTRLCMT_MULTILINE == true)
        {
            $vars['msg'] = preg_replace("/(\n|\r|\r\n)/", "\n&br;", $vars['msg']);
        }
        $PLUGIN_CTRLCMT_BLACKIPLIST = PLUGIN_CTRLCMT_BLACKIPLIST;
        if (!$PLUGIN_CTRLCMT_BLACKIPLIST == ''){
            $blockiparray = explode(",",$PLUGIN_CTRLCMT_BLACKIPLIST);
            foreach($blockiparray as $value){
                if ($value == $_SERVER["REMOTE_ADDR"])
                {
                    die_message('あなたの利用しているIPアドレスは、当WikiのCtrlCmtプラグインのブラックリストに入っています。');
                }
            }
        }
        $PLUGIN_CTRLCMT_NGWORD = PLUGIN_CTRLCMT_NGWORD;
        $PLUGIN_CTRLCMT_NGWORD_MODE = PLUGIN_CTRLCMT_NGWORD_MODE;

        if ((!$PLUGIN_CTRLCMT_NGWORD_MODE == 'false') || (!$PLUGIN_CTRLCMT_NGWORD == "") && (isset($PLUGIN_CTRLCMT_NGWORD)))
        {
            $PLUGIN_CTRLCMT_NGWORD = "(" . str_replace(",", "|", $PLUGIN_CTRLCMT_NGWORD) . ")";
            if ($PLUGIN_CTRLCMT_NGWORD_MODE == 'stop')
            {
                if (preg_match_all('/' . $PLUGIN_CTRLCMT_NGWORD . '/u', $vars['msg'], $match, PREG_SET_ORDER))
                {
                    $ngwords = array();
                    foreach ($match as $value)
                    {
                        $ngwords[] = $value[0];
                    }
                    die_message('コメントに以下のNGワードが含まれています。<br /><pre>' . implode(" , ", $ngwords) . '</pre>');
                }
            }
            else if ($PLUGIN_CTRLCMT_NGWORD_MODE == 'hide')
            {
                $vars['msg'] = preg_replace('/' . $PLUGIN_CTRLCMT_NGWORD . '/u', PLUGIN_CTRLCMT_NGWORD_MASK, $vars['msg']);
            }

        }
        if (!isset($vars['msg'])) return array(
            'msg' => '',
            'body' => ''
        ); // Do nothing
        $vars['msg'] = str_replace("\n", '', $vars['msg']); // Cut LFs
        $head = '';
        $match = array();
        if (preg_match('/^(-{1,2})-*\s*(.*)/', $vars['msg'], $match))
        {
            $head = & $match[1];
            $vars['msg'] = & $match[2];
        }
        if ($vars['msg'] == '') return array(
            'msg' => '',
            'body' => ''
        ); // Do nothing
        $comment = str_replace('$msg', $vars['msg'], PLUGIN_CTRLCMT_FORMAT_MSG);
        if (isset($vars['name']) || ($vars['nodate'] != '1'))
        {
            $_name = (!isset($vars['name']) || $vars['name'] == '') ? $_no_name : $vars['name'];
            $_name2 = $_name;
            if (PLUGIN_CTRLCMT_NONAME == ""){
                $PLUGIN_CTRLCMT_NONAME = "";
            }else{
                $PLUGIN_CTRLCMT_NONAME = "[[".PLUGIN_CTRLCMT_NONAME."]]";
            }
            $_name = ($_name == '') ? $PLUGIN_CTRLCMT_NONAME : str_replace('$name', $_name, PLUGIN_CTRLCMT_FORMAT_NAME);
            if (PLUGIN_CTRLCMT_IPCRY == true)
            {
                $_cryedip = substr(base64_encode(hash_hmac("sha1", date("Ymd") . $_SERVER["REMOTE_ADDR"], $password)) , 0, 8);
                $_ipcry = " &size(10){[" . $_cryedip . "]};";
            }
            else
            {
                $_ipcry = "";
            }
            $_now = ($vars['nodate'] == '1') ? '' : str_replace('$now', $now, PLUGIN_CTRLCMT_FORMAT_NOW);
            if (PLUGIN_CTRLCMT_SAVEIP == true)
            {
                $iplog_json = array();
                $iplog_loadjson = array();
                $iplogIsExist = false;
                if (file_exists(PLUGIN_CTRLCMT_DIR . '/cmt/.htaccess') == false)
                {
                    mkdir(PLUGIN_CTRLCMT_DIR);
                    mkdir(PLUGIN_CTRLCMT_DIR . "/cmt");
                    $open_ht = fopen(PLUGIN_CTRLCMT_DIR . "/cmt/.htaccess", a);
                    @fwrite($open_ht, "Require all denied");
                    fclose($open_ht);
                }
                if (file_exists(PLUGIN_CTRLCMT_DIR . '/cmt/log.json'))
                {
                    $iplog_loadjson = json_decode(file_get_contents(PLUGIN_CTRLCMT_DIR . "/cmt/log.json") , true);
                    $iplogIsExist = true;
                }
                $iplog_json[] = array(
                    'name' => $_name2,
                    'comment' => $comment,
                    'ipcry' => $_cryedip,
                    'date' => $now,
                    'page' => $vars['refer'],
                    'ip_address' => $_SERVER["REMOTE_ADDR"],
                );
                if ($iplogIsExist == true)
                {
                    $iplog_json = array_merge($iplog_json, $iplog_loadjson);
                }
                file_put_contents(PLUGIN_CTRLCMT_DIR . "/cmt/log.json", json_encode($iplog_json, JSON_PRETTY_PRINT));
            }
            $comment = str_replace("\$MSG\$", $comment, PLUGIN_CTRLCMT_FORMAT_STRING);
            $comment = str_replace("\$NAME\$", $_name, $comment);
            $comment = str_replace("\$IPCRY\$", $_ipcry, $comment);
            $comment = str_replace("\$NOW\$", $_now, $comment);
        }
        $comment = '-' . $head . ' ' . $comment;

        $postdata = '';
        $comment_no = 0;
        $above = (isset($vars['above']) && $vars['above'] == '1');
        $comment_added = false;
        foreach (get_source($vars['refer']) as $line)
        {
            if (!$above) $postdata .= $line;
            if (preg_match('/^#ctrlcmt/i', $line) && $comment_no++ == $vars['ctrlcmt_no'])
            {
                $comment_added = true;
                if ($above)
                {
                    $postdata = rtrim($postdata) . "\n" . $comment . "\n" . "\n"; // Insert one blank line above #commment, to avoid indentation
                    
                }
                else
                {
                    $postdata = rtrim($postdata) . "\n" . $comment . "\n";
                }
            }
            if ($above) $postdata .= $line;
        }
        $title = $_title_updated;
        $body = '';
        if ($comment_added)
        {
            // new comment added
            if (md5(get_source($vars['refer'], true, true)) !== $vars['digest'])
            {
                $title = $_title_comment_collided;
                $body = $_msg_comment_collided . make_pagelink($vars['refer']);
            }
            page_write($vars['refer'], $postdata);
        }
        else
        {
            // failed to add the comment
            $title = $_title_comment_collided;
            $body = $_comment_plugin_fail_msg . make_pagelink($vars['refer']);
        }
        $retvars['msg'] = $title;
        $retvars['body'] = $body;
        $vars['page'] = $vars['refer'];
        return $retvars;
    }
    elseif (!isset($vars['do']))
    {
        $msg = "CtrlCmt";
        session_start();

        if ($vars['logout'] == "true"){
            unset($_SESSION['ctrlcmt_loginpass']);
            $msg = "ログアウトしました。";
        }

        if (pkwk_login($vars['ctrlcmt_loginpass'])){
            $_SESSION['ctrlcmt_loginpass'] = $vars['ctrlcmt_loginpass'];
        }elseif (isset($vars['ctrlcmt_loginpass'])){
            $msg = "ログインに失敗しました。";
        }
        
        if (pkwk_login($_SESSION['ctrlcmt_loginpass'])){
            if (isset($vars['setting_save'])){
                $filedata = file_get_contents(PLUGIN_DIR.basename(__FILE__));
                if (isset($vars['PLUGIN_CTRLCMT_FORMAT_STRING'])){
                    $vars['PLUGIN_CTRLCMT_FORMAT_STRING'] = str_replace('$','\\\$',$vars['PLUGIN_CTRLCMT_FORMAT_STRING']);
                    $filedata = preg_replace('/define\(\s*?\'PLUGIN_CTRLCMT_FORMAT_STRING\'\s*?,\s*?"(.*?)"\s*?\);/u', "define('PLUGIN_CTRLCMT_FORMAT_STRING', \"" . $vars['PLUGIN_CTRLCMT_FORMAT_STRING'] . "\");", $filedata, 1);
                }
                if (isset($vars['PLUGIN_CTRLCMT_NONAME'])){
                    $filedata = preg_replace('/define\(\s*?\'PLUGIN_CTRLCMT_NONAME\'\s*?,\s*?"(.*?)"\s*?\);/u', "define('PLUGIN_CTRLCMT_NONAME', \"" . $vars['PLUGIN_CTRLCMT_NONAME'] . "\");", $filedata, 1);
                }
                if (isset($vars['PLUGIN_CTRLCMT_MULTILINE'])){
                    $filedata = preg_replace('/define\(\s*?\'PLUGIN_CTRLCMT_MULTILINE\'\s*?,\s*?(true|false)\s*?\);/u', "define('PLUGIN_CTRLCMT_MULTILINE', " . $vars['PLUGIN_CTRLCMT_MULTILINE'] . ");", $filedata, 1);
                }
                if (isset($vars['PLUGIN_CTRLCMT_ROWS'])){
                    $filedata = preg_replace('/define\(\s*?\'PLUGIN_CTRLCMT_ROWS\'\s*?,\s*?\'(.*?)\'\s*?\);/u', "define('PLUGIN_CTRLCMT_ROWS', '" . $vars['PLUGIN_CTRLCMT_ROWS'] . "');", $filedata, 1);
                }
                if (isset($vars['PLUGIN_CTRLCMT_NGWORD'])){
                    $filedata = preg_replace('/define\(\s*?\'PLUGIN_CTRLCMT_NGWORD\'\s*?,\s*?"(.*?)"\s*?\);/u', "define('PLUGIN_CTRLCMT_NGWORD', \"" . $vars['PLUGIN_CTRLCMT_NGWORD'] . "\");", $filedata, 1);
                }
                if (isset($vars['PLUGIN_CTRLCMT_NGWORD_MODE'])){
                    $filedata = preg_replace('/define\(\s*?\'PLUGIN_CTRLCMT_NGWORD_MODE\'\s*?,\s*?\'(.*?)\'\s*?\);/u', "define('PLUGIN_CTRLCMT_NGWORD_MODE', '" . $vars['PLUGIN_CTRLCMT_NGWORD_MODE'] . "');", $filedata, 1);
                }
                if (isset($vars['PLUGIN_CTRLCMT_NGWORD_MASK'])){
                    $filedata = preg_replace('/define\(\s*?\'PLUGIN_CTRLCMT_NGWORD_MASK\'\s*?,\s*?\'(.*?)\'\s*?\);/u', "define('PLUGIN_CTRLCMT_NGWORD_MASK', '" . $vars['PLUGIN_CTRLCMT_NGWORD_MASK'] . "');", $filedata, 1);
                }
                if (isset($vars['PLUGIN_CTRLCMT_IPCRY'])){
                    $filedata = preg_replace('/define\(\s*?\'PLUGIN_CTRLCMT_IPCRY\'\s*?,\s*?(true|false)\s*?\);/u', "define('PLUGIN_CTRLCMT_IPCRY', " . $vars['PLUGIN_CTRLCMT_IPCRY'] . ");", $filedata, 1);
                }
                if (isset($vars['PLUGIN_CTRLCMT_SAVEIP'])){
                    $filedata = preg_replace('/define\(\s*?\'PLUGIN_CTRLCMT_SAVEIP\'\s*?,\s*?(true|false)\s*?\);/u', "define('PLUGIN_CTRLCMT_SAVEIP', " . $vars['PLUGIN_CTRLCMT_SAVEIP'] . ");", $filedata, 1);
                }
                if (isset($vars['PLUGIN_CTRLCMT_DIR'])){
                    $filedata = preg_replace('/define\(\s*?\'PLUGIN_CTRLCMT_DIR\'\s*?,\s*?\'(.*?)\'\s*?\);/u', "define('PLUGIN_CTRLCMT_DIR', '" . $vars['PLUGIN_CTRLCMT_DIR'] . "');", $filedata, 1);
                }
                if (isset($vars['PLUGIN_CTRLCMT_DIRECTION_DEFAULT'])){
                    $filedata = preg_replace('/define\(\s*?\'PLUGIN_CTRLCMT_DIRECTION_DEFAULT\'\s*?,\s*?\'(.*?)\'\s*?\);/u', "define('PLUGIN_CTRLCMT_DIRECTION_DEFAULT', '" . $vars['PLUGIN_CTRLCMT_DIRECTION_DEFAULT'] . "');", $filedata, 1);
                }
                if (isset($vars['PLUGIN_CTRLCMT_SIZE_MSG'])){
                    $filedata = preg_replace('/define\(\s*?\'PLUGIN_CTRLCMT_SIZE_MSG\'\s*?,\s*?(.*?)\s*?\);/u', "define('PLUGIN_CTRLCMT_SIZE_MSG', " . $vars['PLUGIN_CTRLCMT_SIZE_MSG'] . ");", $filedata, 1);
                }
                if (isset($vars['PLUGIN_CTRLCMT_SIZE_NAME'])){
                    $filedata = preg_replace('/define\(\s*?\'PLUGIN_CTRLCMT_SIZE_NAME\'\s*?,\s*?(.*?)\s*?\);/u', "define('PLUGIN_CTRLCMT_SIZE_NAME', " . $vars['PLUGIN_CTRLCMT_SIZE_NAME'] . ");", $filedata, 1);
                }
                if (isset($vars['PLUGIN_CTRLCMT_BLACKIPLIST'])){
                    $filedata = preg_replace('/define\(\s*?\'PLUGIN_CTRLCMT_BLACKIPLIST\'\s*?,\s*?\'(.*?)\'\s*?\);/u', "define('PLUGIN_CTRLCMT_BLACKIPLIST', '" . $vars['PLUGIN_CTRLCMT_BLACKIPLIST'] . "');", $filedata, 1);
                }
                file_put_contents(PLUGIN_DIR.basename(__FILE__), $filedata);
                echo "saved";
                exit;
            }
            if (exist_plugin("manageform")){
                $addons_tab .= "<a href=\"?plugin=manageform\"><li>PukiWikiManageForm</li></a>";
                $addons_form .= "<div class=\"tabContent\"></div>";
            }
            //-
            $PLUGIN_CTRLCMT_FORMAT_STRING = PLUGIN_CTRLCMT_FORMAT_STRING;
            $PLUGIN_CTRLCMT_NONAME = PLUGIN_CTRLCMT_NONAME;
            $PLUGIN_CTRLCMT_ROWS = PLUGIN_CTRLCMT_ROWS;
            $PLUGIN_CTRLCMT_NGWORD = PLUGIN_CTRLCMT_NGWORD;
            $PLUGIN_CTRLCMT_NGWORD_MASK = PLUGIN_CTRLCMT_NGWORD_MASK;
            $PLUGIN_CTRLCMT_BLACKIPLIST = PLUGIN_CTRLCMT_BLACKIPLIST;
            $PLUGIN_CTRLCMT_SIZE_MSG = PLUGIN_CTRLCMT_SIZE_MSG;
            $PLUGIN_CTRLCMT_SIZE_NAME = PLUGIN_CTRLCMT_SIZE_NAME;
            if(PLUGIN_CTRLCMT_SAVEIP == true){
                $PLUGIN_CTRLCMT_SAVEIP_TRUE = "selected";
            }else if(PLUGIN_CTRLCMT_SAVEIP == false){
                $PLUGIN_CTRLCMT_SAVEIP_FALSE = "selected";
            }
            if(PLUGIN_CTRLCMT_IPCRY == true){
                $PLUGIN_CTRLCMT_IPCRY_TRUE = "selected";
            }else if(PLUGIN_CTRLCMT_IPCRY == false){
                $PLUGIN_CTRLCMT_IPCRY_FALSE = "selected";
            }
            if(PLUGIN_CTRLCMT_MULTILINE == true){
                $PLUGIN_CTRLCMT_MULTILINE_TRUE = "selected";
            }else if(PLUGIN_CTRLCMT_MULTILINE == false){
                $PLUGIN_CTRLCMT_MULTILINE_FALSE = "selected";
            }
            if(PLUGIN_CTRLCMT_NGWORD_MODE == "stop"){
                $PLUGIN_CTRLCMT_NGWORD_MODE_STOP = "selected";
            }else if(PLUGIN_CTRLCMT_NGWORD_MODE == "hide"){
                $PLUGIN_CTRLCMT_NGWORD_MODE_HIDE = "selected";
            }else if(PLUGIN_CTRLCMT_NGWORD_MODE == "false"){
                $PLUGIN_CTRLCMT_NGWORD_MODE_FALSE = "selected";
            }
            if (file_exists(PLUGIN_CTRLCMT_DIR . '/cmt/log.json'))
            {
                $commentlogs = '';
                $log_json = json_decode(file_get_contents(PLUGIN_CTRLCMT_DIR . "/cmt/log.json") , true);
                foreach($log_json as $value){
                    $commentlogs .= <<<EOD
                    <tr>
                        <td class="style_td">名前</td>
                        <td class="style_td">{$value['name']}</td>
                    </tr>
                    <tr>
                        <td class="style_td">コメント</td>
                        <td class="style_td">{$value['comment']}</td>
                    </tr>
                    <tr>
                        <td class="style_td">ID</td>
                        <td class="style_td">{$value['ipcry']}</td>
                    </tr>
                    <tr>
                        <td class="style_td">日付</td>
                        <td class="style_td">{$value['date']}</td>
                    </tr>
                    <tr>
                        <td class="style_td">ページ</td>
                        <td class="style_td">{$value['page']}</td>
                    </tr>
                    <tr>
                        <td class="style_td">IPアドレス</td>
                        <td class="style_td">{$value['ip_address']}</td>
                    </tr>
                    <tr>
                        <th class="style_th" colspan="2" style="text-align:center;height:20px;"><hr /></th>
                    </tr>
                    EOD;
                }
            }else{
                $commentlogs = <<<EOD
                <tr>
                    <td class="style_td" style="text-align:center;" colspan="2">ログは存在しません。<br />この機能を利用したい場合は「コメント/IPアドレスの保存」を有効にして下さい。</td>
                </tr>
                EOD;
            }
            //-
            $body = <<<EOD
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
            <h2>コントロールパネル</h2>
            <span id="form_result"></span>
            <ul class="form_tab">
                <li class="form_active">設定</li>
                <li>コメントログ</li>
                {$addons_tab}
                <li>ログアウト</li>
            </ul>
            <div class="tabContent form_active">
                <form method="post" target="sendForm">
                    <input type="hidden" name="encode_hint" value="ぷ" />
                    <input type="hidden" name="plugin" value="ctrlcmt" />
                    <table class="style_table" cellspacing="1" border="0" width="100%">
                        <tbody>
                            <tr>
                                <th style="text-align:center;height:20px;" colspan="2" class="style_th">標準設定</th>
                            </tr>
                            <tr>
                                <td class="style_td" width="20%">出力コメントフォーマット</td>
                                <td class="style_td" width="80%"><input type="text" size="30" value="{$PLUGIN_CTRLCMT_FORMAT_STRING}" placeholder="\$MSG\$ -- \$NAME\$\$IPCRY\$ \$NOW\$" name="PLUGIN_CTRLCMT_FORMAT_STRING"></input></td>
                            </tr>
                            <tr>
                                <td class="style_td" width="20%">名前未入力時の自動割り当てネーム</td>
                                <td class="style_td" width="80%"><input type="text" value="{$PLUGIN_CTRLCMT_NONAME}" placeholder="名無しさん" name="PLUGIN_CTRLCMT_NONAME"></input></td>
                            </tr>
                            <tr>
                                <td class="style_td" width="20%">メッセージの入力フォームのサイズ</td>
                                <td class="style_td" width="80%"><input type="number" value="{$PLUGIN_CTRLCMT_SIZE_MSG}" placeholder="70" name="PLUGIN_CTRLCMT_SIZE_MSG"></input></td>
                            </tr>
                            <tr>
                                <td class="style_td" width="20%">名前の入力フォームのサイズ</td>
                                <td class="style_td" width="80%"><input type="number" value="{$PLUGIN_CTRLCMT_SIZE_NAME}" placeholder="15" name="PLUGIN_CTRLCMT_SIZE_NAME"></input></td>
                            </tr>
                            <tr>
                                <td class="style_td" width="20%">複数行コメント</td>
                                <td class="style_td" width="80%">
                                    <select name="PLUGIN_CTRLCMT_MULTILINE" size="1">
                                        <option value="false" {$PLUGIN_CTRLCMT_MULTILINE_FALSE}>無効</option>
                                        <option value="true" {$PLUGIN_CTRLCMT_MULTILINE_TRUE}>有効</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="style_td" width="20%">複数行数</td>
                                <td class="style_td" width="80%"><input type="number" value="{$PLUGIN_CTRLCMT_ROWS}" placeholder="3" name="PLUGIN_CTRLCMT_ROWS"></input></td>
                            </tr>
                            <tr>
                                <th style="text-align:center;height:20px;" colspan="2" class="style_th">スパム対策設定</th>
                            </tr>
                            <tr>
                                <td class="style_td" width="20%">IPアドレス/コメントの保存</td>
                                <td class="style_td" width="80%">
                                    <select name="PLUGIN_CTRLCMT_SAVEIP" size="1">
                                        <option value="false" {$PLUGIN_CTRLCMT_SAVEIP_FALSE}>無効</option>
                                        <option value="true" {$PLUGIN_CTRLCMT_SAVEIP_TRUE}>有効</option>
                                    </select>
                                    <br />
                                    保存されたログはコメントログで確認できます。
                                </td>
                            </tr>
                            <tr>
                                <td class="style_td" width="20%">ブラックIPリスト</td>
                                <td class="style_td" width="80%">
                                    <input type="text" value="{$PLUGIN_CTRLCMT_BLACKIPLIST}" placeholder="127.0.0.1" name="PLUGIN_CTRLCMT_BLACKIPLIST"></input>
                                    <br />
                                    ブラックIPリストに入れられたIPアドレスの使用者はエラーメッセージが表示され、CtrlCmtでコメントができなくなります。
                                    <br />
                                    「,」で複数指定が可能。
                                </td>
                            </tr>
                            <tr>
                                <td class="style_td" width="20%">自作自演防止ID生成機能</td>
                                <td class="style_td" width="80%">
                                    <select name="PLUGIN_CTRLCMT_IPCRY" size="1">
                                        <option value="false" {$PLUGIN_CTRLCMT_IPCRY_FALSE}>無効</option>
                                        <option value="true" {$PLUGIN_CTRLCMT_IPCRY_TRUE}>有効</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th style="text-align:center;height:20px;" colspan="2" class="style_th">禁止ワード設定</th>
                            </tr>
                            <tr>
                                <td class="style_td" width="20%">NGワード対策モード</td>
                                <td class="style_td" width="80%">
                                    <select name="PLUGIN_CTRLCMT_NGWORD_MODE" size="1">
                                        <option value="false" {$PLUGIN_CTRLCMT_NGWORD_MODE_FALSE}>無効</option>
                                        <option value="hide" {$PLUGIN_CTRLCMT_NGWORD_MODE_HIDE}>マスク</option>
                                        <option value="stop" {$PLUGIN_CTRLCMT_NGWORD_MODE_STOP}>エラー</option>
                                    </select>
                                    <br />
                                    マスク...NGワードが存在する場合は、NGワードを「NGワードのマスク」で設定した文字列に置き換えられる。
                                    <br />
                                    エラー...NGワードが存在する場合は、投稿を拒否し、エラー画面を表示する。
                                </td>
                            </tr>
                            <tr>
                                <td class="style_td" width="20%">NGワード</td>
                                <td class="style_td" width="80%">
                                    <input type="text" value="{$PLUGIN_CTRLCMT_NGWORD}" placeholder="ちん(こ|ちん),死ね" name="PLUGIN_CTRLCMT_NGWORD"></input>
                                    <br />
                                    正規表現の利用が可能。「,」で複数指定が可能。
                                </td>
                            </tr>
                            <tr>
                                <td class="style_td" width="20%">NGワードのマスク</td>
                                <td class="style_td" width="80%">
                                    <input type="text" value="{$PLUGIN_CTRLCMT_NGWORD_MASK}" placeholder="＊" name="PLUGIN_CTRLCMT_NGWORD_MASK"></input>
                                    <br />
                                    NGワード対策モードがマスクの場合のみ、使われる。
                                </td>
                            </tr>
                            <tr>
                                <td style="text-align:CENTER" colspan="2" class="style_td">
                                    <input class="form_button" type="submit" value="設定を保存する" name="setting_save" id="setting_save"></input>
                                </td>
                           </tr>
                        </tbody>
                    </table>
                </form>
                ※入力フォームでは「'」や「"」の特殊文字が使えない場合がございます。
                <br />
                　どうしても利用したい場合は文字コードを利用するか、直接ctrlcmt.inc.phpを編集してください。
            </div>
            <div class="tabContent">
                <table class="style_table" cellspacing="1" border="0" width="100%">
                    <tbody>
                        <tr>
                            <th style="text-align:center;height:20px;" colspan="2" class="style_th">コメントログ</th>
                        </tr>
                        {$commentlogs}
                    </tbody>
                </table>
            </div>
            {$addons_form}
            <div class="tabContent">
                <form method="post" action="?plugin=ctrlcmt">
                    <input type="hidden" name="encode_hint" value="ぷ" />
                    <input type="hidden" name="logout" value="true" />
                    <input type="submit" value="ログアウト" />
                </form>
            </div>
            <script>
            \$('.form_button').click(function() {
                document.getElementById("form_result").innerHTML = "設定を保存しました。(" + new Date()+")";
            })
            </script>
            EOD;
            return array('msg' => $msg, 'body' => $body);
        }
        $body = <<<EOD
        <h3>コントロールパネルへログイン</h3>
        <form method="post" action="?plugin=ctrlcmt">
            <input type="hidden" name="encode_hint" value="ぷ" />
            管理者パスワード:<input type="password" name="ctrlcmt_loginpass"></input> <input type="submit" value="ログイン" />
        </form>
        EOD;
        return array('msg' => $msg, 'body' => $body);
    }
}

function plugin_ctrlcmt_inline()
{
	global $vars, $digest, $_btn_comment, $_btn_name, $_msg_comment;
	static $numbers = array();
	static $comment_cols = PLUGIN_CTRLCMT_SIZE_MSG;
    static $comment_rows = PLUGIN_CTRLCMT_ROWS;
	if (PKWK_READONLY) return ''; // Show nothing

	$page = $vars['page'];
	if (! isset($numbers[$page])) $numbers[$page] = 0;
	$comment_no = $numbers[$page]++;

	$options = func_num_args() ? func_get_args() : array();
	if (in_array('noname', $options)) {
		$nametags = '<label for="_p_comment_comment_' . $comment_no . '">' .
			$_msg_comment . '</label>';
	} else {
		$nametags = '<label for="_p_comment_name_' . $comment_no . '">' .
			$_btn_name . '</label>' .
			'<input type="text" name="name" id="_p_comment_name_' .
			$comment_no .  '" size="' . PLUGIN_CTRLCMT_SIZE_NAME .
			'" />' . "\n";
	}
	$nodate = in_array('nodate', $options) ? '1' : '0';
	$above  = in_array('above',  $options) ? '1' :
		(in_array('below', $options) ? '0' : PLUGIN_CTRLCMT_DIRECTION_DEFAULT);

	$script = get_page_uri($page);
	$s_page = htmlsc($page);
    if (PLUGIN_CTRLCMT_MULTILINE == true){
        $msg_input_element = "<br />\n<textarea name=\"msg\" id=\"_p_comment_comment_" . $comment_no . "\" rows=\"".$comment_rows."\" cols=\"" . $comment_cols . "\"></textarea>\n<br />\n";
    }else{
        $msg_input_element = '<input type="text"   name="msg" id="_p_comment_comment_' . $comment_no . '"
   size="' . $comment_cols . '" required />';
    }
	$string = <<<EOD
<br />
<form action="$script" method="post" class="_p_comment_form">
 <div>
  <input type="hidden" name="do" value="comment" />
  <input type="hidden" name="plugin" value="ctrlcmt" />
  <input type="hidden" name="refer"  value="$s_page" />
  <input type="hidden" name="ctrlcmt_no" value="$comment_no" />
  <input type="hidden" name="nodate" value="$nodate" />
  <input type="hidden" name="above"  value="$above" />
  <input type="hidden" name="digest" value="$digest" />
  $nametags
  {$msg_input_element}
  <input type="submit" name="ctrlcmt" value="$_btn_comment" />
 </div>
</form>
EOD;
        

        return $string;
    }
    
