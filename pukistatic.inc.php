<?php
// $Id: pukistatic.inc.php,v 1.1 2024/12/16 14:26:16 PTOM76 $
/**
 * PukiStatic
 *
 * PukiWikiを静的化します
 * 
 * @license MIT License
 * @author Pitan <admin@pitan76.net>
 * @copyright 2023-2024 Pitan
 */
 
define("PUKISTATIC_URL", ""); // 空の場合はそのまま

define("PUKISTATIC_PATHES", 
	[
		"cmd=list",
		"cmd=filelist",
		"cmd=search",
		"cmd=rss",
	]
);

function plugin_pukistatic_action() {
	global $defaultpage, $script;
	
	if (!empty(PUKISTATIC_URL)) {
		$script = PUKISTATIC_URL;
	}

	if (!file_exists("pukistatic/"))
		mkdir("pukistatic/", 0777, true);
	
	$indexphp = file_get_contents("index.php");
	$indexphp = str_replace("define('PKWK_READONLY', 1);\n", "", $indexphp);
	$r_indexphp = str_replace("<?php", "<?php\n" . "define('PKWK_READONLY', 1);", $indexphp);
	file_put_contents('index.php', $r_indexphp);

	$pathes = array_merge(glob("./wiki/*.txt"), PUKISTATIC_PATHES);

	if (exist_plugin('recentupdates'))
		$pathes[] = "plugin=recentupdates";
	
	foreach ($pathes as $path) {
		if (substr($path, -4) == ".txt")
			$page = hex2bin(basename($path, ".txt"));
		else if (false !== strpos($path, 'plugin='))
			$page = $path;
		else 
			$page = $path;
		
		$page = str_replace(' ', '+', $page);
		$data = file_get_contents((empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/?' . $page);
				
		if (empty($data))
			continue;
		
		$c = substr_count($page, '/') + 1;
		$root = str_repeat('../', $c);
		
		if ($page === $defaultpage) $root = './';

		// 
		$dom = new DOMDocument;
		$dom->loadHTML($data);
		$a_tags = $dom->getElementsByTagName('a');
		$img_tags = $dom->getElementsByTagName('img');
		$link_tags = $dom->getElementsByTagName('link');
		$script_tags = $dom->getElementsByTagName('script');
		$all_tags = [$a_tags, $link_tags, $img_tags, $script_tags];
		foreach ($all_tags as $tags) {
			foreach ($tags as $tag) {
				if ($tag->hasAttribute('href')) {
					$href = $tag->getAttribute('href');
					$href = preg_replace('#^\.?/?\??(.+?)$#u', $root . '$1', $href);
					$href = str_replace('..//', '../', $href);
					$tag->setAttribute('href', $href);
				}
				if ($tag->hasAttribute('src')) {
					$href = $tag->getAttribute('src');
					$href = preg_replace('#^\.?/?\??(.+?)$#u', $root . '$1', $href);
					$href = str_replace('..//', '../', $href);
					$tag->setAttribute('src', $href);
				}
			}
		}
		
		$data = $dom->saveHTML();
		
		if ($page == $defaultpage) {
			file_put_contents('pukistatic/index.html', $data);
		} else if (!file_exists("pukistatic/" . $page)) mkdir("pukistatic/" . $page, 0777, true);
			file_put_contents("pukistatic/" . $page . '/index.html', $data);
		
	}
	
	if (!file_exists("pukistatic/" . SKIN_DIR))
		mkdir("pukistatic/" . SKIN_DIR, 0777, true);
	
	foreach (pukistatic_glob(SKIN_DIR . "*", ['css', 'png', 'js']) as $path) {
		$filename = basename($path);
		$dir = preg_replace('/'. preg_quote(SKIN_DIR, "/") . '(.*\/)' . preg_quote($filename) . '/', '$1', $path);
		if (basename($dir) == $filename) $dir = '';
		if (!file_exists("pukistatic/" . SKIN_DIR . $dir))
			mkdir("pukistatic/" . SKIN_DIR . $dir, 0777, true);
		copy($path, 'pukistatic/' . SKIN_DIR . $dir . $filename);
	}
	
	if (!file_exists("pukistatic/" . IMAGE_DIR))
		mkdir("pukistatic/" . IMAGE_DIR, 0777, true);
	
	foreach (pukistatic_glob(IMAGE_DIR . "*", ['jpeg', 'png', 'gif', 'jpg']) as $path) {
		$filename = basename($path);
		$dir = preg_replace('/'. preg_quote(IMAGE_DIR, "/") . '(.*\/)' . preg_quote($filename) . '/', '$1', $path);
		if (basename($dir) == $filename) $dir = '';
		if (!file_exists("pukistatic/" . IMAGE_DIR . $dir))
			mkdir("pukistatic/" . IMAGE_DIR . $dir, 0777, true);
		copy($path, 'pukistatic/' . IMAGE_DIR . $dir . $filename);
	}
	file_put_contents('index.php', $indexphp);
	
	return array('msg' => "生成しました", 'body' => "全ページを静的化しました。<br /><br /><a href='./pukistatic/'>生成されたURL</a>");
}

function pukistatic_glob($dir, $allow_ext) {
    $list = array();
    foreach (glob($dir , GLOB_BRACE) as $path) {
    	if ($c == 3) $path;
        if (is_file($path)) {
        	if (in_array(substr($path, strrpos($path, '.') + 1), $allow_ext))
	            $list[] = $path;
        }
        if (is_dir($path)) {
            $list = array_merge($list, pukistatic_glob($path . "/*", $allow_ext));
        }
    }
    return $list;
}
