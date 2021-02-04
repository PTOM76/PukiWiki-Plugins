<?php
// $Id: exrules2.inc.php,v 1.00 2021/02/04 23:19:45 K Exp $

/** 
* @link http://pkom.ml/?プラグイン/exrules2.inc.php
* @author K
* @license http://www.gnu.org/licenses/gpl.ja.html GPL
*/

function plugin_exrules2_convert(){
    $body = func_get_arg(func_num_args() - 1);
    if(preg_match("/&exrules2\(.*?\);/", $body)){
        global $exrules2_rules;
        $exrules2_rules = array();
        $body = preg_replace_callback("/&exrules2\((.*?)\);/", function($m){
            global $exrules2_rules;
            static $exrules2_count = 0;
            ++$exrules2_count;
            $args = explode(',', $m[1]);
            $exrules2_rules[$exrules2_count] = array('rule' => str_replace("\\,", ",", $args[0]),'replace' => str_replace("\\,", ",", $args[1]));
            return '&exrules2;';
        }, $body);
        $rules = $exrules2_rules;
    }
    foreach($rules as $rule){
        $rule['rule'] = str_replace("*", "(.*?)", $rule['rule']);
        $rule['replace'] = str_replace("*", "$1", $rule['replace']);
        $body = preg_replace("/" . $rule['rule'] . "/u", $rule['replace'], $body);
    }
    $body = preg_replace("/&exrules2;(\n|\r\n|\r)?/", "", $body);
    return convert_html($body);
}

function plugin_exrules2_inline(){
    return "#exrules2から呼び出してください。";
}