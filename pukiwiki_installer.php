<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <style>
        h1, h2 {
          font-family:verdana, arial, helvetica, Sans-Serif;
          color:inherit;
          background-color:#DDEEFF;
          padding:.3em;
          border:0;
          margin:0 0 .5em 0;
        }
        h3 {
          font-family:verdana, arial, helvetica, Sans-Serif;
          border-bottom:3px solid #DDEEFF;
          border-top:1px solid #DDEEFF;
          border-left:10px solid #DDEEFF;
          border-right:5px solid #DDEEFF;
          color:inherit;
          background-color:#FFFFFF;
          padding:.3em;
          margin:0 0 .5em 0;
        }
        </style>
    </head>
    <body>
        <div style="padding-left:30%;padding-right:30%;">
<?php
	$tooltitle = "PukiWiki Installer v1.0";
    if(empty($_POST['v_adminpass']) && isset($_POST['install'])){
        echo "エラー: パスワードが設定されていません。";
    }elseif(isset($_POST['install'])){
        $url = $_POST['pkwk_url'];
        $fp = fopen($url, "r");
        if ($fp !== FALSE) {
            file_put_contents("./" . basename($url), "");
            while(!feof($fp)) {
                $buffer = fread($fp, 4096);
                if ($buffer !== FALSE) {
                    file_put_contents("./" . basename($url), $buffer, FILE_APPEND);
                }
            }
            fclose($fp);
        }
        if(file_exists("./" . basename($url))){
            $zip = new ZipArchive;
            if ($zip->open("./" . basename($url)) === TRUE) {
                $zip->extractTo('./');
                $zip->close();
                if(!dir_copy(pathinfo(basename($url))['filename'], __DIR__)){echo 'エラー: ディレクトリのコピーに失敗しました。もう一度試してみてください。';exit;}
                if(!remove_dir(pathinfo(basename($url))['filename'])){echo "エラー: 解凍後のディレクトリが消えていない可能性があります。手動で削除してください。";}
                if($_POST['del_tempzip'] = 'on'){
                    unlink("./" . basename($url));
                }
                if($_POST['del_this'] = 'on'){
                    unlink(__FILE__);
                }
                pkwk_ini_replace();
                ?>
<h2><?php echo $tooltitle; ?></h2>
            <h3>インストールが完了しました。</h3>
            <a href="./">インストールしたPukiWikiへ移動</a>
        </div>
    </body>
</html>
                <?php exit;
            } else {
                echo 'エラー: ZIPの展開に失敗しました。もう一度試してみてください。';
            }
        }
    }
    function dir_copy($dir_name, $new_dir) {
        if (!is_dir($new_dir)) {
            mkdir($new_dir);
        }
     
        if (is_dir($dir_name)) {
            if ($dh = opendir($dir_name)) {
                while (($file = readdir($dh)) !== false) {
                    if ($file == "." || $file == "..") {
                        continue;
                    }
                    if (is_dir($dir_name . "/" . $file)) {
                        dir_copy($dir_name . "/" . $file, $new_dir . "/" . $file);
                    }
                    else {
                        copy($dir_name . "/" . $file, $new_dir . "/" . $file);
                    }
                }
                closedir($dh);
            }
        }
        return true;
    }
    function remove_dir($dir) {
        $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
            if (is_dir("$dir/$file")) {
                remove_dir("$dir/$file");
            } else {
                unlink("$dir/$file");
            }
        }
        return rmdir($dir);
    }
    function pkwk_ini_replace() {
        $result = true;
        $data = file_get_contents("pukiwiki.ini.php");
        $data = preg_replace('/^(\s*?)\$page_title(\s*?)=(\s*?)\'.*?\'(\s*?);/m', '$1\$page_title$2=$3\'' . htmlspecialchars($_POST['v_page_title']) . '\'$4;', $data);
        $data = preg_replace('/^(\s*?)\$modifier(\s*?)=(\s*?)\'.*?\'(\s*?);/m', '$1\$modifier$2=$3\'' . htmlspecialchars($_POST['v_modifier']) . '\'$4;', $data);
        $data = preg_replace('/^(\s*?)\$modifierlink(\s*?)=(\s*?)\'.*?\'(\s*?);/m', '$1\$modifierlink$2=$3\'' . htmlspecialchars($_POST['v_modifierlink']) . '\'$4;', $data);
        $data = preg_replace('/^(\s*?)\$adminpass(\s*?)=(\s*?)\'.*?\'(\s*?);/m', '$1\$adminpass$2=$3\'{x-php-md5}' . md5($_POST['v_adminpass']) . '\'$4;', $data);
        file_put_contents("pukiwiki.ini.php", $data);
        return $result;
    }
?>

            <h2><?php echo $tooltitle; ?></h2>
            <p><a href="https://pukiwiki.osdn.jp/">PukiWiki</a>のインストーラーです。</p>
            <p>※PukiWikiは、pukiwiki_installer.phpが設置されているところと同じ場所にインストールされます。</p>
            <form method="post">
                <h3>設定</h3>
                タイトル名: <input type="text" name="v_page_title" value="PukiWiki" />
                <br />
                管理者名: <input type="text" name="v_modifier" value="anonymous" />
                <br />
                Webページ: <input type="text" name="v_modifierlink" value="http://pukiwiki.example.com/" />
                <br />
                パスワード: <input type="password" name="v_adminpass" value="" />
                <br /><br />
                ダウンロードしたZIPを削除する: <input type="checkbox" name="del_tempzip" value="" checked />
                <br />
                PukiWiki Installerを削除する: <input type="checkbox" name="del_this" value="" checked />
                <h3>インストール</h3>
                バージョン: <select name="pkwk_url" size="1">
                    <option value="https://github.com/pukiwiki/pukiwiki/archive/2cf98c47a812b551b49500971fd75964e59a7dee.zip" selected>1.5.4</option>
                    <option value="https://ja.osdn.net/frs/redir.php?f=pukiwiki/72656/pukiwiki-1.5.3_utf8.zip">1.5.3</option>
                    <option value="https://ja.osdn.net/frs/redir.php?f=pukiwiki/69652/pukiwiki-1.5.2_utf8.zip">1.5.2</option>
                    <option value="https://ja.osdn.net/frs/redir.php?f=pukiwiki/64807/pukiwiki-1.5.1_utf8.zip">1.5.1</option>
                    <option value="https://ja.osdn.net/frs/redir.php?f=pukiwiki/61634/pukiwiki-1_5_0_utf8.zip">1.5.0</option>
                    <option value="https://ja.osdn.net/frs/redir.php?f=pukiwiki/12957/pukiwiki-1.4.7_notb_utf8.zip">1.4.7</option>
                    <option value="https://web.archive.org/web/20210228080730/https://jaist.dl.osdn.jp/pukiwiki/72656/pukiwiki-1.5.3_utf8.zip">1.5.3(Archive)</option>
                </select>
                <br />
                <input type="submit" value="インストール" name="install" />
            </form>
        </div>
    </body>
</html>