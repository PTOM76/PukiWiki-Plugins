<?php
// $Id: timestamp_backup.inc.php,v 1.00 2020/10/28 18:09:00 K Exp $

define("PLUGIN_TIMESTAMP_BACKUP_WIKI_DIR",'./wiki/');
define("PLUGIN_TIMESTAMP_BACKUP_CACHE_DIR",'./cache/');

define("PLUGIN_TIMESTAMP_BACKUP_ADMIN_ONLY", "1");//管理者パスワード入力フォーム設置

function plugin_timestamp_backup_convert()
{
    $args = func_get_args();
    $script = get_base_uri();
    $str = <<<EOD
        <form action="?plugin=timestamp_backup" method="post">
            ページの更新日時をバックアップ
            <br />
            <input type="submit" value="バックアップ" />
        <form>
    EOD;
    return $str;
}
function plugin_timestamp_backup_inline() {
    $args = func_get_args();
	return call_user_func_array('plugin_timestamp_backup_convert', $args);
}
function plugin_timestamp_backup_action() {
    global $vars;
    if ((isset($vars["plugin_timeStamp_backup_admin_password"]))&&(PLUGIN_TIMESTAMP_BACKUP_ADMIN_ONLY=="1")){
        if (pkwk_login($vars["plugin_timeStamp_backup_admin_password"]) == false){
            return array('msg'=>"ページ更新日時のバックアップ", 'body'=>"パスワードが間違っています。");exit;
        }
    }
    
    if ($vars['c']=="download"){
        $filedata = null;
        foreach(glob(PLUGIN_TIMESTAMP_BACKUP_WIKI_DIR.'{*.txt}',GLOB_BRACE) as $file){
            if(is_file($file)){
                $filedata .= '"filename":"'.basename($file).'","time":"'.filemtime($file)."\"\n";
            }
        }
        file_put_contents(PLUGIN_TIMESTAMP_BACKUP_CACHE_DIR.'timestamp_backup.txt',$filedata);
        plugin_timestamp_backup_download(PLUGIN_TIMESTAMP_BACKUP_CACHE_DIR.'timestamp_backup.txt');
    }
    if ($vars['c']=="upload"){
        $uploaded_file = $_FILES['upload_file']['tmp_name'];
        $uploaded_filedata = explode("\n", file_get_contents($uploaded_file));
        $cnt = count($uploaded_filedata);
        for( $i=0;$i<$cnt;$i++ )
        {
            if (preg_match("/\"filename\":\"(.+?)\",\"time\":\"(.+?)\"/u",$uploaded_filedata[$i],$matches)){
                touch(PLUGIN_TIMESTAMP_BACKUP_WIKI_DIR.$matches[1],$matches[2]);
                //--------
                //キャッシュ削除
                unlink(PLUGIN_TIMESTAMP_BACKUP_CACHE_DIR.'recent.dat');//recent.dat
                //--------
            }
        }
    }
    if (PLUGIN_TIMESTAMP_BACKUP_ADMIN_ONLY=="1"){
        $admin_check = "<br />管理者パスワード:<input type=\"password\" style=\"width: 100px\" name=\"plugin_timeStamp_backup_admin_password\" />";
    }else{
        $admin_check = "";
    }
    $msg='ページ更新日時のバックアップ';
    $body='
    <h2>ページ更新日時のバックアップ</h2>
    <ul class="list1 list-indent1"><li>Wikiのドメイン、ディレクトリ、サーバーなどの引っ越しの際に更新日時を保つ為に、Wiki引っ越し前にダウンロードし、Wiki引っ越し後にアップロードして利用します。</li></ul>
    <h3>復元用ファイルのダウンロード</h3>
    <ul class="list1 list-indent1"><li>復元用ファイルをダウンロードして更新日時を保存します。</li></ul>
    <form action="?plugin=timestamp_backup&c=download" method="post">'.$admin_check.'
    <input type="submit" value="ダウンロード" /></form>
    <br />
    <h3>復元用ファイルのアップロード</h3>
    <ul class="list1 list-indent1"><li>復元用ファイルをアップロードして更新日時を復元します。</li></ul>
    <form enctype="multipart/form-data"  action="./?plugin=timestamp_backup&c=upload" method="post">
        <input name="upload_file" type="file" />
        '.$admin_check.'
        <input type="submit" value="アップロード" />
    </form>
    <br />
    ';
    return array('msg'=>$msg, 'body'=>$body);
}
function plugin_timestamp_backup_download($pPath, $pMimeType = null)
{
    if (!is_readable($pPath)) { die($pPath); }
    $mimeType = (isset($pMimeType)) ? $pMimeType
                                    : (new finfo(FILEINFO_MIME_TYPE))->file($pPath);
    if (!preg_match('/\A\S+?\/\S+/', $mimeType)) {
        $mimeType = 'application/octet-stream';
    }
    header('Content-Type: ' . $mimeType);
    header('X-Content-Type-Options: nosniff');
    header('Content-Length: ' . filesize($pPath));
    header('Content-Disposition: attachment; filename="' . basename($pPath) . '"');
    header('Connection: close');
    while (ob_get_level()) { ob_end_clean(); }
    readfile($pPath);
    exit;
}
?>