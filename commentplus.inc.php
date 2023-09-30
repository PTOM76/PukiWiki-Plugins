<?php
define('PLUGIN_COMMENTplus_DIRECTION_DEFAULT', '1'); // 1: above 0: below
define('PLUGIN_COMMENTplus_SIZE_MSG',  70);
define('PLUGIN_COMMENTplus_SIZE_NAME', 15);
// ----
define('PLUGIN_COMMENTplus_FORMAT_MSG',  '$msg');
define('PLUGIN_COMMENTplus_FORMAT_NAME', '[[$name]]');
define('PLUGIN_COMMENTplus_FORMAT_NOW',  '&new{$now};');
define('PLUGIN_COMMENTplus_FORMAT_STRING', "\x08MSG\x08 -- \x08NAME\x08 \x08NOW\x08");

function plugin_commentplus_action()
{
	global $vars, $now, $_title_updated, $_no_name;
	global $_msg_commentplus_collided, $_title_commentplus_collided;
	global $_commentplus_plugin_fail_msg;

	if (PKWK_READONLY) die_message('PKWK_READONLY prohibits editing');

	if (! isset($vars['msg'])) return array('msg'=>'', 'body'=>''); // Do nothing

	$vars['msg'] = str_replace("\n", '', $vars['msg']); // Cut LFs
	$head = '';
	$match = array();
	if (preg_match('/^(-{1,2})-*\s*(.*)/', $vars['msg'], $match)) {
		$head        = & $match[1];
		$vars['msg'] = & $match[2];
	}
	if ($vars['msg'] == '') return array('msg'=>'', 'body'=>''); // Do nothing

	$commentplus  = str_replace('$msg', $vars['msg'], PLUGIN_COMMENTplus_FORMAT_MSG);
	if(isset($vars['name']) || ($vars['nodate'] != '1')) {
		$_name = (! isset($vars['name']) || $vars['name'] == '') ? $_no_name : $vars['name'];
    if ($_name == null){
      $_name = 'Anonymous';
    }
		$_name = ($_name == '') ? '' : str_replace('$name', $_name, PLUGIN_COMMENTplus_FORMAT_NAME);
		$_now  = ($vars['nodate'] == '1') ? '' :
			str_replace('$now', $now, PLUGIN_COMMENTplus_FORMAT_NOW);
		$commentplus = str_replace("\x08MSG\x08",  $commentplus, PLUGIN_COMMENTplus_FORMAT_STRING);
		$commentplus = str_replace("\x08NAME\x08", $_name, $commentplus);
		$commentplus = str_replace("\x08NOW\x08",  $_now,  $commentplus);
	}
	$commentplus = '-' . $head . ' ' . $commentplus;

	$postdata    = '';
	$commentplus_no  = 0;
	$above       = (isset($vars['above']) && $vars['above'] == '1');
	$commentplus_added = FALSE;
	foreach (get_source($vars['refer']) as $line) {
		if (! $above) $postdata .= $line;
		if (preg_match('/^#commentplus/i', $line) && $commentplus_no++ == $vars['commentplus_no']) {
			$commentplus_added = TRUE;

			if ($above) {
				$postdata = rtrim($postdata) . "\n" .
					$commentplus . "\n" .
					"\n";  // Insert one blank line above #commment, to avoid indentation
			} else {
				$postdata = rtrim($postdata) . "\n" .
					$commentplus . "\n";
			}
		}
		if ($above) $postdata .= $line;
	}
	$title = $_title_updated;
	$body = '';
	if ($commentplus_added) {
		// new commentplus added
		if (md5(get_source($vars['refer'], TRUE, TRUE)) !== $vars['digest']) {
			$title = $_title_commentplus_collided;
			$body  = $_msg_commentplus_collided . make_pagelink($vars['refer']);
		}
//            require "iplog.php";//IPLOGを入れてる場合のみ可能
//            addLog($commentplus,'コメント＋');
		page_write($vars['refer'], $postdata);
	} else {
		// failed to add the commentplus
		$title = $_title_commentplus_collided;
		$body  = $_commentplus_plugin_fail_msg . make_pagelink($vars['refer']);
	}
	$retvars['msg']  = $title;
	$retvars['body'] = $body;
	$vars['page'] = $vars['refer'];
	return $retvars;
}

function plugin_commentplus_convert()
{
	global $vars, $digest, $_btn_comment, $_btn_name, $_msg_commentplus;
	static $numbers = array();
	static $commentplus_cols = PLUGIN_COMMENTplus_SIZE_MSG;

	if (PKWK_READONLY) return ''; // Show nothing

	$page = $vars['page'];
	if (! isset($numbers[$page])) $numbers[$page] = 0;
	$commentplus_no = $numbers[$page]++;

	$options = func_num_args() ? func_get_args() : array();
	if (in_array('noname', $options)) {
		$nametags = '<label for="_p_commentplus_commentplus_' . $commentplus_no . '">' .
			$_msg_commentplus . '</label>';
	} else {
		$nametags = '<label for="_p_commentplus_name_' . $commentplus_no . '">' .
			$_btn_name . '</label>' .
			'<input type="text" name="name" id="_p_commentplus_name_' .
			$commentplus_no .  '" size="' . PLUGIN_COMMENTplus_SIZE_NAME .
			'" />' . "\n";
	}
	$nodate = in_array('nodate', $options) ? '1' : '0';
	$above  = in_array('above',  $options) ? '1' :
		(in_array('below', $options) ? '0' : PLUGIN_COMMENTplus_DIRECTION_DEFAULT);
  
	$script = get_page_uri($page);
	$s_page = htmlsc($page);
	$string = <<<EOD
<br />
<form action="$script" method="post" class="_p_commentplus_form">
 <div>
  <input type="hidden" name="plugin" value="commentplus" />
  <input type="hidden" name="refer"  value="$s_page" />
  <input type="hidden" name="commentplus_no" value="$commentplus_no" />
  <input type="hidden" name="nodate" value="$nodate" />
  <input type="hidden" name="above"  value="$above" />
  <input type="hidden" name="digest" value="$digest" />
  $nametags
  <input type="text" name="msg" id="_p_commentplus_commentplus_{$commentplus_no}"
   size="$commentplus_cols" required />
  <input type="submit" name="commentplus" value="$_btn_comment" />
 </div>
</form>
<script>
function inputToCommentplusArea(input_text) {
   document.getElementById('_p_commentplus_commentplus_{$commentplus_no}').value += input_text;
}
function inputToCommentplusArea2(input_text1,input_text2,input_text3) {
   size = window.prompt("サイズ:", "10");
   text1 = window.prompt("テキスト:", "");
   document.getElementById('_p_commentplus_commentplus_{$commentplus_no}').value += input_text1 + size + input_text2 + text1 + input_text3;
}
function inputToCommentplusAreaURL(input_text1,input_text2,input_text3) {
   text1 = window.prompt("テキスト:", "");
   url1 = window.prompt("URL:", "http://");
   document.getElementById('_p_commentplus_commentplus_{$commentplus_no}').value += "[["+text1+">"+url1+"]]";
}

</script>
<a href="javascript:inputToCommentplusArea('&br;')">[改行]</a>&nbsp;
<a href="javascript:inputToCommentplusArea('&attachref();')">[添付]</a>&nbsp;
<a href="javascript:inputToCommentplusArea2('&size(','){','};')">[サイズ]</a>&nbsp;
<a href="javascript:inputToCommentplusAreaURL()">[URL]</a>&nbsp;
<a href="javascript:inputToCommentplusArea('&smile&#59;')"><img src="./image/face/smile.png"/></a>&nbsp;
<a href="javascript:inputToCommentplusArea('&bigsmile&#59;')"><img src="./image/face/bigsmile.png"/></a>&nbsp;
<a href="javascript:inputToCommentplusArea('&huh&#59;')"><img src="./image/face/huh.png"/></a>&nbsp;
<a href="javascript:inputToCommentplusArea('&oh&#59;')"><img src="./image/face/oh.png"/></a>&nbsp;
<a href="javascript:inputToCommentplusArea('&wink&#59;')"><img src="./image/face/wink.png"/></a>&nbsp;
<a href="javascript:inputToCommentplusArea('&sad&#59;')"><img src="./image/face/sad.png"/></a>&nbsp;
<a href="javascript:inputToCommentplusArea('&heart&#59;')"><img src="./image/face/heart.png"/></a>&nbsp;
EOD;
//顔文字↑
	return $string;
}