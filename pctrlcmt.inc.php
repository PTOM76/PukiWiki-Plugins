<?php
// $Id: pctrlcmt.inc.php,v 1.0 2023/10/01 15:11:00 Pitan Exp $

// need plugins: ctrlcmt.inc.php, pcomment.inc.php
// オプションはctrlcmt.inc.phpとpcomment.inc.phpを参照する

function plugin_pctrlcmt_action()
{
	global $vars;

	if (PKWK_READONLY) die_message('PKWK_READONLY prohibits editing');
    
    if (!exist_plugin("pcomment")) die_message("pcomment plugin is not found");
    if (!exist_plugin("ctrlcmt")) die_message("ctrlcmt plugin is not found");
    do_plugin_init("pcomment");
    do_plugin_init("ctrlcmt");

	if (! isset($vars['msg']) || $vars['msg'] == '') return array();
	$refer = isset($vars['refer']) ? $vars['refer'] : '';

	$retval = plugin_pctrlcmt_insert();
	if ($retval['collided']) {
		$vars['page'] = $refer;
		return $retval;
	}

	pkwk_headers_sent();
	header('Location: ' . get_page_uri($refer, PKWK_URI_ROOT));
	exit;
}

function plugin_pctrlcmt_convert()
{
	global $vars;
	global $_pcmt_messages;
    
    if (!exist_plugin("pcomment")) return "pcomment plugin is not found";
    if (!exist_plugin("ctrlcmt")) return "ctrlcmt plugin is not found";
    do_plugin_init("pcomment");
    do_plugin_init("ctrlcmt");

	$params = array(
		'noname'=>FALSE,
		'nodate'=>FALSE,
		'below' =>FALSE,
		'above' =>FALSE,
		'reply' =>FALSE,
		'_args' =>array()
	);

	foreach (func_get_args() as $arg)
		plugin_pcomment_check_arg($arg, $params);

	$vars_page = isset($vars['page']) ? $vars['page'] : '';
	if (isset($params['_args'][0]) && $params['_args'][0] != '') {
		$page = $params['_args'][0];
	} else {
		$raw_vars_page = strip_bracket($vars_page);
		$page = sprintf(PLUGIN_PCOMMENT_PAGE, $raw_vars_page);
		$raw_page = strip_bracket($page);
		if (!is_page($raw_page)) {
			// If the page doesn't exist, search backward-compatible page
			// If only compatible page exists, set the page as comment target
			$page_compat = sprintf(PLUGIN_PCOMMENT_PAGE_COMPATIBLE, $raw_vars_page);
			if (is_page(strip_bracket($page_compat))) {
				$page = $page_compat;
			}
		}
	}
	$count = isset($params['_args'][1]) ? intval($params['_args'][1]) : 0;
	if ($count == 0) $count = PLUGIN_PCOMMENT_NUM_COMMENTS;

	$_page = get_fullname(strip_bracket($page), $vars_page);
	if (!is_pagename($_page))
		return sprintf($_pcmt_messages['err_pagename'], htmlsc($_page));

	$dir = PLUGIN_CTRLCMT_DIRECTION_DEFAULT;
	if ($params['below']) {
		$dir = 0;
	} elseif ($params['above']) {
		$dir = 1;
	}

	list($comments, $digest) = plugin_pcomment_get_comments($_page, $count, $dir, $params['reply']);

	if (PKWK_READONLY) {
		$form_start = $form = $form_end = '';
	} else {
		// Show a form

		if ($params['noname']) {
			$title = $_pcmt_messages['msg_comment'];
			$name = '';
		} else {
			$title = $_pcmt_messages['btn_name'];
			$name = '<input type="text" name="name" size="' . PLUGIN_CTRLCMT_SIZE_NAME . '" />';
		}

		$radio   = $params['reply'] ?
			'<input type="radio" name="reply" value="0" tabindex="0" checked="checked" />' : '';
		$comment = PLUGIN_CTRLCMT_MULTILINE
         ? '<br />' . "\n" . '<textarea name="msg" rows="' . PLUGIN_CTRLCMT_ROWS . '" cols="' . PLUGIN_CTRLCMT_SIZE_MSG . '"></textarea>' . "\n" . '<br />' . "\n"
         : '<input type="text" name="msg" size="' . PLUGIN_CTRLCMT_SIZE_MSG . '" required />';
        

		$s_page   = htmlsc($page);
		$s_refer  = htmlsc($vars_page);
		$s_nodate = htmlsc($params['nodate']);

		$form_start = '<form action="' . get_base_uri() .
			'" method="post" class="_p_pcomment_form">' . "\n";
		$form = <<<EOD
  <div>
  <input type="hidden" name="digest" value="$digest" />
  <input type="hidden" name="plugin" value="pctrlcmt" />
  <input type="hidden" name="refer"  value="$s_refer" />
  <input type="hidden" name="page"   value="$s_page" />
  <input type="hidden" name="nodate" value="$s_nodate" />
  <input type="hidden" name="dir"    value="$dir" />
  <input type="hidden" name="count"  value="$count" />
  $radio $title $name $comment
  <input type="submit" value="{$_pcmt_messages['btn_comment']}" />
  </div>
EOD;
		$form_end = '</form>' . "\n";
	}

	if (! is_page($_page)) {
		$link   = make_pagelink($_page);
		$recent = $_pcmt_messages['msg_none'];
	} else {
		$msg    = ($_pcmt_messages['msg_all'] != '') ? $_pcmt_messages['msg_all'] : $_page;
		$link   = make_pagelink($_page, $msg);
		$recent = ! empty($count) ? sprintf($_pcmt_messages['msg_recent'], $count) : '';
	}

    $string = '';
	if ($dir) {
		$string = '<div>' .
			'<p>' . $recent . ' ' . $link . '</p>' . "\n" .
			$form_start .
				$comments . "\n" .
				$form .
			$form_end .
			'</div>' . "\n";
	} else {
		$string = '<div>' .
			$form_start .
				$form .
				$comments. "\n" .
			$form_end .
			'<p>' . $recent . ' ' . $link . '</p>' . "\n" .
			'</div>' . "\n";
	}
    return $string . (exist_plugin("inputtoolbar") ? "\n" . plugin_inputtoolbar_convert() : '');
}

function plugin_pctrlcmt_insert() {
	global $vars, $now, $_title_updated, $_no_name, $_pcmt_messages;
    global $_ctrlcmt_messages;

    $lang = in_array(LANG, $_ctrlcmt_messages) ? $_ctrlcmt_messages[LANG] : $_ctrlcmt_messages['ja'];
    
	$refer = isset($vars['refer']) ? $vars['refer'] : '';
	$page  = isset($vars['page'])  ? $vars['page']  : '';
	$page  = get_fullname($page, $refer);

	if (! is_pagename($page))
		return array(
			'msg' =>'Invalid page name',
			'body'=>'Cannot add comment' ,
			'collided'=>TRUE
		);

	check_editable($page, true, true);

	$ret = array('msg' => $_title_updated, 'collided' => FALSE);

    if (PLUGIN_CTRLCMT_MULTILINE)
        $vars['msg'] = preg_replace("/(\n|\r|\r\n)/", "\n&br;", $vars['msg']);
        
    $PLUGIN_CTRLCMT_BLACKIPLIST = PLUGIN_CTRLCMT_BLACKIPLIST;
    if (!$PLUGIN_CTRLCMT_BLACKIPLIST == '') {
        $blockiparray = explode(",", $PLUGIN_CTRLCMT_BLACKIPLIST);
        foreach($blockiparray as $value) {
            if ($value == $_SERVER["REMOTE_ADDR"]) {
                die_message($lang['err_blackip']);
            }
        }
    }
    $PLUGIN_CTRLCMT_NGWORD = PLUGIN_CTRLCMT_NGWORD;

    if ((!PLUGIN_CTRLCMT_NGWORD_MODE == 'false') || (isset($PLUGIN_CTRLCMT_NGWORD) && !empty($PLUGIN_CTRLCMT_NGWORD))) {
        $PLUGIN_CTRLCMT_NGWORD = "(" . str_replace(",", "|", $PLUGIN_CTRLCMT_NGWORD) . ")";
        if (PLUGIN_CTRLCMT_NGWORD_MODE == 'stop') {
            if (preg_match_all('/' . $PLUGIN_CTRLCMT_NGWORD . '/u', $vars['msg'], $match, PREG_SET_ORDER)) {
                $ngwords = array();
                foreach ($match as $value)
                {
                    $ngwords[] = $value[0];
                }
                die_message($lang['err_ngword'] . '<br /><pre>' . implode(" , ", $ngwords) . '</pre>');
            }
        }
        if (PLUGIN_CTRLCMT_NGWORD_MODE == 'hide') {
            $vars['msg'] = preg_replace('/' . $PLUGIN_CTRLCMT_NGWORD . '/u', PLUGIN_CTRLCMT_NGWORD_MASK, $vars['msg']);
        }
    }
    
    $comment = str_replace('$msg', $vars['msg'], PLUGIN_CTRLCMT_FORMAT_MSG);
    if (isset($vars['name']) || ($vars['nodate'] != '1')) {
        $_name = (!isset($vars['name']) || $vars['name'] == '') ? $_no_name : $vars['name'];
        $_name2 = $_name;
        if (PLUGIN_CTRLCMT_NONAME == "") {
            $PLUGIN_CTRLCMT_NONAME = "";
        } else {
            $PLUGIN_CTRLCMT_NONAME = "[[" . PLUGIN_CTRLCMT_NONAME . "]]";
        }
        $_name = ($_name == '') ? $PLUGIN_CTRLCMT_NONAME : str_replace('$name', $_name, PLUGIN_CTRLCMT_FORMAT_NAME);
        if (PLUGIN_CTRLCMT_IPCRY) {
            $_cryedip = substr(base64_encode(hash_hmac("sha1", date("Ymd") . $_SERVER["REMOTE_ADDR"], PLUGIN_CTRLCMT_IPCRYPASS)) , 0, 8);
            $_ipcry = " &size(10){[" . $_cryedip . "]};";
        } else {
            $_ipcry = "";
        }
        $_now = ($vars['nodate'] == '1') ? '' : str_replace('$now', $now, PLUGIN_CTRLCMT_FORMAT_NOW);
        if (PLUGIN_CTRLCMT_SAVEIP) {
            $iplog_json = array();
            $iplog_loadjson = array();
            $iplogIsExist = false;
            if (file_exists(PLUGIN_CTRLCMT_DIR . '/cmt/.htaccess') == false) {
                mkdir(PLUGIN_CTRLCMT_DIR);
                mkdir(PLUGIN_CTRLCMT_DIR . "/cmt");
                $open_ht = fopen(PLUGIN_CTRLCMT_DIR . "/cmt/.htaccess", 'a');
                @fwrite($open_ht, "Require all denied");
                fclose($open_ht);
            }
            if (file_exists(PLUGIN_CTRLCMT_DIR . '/cmt/log.json')) {
                $iplog_loadjson = json_decode(file_get_contents(PLUGIN_CTRLCMT_DIR . "/cmt/log.json") , true);
                $iplogIsExist = true;
            }
            $iplog_json[] = array(
                'name' => $_name2,
                'comment' => $comment,
                'ipcry' => $_cryedip,
                'date' => $now,
                'page' => $page,
                'ip_address' => $_SERVER["REMOTE_ADDR"],
            );
            if ($iplogIsExist == true) {
                $iplog_json = array_merge($iplog_json, $iplog_loadjson);
            }
            file_put_contents(PLUGIN_CTRLCMT_DIR . "/cmt/log.json", json_encode($iplog_json, JSON_PRETTY_PRINT));
        }
        $comment = str_replace("\$MSG\$", $comment, PLUGIN_CTRLCMT_FORMAT_STRING);
        $comment = str_replace("\$NAME\$", $_name, $comment);
        $comment = str_replace("\$IPCRY\$", $_ipcry, $comment);
        $comment = str_replace("\$NOW\$", $_now, $comment);
    }

	$reply_hash = isset($vars['reply']) ? $vars['reply'] : '';
	if ($reply_hash || ! is_page($page)) {
		$comment = preg_replace('/^\-+/', '', $comment);
	}
	$comment = rtrim($comment);

	if (! is_page($page)) {
		$postdata = '[[' . htmlsc(strip_bracket($refer)) . ']]' . "\n\n" .
			'-' . $comment . "\n";
	} else {
		$postdata = get_source($page);
		$count    = count($postdata);

		$digest = isset($vars['digest']) ? $vars['digest'] : '';
		if (md5(join('', $postdata)) !== $digest) {
			$ret['msg']  = $_pcmt_messages['title_collided'];
			$ret['body'] = $_pcmt_messages['msg_collided'];
		}

		$start_position = 0;
		while ($start_position < $count) {
			if (preg_match('/^\-/', $postdata[$start_position])) break;
			++$start_position;
		}
		$end_position = $start_position;

		$dir = isset($vars['dir']) ? $vars['dir'] : '';

		// Find the comment to reply
		$level   = 1;
		$b_reply = FALSE;
		if ($reply_hash != '') {
			while ($end_position < $count) {
				$matches = array();
				if (preg_match('/^(\-{1,2})(?!\-)(.*)$/', $postdata[$end_position++], $matches)
					&& md5($matches[2]) === $reply_hash)
				{
					$b_reply = TRUE;
					$level   = strlen($matches[1]) + 1;

					while ($end_position < $count) {
						if (preg_match('/^(\-{1,3})(?!\-)/', $postdata[$end_position], $matches)
							&& strlen($matches[1]) < $level) break;
						++$end_position;
					}
					break;
				}
			}
		}

		if ($b_reply == FALSE)
			$end_position = ($dir == '0') ? $start_position : $count;

		// Insert new comment
		array_splice($postdata, $end_position, 0, str_repeat('-', $level) . $comment . "\n");

		if (PLUGIN_PCOMMENT_AUTO_LOG) {
			$_count = isset($vars['count']) ? $vars['count'] : '';
			plugin_pcomment_auto_log($page, $dir, $_count, $postdata);
		}

		$postdata = join('', $postdata);
	}
	page_write($page, $postdata, PLUGIN_PCOMMENT_TIMESTAMP);

	if (PLUGIN_PCOMMENT_TIMESTAMP) {
		if ($refer != '') pkwk_touch_file(get_filename($refer));
		put_lastmodified();
	}

	return $ret;
}