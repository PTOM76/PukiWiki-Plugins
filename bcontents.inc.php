<?php
// $Id: bcontents.inc.php,v 1.0 2021/01/22 16:46:21 K Exp $

function plugin_bcontents_convert()
{
    static $head = false;
    static $bcontents_num = 0;
    ++$bcontents_num;
    if(!$head){
        $embed_tags = <<<EOD
        <style>
        div#bcontents span.bcontents_body ul{
            padding-left:0;
            list-style:none;
            counter-reset:section;
            padding:5px;
        }
        div#bcontents span.bcontents_body li::before{
            counter-increment:section;
            content:counters(section, "-") " ";
        }
        div#bcontents span#bcontents_title+span.bcontents_body{
            border-top:1px dashed #8F8F8F;
            display:block;
        }
        div#bcontents span#bcontents_title{
            display:none;
        }
        </style>
        <script>
        function bcontents_click(bcontents_num)
        {
            bcontents_open = '' + "bcontents_region_close" + bcontents_num;
            bcontents_close = '' + "bcontents_region_open" + bcontents_num;
            if(document.getElementById(bcontents_close).style.display == "none") {
                document.getElementById(bcontents_close).style.display = "inline";
                document.getElementById(bcontents_open).style.display = "none";
                document.getElementById('bcontents_body' + bcontents_num).style.display = "block";
                document.getElementById('bcontents_body' + bcontents_num).animate([{opacity: '0'}, {opacity: '1'}], 500);
                document.getElementById('bcontents_body' + bcontents_num).style.display = "block";
            }else{
                document.getElementById(bcontents_close).style.display = "none";
                document.getElementById(bcontents_open).style.display = "inline";
                document.getElementById('bcontents_body' + bcontents_num).animate([{opacity: '1'}, {opacity: '0'}], 250);
                setTimeout(function(){document.getElementById('bcontents_body' + bcontents_num).style.display = "none";}, 200)
            }
        }
        </script>
        EOD;
        $head = true;
    }else{
        $embed_tags = '';
    }
	return $embed_tags . '<div id="bcontents"><table class="style_table" cellspacing="1" border="0"><tbody><tr><td class="style_td"><span id="bcontents_title" style="display:inline-block;width:100%;text-align:center;font-size:15px;min-width:250px;"><strong>目次</strong><a href="javascript:bcontents_click(' . $bcontents_num . ');"><span id="bcontents_region_open' . $bcontents_num . '" style="font-size:13px;display:inline;">▼</span></a><a href="javascript:bcontents_click(' . $bcontents_num . ');"><span id="bcontents_region_close' . $bcontents_num . '" style="font-size:13px;display:none;">◀</span></a></span><span class="bcontents_body" id="bcontents_body' . $bcontents_num . '"><#_contents_></span></td></tr></tbody></table></div>';
}
?>
