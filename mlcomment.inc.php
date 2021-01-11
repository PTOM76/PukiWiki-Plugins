<?php

// $Id: mlcomment.inc.php,v 1.2 2020/11/29 12:00:00 K Exp $

define('PLUGIN_MLCOMMENT_DIRECTION_DEFAULT', '1'); // 1: above 0: below
define('PLUGIN_MLCOMMENT_SIZE_COLS',  70);
define('PLUGIN_MLCOMMENT_SIZE_ROWS',  3);
define('PLUGIN_MLCOMMENT_SIZE_NAME', 15);

// ----
define('PLUGIN_MLCOMMENT_FORMAT_MSG',  '$msg');
define('PLUGIN_MLCOMMENT_FORMAT_NAME', '[[$name]]');
define('PLUGIN_MLCOMMENT_FORMAT_NOW',  '&new{$now};');
define('PLUGIN_MLCOMMENT_FORMAT_STRING', "\x08MSG\x08 -- \x08NAME\x08 \x08NOW\x08");

function plugin_mlcomment_action()
{
	global $vars, $now, $_title_updated, $_no_name;
	global $_msg_comment_collided, $_title_comment_collided;
	global $_comment_plugin_fail_msg;

	if (PKWK_READONLY) die_message('PKWK_READONLY prohibits editing');

	if (! isset($vars['msg'])) return array('msg'=>'', 'body'=>''); // Do nothing
    $vars['msg'] = preg_replace("/\r\n|\r|\n/", "&br;", $vars['msg']);
	$head = '';
	$match = array();
	if (preg_match('/^(-{1,2})-*\s*(.*)/', $vars['msg'], $match)) {
		$head        = & $match[1];
		$vars['msg'] = & $match[2];
	}
	if ($vars['msg'] == '') return array('msg'=>'', 'body'=>''); // Do nothing
	$comment  = str_replace('$msg', $vars['msg'], PLUGIN_MLCOMMENT_FORMAT_MSG);
	if(isset($vars['name']) || ($vars['nodate'] != '1')) {
		$_name = (! isset($vars['name']) || $vars['name'] == '') ? $_no_name : $vars['name'];
		$_name = ($_name == '') ? '' : str_replace('$name', $_name, PLUGIN_MLCOMMENT_FORMAT_NAME);
		$_now  = ($vars['nodate'] == '1') ? '' :
			str_replace('$now', $now, PLUGIN_MLCOMMENT_FORMAT_NOW);
		$comment = str_replace("\x08MSG\x08",  $comment, PLUGIN_MLCOMMENT_FORMAT_STRING);
		$comment = str_replace("\x08NAME\x08", $_name, $comment);
		$comment = str_replace("\x08NOW\x08",  $_now,  $comment);
	}
	$comment = '-' . $head . ' ' . $comment;

	$postdata    = '';
	$mlcomment_no  = 0;
	$above       = (isset($vars['above']) && $vars['above'] == '1');
	$comment_added = FALSE;
	foreach (get_source($vars['refer']) as $line) {
		if (! $above) $postdata .= $line;
		if (preg_match('/^#mlcomment/i', $line) && $mlcomment_no++ == $vars['mlcomment_no']) {
			$comment_added = TRUE;
			if ($above) {
				$postdata = rtrim($postdata) . "\n" .
					$comment . "\n" .
					"\n";  // Insert one blank line above #commment, to avoid indentation
			} else {
				$postdata = rtrim($postdata) . "\n" .
					$comment . "\n";
			}
		}
		if ($above) $postdata .= $line;
	}
	$title = $_title_updated;
	$body = '';
	if ($comment_added) {
		// new comment added
		if (md5(get_source($vars['refer'], TRUE, TRUE)) !== $vars['digest']) {
			$title = $_title_comment_collided;
			$body  = $_msg_comment_collided . make_pagelink($vars['refer']);
		}
		page_write($vars['refer'], $postdata);
	} else {
		// failed to add the comment
		$title = $_title_comment_collided;
		$body  = $_comment_plugin_fail_msg . make_pagelink($vars['refer']);
	}
	$retvars['msg']  = $title;
	$retvars['body'] = $body;
	$vars['page'] = $vars['refer'];
	return $retvars;
}

function plugin_mlcomment_convert()
{
	global $vars, $digest, $_btn_comment, $_btn_name, $_msg_comment;
	static $numbers = array();
	static $mlcomment_cols = PLUGIN_MLCOMMENT_SIZE_COLS;
	static $mlcomment_rows = PLUGIN_MLCOMMENT_SIZE_ROWS;

	if (PKWK_READONLY) return ''; // Show nothing

	$page = $vars['page'];
	if (! isset($numbers[$page])) $numbers[$page] = 0;
	$mlcomment_no = $numbers[$page]++;

	$options = func_num_args() ? func_get_args() : array();
	if (in_array('noname', $options)) {
		$nametags = '<label for="_p_comment_comment_' . $mlcomment_no . '">' .
			$_msg_comment . '</label>';
	} else {
		$nametags = 
			'<input type="text" name="name" id="_p_comment_name_' .
			$mlcomment_no .  '" placeholder="お名前" size="' . PLUGIN_MLCOMMENT_SIZE_NAME .
			'" />' . "\n";
	}
	$nodate = in_array('nodate', $options) ? '1' : '0';
	$above  = in_array('above',  $options) ? '1' :
		(in_array('below', $options) ? '0' : PLUGIN_MLCOMMENT_DIRECTION_DEFAULT);

	$script = get_page_uri($page);
	$s_page = htmlsc($page);
	$string = <<<EOD
<br />
<form action="$script" method="post" class="_p_comment_form">
 <div>
  <input type="hidden" name="plugin" value="mlcomment" />
  <input type="hidden" name="refer"  value="$s_page" />
  <input type="hidden" name="mlcomment_no" value="$mlcomment_no" />
  <input type="hidden" name="nodate" value="$nodate" />
  <input type="hidden" name="above"  value="$above" />
  <input type="hidden" name="digest" value="$digest" />
  $nametags
  <br />
  <textarea name="msg" id="_p_comment_comment_{$mlcomment_no}"
   cols="$mlcomment_cols" rows="$mlcomment_rows" required></textarea>
   <br />
  <input type="submit" name="comment" value="$_btn_comment" />
 </div>
</form>
EOD;

	return $string;
}
