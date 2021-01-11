<?php
// $Id: pluglist.inc.php,v 1.02 2020/12/05 14:21:00 K Exp $

/** 
* @link http://pkom.ml/?%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/PlugList
* @author K
* @license http://www.gnu.org/licenses/gpl.ja.html GPL
*/

function plugin_pluglist_convert()
{
	if (PKWK_SAFE_MODE) return 'PKWK_SAFE_MODEが有効なので表示できません。'; // Show nothing
	$SCRIPT_DIR = array(PLUGIN_DIR);
	array_push($SCRIPT_DIR);
	$comments = array();
    $plug_count = 0;
	foreach ($SCRIPT_DIR as $sdir)
	{
		if (!$dir = @dir($sdir))
		{
			continue;
		}
		while($file = $dir->read())
		{
			if (!preg_match("/\.(inc.php)$/i",$file))
			{
				continue;
			}
            $plug_count++;
			$data = join('',file($sdir.$file));
			$comment = array('file'=>htmlsc($file),'rev'=>'','date'=>'');
            //mypluglist用 URL:https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/mypluglist.inc.php
            if (preg_match('/\/\*\*/', $data, $matches)) {
                $docc_start = TRUE;
			}
            if ($docc_start){
				if (preg_match('/\@author(.+)/u', $data, $matches)) {
				    $comment['author'] = htmlsc(trim($matches[1]));
				}
        	    if (preg_match('/\@tutorial(.+)/u', $data, $matches)) {
				    $comment['tutorial'] = htmlsc(trim($matches[1]));
				}
				if (preg_match('/\@link(.+)/u', $data, $matches)) {
				    $comment['tutorial'] = htmlsc(trim($matches[1]));
				}
            }
            //--------
            if ((preg_match('/\$'.'Id: (.+),v (\d+\.\d+) (\d{4}-\d{1,2}-\d{1,2} \d{1,2}:\d{1,2}:\d{1,2})Z (.+) \$/u',$data,$matches))||(preg_match('/\$'.'Id: (.+),v (\d+\.\d+) (\d{4}\/\d{1,2}\/\d{1,2} \d{1,2}:\d{1,2}:\d{1,2}) (.+) Exp \$/u',$data,$matches))||(preg_match('/\$'.'Id: (.+),v (\d+\.\d+) (\d{4}\/\d{1,2}\/\d{1,2} \d{1,2}:\d{1,2}:\d{1,2}) (.+) \$/u',$data,$matches))||(preg_match('/\$'.'Id: (.+),v (\d+\.\d+) (\d{4}\/\d{1,2}\/\d{1,2} \d{1,2}:\d{1,2}:\d{1,2})Z (.+) \$/u',$data,$matches))||(preg_match('/\$'.'Id: (.+),v (\d+\.\d+) (\d{4}-\d{1,2}-\d{1,2} \d{1,2}:\d{1,2}:\d{1,2}) (.+) \$/u',$data,$matches)))
			{
				$comment['rev'] = htmlsc($matches[2]);
				$comment['date'] = preg_replace("/-/","/",htmlsc($matches[3]));
				$comment['author'] = htmlsc($matches[4]);
			}
			$comments[$sdir.$file] = $comment;
        }
		$dir->close();
	}
	if (count($comments) == 0)
	{
		return '';
	}
	ksort($comments, SORT_STRING);
	$retval = '';
	foreach ($comments as $comment)
	{
        if (isset($comment['tutorial'])){
            $comment_file = '<a href="'.$comment['tutorial'].'">'.$comment['file'].'</a>';
        }else{
            $comment_file = $comment['file'];
        }
		$retval .= <<<EOD

  <tr>
   <td>{$comment_file}</td>
   <td align="right">{$comment['rev']}</td>
   <td>{$comment['date']}</td>
   <td>{$comment['author']}</td>
  </tr>
EOD;
	}
	$retval = <<<EOD
<table class="style_table" cellspacing="1" border="1">
 <thead>
  <tr>
   <th>ファイル名</th>
   <th>リビジョン</th>
   <th>更新日時</th>
   <th>作者</th>
  </tr>
 </thead>
 <tbody>
{$retval}
<tr>
   <th>合計</th>
   <td colspan="3">{$plug_count}個</td>
</tr>
 </tbody>
</table>
EOD;
	return $retval;
}
?>
