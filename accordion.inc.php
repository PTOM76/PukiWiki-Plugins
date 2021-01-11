<?php
// $Id: accordion.inc.php,v 1.3 2020/12/01 23:20:41 K Exp $

/** 
* @link http://pkom.ml/?%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/accordion.inc.php
* @author K
* @license http://www.gnu.org/licenses/gpl.ja.html GPL
*/

// region.inc.php(author:xxxxx) https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/region.inc.php

function plugin_accordion_convert()
{
	static $builder = 0;
	if( $builder==0 ) $builder = new AccordionPluginHTMLBuilder();

	$builder->setDefaultSettings();

	if (func_num_args() >= 3){
		$args = func_get_args();
		$builder->setDescription( array_shift($args) );
		$builder->setHeading( array_shift($args) );
		foreach( $args as $value ){
			if( preg_match("/^open/i", $value) ){
				$builder->setOpened();
			}elseif( preg_match("/^close/i", $value) ){
				$builder->setClosed();
			}
		}
	}
    $args = func_get_args();
    $contents1 = $args[func_num_args()-1];
    $contents1 = preg_replace("/\r\n|\r/", "\n", $contents1);
    $contents1 = explode("\n",$contents1);

return $builder->build()
.convert_html($contents1)
.<<<EOD
</td></tr></table>
EOD;
}

class AccordionPluginHTMLBuilder
{
	var $description;
	var $heading;
	var $isopened;
	var $scriptVarName;
	var $callcount;

	function AccordionPluginHTMLBuilder() {
		$this->callcount = 0;
		$this->setDefaultSettings();
	}
	function setDefaultSettings(){
		$this->description = "...";
        $this->heading = "h2";
		$this->isopened = false;
	}
	function setClosed(){ $this->isopened = false; }
	function setOpened(){ $this->isopened = true; }
	function setDescription($description){
		$this->description = convert_html($description);
		$this->description = preg_replace( "/^<p>/i", "", $this->description);
		$this->description = preg_replace( "/<\/p>$/i", "", $this->description);
	}
    function setHeading($heading){
        if ($heading == "1"){$this->heading = "h2";}
        else if ($heading == "2"){$this->heading = "h3";}
        else if ($heading == "3"){$this->heading = "h4";}
        else if ($heading == "4"){$this->heading = "h5";}
    }
	function build(){
		$this->callcount++;
		$html = array();
		array_push( $html, $this->buildButtonHtml() );
		array_push( $html, $this->buildContentHtml() );
		return join($html);
	}

	function buildButtonHtml(){
		$button = ($this->isopened) ? "-" : "+";
        //white style
        //cursor:pointer;font-family: "ＭＳ Ｐゴシック", "MS PGothic", "ヒラギノ角ゴ Pro W3","Hiragino Kaku Gothic Pro", Osaka, arial, verdana, sans-serif;padding:1px 4px;border:gray 1px solid;border-radius:3px;background-color:white;color:gray

        //black style
        //cursor:pointer;font-family: "ＭＳ Ｐゴシック", "MS PGothic", "ヒラギノ角ゴ Pro W3","Hiragino Kaku Gothic Pro", Osaka, arial, verdana, sans-serif;padding:1px 4px;border:black 1px solid;border-radius:3px;background-color:black;color:white

        //default style
        //cursor:pointer;font-family: "ＭＳ Ｐゴシック", "MS PGothic", "ヒラギノ角ゴ Pro W3","Hiragino Kaku Gothic Pro", Osaka, arial, verdana, sans-serif;padding:1px 4px;border:gray 1px solid;color:gray;
		return <<<EOD

    <{$this->heading}><span id=acd_button$this->callcount style='cursor:pointer;font-family: "ＭＳ Ｐゴシック", "MS PGothic", "ヒラギノ角ゴ Pro W3","Hiragino Kaku Gothic Pro", Osaka, arial, verdana, sans-serif;padding:1px 4px;border:gray 1px solid;color:gray;'
	onclick="
	if(document.getElementById('acd_content$this->callcount').style.display!='inline'){
		document.getElementById('acd_content$this->callcount').style.display='inline';
		document.getElementById('acd_button$this->callcount').innerHTML='-';
	}else{
		document.getElementById('acd_content$this->callcount').style.display='none';
		document.getElementById('acd_button$this->callcount').innerHTML='+';
	}
	">$button</span>&nbsp;$this->description</{$this->heading}>
    <table><tr>
EOD;
	}
	function buildContentHtml(){
		$contentstyle = ($this->isopened) ? "display:block;" : "display:none;";
		return <<<EOD
<td id=acd_content$this->callcount style="{$contentstyle}">
EOD;
	}

}// end class RegionPluginHTMLBuilder

?>
