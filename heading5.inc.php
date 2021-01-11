<?php
// $Id: heading5.inc.php,v 1.00 2020/11/29 06:53:34 K Exp $

/** 
* @link http://pkom.ml/?%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3
* @author K
* @license http://www.gnu.org/licenses/gpl.ja.html GPL
*/

function plugin_heading5_convert(){
    $arg = func_get_arg(0);
    return <<<EOD
        <h5>{$arg}</h5>
    EOD;
}