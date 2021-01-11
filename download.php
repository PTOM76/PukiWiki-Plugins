<?php
if (isset($_GET['file']))
{
    $count_path = "./downloader/count/".str_replace('/','-',$_GET['file']).".txt";
    $fp = fopen($count_path, "r");
	$line = fgets($fp);
	$num10 = intval($line);
	fclose($fp);
	$num10++;
	$fp = fopen($count_path, "w");
	fwrite($fp, $num10);
	fclose($fp);
    download($_GET['file']);
}
function download($pPath, $pMimeType = null)
{
    if (!is_readable($pPath)) { die($pPath); }
    $mimeType = (isset($pMimeType)) ? $pMimeType
                                    : (new finfo(FILEINFO_MIME_TYPE))->file($pPath);
    if (!preg_match('/\A\S+?\/\S+/', $mimeType)) {
        $mimeType = 'application/octet-stream';
    }
    header('Content-Type: ' . $mimeType);
    header('X-Content-Type-Options: nosniff');
    header('Content-Length: ' . filesize($pPath));
    header('Content-Disposition: attachment; filename="' . basename($pPath) . '"');
    header('Connection: close');
    while (ob_get_level()) { ob_end_clean(); }
    readfile($pPath);
    exit;
}
?>
