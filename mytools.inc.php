<?php
// $Id: mytools.inc.php,v 1.00 2021/01/01 23:46:12 K Exp $

/** 
* @link http://example.com/
* @author K
* @license http://www.gnu.org/licenses/gpl.ja.html GPL
*/

class plugin_mytools{
    public function convert(){
        $args = func_get_args();
        if(!file_exists("./mytools")){
            mkdir("./mytools", 0777);
        }
        if(file_exists("./mytools/" . $args[0] . ".php")){
            $out = eval(file_get_contents("./mytools/" . $args[0] . ".php"));
        }else{
            $out = "mytoolsに" . $args[0] . ".phpというファイルは存在しません。";
        }
        return $out;
    }
}

function plugin_mytools_convert() {
	return "<div id=\"plugin_mytools\">" . call_user_func_array('plugin_mytools_inline', func_get_args()) . "</div>";
}

function plugin_mytools_inline() {
    $class = new plugin_mytools();
    $args = func_get_args();
	return call_user_func_array(array($class, "convert"), $args);
}