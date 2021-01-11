<?php
// $Id: region2.inc.php,v 1.3 2020/11/11 07:09:33 K,xxxxx Exp $

/** 
* @link http://pkom.ml/
* @author K,xxxxx
* @license http://www.gnu.org/licenses/gpl.ja.html GPL
*/

function plugin_region2_convert()
{
	static $builder = 0;
	if( $builder==0 ) $builder = new Region2PluginHTMLBuilder();

	$builder->setDefaultSettings();

	if (func_num_args() >= 2){
		$args = func_get_args();
		$builder->setDescription( array_shift($args) );
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


class Region2PluginHTMLBuilder
{
	var $description;
	var $isopened;
	var $scriptVarName;
	var $callcount;

	function Region2PluginHTMLBuilder() {
		$this->callcount = 0;
		$this->setDefaultSettings();
	}
	function setDefaultSettings(){
		$this->description = "...";
		$this->isopened = false;
	}
	function setClosed(){ $this->isopened = false; }
	function setOpened(){ $this->isopened = true; }
	function setDescription($description){
		$this->description = convert_html($description);
		$this->description = preg_replace( "/^<p>/i", "", $this->description);
		$this->description = preg_replace( "/<\/p>$/i", "", $this->description);
	}
	function build(){
		$this->callcount++;
		$html = array();
		array_push( $html, $this->buildButtonHtml() );
		array_push( $html, $this->buildBracketHtml() );
		array_push( $html, $this->buildSummaryHtml() );
		array_push( $html, $this->buildContentHtml() );
		return join($html);
	}

	function buildButtonHtml(){
		$button = ($this->isopened) ? "-" : "+";
		return <<<EOD
<table cellpadding=1 cellspacing=2><tr>
<td valign=top>
	<span id=rgn2_button$this->callcount style="cursor:pointer;font:normal 15px ＭＳ Ｐゴシック;border:gray 1px solid;"
	onclick="
	if(document.getElementById('rgn2_summary$this->callcount').style.display!='none'){
		document.getElementById('rgn2_summary$this->callcount').style.display='none';
		document.getElementById('rgn2_content$this->callcount').style.display='block';
		document.getElementById('rgn2_bracket$this->callcount').style.borderStyle='solid none solid solid';
		document.getElementById('rgn2_button$this->callcount').innerHTML='-';
	}else{
		document.getElementById('rgn2_summary$this->callcount').style.display='block';
		document.getElementById('rgn2_content$this->callcount').style.display='none';
		document.getElementById('rgn2_bracket$this->callcount').style.borderStyle='none';
		document.getElementById('rgn2_button$this->callcount').innerHTML='+';
	}
	">$button</span>
</td>
EOD;
	}

	function buildBracketHtml(){
		$bracketstyle = ($this->isopened) ? "border-style: solid none solid solid;" : "border-style:none;";
		return <<<EOD
<td id=rgn2_bracket$this->callcount style="font-size:1pt;border:gray 1px;{$bracketstyle}">&nbsp;</td>
EOD;
	}

	function buildSummaryHtml(){
		$summarystyle = ($this->isopened) ? "display:none;" : "display:block;";
		return <<<EOD
<td id=rgn2_summary$this->callcount style="color:gray;border:gray 1px solid;{$summarystyle}">$this->description</td>
EOD;
	}

	function buildContentHtml(){
		$contentstyle = ($this->isopened) ? "display:block;" : "display:none;";
		return <<<EOD
<td valign=top id=rgn2_content$this->callcount style="{$contentstyle}">
EOD;
	}

}// end class RegionPluginHTMLBuilder

?>
