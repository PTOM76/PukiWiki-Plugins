<?php
// $Id: bot.inc.php,v 1.3 2022/03/06 13:59:00 Pitqn Exp $

/** 
* @link https://pkom.ml/?プラグイン/bot.inc.php
* @author Pitqn
* @license http://www.gnu.org/licenses/gpl.ja.html GPL
*/

// Require: recent.inc.php

// トークンの指定、作成
define('PLUGIN_BOT_ALLOW_TOKEN_LIST', array("MDhEOTQ2MjE4NjJDRjAwRjdGNzhCNDlEQTgxN0RBMzk"));

// ボット名設定 ("(TOKEN)" => (ボット名))
// こちらでユーザー認証をボットに適用されることもできます
define('PLUGIN_BOT_NAMES', array(
	'DEFAULT' => 'bot',
	'MDhEOTQ2MjE4NjJDRjAwRjdGNzhCNDlEQTgxN0RBMzk' => 'example_bot',
));

// パーミッション設定 ("(TOKEN)" => (パーミッション))
// パーミッションはplugin_bot_define_permissions関数を参照してください
plugin_bot_define_permissions();
define('PLUGIN_BOT_PERMISSIONS', array(
	"DEFAULT" => BOT_PERMISSION_DEFAULT,
	"MDhEOTQ2MjE4NjJDRjAwRjdGNzhCNDlEQTgxN0RBMzk" => BOT_PERMISSION_DEFAULT | BOT_PERMISSION_PAGE_EDIT,
));



// 一覧で取得できるページ数
define('PLUGIN_BOT_LIST_PAGE_NUM_DEFAULT', 50);
define('PLUGIN_BOT_LIST_PAGE_NUM_MAX', 300);



// ここからはサーバーの性能に合わせて設定してください
// 処理時間制限 (0 = 無制限 or 秒数)
ini_set('max_execution_time', 60);

function plugin_bot_action() {
	global $vars;
	if (isset($vars['authorization'])) {
		if (!in_array($vars['authorization'], PLUGIN_BOT_ALLOW_TOKEN_LIST)) {
			PluginBot::showError("the authorization code is wrong", 401, "TOKEN_MISS");
		}
	} else {
		// 認証コードが送信されていない際はDEFAULTトークンとなる
		$vars['authorization'] = "DEFAULT";
	}
	new PluginBot_Bot($vars['authorization']);
}

class PluginBot {
	public static function showError($msg, $code, $error = '') {
		http_response_code($code);
		PluginBot::sendJson(array(
			'code' => $code,
			'msg' => $msg,
			'error' => $error
		));
	}
	
	public static function sendJson($arr) {
		header("Content-Type: application/json; charset=utf-8");
		echo json_encode($arr, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
		exit;
	}
}

class PluginBot_Bot {
	var $token; // トークン
	var $name; // 認証用ネーム
	var $fullname; // フルネーム
	
	function __construct($token) {
		global $vars, $auth_user, $auth_user_fullname;
		$this->token = $token;
		$this->name = (isset(PLUGIN_BOT_NAMES[$token]) ? PLUGIN_BOT_NAMES[$token] : $this->name = "bot");
		
		$this->fullname = "bot-" . $this->name;
		if (isset($vars['fullname']))
			$this->fullname = "bot-" . $vars['fullname'];
		$auth_user = $this->name;
		$auth_user_fullname = $this->fullname;
		$this->proc();
	}
	
	function proc() {
		global $vars;
		if (isset($_SERVER['REQUEST_METHOD']))
			$_METHOD = strtoupper($_SERVER['REQUEST_METHOD']);
		
		if ($_METHOD == "PUT" || $_METHOD == "DELETE") {
			$param = array();
			parse_str(file_get_contents('php://input'), $param);
			$vars = array_merge($vars, $param);
		}
				
		if ($_GET['api'] == "permission") {
			PluginBot::sendJson(array(
				"permission" => PLUGIN_BOT_PERMISSIONS[$this->token],
				"permissions" => array(
					"BOT_PERMISSION_NONE" => BOT_PERMISSION_NONE,
					"BOT_PERMISSION_INFO" => BOT_PERMISSION_INFO,
					"BOT_PERMISSION_PAGE_READ" => BOT_PERMISSION_PAGE_READ,
					"BOT_PERMISSION_PAGE_EDIT" => BOT_PERMISSION_PAGE_EDIT,
					"BOT_PERMISSION_PAGE_LIST" => BOT_PERMISSION_PAGE_LIST,
					"BOT_PERMISSION_PAGE_CHECK" => BOT_PERMISSION_PAGE_CHECK,
					"BOT_PERMISSION_PAGE_SEARCH" => BOT_PERMISSION_PAGE_SEARCH,
					"BOT_PERMISSION_PAGE_TOTAL" => BOT_PERMISSION_PAGE_TOTAL,
					"BOT_PERMISSION_PAGE_RAW" => BOT_PERMISSION_PAGE_RAW,
					"BOT_PERMISSION_PAGE_ALL" => BOT_PERMISSION_PAGE_ALL,
					"BOT_PERMISSION_PLUGIN_EXECUTE" => BOT_PERMISSION_PLUGIN_EXECUTE,
					"BOT_PERMISSION_PLUGIN_LIST" => BOT_PERMISSION_PLUGIN_LIST,
					"BOT_PERMISSION_PLUGIN_CHECK" => BOT_PERMISSION_PLUGIN_CHECK,
					"BOT_PERMISSION_PLUGIN_TOTAL" => BOT_PERMISSION_PLUGIN_TOTAL,
					"BOT_PERMISSION_PLUGIN_ALL" => BOT_PERMISSION_PLUGIN_ALL,
					"BOT_PERMISSION_ATTACH_TOTAL" => BOT_PERMISSION_ATTACH_TOTAL,
					"BOT_PERMISSION_ATTACH_ALL" => BOT_PERMISSION_ATTACH_ALL,
					"BOT_PERMISSION_BACKUP_READ" => BOT_PERMISSION_BACKUP_READ,
					"BOT_PERMISSION_BACKUP_TOTAL" => BOT_PERMISSION_BACKUP_TOTAL,
					"BOT_PERMISSION_BACKUP_ALL" => BOT_PERMISSION_BACKUP_ALL,
					"BOT_PERMISSION_DIFF_READ" => BOT_PERMISSION_DIFF_READ,
					"BOT_PERMISSION_DIFF_TOTAL" => BOT_PERMISSION_DIFF_TOTAL,
					"BOT_PERMISSION_DIFF_ALL" => BOT_PERMISSION_DIFF_ALL,
					"BOT_PERMISSION_TOTAL_ALL" => BOT_PERMISSION_TOTAL_ALL,
					"BOT_PERMISSION_ALL" => BOT_PERMISSION_ALL,
				),
				"info" => (bool) (BOT_PERMISSION_INFO & PLUGIN_BOT_PERMISSIONS[$this->token]),
				"page" => array(
					"read" => (bool) (BOT_PERMISSION_PAGE_READ & PLUGIN_BOT_PERMISSIONS[$this->token]),
					"edit" => (bool) (BOT_PERMISSION_PAGE_EDIT & PLUGIN_BOT_PERMISSIONS[$this->token]),
					"list" => (bool) (BOT_PERMISSION_PAGE_LIST & PLUGIN_BOT_PERMISSIONS[$this->token]),
					"exist" => (bool) (BOT_PERMISSION_PAGE_CHECK & PLUGIN_BOT_PERMISSIONS[$this->token]),
					"search" => (bool) (BOT_PERMISSION_PAGE_SEARCH & PLUGIN_BOT_PERMISSIONS[$this->token]),
					"total" => (bool) (BOT_PERMISSION_PAGE_TOTAL & PLUGIN_BOT_PERMISSIONS[$this->token]),
				),
				"plugin" => array(
					"execute" => (bool) (BOT_PERMISSION_PLUGIN_EXECUTE & PLUGIN_BOT_PERMISSIONS[$this->token]),
					"list" => (bool) (BOT_PERMISSION_PLUGIN_LIST & PLUGIN_BOT_PERMISSIONS[$this->token]),
					"exist" => (bool) (BOT_PERMISSION_PLUGIN_CHECK & PLUGIN_BOT_PERMISSIONS[$this->token]),
					"total" => (bool) (BOT_PERMISSION_PLUGIN_TOTAL & PLUGIN_BOT_PERMISSIONS[$this->token]),
				),
				"attach" => array(
					"total" => (bool) (BOT_PERMISSION_ATTACH_TOTAL & PLUGIN_BOT_PERMISSIONS[$this->token]),
				),
				"backup" => array(
					"read" => (bool) (BOT_PERMISSION_BACKUP_READ & PLUGIN_BOT_PERMISSIONS[$this->token]),
					"total" => (bool) (BOT_PERMISSION_BACKUP_TOTAL & PLUGIN_BOT_PERMISSIONS[$this->token]),
				),
				"diff" => array(
					"read" => (bool) (BOT_PERMISSION_DIFF_READ & PLUGIN_BOT_PERMISSIONS[$this->token]),
					"total" => (bool) (BOT_PERMISSION_DIFF_TOTAL & PLUGIN_BOT_PERMISSIONS[$this->token]),
				),
			));
		}
		
		if ($_GET['api'] == "info") {
			if (BOT_PERMISSION_INFO & ~PLUGIN_BOT_PERMISSIONS[$this->token]) {
				PluginBot::showError("bot has not permission 'BOT_PERMISSION_INFO'", 403, "HAS_NOT_" . "BOT_PERMISSION_INFO");
			}
			
			global $page_title, $modifier, $modifierlink, $nofollow, $nowikiname, $line_break, $usedatetime, 
				$autolink, $autoalias, $autoalias_max_words, $function_freeze, $notimeupdate, $rss_max, 
				$do_backup, $del_backup, $maxshow, $topicpath_title, $maxshow_deleted, $lastmod, $date_format, $time_format;
			PluginBot::sendJson(array(
				"page_title" => $page_title, "modifier" => $modifier, "modifierlink" => $modifierlink,
				"nofollow" => $nofollow, "nowikiname" => $nowikiname, "autolink" => $autolink,
				"autoalias" => $autoalias, "autoalias_max_words" => $autoalias_max_words, "function_freeze" => $function_freeze,
				"notimeupdate" => $notimeupdate, "line_break" => $line_break, "usedatetime" => $usedatetime,
				"rss_max" => $rss_max, "do_backup" => $do_backup, "del_backup" => $del_backup,
				"topicpath_title" => $topicpath_title, "maxshow" => $maxshow, "maxshow_deleted" => $maxshow_deleted,
				"lastmod" => $lastmod, "date_format" => $date_format, "time_format" => $time_format,
				"pukiwiki" => array(
					"version" => S_VERSION,
				),
			));
		}
		
		if ($vars['api'] == "search") {
			if (BOT_PERMISSION_PAGE_SEARCH & ~PLUGIN_BOT_PERMISSIONS[$this->token]) {
				PluginBot::showError("bot has not permission 'BOT_PERMISSION_PAGE_SEARCH'", 403, "HAS_NOT_" . "BOT_PERMISSION_PAGE_SEARCH");
			}
			if (!isset($vars['word'])) PluginBot::showError("word parameter is not set", 400, "NOT_WORD_PARAMETER");
			$type = "AND";
			if (isset($vars['type']))
				$type = strtoupper($vars['type']);
			$pages = do_search($vars['word'], $type, true);
			PluginBot::sendJson($pages);
		}
		
		if ($vars['api'] == "plugin") {
			if (BOT_PERMISSION_PLUGIN_EXECUTE & ~PLUGIN_BOT_PERMISSIONS[$this->token]) {
				PluginBot::showError("bot has not permission 'BOT_PERMISSION_PLUGIN_EXECUTE'", 403, "HAS_NOT_" . "BOT_PERMISSION_PLUGIN_EXECUTE");
			}
			
			if (!isset($vars['plugin_type'])) PluginBot::showError("plugin_type parameter is not set", 400, "NOT_PLUGIN_TYPE_PARAMETER");
			if (!isset($vars['plugin_name'])) PluginBot::showError("plugin_name parameter is not set", 400, "NOT_PLUGIN_NAME_PARAMETER");
			$type = strtolower($vars['plugin_type']);
			$name = strtolower($vars['plugin_name']);
			if (isset($vars['plugin_args'])) $args = $vars['plugin_args'];
			
			if (!exist_plugin($name))
				PluginBot::showError("the plugin do not exist", 500, "NOT_EXIST_PLUGIN");			
			if ($vars['plugin_type'] == "action") {
				if (exist_plugin_action($name)) {
					do_plugin_action($name);
					PluginBot::showError("executed action function", 201, "PLUGIN_EXECUTED");
				}
			}
			if ($vars['plugin_type'] == "convert") {
				if (exist_plugin_convert($name)) {
					if (isset($args))
						do_plugin_convert($name, $args);
					else
						do_plugin_convert($name);
					
					PluginBot::showError("executed convert function", 201, "PLUGIN_EXECUTED");
				}
			}
			if ($vars['plugin_type'] == "inline") {
				if (exist_plugin_inline($name)) {
					if (isset($args))
						do_plugin_inline($name, $args);
					else
						do_plugin_inline($name);
					
					PluginBot::showError("executed action function", 201, "PLUGIN_EXECUTED");
				}
			}
			$pluginlist = glob(PLUGIN_DIR . '*.inc.php');
			PluginBot::sendJson($pluginlist);
		}
		
		if ($vars['api'] == "pluginlist") {
			if (BOT_PERMISSION_PLUGIN_LIST & ~PLUGIN_BOT_PERMISSIONS[$this->token]) {
				PluginBot::showError("bot has not permission 'BOT_PERMISSION_PLUGIN_LIST'", 403, "HAS_NOT_" . "BOT_PERMISSION_PLUGIN_LIST");
			}
			
			$pluginlist = array_map(function($filename) {
				return substr($filename, 0, -8); // inc.phpを取り除く
			}, array_map('basename', glob(PLUGIN_DIR . '*.inc.php')));
			PluginBot::sendJson($pluginlist);
		}
		
		if ($vars['api'] == "exist") {
			if (!isset($vars['type'])) PluginBot::showError("type parameter is not set", 400, "NOT_TYPE_PARAMETER");
			if ($vars['type'] == "page") {
				if (BOT_PERMISSION_PAGE_CHECK & ~PLUGIN_BOT_PERMISSIONS[$this->token]) {
					PluginBot::showError("bot has not permission 'BOT_PERMISSION_PAGE_CHECK'", 403, "HAS_NOT_" . "BOT_PERMISSION_PAGE_CHECK");
				}
				if (!isset($vars['name'])) PluginBot::showError("name parameter is not set", 400, "NOT_NAME_PARAMETER");
				
				PluginBot::sendJson(array(
					"type" => "page",
					"name" => $vars['name'],
					"exist" => is_page($vars['name'])
				));
			}
			if ($vars['type'] == "plugin") {
				if (BOT_PERMISSION_PAGE_CHECK & ~PLUGIN_BOT_PERMISSIONS[$this->token]) {
					PluginBot::showError("bot has not permission 'BOT_PERMISSION_PAGE_CHECK'", 403, "HAS_NOT_" . "BOT_PERMISSION_PAGE_CHECK");
				}
				if (!isset($vars['name'])) PluginBot::showError("name parameter is not set", 400, "NOT_NAME_PARAMETER");
				
				PluginBot::sendJson(array(
					"type" => "plugin",
					"name" => $vars['name'],
					"exist" => exist_plugin($vars['name'])
				));
			}
			
		}
		
		if ($vars['api'] == "total") {
			if (!isset($vars['type'])) PluginBot::showError("type parameter is not set", 400, "NOT_TYPE_PARAMETER");
			if ($vars['type'] == "page") {
				if (BOT_PERMISSION_PAGE_TOTAL & ~PLUGIN_BOT_PERMISSIONS[$this->token]) {
					PluginBot::showError("bot has not permission 'BOT_PERMISSION_PAGE_TOTAL'", 403, "HAS_NOT_" . "BOT_PERMISSION_PAGE_TOTAL");
				}
				PluginBot::sendJson(array(
					"type" => "page",
					"total" => count(glob(DATA_DIR . "*.txt"))
				));
			}
			
			if ($vars['type'] == "plugin") {
				if (BOT_PERMISSION_PLUGIN_TOTAL & ~PLUGIN_BOT_PERMISSIONS[$this->token]) {
					PluginBot::showError("bot has not permission 'BOT_PERMISSION_PLUGIN_TOTAL'", 403, "HAS_NOT_" . "BOT_PERMISSION_PLUGIN_TOTAL");
				}
				PluginBot::sendJson(array(
					"type" => "plugin",
					"total" => count(glob(PLUGIN_DIR . "*.php"))
				));
			}
			
			if ($vars['type'] == "attach") {
				if (BOT_PERMISSION_ATTACH_TOTAL & ~PLUGIN_BOT_PERMISSIONS[$this->token]) {
					PluginBot::showError("bot has not permission 'BOT_PERMISSION_ATTACH_TOTAL'", 403, "HAS_NOT_" . "BOT_PERMISSION_ATTACH_TOTAL");
				}
				PluginBot::sendJson(array(
					"type" => "attach",
					"total" => (count(glob(UPLOAD_DIR . "*")) - count(glob(UPLOAD_DIR . "*.html")) - count(glob(UPLOAD_DIR . "*.log")))
				));
			}
			if ($vars['type'] == "backup") {
				if (BOT_PERMISSION_BACKUP_TOTAL & ~PLUGIN_BOT_PERMISSIONS[$this->token]) {
					PluginBot::showError("bot has not permission 'BOT_PERMISSION_BACKUP_TOTAL'", 403, "HAS_NOT_" . "BOT_PERMISSION_BACKUP_TOTAL");
				}
				PluginBot::sendJson(array(
					"type" => "backup",
					"total" => count(glob(BACKUP_DIR . "*.gz"))
				));
			}
			if ($vars['type'] == "diff") {
				if (BOT_PERMISSION_DIFF_TOTAL & ~PLUGIN_BOT_PERMISSIONS[$this->token]) {
					PluginBot::showError("bot has not permission 'BOT_PERMISSION_DIFF_TOTAL'", 403, "HAS_NOT_" . "BOT_PERMISSION_DIFF_TOTAL");
				}
				PluginBot::sendJson(array(
					"type" => "diff",
					"total" => count(glob(DIFF_DIR . "*.txt"))
				));
			}
		}
		
		if ($vars['api'] == "pagelist") {
			if (BOT_PERMISSION_PAGE_LIST & ~PLUGIN_BOT_PERMISSIONS[$this->token]) {
				PluginBot::showError("bot has not permission 'BOT_PERMISSION_PAGE_LIST'", 403, "HAS_NOT_" . "BOT_PERMISSION_PAGE_LIST");
			}
			if (!exist_plugin("recent")) {
				PluginBot::showError("recent.inc.php do not exist", 500, "NOT_EXIST_RECENT_PLUGIN");
			}
			
			if (!file_exists(PLUGIN_RECENT_CACHE)) {
				put_lastmodified();
				if (!file_exists(PLUGIN_RECENT_CACHE)) {
					PluginBot::showError(PLUGIN_RECENT_CACHE . " not found", 500, "NOT_EXIST_RECENT_CACHE_FILE");
				}
			}
			$extlines = PLUGIN_BOT_LIST_PAGE_NUM_DEFAULT;
			if (isset($vars['limit'])) {
				$extlines = (int) $vars['limit'];
				if ($extlines > PLUGIN_BOT_LIST_PAGE_NUM_MAX) $extlines = PLUGIN_BOT_LIST_PAGE_NUM_MAX;
			}
			if (isset($vars['pos'])) {
				$pos = (int) $vars['pos'];
				$extlines += $pos;
			}
			$lines = file_head(PLUGIN_RECENT_CACHE, $extlines);
			if (isset($vars['pos'])) {
				$lines = array_slice($lines, $pos);
			}
			if ($lines == FALSE) PluginBot::showError(PLUGIN_RECENT_CACHE . ' can not open', 500, "CAN_NOT_OPEN_RECENT_CACHE_FILE");
			$pagelist = array();
			foreach ($lines as $line) {
				list($time, $page) = explode("\t", rtrim($line));
				$pagelist[] = array("name" => $page, "time" => $time);
			}
			
			PluginBot::sendJson($pagelist);
		}
		
		if ($vars['api'] == "backup") {
			if (isset($_GET['name'])) {
				if (BOT_PERMISSION_BACKUP_READ & ~PLUGIN_BOT_PERMISSIONS[$this->token]) {
					PluginBot::showError("bot has not permission 'BOT_PERMISSION_BACKUP_READ'", 403, "HAS_NOT_" . "BOT_PERMISSION_BACKUP_READ");
				}
				$name = $_GET['name'];
				if (isset($vars['age'])) $age = (int) $vars['age'];
				$backups = get_backup($name);
				$age_count = count($backups);
				
				if (!isset($age) && $age_count > 0) {
					$backuplist = array();
					foreach($backups as $age => $value) {
						$backuplist[] = array(
							"age" => $age,
							"unixtime" => $value['time'],
							"time" => date("Y-m-d\TH:i:s", $value['time']),
						);
					}
					
					PluginBot::sendJson(array(
						"backup_count" => $age_count,
						"backups" => $backuplist
					));
				}
				
				if ($age > $age_count) $age = $age_count;
				if (isset($backups[$age]['data'])) {
					$source = join('', $backups[$age]['data']);
				} else if (is_page($name)) {
					PluginBot::showError("backup is not found", 404, "BACKUP_NOT_FOUND");
				} else {
					PluginBot::showError("page is not found", 404, "PAGE_NOT_FOUND");
				}
				
				PluginBot::sendJson(array(
					"name" => $name,
					"source" => $source
				));
			}
		}
		
		if ($vars['api'] == "diff") {
			if (isset($_GET['name'])) {
				if (BOT_PERMISSION_DIFF_READ & ~PLUGIN_BOT_PERMISSIONS[$this->token]) {
					PluginBot::showError("bot has not permission 'BOT_PERMISSION_DIFF_READ'", 403, "HAS_NOT_" . "BOT_PERMISSION_DIFF_READ");
				}
				$name = $_GET['name'];
				
				$filename = DIFF_DIR . encode($name) . '.txt';
				if (file_exists($filename)) {
					$source = join('', file($filename));
				} else if (is_page($name)) {
					PluginBot::showError("diff is not found", 404, "DIFF_NOT_FOUND");
				} else {
					PluginBot::showError("page is not found", 404, "PAGE_NOT_FOUND");
				}
				
				PluginBot::sendJson(array(
					"name" => $name,
					"source" => $source
				));
			}
		}
		if ($vars['api'] == "attach") {
			
		}

		if ($vars['api'] == "page") {
			if (isset($vars['name']) && $_METHOD == "DELETE") {
				if (BOT_PERMISSION_PAGE_EDIT & ~PLUGIN_BOT_PERMISSIONS[$this->token]) {
					PluginBot::showError("bot has not permission 'BOT_PERMISSION_PAGE_EDIT'", 403, "HAS_NOT_" . "BOT_PERMISSION_PAGE_EDIT");
				}
				page_write($vars['name'], "");
				PluginBot::showError("page deleted", 200, "PAGE_DELETED");
			}
			
			if (isset($vars['name']) && $_METHOD == "PUT") {
				if (BOT_PERMISSION_PAGE_EDIT & ~PLUGIN_BOT_PERMISSIONS[$this->token]) {
					PluginBot::showError("bot has not permission 'BOT_PERMISSION_PAGE_EDIT'", 403, "HAS_NOT_" . "BOT_PERMISSION_PAGE_EDIT");
				}
				if (isset($vars['notimestamp'])) {
					if (strtoupper($vars['notimestamp']) == "TRUE") {
						$vars['notimestamp'] = true;
					} else {
						$vars['notimestamp'] = false;
					}
				}
				if (!isset($vars['source'])) PluginBot::showError("source parameter is not set", 400, "NOT_SOURCE_PARAMETER");
				if (!is_editable($vars['name'])) PluginBot::showError("the page is not editable", 403, "PAGE_NOT_EDITABLE");
				page_write($vars['name'], $vars['source'], isset($vars['notimestamp']) ? $vars['notimestamp'] : FALSE);
				PluginBot::showError("page wrote", 201, "PAGE_WROTE");
			}
			
			if (isset($_GET['name'])) {
				if (BOT_PERMISSION_PAGE_READ & ~PLUGIN_BOT_PERMISSIONS[$this->token]) {
					PluginBot::showError("bot has not permission 'BOT_PERMISSION_PAGE_READ'", 403, "HAS_NOT_" . "BOT_PERMISSION_PAGE_READ");
				}
				$name = $_GET['name'];
				if (!is_page($name))
					PluginBot::showError("page is not found", 404, "PAGE_NOT_FOUND");
				if (!read_auth($name)) PluginBot::showError("page is not readable", 403, "PAGE_NOT_READABLE");
				$source = get_source($name, true, true);
				$author_info = get_author_info($source);
				if (preg_match('/^#author\("(.*?)","(.*?)","(.*?)"\)/', $author_info, $m)) {
					$time = $m[1];
					$username = $m[2];
					$fullname = $m[3];
				} else {
					$time = "";
					$username = "";
					$fullname = "";
				}
				$attaches = array();
				if (exist_plugin("attach")) {
					$obj = new AttachPages($name, 0);
					if (isset($obj->pages[$name]))
						$attaches = array_keys($obj->pages[$name]->files);
				}
				
				PluginBot::sendJson(array(
					"name" => $name,
					"source" => $source,
					"modified_unixtime" => get_filetime($name),
					"modified_time" => $time,
					"author_username" => $username,
					"author_fullname" => $fullname,
					"editable" => is_editable($name),
					"freeze" => is_freeze($name),
					"attaches" => $attaches,
				));
			}
		}
		PluginBot::showError("parameter is invalid", 400, "NOT_API_PARAMETER");
	}
}

// パーミッションの定義
function plugin_bot_define_permissions() {
	$GEN_FLAG = function() {
		static $flag = 0b1;
		$result = $flag;
		$flag = $flag << 1;
		return $result;
	};
	
	define('BOT_PERMISSION_NONE', 0b0); // なし
	
	// 情報関連のパーミッション
	define('BOT_PERMISSION_INFO', $GEN_FLAG()); // 情報取得
	
	// ページ関連のパーミッション
	define('BOT_PERMISSION_PAGE_READ', $GEN_FLAG()); // 読み取り
	define('BOT_PERMISSION_PAGE_EDIT', $GEN_FLAG()); // 書き込み
	define('BOT_PERMISSION_PAGE_LIST', $GEN_FLAG()); // 一覧取得
	define('BOT_PERMISSION_PAGE_CHECK', $GEN_FLAG()); // 存在チェック
	define('BOT_PERMISSION_PAGE_SEARCH', $GEN_FLAG()); // 検索
	define('BOT_PERMISSION_PAGE_TOTAL', $GEN_FLAG()); // 合計
	
	define('BOT_PERMISSION_PAGE_RAW', BOT_PERMISSION_PAGE_READ | BOT_PERMISSION_PAGE_EDIT); // 読み取り / 書き込み

	define('BOT_PERMISSION_PAGE_ALL', BOT_PERMISSION_PAGE_SEARCH | BOT_PERMISSION_PAGE_TOTAL | BOT_PERMISSION_PAGE_CHECK | BOT_PERMISSION_PAGE_RAW | BOT_PERMISSION_PAGE_LIST); // すべて
	
	// プラグイン関連のパーミッション
	define('BOT_PERMISSION_PLUGIN_EXECUTE', $GEN_FLAG()); // 実行
	define('BOT_PERMISSION_PLUGIN_LIST', $GEN_FLAG()); // 一覧
	define('BOT_PERMISSION_PLUGIN_CHECK', $GEN_FLAG()); // 存在チェック
	define('BOT_PERMISSION_PLUGIN_TOTAL', $GEN_FLAG()); // 合計

	define('BOT_PERMISSION_PLUGIN_ALL', BOT_PERMISSION_PLUGIN_EXECUTE | BOT_PERMISSION_PLUGIN_LIST | BOT_PERMISSION_PLUGIN_CHECK | BOT_PERMISSION_PLUGIN_TOTAL); // すべて
	
	// 添付関連のパーミッション
	define('BOT_PERMISSION_ATTACH_TOTAL', $GEN_FLAG()); // 合計
	define('BOT_PERMISSION_ATTACH_ALL', BOT_PERMISSION_ATTACH_TOTAL); // すべて
	
	// 履歴関連のパーミッション
	define('BOT_PERMISSION_BACKUP_READ', $GEN_FLAG()); // 読み取り / 一覧
	define('BOT_PERMISSION_BACKUP_TOTAL', $GEN_FLAG()); // 合計
	define('BOT_PERMISSION_BACKUP_ALL', BOT_PERMISSION_BACKUP_TOTAL | BOT_PERMISSION_BACKUP_READ); // すべて
	
	// 差分関連のパーミッション
	define('BOT_PERMISSION_DIFF_READ', $GEN_FLAG()); // 読み取り
	define('BOT_PERMISSION_DIFF_TOTAL', $GEN_FLAG()); // 合計
	define('BOT_PERMISSION_DIFF_ALL', BOT_PERMISSION_DIFF_TOTAL | BOT_PERMISSION_DIFF_READ); // すべて
	
	// 合計関連のパーミッション
	define('BOT_PERMISSION_TOTAL_ALL', BOT_PERMISSION_PAGE_TOTAL | BOT_PERMISSION_PLUGIN_TOTAL | BOT_PERMISSION_ATTACH_TOTAL | BOT_PERMISSION_BACKUP_TOTAL | BOT_PERMISSION_DIFF_TOTAL);
	
	// すべてのパーミッション
	define("BOT_PERMISSION_ALL", BOT_PERMISSION_INFO | BOT_PERMISSION_PAGE_ALL | BOT_PERMISSION_PLUGIN_ALL | BOT_PERMISSION_ATTACH_ALL | BOT_PERMISSION_BACKUP_ALL | BOT_PERMISSION_DIFF_ALL | BOT_PERMISSION_TOTAL_ALL);
	
	// デフォルト用のパーミッション (書き込みと実行と検索以外はすべて)
	define('BOT_PERMISSION_DEFAULT',BOT_PERMISSION_INFO | BOT_PERMISSION_INFO | BOT_PERMISSION_PAGE_READ | BOT_PERMISSION_PAGE_LIST | BOT_PERMISSION_PAGE_CHECK
	 | BOT_PERMISSION_PLUGIN_LIST | BOT_PERMISSION_PLUGIN_CHECK | BOT_PERMISSION_BACKUP_READ | BOT_PERMISSION_DIFF_READ
	 | BOT_PERMISSION_TOTAL_ALL);
}