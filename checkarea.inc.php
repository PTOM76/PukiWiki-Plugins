<?php
// $Id: checkarea.inc.php,v 1.00 2021/02/12 18:46:44 K Exp $

/** 
* @link http://pkom.ml/?プラグイン/checkarea.inc.php
* @author K
* @license http://www.gnu.org/licenses/gpl.ja.html GPL
*/

function plugin_checkarea_convert(){
    $args = func_get_args();
    return call_user_func_array('plugin_checkarea_inline', $args);
}

function plugin_checkarea_inline(){
    global $vars;
    static $n = 0;
    $args = func_get_args();
    if($args[0] == 'submit'){
        return <<<EOD
        <form id="checkarea" method="post">
            <input type="hidden" name="cmd" value="checkarea" />
            <input type="hidden" name="refer" value="{$vars['page']}" />
            <input type="submit" name="checkarea_submit" value="チェックエリア更新" />
        </form>
        EOD;
    }elseif($args[0] == 'true'){
        $checked = 'checked';
    }else{
        $checked = '';
    }
    ++$n;
    return <<<EOD
    <input type="checkbox" name="checkarea_{$n}" form="checkarea" {$checked} />
    EOD;
}

function plugin_checkarea_action(){
    global $vars, $_title_updated, $_msg_comment_collided;
    $source = get_source($vars['refer'], TRUE, TRUE);
    $source = preg_replace_callback('/(&)checkarea(?:\((.*?)\))?;/', function($m){return plugin_checkarea_replace($m);}, $source);
    $source = preg_replace_callback('/(\#)checkarea(?:\((.*?)\)|([|]|$))/', function($m){return plugin_checkarea_replace($m);}, $source);
    page_write($vars['refer'], $source);
    $vars['page'] = $vars['refer'];
    return array('msg' => $_title_updated, 'body' => '');
}

function plugin_checkarea_replace($m){
    global $vars;
    static $n = 0;
    if($m[2] == 'submit'){
        return $m[0];
    }else{
        ++$n;
        if($vars['checkarea_' . (string)$n] == 'on'){
            if($m[1] == '#'){return "#checkarea(true)" . ($m[2] == '|' ? '|' : '');}
            if($m[1] == '&'){return "&checkarea(true);";}
            return $m[0];
        }else{
            if($m[1] == '#'){return "#checkarea(false)";}
            if($m[1] == '&'){return "&checkarea(false);";}
            return $m[0];
        }
    }
}