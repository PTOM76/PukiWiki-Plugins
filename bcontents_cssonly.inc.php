<?php
// $Id: bcontents_cssonly.inc.php,v 1.0 2021/01/22 15:42:03 K Exp $

function plugin_bcontents_cssonly_convert()
{
	global $head_tags;
    static $head = false;
    if(!$head){
        $head_tags[] = <<<EOD
        <style>
        div#bcontents_cssonly span#bcontents_cssonly_body ul{
            padding-left:0;
            list-style:none;
            counter-reset:section;
            padding:5px;
        }
        div#bcontents_cssonly span#bcontents_cssonly_body li::before{
            counter-increment:section;
            content:counters(section, "-") " ";
        }
        div#bcontents_cssonly input#bcontents_cssonly_region+span#bcontents_cssonly_body{
            display:none;
        }
        div#bcontents_cssonly input#bcontents_cssonly_region:checked+span#bcontents_cssonly_body{
            border-top:1px dashed #8F8F8F;
            display:block;
        }
        div#bcontents_cssonly input#bcontents_cssonly_region{
            display:none;
        }
        </style>
        EOD;
        $head = true;
    }
	return '<div id="bcontents_cssonly"><table class="style_table" cellspacing="1" border="0"><tbody><tr><td class="style_td"><span style="display:inline-block;width:100%;text-align:center;font-size:15px;min-width:250px;"><strong>目次</strong><a href="javascript:var clicked = true;"><label for="bcontents_cssonly_region" style="font-size:13px;">▼</label></a></span><input id="bcontents_cssonly_region" type="checkbox" checked /><span id="bcontents_cssonly_body"><#_contents_></span></td></tr></tbody></table></div>';
}
?>
