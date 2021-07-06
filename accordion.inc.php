<?php
// $Id: accordion.inc.php,v 1.4 2021/07/06 00:00:00 K Exp $

/** 
* @link http://pkom.ml/?%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/accordion.inc.php
* @author K
* @license http://www.gnu.org/licenses/gpl.ja.html GPL
*/

// region.inc.php(author:xxxxx) https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/region.inc.php

//-Accordionプラグインの定義

// 見出し(true) or 開くボタン(false) で開閉
define('PLUGIN_ACCORDION_CLICK_HEADER', true);

// falseで<head>に、trueで<body>にスタイルの要素を生成します
define('PLUGIN_ACCORDION_COMPATIBILITY_STYLE', false);

// スタイルシートの定義
define('PLUGIN_ACCORDION_STYLE', 
<<<EOD
#acd_btn {
	cursor:pointer;
}
#acd_btn span {
	cursor:pointer;
	font-family: "ＭＳ Ｐゴシック", "MS PGothic", "ヒラギノ角ゴ Pro W3","Hiragino Kaku Gothic Pro", Osaka, arial, verdana, sans-serif;
	padding:1px 4px;
	border:gray 1px solid;
	color:gray;
}
EOD);

function plugin_accordion_convert() {
	static $builder = 0;
	if ($builder == 0) $builder = new AccordionPlugin();
	$builder -> setDefaultSettings();
	if (func_num_args() >= 3){
		$args = func_get_args();
		$builder -> setDescription( array_shift($args) );
		$builder -> setHeading( array_shift($args) );
		foreach( $args as $value ){
			if( preg_match("/^open/i", $value) ) {
				$builder -> setOpened();
				continue;
			}
		if( preg_match("/^close/i", $value) ) {
				$builder -> setClosed();
				continue;
			}
		}
	}
	$args = func_get_args();
	$contents = $args[func_num_args()-1];
	$contents = preg_replace("/\r\n|\r/", "\n", $contents);
	$contents = explode("\n", $contents);
	return $builder -> build()
	.convert_html($contents)
	.'</td></tr></table>';
}

class AccordionPlugin {
	private $description;
	private $heading;
	private $isOpened;
	private $scriptVarName;
	private $callCount;
	
	public function __construct() {
		$this -> callCount = 0;
		$this -> setDefaultSettings();
	}

	public function setDefaultSettings() {
		$this -> description = "...";
		$this -> heading = "h2";
		$this -> isOpened = false;
	}

	public function setClosed() { $this -> isOpened = false; }
	public function setOpened() { $this -> isOpened = true; }

	public function setDescription($description) {
		$this -> description = convert_html($description);
		$this -> description = preg_replace( "/^<p>/i", "", $this -> description);
		$this -> description = preg_replace( "/<\/p>$/i", "", $this -> description);
	}

	public function setHeading($heading) {
		if (($heading == "1" ) || ( $heading == "*")) $this -> heading = "h2";
		if (($heading == "2" ) || ( $heading == "**")) $this -> heading = "h3";
		if (($heading == "3" ) || ( $heading == "***")) $this -> heading = "h4";
		if (($heading == "4" ) || ( $heading == "****")) $this -> heading = "h5";
	}

	public function build() {
		$this -> callCount++;
		$html = array();
		if ($this -> callCount <= 1){
			$style = "<style>\n" . PLUGIN_ACCORDION_STYLE . "\n</style>\n";
			if (PLUGIN_ACCORDION_COMPATIBILITY_STYLE) {
				array_push($html, $style);
			} else {
				global $head_tags;
				$head_tags[] .= $style;
			}
		}
		array_push( $html, $this -> buildButtonHtml() );
		array_push( $html, $this -> buildContentHtml() );
		return join($html);
	}

	private function buildButtonHtml() {
		$button = ($this -> isOpened) ? "-" : "+";
		
		$onClick = <<<EOD
		onclick="
		if(document.getElementById('acd_content{$this -> callCount}').style.display!='inline'){
			document.getElementById('acd_content{$this -> callCount}').style.display='inline';
			document.getElementById('acd_button{$this -> callCount}').innerHTML='-';
		}else{
			document.getElementById('acd_content{$this -> callCount}').style.display='none';
			document.getElementById('acd_button{$this -> callCount}').innerHTML='+';
		}
		"
		EOD;
		
		$onHeaderClick = "";
		$onSpanClick = "";
		if (PLUGIN_ACCORDION_CLICK_HEADER) {
			$onHeaderClick = $onClick;
		} else {
			$onSpanClick = $onClick;
			EOD;
		}
		return <<<EOD
		<{$this -> heading} id="acd_btn" $onHeaderClick>
			<span id=acd_button{$this -> callCount} $onSpanClick>$button</span>&nbsp;{$this -> description}
		</{$this -> heading}>
		<table><tr>
		EOD;
	}

	private function buildContentHtml() {
		$contentStyle = ($this -> isOpened) ? "display:inline;" : "display:none;";
		return <<<EOD
		<td id=acd_content{$this -> callCount} style="{$contentStyle}">
		EOD;
	}
}
