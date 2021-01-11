<?php
// PukiWiki - Yet another WikiWikiWeb clone
// article.inc.php
// Copyright
//   2002-2020 PukiWiki Development Team
//   2002      Originally written by OKAWARA,Satoshi <kawara@dml.co.jp>
//             http://www.dml.co.jp/~kawara/pukiwiki/pukiwiki.php
// License: GPL v2 or (at your option) any later version
//
// article: BBS-like plugin

 /*
 メッセージを変更したい場合はLANGUAGEファイルに下記の値を追加してからご使用ください
	$_btn_name    = 'お名前';
	$_btn_article = '記事の投稿';
	$_btn_subject = '題名: ';

 ※$_btn_nameはcommentプラグインで既に設定されている場合があります

 投稿内容の自動メール転送機能をご使用になりたい場合は
 -投稿内容のメール自動配信
 -投稿内容のメール自動配信先
 を設定の上、ご使用ください。

 */

define('PLUGIN_MLARTICLE_COLS',	70); // テキストエリアのカラム数
define('PLUGIN_MLARTICLE_ROWS',	 5); // テキストエリアの行数
define('PLUGIN_MLARTICLE_NAME_COLS',	24); // 名前テキストエリアのカラム数
define('PLUGIN_MLARTICLE_SUBJECT_COLS',	57); // 題名テキストエリアのカラム数
define('PLUGIN_MLARTICLE_NAME_FORMAT',	'[[$name]]'); // 名前の挿入フォーマット
define('PLUGIN_MLARTICLE_SUBJECT_FORMAT',	'**$subject'); // 題名の挿入フォーマット

define('PLUGIN_MLARTICLE_INS',	0); // 挿入する位置 1:欄の前 0:欄の後
define('PLUGIN_MLARTICLE_COMMENT',	1); // 書き込みの下に一行コメントを入れる 1:入れる 0:入れない
define('PLUGIN_MLARTICLE_AUTO_BR',	1); // 改行を自動的変換 1:する 0:しない

define('PLUGIN_MLARTICLE_MAIL_AUTO_SEND',	0); // 投稿内容のメール自動配信 1:する 0:しない
define('PLUGIN_MLARTICLE_MAIL_FROM',	''); // 投稿内容のメール送信時の送信者メールアドレス
define('PLUGIN_MLARTICLE_MAIL_SUBJECT_PREFIX', "[someone's PukiWiki]"); // 投稿内容のメール送信時の題名

// 投稿内容のメール自動配信先
global $_plugin_mlarticle_mailto;
$_plugin_mlarticle_mailto = array (
	''
);

function plugin_mlarticle_action()
{
	global $post, $vars, $cols, $rows, $now;
	global $_title_collided, $_msg_collided, $_title_updated;
	global $_plugin_mlarticle_mailto, $_no_subject, $_no_name;
	global $_msg_article_mail_sender, $_msg_article_mail_page;

	$script = get_base_uri();
	if (PKWK_READONLY) die_message('PKWK_READONLY prohibits editing');

	if ($post['msg'] == '')
		return array('msg'=>'','body'=>'');

	$name = ($post['name'] == '') ? $_no_name : $post['name'];
	$name = ($name == '') ? '' : str_replace('$name', $name, PLUGIN_MLARTICLE_NAME_FORMAT);
	$subject = ($post['subject'] == '') ? $_no_subject : $post['subject'];
	$subject = ($subject == '') ? '' : str_replace('$subject', $subject, PLUGIN_MLARTICLE_SUBJECT_FORMAT);
	$article  = $subject . "\n" . '>' . $name . ' (' . $now . ')~' . "\n" . '~' . "\n";

	$msg = rtrim($post['msg']);
	if (PLUGIN_MLARTICLE_AUTO_BR) {
		//改行の取り扱いはけっこう厄介。特にURLが絡んだときは…
		//コメント行、整形済み行には~をつけないように arino
		$msg = join("\n", preg_replace('/^(?!\/\/)(?!\s)(.*)$/', '$1~', explode("\n", $msg)));
	}
	$article .= $msg . "\n\n" . '//';

	if (PLUGIN_MLARTICLE_COMMENT) $article .= "\n\n" . '#mlcomment' . "\n";

	$postdata = '';
	$postdata_old  = get_source($post['refer']);
	$mlarticle_no = 0;

	foreach($postdata_old as $line) {
		if (! PLUGIN_MLARTICLE_INS) $postdata .= $line;
		if (preg_match('/^#mlarticle/i', $line)) {
			if ($mlarticle_no == $post['mlarticle_no'] && $post['msg'] != '')
				$postdata .= $article . "\n";
			++$mlarticle_no;
		}
		if (PLUGIN_MLARTICLE_INS) $postdata .= $line;
	}

	$postdata_input = $article . "\n";
	$body = '';

	if (md5(get_source($post['refer'], TRUE, TRUE)) !== $post['digest']) {
		$title = $_title_collided;

		$body = $_msg_collided . "\n";

		$s_refer    = htmlsc($post['refer']);
		$s_digest   = htmlsc($post['digest']);
		$s_postdata = htmlsc($postdata_input);
		$body .= <<<EOD
<form action="$script?cmd=preview" method="post">
 <div>
  <input type="hidden" name="refer" value="$s_refer" />
  <input type="hidden" name="digest" value="$s_digest" />
  <textarea name="msg" rows="$rows" cols="$cols" id="textarea">$s_postdata</textarea><br />
 </div>
</form>
EOD;

	} else {
		page_write($post['refer'], trim($postdata));

		// 投稿内容のメール自動送信
		if (PLUGIN_MLARTICLE_MAIL_AUTO_SEND) {
			$mailaddress = implode(',', $_plugin_mlarticle_mailto);
			$mailsubject = PLUGIN_MLARTICLE_MAIL_SUBJECT_PREFIX . ' ' . str_replace('**', '', $subject);
			if ($post['name'])
				$mailsubject .= '/' . $post['name'];
			$mailsubject = mb_encode_mimeheader($mailsubject);

			$mailbody = $post['msg'];
			$mailbody .= "\n\n" . '---' . "\n";
			$mailbody .= $_msg_article_mail_sender . $post['name'] . ' (' . $now . ')' . "\n";
			$mailbody .= $_msg_article_mail_page . $post['refer'] . "\n";
			$mailbody .= '   URL: ' . get_page_uri($post['refer'], PKWK_URI_ABSOLUTE) . "\n";
			$mailbody = mb_convert_encoding($mailbody, 'JIS');

			$mailaddheader = 'From: ' . PLUGIN_MLARTICLE_MAIL_FROM;

			mail($mailaddress, $mailsubject, $mailbody, $mailaddheader);
		}

		$title = $_title_updated;
	}
	$retvars['msg'] = $title;
	$retvars['body'] = $body;

	$post['page'] = $post['refer'];
	$vars['page'] = $post['refer'];

	return $retvars;
}

function plugin_mlarticle_convert()
{
	global $vars, $digest;
	global $_btn_article, $_btn_name;
	static $numbers = array();
    $_btn_subject = "タイトル:";

	$script = get_base_uri();
	if (PKWK_READONLY) return ''; // Show nothing

	if (! isset($numbers[$vars['page']])) $numbers[$vars['page']] = 0;

	$mlarticle_no = $numbers[$vars['page']]++;

	$s_page       = htmlsc($vars['page']);
	$s_digest     = htmlsc($digest);
	$name_cols    = PLUGIN_MLARTICLE_NAME_COLS;
	$subject_cols = PLUGIN_MLARTICLE_SUBJECT_COLS;
	$article_rows = PLUGIN_MLARTICLE_ROWS;
	$article_cols = PLUGIN_MLARTICLE_COLS;
	$string = <<<EOD
<form action="$script" method="post" class="_p_article_form">
 <div>
  <input type="hidden" name="mlarticle_no" value="$mlarticle_no" />
  <input type="hidden" name="plugin" value="mlarticle" />
  <input type="hidden" name="digest" value="$s_digest" />
  <input type="hidden" name="refer" value="$s_page" />
  <label for="_p_article_name_$mlarticle_no">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$_btn_name</label>
  <input type="text" name="name" id="_p_article_name_$mlarticle_no" size="$name_cols" /><br />
  <label for="_p_article_subject_$mlarticle_no">$_btn_subject</label>
  <input type="text" name="subject" id="_p_article_subject_$mlarticle_no" size="$subject_cols" /><br />
  <textarea name="msg" rows="$article_rows" cols="$article_cols">\n</textarea><br />
  <input type="submit" name="mlarticle" value="$_btn_article" />
 </div>
</form>
EOD;

	return $string;
}
