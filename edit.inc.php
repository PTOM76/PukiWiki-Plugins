<?php
// $Id: edit.inc.php,v 1.01 2020/10/17 11:56:23 K Exp $

define('PLUGIN_EDIT_FREEZE_REGEX', '/^(?:#freeze(?!\w)\s*)+/im');
define('PUKIWIKI_CSS', 'skin/pukiwiki.css');

function plugin_edit_action()
{
    if (isset($_POST['data'])){
        $postdata = hex2bin($_POST['data']);
		$postdata = make_str_rules($postdata);
		$postdata = explode("\n", $postdata);
		$postdata = drop_submit(convert_html($postdata));
        $css = PUKIWIKI_CSS;
        echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"{$css}\" />".$postdata;
        exit;
    }
	global $vars, $_title_edit;

	if (PKWK_READONLY) die_message('PKWK_READONLY prohibits editing');

	// Create initial pages
	plugin_edit_setup_initial_pages();

	$page = isset($vars['page']) ? $vars['page'] : '';
	check_editable($page, true, true);
	check_readable($page, true, true);

	if (isset($vars['preview'])) {
		return plugin_edit_preview($vars['msg']);
	} else if (isset($vars['template'])) {
		return plugin_edit_preview_with_template();
	} else if (isset($vars['write'])) {
		return plugin_edit_write();
	} else if (isset($vars['cancel'])) {
		return plugin_edit_cancel();
	}

	$postdata = @join('', get_source($page));
	if ($postdata === '') $postdata = auto_template($page);
	$postdata = remove_author_info($postdata);
	return array('msg'=>$_title_edit, 'body'=>plugin_edit_form($page, $postdata));
}

/**
 * Preview with template
 */
function plugin_edit_preview_with_template()
{
	global $vars;
	$msg = '';
	$page = isset($vars['page']) ? $vars['page'] : '';
	// Loading template
	$template_page;
	if (isset($vars['template_page']) && is_page($template_page = $vars['template_page'])) {
		if (is_page_readable($template_page)) {
			$msg = remove_author_info(get_source($vars['template_page'], TRUE, TRUE));
			// Cut fixed anchors
			$msg = preg_replace('/^(\*{1,3}.*)\[#[A-Za-z][\w-]+\](.*)$/m', '$1$2', $msg);
		}
	}
	return plugin_edit_preview($msg);
}

/**
 * Preview
 *
 * @param msg preview target
 */
function plugin_edit_preview($msg)
{
	global $vars;
	global $_title_preview, $_msg_preview, $_msg_preview_delete;

	$page = isset($vars['page']) ? $vars['page'] : '';

	$msg = preg_replace(PLUGIN_EDIT_FREEZE_REGEX, '', $msg);
	$postdata = $msg;

	if (isset($vars['add']) && $vars['add']) {
		if (isset($vars['add_top']) && $vars['add_top']) {
			$postdata  = $postdata . "\n\n" . @join('', get_source($page));
		} else {
			$postdata  = @join('', get_source($page)) . "\n\n" . $postdata;
		}
	}

	$body = $_msg_preview . '<br />' . "\n";
	if ($postdata === '')
		$body .= '<strong>' . $_msg_preview_delete . '</strong>';
	$body .= '<br />' . "\n";

	if ($postdata) {
		$postdata = make_str_rules($postdata);
		$postdata = explode("\n", $postdata);
		$postdata = drop_submit(convert_html($postdata));
		$body .= '<div id="preview">' . $postdata . '</div>' . "\n";
	}
	$body .= plugin_edit_form($page, $msg, $vars['digest'], FALSE);

	return array('msg'=>$_title_preview, 'body'=>$body);
}

// Inline: Show edit (or unfreeze text) link
function plugin_edit_inline()
{
	static $usage = '&edit(pagename#anchor[[,noicon],nolabel])[{label}];';

	global $vars, $fixed_heading_anchor_edit;

	if (PKWK_READONLY) return ''; // Show nothing 

	// Arguments
	$args = func_get_args();

	// {label}. Strip anchor tags only
	$s_label = strip_htmltag(array_pop($args), FALSE);

	$page = array_shift($args);
	if ($page === NULL) $page = '';
	$_noicon = $_nolabel = FALSE;
	foreach($args as $arg){
		switch(strtolower($arg)){
		case ''       :                   break;
		case 'nolabel': $_nolabel = TRUE; break;
		case 'noicon' : $_noicon  = TRUE; break;
		default       : return $usage;
		}
	}

	// Separate a page-name and a fixed anchor
	list($s_page, $id, $editable) = anchor_explode($page, TRUE);

	// Default: This one
	if ($s_page == '') $s_page = isset($vars['page']) ? $vars['page'] : '';

	// $s_page fixed
	$isfreeze = is_freeze($s_page);
	$ispage   = is_page($s_page);

	// Paragraph edit enabled or not
	$short = htmlsc('Edit');
	if ($fixed_heading_anchor_edit && $editable && $ispage && ! $isfreeze) {
		// Paragraph editing
		$id    = rawurlencode($id);
		$title = htmlsc(sprintf('Edit %s', $page));
		$icon = '<img src="' . IMAGE_DIR . 'paraedit.png' .
			'" width="9" height="9" alt="' .
			$short . '" title="' . $title . '" /> ';
		$class = ' class="anchor_super"';
	} else {
		// Normal editing / unfreeze
		$id    = '';
		if ($isfreeze) {
			$title = 'Unfreeze %s';
			$icon  = 'unfreeze.png';
		} else {
			$title = 'Edit %s';
			$icon  = 'edit.png';
		}
		$title = htmlsc(sprintf($title, $s_page));
		$icon = '<img src="' . IMAGE_DIR . $icon .
			'" width="20" height="20" alt="' .
			$short . '" title="' . $title . '" />';
		$class = '';
	}
	if ($_noicon) $icon = ''; // No more icon
	if ($_nolabel) {
		if (!$_noicon) {
			$s_label = '';     // No label with an icon
		} else {
			$s_label = $short; // Short label without an icon
		}
	} else {
		if ($s_label == '') $s_label = $title; // Rich label with an icon
	}

	// URL
	$script = get_base_uri();
	if ($isfreeze) {
		$url   = $script . '?cmd=unfreeze&amp;page=' . rawurlencode($s_page);
	} else {
		$s_id = ($id == '') ? '' : '&amp;id=' . $id;
		$url  = $script . '?cmd=edit&amp;page=' . rawurlencode($s_page) . $s_id;
	}
	$atag  = '<a' . $class . ' href="' . $url . '" title="' . $title . '">';
	static $atags = '</a>';

	if ($ispage) {
		// Normal edit link
		return $atag . $icon . $s_label . $atags;
	} else {
		// Dangling edit link
		return '<span class="noexists">' . $atag . $icon . $atags .
			$s_label . $atag . '?' . $atags . '</span>';
	}
}

// Write, add, or insert new comment
function plugin_edit_write()
{
	global $vars;
	global $_title_collided, $_msg_collided_auto, $_msg_collided, $_title_deleted;
	global $notimeupdate, $_msg_invalidpass, $do_update_diff_table;

	$page   = isset($vars['page'])   ? $vars['page']   : '';
	$add    = isset($vars['add'])    ? $vars['add']    : '';
	$digest = isset($vars['digest']) ? $vars['digest'] : '';

	$vars['msg'] = preg_replace(PLUGIN_EDIT_FREEZE_REGEX, '', $vars['msg']);
	$msg = & $vars['msg']; // Reference

	$retvars = array();

	// Collision Detection
	$oldpagesrc = join('', get_source($page));
	$oldpagemd5 = md5($oldpagesrc);
	if ($digest !== $oldpagemd5) {
		$vars['digest'] = $oldpagemd5; // Reset

		$original = isset($vars['original']) ? $vars['original'] : '';
		$old_body = remove_author_info($oldpagesrc);
		list($postdata_input, $auto) = do_update_diff($old_body, $msg, $original);

		$retvars['msg' ] = $_title_collided;
		$retvars['body'] = ($auto ? $_msg_collided_auto : $_msg_collided) . "\n";
		$retvars['body'] .= $do_update_diff_table;
		$retvars['body'] .= plugin_edit_form($page, $postdata_input, $oldpagemd5, FALSE);
		return $retvars;
	}

	// Action?
	if ($add) {
		// Add
		if (isset($vars['add_top']) && $vars['add_top']) {
			$postdata  = $msg . "\n\n" . @join('', get_source($page));
		} else {
			$postdata  = @join('', get_source($page)) . "\n\n" . $msg;
		}
	} else {
		// Edit or Remove
		$postdata = & $msg; // Reference
	}

	// NULL POSTING, OR removing existing page
	if ($postdata === '') {
		page_write($page, $postdata);
		$retvars['msg' ] = $_title_deleted;
		$retvars['body'] = str_replace('$1', htmlsc($page), $_title_deleted);
		return $retvars;
	}

	// $notimeupdate: Checkbox 'Do not change timestamp'
	$notimestamp = isset($vars['notimestamp']) && $vars['notimestamp'] != '';
	if ($notimeupdate > 1 && $notimestamp && ! pkwk_login($vars['pass'])) {
		// Enable only administrator & password error
		$retvars['body']  = '<p><strong>' . $_msg_invalidpass . '</strong></p>' . "\n";
		$retvars['body'] .= plugin_edit_form($page, $msg, $digest, FALSE);
		return $retvars;
	}

	page_write($page, $postdata, $notimeupdate != 0 && $notimestamp);
	pkwk_headers_sent();
	header('Location: ' . get_page_uri($page, PKWK_URI_ROOT));
	exit;
}

// Cancel (Back to the page / Escape edit page)
function plugin_edit_cancel()
{
	global $vars;
	pkwk_headers_sent();
	header('Location: ' . get_page_uri($vars['page'], PKWK_URI_ROOT));
	exit;
}

/**
 * Setup initial pages
 */
function plugin_edit_setup_initial_pages()
{
	global $autoalias;

	// Related: Rename plugin
	if (exist_plugin('rename') && function_exists('plugin_rename_setup_initial_pages')) {
		plugin_rename_setup_initial_pages();
	}
	// AutoTicketLinkName page
	init_autoticketlink_def_page();
	// AutoAliasName page
	if ($autoalias) {
		init_autoalias_def_page();
	}
}
function plugin_edit_form($page, $postdata, $digest = FALSE, $b_template = TRUE)
{
	global $vars, $rows, $cols;
	global $_btn_preview, $_btn_repreview, $_btn_update, $_btn_cancel, $_msg_help;
	global $_btn_template, $_btn_load, $load_template_func;
	global $notimeupdate;
	global $_msg_edit_cancel_confirm, $_msg_edit_unloadbefore_message;
	global $rule_page;
	$script = get_base_uri();
	if ($digest === FALSE) $digest = md5(join('', get_source($page)));
	$refer = $template = '';
	$addtag = $add_top = '';
	if(isset($vars['add'])) {
		global $_btn_addtop;
		$addtag  = '<input type="hidden" name="add"    value="true" />';
		$add_top = isset($vars['add_top']) ? ' checked="checked"' : '';
		$add_top = '<input type="checkbox" name="add_top" ' .
			'id="_edit_form_add_top" value="true"' . $add_top . ' />' . "\n" .
			'  <label for="_edit_form_add_top">' .
				'<span class="small">' . $_btn_addtop . '</span>' .
			'</label>';
	}
	if($load_template_func && $b_template) {
		$template_page_list = get_template_page_list();
		$tpages = array(); // Template pages
		foreach($template_page_list as $p) {
			$ps = htmlsc($p);
			$tpages[] = '   <option value="' . $ps . '">' . $ps . '</option>';
		}
		if (count($template_page_list) > 0) {
			$s_tpages = join("\n", $tpages);
		} else {
			$s_tpages = '   <option value="">(no template pages)</option>';
		}
		$template = <<<EOD
  <select name="template_page">
   <option value="">-- $_btn_template --</option>
$s_tpages
  </select>
  <input type="submit" name="template" value="$_btn_load" accesskey="r" />
  <br />
EOD;

		if (isset($vars['refer']) && $vars['refer'] != '')
			$refer = '[[' . strip_bracket($vars['refer']) . ']]' . "\n\n";
	}

	$r_page      = rawurlencode($page);
	$s_page      = htmlsc($page);
	$s_digest    = htmlsc($digest);
	$s_postdata  = htmlsc($refer . $postdata);
    $postdatahex = bin2hex($s_postdata);
	$s_original  = isset($vars['original']) ? htmlsc($vars['original']) : $s_postdata;
	$b_preview   = isset($vars['preview']); // TRUE when preview
	$btn_preview = $b_preview ? $_btn_repreview : $_btn_preview;

	$add_notimestamp = '';
	if ($notimeupdate != 0) {
		global $_btn_notchangetimestamp;
		$checked_time = isset($vars['notimestamp']) ? ' checked="checked"' : '';
		if ($notimeupdate == 2) {
			$add_notimestamp = '   ' .
				'<input type="password" name="pass" size="12" />' . "\n";
		}
		$add_notimestamp = '<input type="checkbox" name="notimestamp" ' .
			'id="_edit_form_notimestamp" value="true"' . $checked_time . ' />' . "\n" .
			'   ' . '<label for="_edit_form_notimestamp"><span class="small">' .
			$_btn_notchangetimestamp . '</span></label>' . "\n" .
			$add_notimestamp .
			'&nbsp;';
	}
	$h_msg_edit_cancel_confirm = htmlsc($_msg_edit_cancel_confirm);
	$h_msg_edit_unloadbefore_message = htmlsc($_msg_edit_unloadbefore_message);
	$body = <<<EOD
<div class="edit_form" id="viewedit_form">
  <style>
    #viewedit_form_textarea {
        float:left;
        width:50%;
    }
    #resizable {
        overflow: auto;
        resize: both;
        width: 100%;
        height: 265px;
        border: 0.1px solid #777777;
        border-radius: 0.2em;
    }
    #resizable > iframe {
        overflow:hidden;
        width:100%;
        height:99%;
    }
    #viewedit_form_preview {
        float:right;
        width:50%;
    }
    #viewedit_form_preview iframe {
        border: none;
        width:100%;
        padding:0;
        margin:0;
    }
    #viewedit_form_textarea textarea {
        border: 0.1px solid #777777;
        border-radius: 0.2em;
        width:100%;
        height:265px;
        padding:0;
        margin:0;
    }
    #clear_float_viewedit {
        clear:left;	
    }
      
  </style>
 <form action="$script" method="post" class="_plugin_edit_viewedit_form" style="margin-bottom:0;right:50%">
$template
  $addtag
  <input type="hidden" name="cmd"    value="edit" />
  <input type="hidden" name="page"   value="$s_page" />
  <input type="hidden" name="digest" value="$s_digest" />
  <input type="hidden" id="_msg_edit_cancel_confirm" value="$h_msg_edit_cancel_confirm" />
  <input type="hidden" id="_msg_edit_unloadbefore_message" value="$h_msg_edit_unloadbefore_message" />
<script src="https&#58;//ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script>
function string_to_utf8_hex_string(text)
{
	var bytes1 = string_to_utf8_bytes(text);
	var hex_str1 = bytes_to_hex_string(bytes1);
	return hex_str1;
}
function string_to_utf8_bytes(text)
{
    var result = [];
    if (text == null)
        return result;
    for (var i = 0; i < text.length; i++) {
        var c = text.charCodeAt(i);
        if (c <= 0x7f) {
            result.push(c);
        } else if (c <= 0x07ff) {
            result.push(((c >> 6) & 0x1F) | 0xC0);
            result.push((c & 0x3F) | 0x80);
        } else {
            result.push(((c >> 12) & 0x0F) | 0xE0);
            result.push(((c >> 6) & 0x3F) | 0x80);
            result.push((c & 0x3F) | 0x80);
        }
    }
    return result;
}
function byte_to_hex(byte_num)
{
	var digits = (byte_num).toString(16);
    if (byte_num < 16) return '0' + digits;
    return digits;
}
function bytes_to_hex_string(bytes)
{
	var	result = "";

	for (var i = 0; i < bytes.length; i++) {
		result += byte_to_hex(bytes[i]);
	}
	return result;
}
function change_vieweditarea(){
    var editor_data = document.getElementById("editor").value;
    document.getElementById("previewpostdata").value = string_to_utf8_hex_string(editor_data);
    var previewpost2=document.getElementById("previewpost");
    previewpost2.submit();
var viewedit_load = function() {
    var viewedit_preview = document.getElementById('preview_viewedit').contentWindow.document.body;
    var viewedit_textarea = document.getElementById('editor');
    if(document.getElementById('linkscrollbar').checked){
        viewedit_preview.scrollTop = viewedit_preview.scrollHeight / viewedit_textarea.scrollHeight * viewedit_textarea.scrollTop;
        viewedit_preview.scrollLeft = viewedit_preview.scrollWidth / viewedit_textarea.scrollWidth * viewedit_textarea.scrollTop;
    }
}
setTimeout(viewedit_load, 1500);
setTimeout(viewedit_load, 2500);
setTimeout(viewedit_load, 3000);
}
</script>
  <div id="viewedit_form_textarea">
    <textarea id="editor" name="msg" rows="$rows" onInput="change_vieweditarea();" 
    onScroll="
    var viewedit_preview = document.getElementById('preview_viewedit').contentWindow.document.body;
    var viewedit_textarea = document.getElementById('editor');
    if(document.getElementById('linkscrollbar').checked){
        viewedit_preview.scrollTop = viewedit_preview.scrollHeight / viewedit_textarea.scrollHeight * viewedit_textarea.scrollTop;
        viewedit_preview.scrollLeft = viewedit_preview.scrollWidth / viewedit_textarea.scrollWidth * viewedit_textarea.scrollTop;
    }">$s_postdata</textarea>
  </div>
  <div id="viewedit_form_preview">
    <div id="resizable">
      <iframe name="preview_viewedit" id="preview_viewedit" src="./?plugin=viewedit"></iframe>
    </div>
  </div>
  <br />
<script>
function getEditorBox() 
{
    dae = document.getElementById("editor");
    text_len = dae.value.length;
    text_pos = dae.selectionStart;
    text_bf = dae.value.substr(0, text_pos);
    text_af = dae.value.substr(text_pos, text_len);
};
function inputToCommentplusArea(input_text) {
    getEditorBox();
    dae.value = text_bf + input_text + text_af;
    text_len = dae.value.length;
    text_pos = text_pos + input_text.length;
    text_bf = dae.value.substr(0, text_pos);
    text_af = dae.value.substr(text_pos, text_len);
};
function inputToCommentplusArea2(input_text1,input_text2,input_text3) {
    getEditorBox();
    size = window.prompt("サイズ:", "10");
    text1 = window.prompt("テキスト:", dae.value.substring(dae.selectionStart,dae.selectionEnd));
    text_af = dae.value.substr(text_pos + dae.value.substring(dae.selectionStart,dae.selectionEnd).length, text_len);
    dae.value = text_bf + input_text1 + size + input_text2 + text1 + input_text3 + text_af;
    text_len = dae.value.length;
    text_pos = text_pos + input_text1.length + size.length + input_text2.length + text1.length + input_text3.length;
    text_bf = dae.value.substr(0, text_pos);
    text_af = dae.value.substr(text_pos, text_len);
};
function inputToCommentplusAreaURL(input_text1,input_text2,input_text3) {
    getEditorBox();
    text1 = window.prompt("テキスト:", "");
    url1 = window.prompt("URL:", "http://");
    dae.value = text_bf + "[["+text1+">"+url1+"]]" + text_af;
    text_len = dae.value.length;
    text_pos = text_pos + ("[["+text1+">"+url1+"]]").length;
    text_bf = dae.value.substr(0, text_pos);
    text_af = dae.value.substr(text_pos, text_len);
};
function inputToCommentplusArea3(input_text) {
    getEditorBox();
    text_af = dae.value.substr(text_pos + dae.value.substring(dae.selectionStart,dae.selectionEnd).length, text_len);
    dae.value = text_bf + input_text + dae.value.substring(dae.selectionStart,dae.selectionEnd) + input_text + text_af;
    text_se_1 = dae.selectionEnd;
    text_len = dae.value.length;
    text_pos = text_pos + input_text.length;
    text_bf = dae.value.substr(0, text_pos);
    text_af = dae.value.substr(text_pos, text_len);
};
</script>
<div id="clear_float_viewedit">
  <div style="float:left;">
スクロールバーを連動:<input type="checkbox" id="linkscrollbar" />
<a href="javascript:inputToCommentplusAreaURL()">[URL]</a>&nbsp;
<a href="javascript:inputToCommentplusArea3('\&#39;\&#39;')">[B]</a>&nbsp;
<a href="javascript:inputToCommentplusArea3('\&#39;\&#39;\&#39;')">[I]</a>&nbsp;
<a href="javascript:inputToCommentplusArea3('%%%')">[U]</a>&nbsp;
<a href="javascript:inputToCommentplusArea3('%%')">[S]</a>&nbsp;
<a href="javascript:inputToCommentplusArea2('&size(','){','};')">[サイズ]</a>&nbsp;
<a href="javascript:inputToCommentplusArea('&attachref();')">[添付]</a>&nbsp;
<a href="javascript:inputToCommentplusArea('&br;')">[改行]</a>&nbsp;
<a href="javascript:inputToCommentplusArea('&smile&#59;')"><img src="./image/face/smile.png"/></a>&nbsp;
<a href="javascript:inputToCommentplusArea('&bigsmile&#59;')"><img src="./image/face/bigsmile.png"/></a>&nbsp;
<a href="javascript:inputToCommentplusArea('&huh&#59;')"><img src="./image/face/huh.png"/></a>&nbsp;
<a href="javascript:inputToCommentplusArea('&oh&#59;')"><img src="./image/face/oh.png"/></a>&nbsp;
<a href="javascript:inputToCommentplusArea('&wink&#59;')"><img src="./image/face/wink.png"/></a>&nbsp;
<a href="javascript:inputToCommentplusArea('&sad&#59;')"><img src="./image/face/sad.png"/></a>&nbsp;
<a href="javascript:inputToCommentplusArea('&heart&#59;')"><img src="./image/face/heart.png"/></a>&nbsp;
</div>
<br />
<br />
<div style="float:left;">
   <input type="submit" name="preview" value="$btn_preview" accesskey="p" />
   <input type="submit" name="write"   value="$_btn_update" accesskey="s" />
   $add_top
   $add_notimestamp
  </div>
</div>
  
  <textarea name="original" rows="1" cols="1" style="display:none">$s_original</textarea>
 </form>
 <form action="$script" method="post" class="_plugin_edit_cancel" style="margin-top:0;">
  <input type="hidden" name="cmd"    value="edit" />
  <input type="hidden" name="page"   value="$s_page" />
  <input type="submit" name="cancel" value="$_btn_cancel" accesskey="c" />
 </form>
</div>

<form id="previewpost" target="preview_viewedit" action="./?plugin=edit" method="post">
    <input type="text" id="previewpostdata" name="data" value="{$postdatahex}" style="display:none;" />
    <input type="submit" style="display:none;" />
</form>

<script>
change_vieweditarea();


</script>
EOD;

	$body .= '<ul><li><a href="' .
		get_page_uri($rule_page) .
		'" target="_blank">' . $_msg_help . '</a></li></ul>';
	return $body;
}
