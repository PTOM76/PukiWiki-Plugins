<?php
/*
$Id: timestamp.inc.php,v 1.03 2024/12/07 16:38:00 Pitan Exp $
License: GPL v3
*/
//----(設定)

// 1(Enable) / 0(Disable)
define("PLUGIN_TIMESTAMP_CHECK_EDITABLE", "1"); //編集権限、凍結をチェックしてタイムスタンプをする。(凍結確認、ページ権限確認)
define("PLUGIN_TIMESTAMP_CHECK_READABLE", "1"); //閲覧権限をチェックしてタイムスタンプをする。(ページ権限確認)
define("PLUGIN_TIMESTAMP_UESR_ADMIN_ONLY", "1"); //管理者パスワード入力フォーム設置

define("PLUGIN_TIMESTAMP_ADD_HOURS", ""); //時間のズレに対応

//----(ここまで)

define("PLUGIN_TIMESTAMP_TITLE", "タイムスタンプ");

function plugin_timestamp_action() {
	global $vars;
	if (isset($vars["plugin_timestamp_form_submit"]) && is_pagename($vars["plugin_timestamp_form_page"])) {
		if (isset($vars["plugin_timestamp_admin_password"]) && PLUGIN_TIMESTAMP_UESR_ADMIN_ONLY == "1") {
			if (pkwk_login($vars["plugin_timestamp_admin_password"]) == false) {
				return array('msg' => PLUGIN_TIMESTAMP_TITLE, 'body' => "パスワードが間違っています。");
			}
		}
		$filename = "./wiki/" . strtoupper(bin2hex($vars['plugin_timestamp_form_page'])) . ".txt";
		if (file_exists($filename)) {
			if (PLUGIN_TIMESTAMP_CHECK_EDITABLE == "1")
				check_editable($vars['plugin_timestamp_form_page'], true, true);
			if (PLUGIN_TIMESTAMP_CHECK_READABLE == "1")
				check_readable($vars['plugin_timestamp_form_page'], true, true);

			$date = new DateTime($vars["plugin_timestamp_form_y"] . '-' . $vars["plugin_timestamp_form_m"] . '-' . $vars["plugin_timestamp_form_d"] . ' ' . $vars["plugin_timestamp_form_h"] . ':' . $vars["plugin_timestamp_form_min"] . ':' . $vars["plugin_timestamp_form_s"]);

			//PukiWiki 1.5.x～用
			$filedata = file_get_contents($filename);
			if (preg_match('/\#author\((.+?),(.+?),(.+?)\)/u', $filedata, $matchs)) {
				$filedata = str_replace($matchs[0], '#author("' . $vars["plugin_timestamp_form_y"] . '-' . $vars["plugin_timestamp_form_m"] . '-' . $vars["plugin_timestamp_form_d"] . 'T' . $vars["plugin_timestamp_form_h"] . ':' . $vars["plugin_timestamp_form_min"] . ':' . $vars["plugin_timestamp_form_s"] . '+09:00",' . $matchs[2] . ',' . $matchs[3] . ')', $filedata);
				file_put_contents($filename, $filedata);
			}

			//キャッシュ削除
			unlink('./cache/recent.dat');

			touch($filename, $date->format('U') - (int)PLUGIN_TIMESTAMP_ADD_HOURS);
			$msg = PLUGIN_TIMESTAMP_TITLE;
			$body = "設定が完了しました。<a href=\"?" . $vars["plugin_timestamp_form_page"] . "\">" . $vars["plugin_timestamp_form_page"] . "へ戻る</a>";
			}else{
			$msg = PLUGIN_TIMESTAMP_TITLE;
			$body = "ページが存在しません。";
		}
	} else {
		$msg = PLUGIN_TIMESTAMP_TITLE;
		if ($vars['page'] != "") {
			if (PLUGIN_TIMESTAMP_CHECK_EDITABLE == "1")
				check_editable($vars['page'], true, true);
			if (PLUGIN_TIMESTAMP_CHECK_READABLE == "1")
				check_readable($vars['page'], true, true);
			$filename = "./wiki/" . strtoupper(bin2hex($vars['page'])) . ".txt";
			$filetime = filemtime($filename) + (int)PLUGIN_TIMESTAMP_ADD_HOURS;
			$y = date("Y", $filetime);
			$m = date("m", $filetime);
			$d = date("d", $filetime);
			$h = date("H", $filetime);
			$min = date("i", $filetime);
			$s = date("s", $filetime);
			if (PLUGIN_TIMESTAMP_UESR_ADMIN_ONLY == "1") {
				$admin_check = "<br />管理者パスワード:<input type=\"password\" style=\"width: 100px\" name=\"plugin_timestamp_admin_password\" />";
			} else {
				$admin_check = "";
			}
			$body = <<<EOD
			<h2>ページ: {$vars['page']}</h2>
			<form action="?plugin=timestamp" method="POST">
				<input type="text" style="width: 30px" name="plugin_timestamp_form_y" value="{$y}" />年
				<input type="text" style="width: 15px" name="plugin_timestamp_form_m" value="{$m}" />月
				<input type="text" style="width: 15px" name="plugin_timestamp_form_d" value="{$d}" />日
				<input type="text" style="width: 15px" name="plugin_timestamp_form_h" value="{$h}" />時
				<input type="text" style="width: 15px" name="plugin_timestamp_form_min" value="{$min}" />分
				<input type="text" style="width: 15px" name="plugin_timestamp_form_s" value="{$s}" />秒
				<input type="hidden" name="plugin_timestamp_form_page" value="{$vars['page']}" />
				{$admin_check}
				<input type="submit" name="plugin_timestamp_form_submit" value="変更" />
			</form>
			現在のページの更新日時: {$y}年{$m}月{$d}日{$h}時{$min}分{$s}秒
EOD;
		} else {
			$body = <<<EOD
			<h2>ページが指定されていません。</h2>
			<br />
			<form action="?plugin=timestamp" method="POST">
				ページ: <input type="text" name="page" />
				<input type="submit" name="plugin_timestamp_form_submit" value="タイムスタンプ" />
			</form>
EOD;
		}
	}
	return array('msg' => $msg, 'body' => $body);
}

function plugin_timestamp_convert() {
	global $vars;
	return "<a href=\"?plugin=timestamp&page={$vars['page']}\"><font size=\"2px\">[タイムスタンプ変更]</font></a>";
}
