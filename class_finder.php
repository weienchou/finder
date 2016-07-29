<?php 
class Finder {
	var $current_type = Array();
	var $current_keyword = Array();
	var $repeat_category = Array();
	var $tmp_category = Array();
	var $limit_woods = -1;
	var $current_limit_woods = 0;

	var $start_time = '';
	var $stop_time = '';

	function __construct($type) {
		ini_set('max_execution_time', 0);

		//*  // 我要顯示 error message
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL ^ E_DEPRECATED);
		//*/

		$this->db_connect();
		$this->check_type($type);

		if($_GET) {
			if(isset($_GET['keyword']) && !empty($_GET['keyword'])) {
				$this->current_keyword = (array)mysql_real_escape_string($_GET['keyword']);
			} else {
				$this->get_keyword();
			}

			if(isset($_GET['limit']) && !empty($_GET['limit'])) {
				$is_int = ( !is_int($_GET['limit']) ? (ctype_digit($_GET['limit'])) : true );
				$this->limit_woods = ($is_int) ? $_GET['limit'] : -1;
			}
		} else {
			$this->get_keyword();
		}
	}

	// 資料庫連線 function
	function db_connect() {
		$dbhost = 'localhost';
	    $dbuser = 'root';
	    $dbpass = 'root';
	    $dbname = 'finder';
	    $conn = mysql_connect($dbhost, $dbuser, $dbpass) or $this->finder_error("資料庫連結錯誤", mysql_error(), 999 );
	    mysql_query("SET NAMES 'utf8'");
	    mysql_select_db($dbname);
	}

	function set_start_time() {
		$this->start_time = strtotime('now');
	}

	function set_stop_time() {
		$this->stop_time = strtotime('now');
	}

	function show_time() {
		$diff = floor($this->stop_time-$this->start_time);
		echo '<hr />';
		echo 'Start '.date('Y/m/d H:i:s', $this->start_time).' . <br />';
		echo 'Stop '.date('Y/m/d H:i:s', $this->stop_time).' . <br />';
		echo 'Spend '.$diff.' s. <br />';
	}

	// 檢查 construct type 是否有在 db 中
	function check_type($type) {
		$sql = "
			SELECT * 

			FROM `finder_type`

			WHERE `finder_type`.`ftname` LIKE '%{$type}%';";

		$result = mysql_fetch_assoc(mysql_query($sql)) or $this->finder_error("Check Type Error.", mysql_error(), 888 );
		if(empty($result)) {
			die('Finder construct error.');
		} else {
			$this->current_type = $result;
		}
	}

	//由 db 中取得關鍵字
	function get_keyword() {
		$this->current_keyword = Array(
			'apple'
		);
	}

	// 由 $url 網址取得網頁原始碼， $post_data 為要傳送的 post_data
	function get_html_code($url = '', $post = false, $post_data = Array()) {
		if(empty($url)) return '';

		$options = array(
	        CURLOPT_RETURNTRANSFER => true,
	        CURLOPT_HEADER         => true,
	        CURLOPT_FOLLOWLOCATION => true,
	        CURLOPT_VERBOSE		   => true,
	        CURLOPT_ENCODING       => '',
	        CURLOPT_USERAGENT      => 'Mozilla/5.0 (WAYNE) Gecko/20120101 Firefox/33.0',
	        CURLOPT_AUTOREFERER    => true,
	        CURLOPT_CONNECTTIMEOUT => 120,
	        CURLOPT_TIMEOUT        => 180,
	        CURLOPT_COOKIEJAR	   => 'cookie/'.$this->current_type['ftname'].'_cookie.txt',
	        CURLOPT_BUFFERSIZE	   => 128,
	        CURLOPT_COOKIEFILE	   => 'cookie/'.$this->current_type['ftname'].'_cookie.txt'
	    ); 

	    if($post == true && !empty($post_data)) {
			$options[CURLOPT_POST] = true;
			$options[CURLOPT_POSTFIELDS] = http_build_query($post_data);
	    }

	    $aCurlopt = curl_init($url);
	    curl_setopt_array($aCurlopt, $options);

	    $response_html_code  = curl_exec($aCurlopt);

	    if($response_html_code === false) $this->finder_error("Get Html Error.", '['.$url.'] '.curl_error($aCurlopt), 900 );

	    $header_size = curl_getinfo($aCurlopt, CURLINFO_HEADER_SIZE);
		$header = substr($response_html_code, 0, $header_size);
		$body = substr($response_html_code, $header_size);
		$this->finder_log($url, json_encode($post_data), $header, $body);

	    curl_close($aCurlopt);
	    flush();
		ob_flush();
		sleep($this->current_type['ftsleep_time']);

	    return $body;
	}

	function find_woods($wid) {
		// echo '　　find_woods '.$wid.'<br />';
		$sql = "
			SELECT * 
			FROM `finder_goods` 
			WHERE `finder_goods`.`fguid` = '{$wid}';";

		$result = mysql_query($sql) or $this->finder_error("Find Woods Error.", mysql_error(), 888 );
		$count = mysql_num_rows($result);

		if($count == 0) {
			return Array();
		} else {
			return mysql_fetch_assoc($result);
		}
	}

	function update_woods($wid, $wname, $wprice, $woffer, $wpic) {
		if(empty($wid) || empty($wname)) return false;
		// echo '　update_woods '.$wid.'<br />';

		$wid 		= mysql_real_escape_string($wid);
		$wname 		= mysql_real_escape_string($wname);
		$wprice 	= mysql_real_escape_string($wprice);
		$woffer 	= mysql_real_escape_string($woffer);
		$wpic 		= mysql_real_escape_string($wpic);

		$sql = "
			UPDATE `finder_goods` 
			SET 
				`fgname`		= '{$wname}',
				`fgprice`		= '{$wprice}',
				`fgoffer`		= '{$woffer}',
				`fgpic_url`		= '{$wpic}'
			WHERE `fguid` = '{$wid}'  ;";

		mysql_query($sql) or $this->finder_error("Update Woods Error.", mysql_error(), 888 );
	}

	function create_woods($wid, $wname, $wprice, $woffer, $wcategory = '', $wpic) {
		if(empty($wid) || empty($wname)) return false;
		// echo 'create_woods '.$wid.'<br />';

		$wid 		= mysql_real_escape_string($wid);
		$wname 		= mysql_real_escape_string($wname);
		$wprice 	= mysql_real_escape_string($wprice);
		$woffer 	= mysql_real_escape_string($woffer);
		$wpic 		= mysql_real_escape_string($wpic);

		$wprice = preg_replace('/\D/', '', $wprice);
		$woffer = preg_replace('/\D/', '', $woffer);

		$primary_id = md5($wid);
		$chk_woods = $this->find_woods($primary_id);

		if(!empty($wcategory)) {
			$this->create_relation($wcategory, $primary_id);
		}

		if(count($chk_woods) > 0) {
			$this->update_woods($primary_id, $wname, $wprice, $woffer, $wpic);
			return true;
		}

		$sql = "
			INSERT INTO `finder_goods` (`fguid`, `fgsid`, `fgname`, `fgprice`, `fgoffer`, `fgpic_url`, `fgtype`, `fgupdate_time`, `fgcreate_time`) 
			VALUES ('{$primary_id}', '{$wid}', '{$wname}', '{$wprice}', '{$woffer}', '{$wpic}', '{$this->current_type['ftuid']}', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);";

		$this->current_limit_woods += 1;
		
		mysql_query($sql) or $this->finder_error("Create Woods Error.", mysql_error(), 888 );
	}

	function find_relation($rid, $wid) {
		$sql = "
			SELECT * 

			FROM `finder_relation` 

			WHERE `finder_relation`.`fcrcategory_uid` = '{$rid}' AND
				`finder_relation`.`fcrgoods_uid` = '{$wid}';";

		$result = mysql_query($sql) or $this->finder_error("Find Relation Error.", mysql_error(), 888 );
		$count = mysql_num_rows($result);

		if($count == 0) {
			return Array();
		} else {
			return mysql_fetch_assoc($result);
		}

	}

	function create_relation($rid, $wid) {
		if(empty($wid) || empty($rid)) return false;

		$primary_id = md5($rid);
		$chk_relation = $this->find_relation($primary_id, $wid);

		$this->create_category($rid, '9999');

		//var_dump($primary_id); die();

		if(count($chk_relation) == 0) {
			$sql = "
				INSERT INTO `finder_relation` (`fcrcategory_uid`, `fcrgoods_uid`) 
				VALUES ('{$primary_id}', '{$wid}');";

			mysql_query($sql) or $this->finder_error("Create Relation Error.", mysql_error(), 888 );
		}
	}

	function find_category($cid) {
		$sql = "
			SELECT * 
			FROM `finder_category` 
			WHERE `finder_category`.`fcuid` = '{$cid}';";

		$result = mysql_query($sql) or $this->finder_error("Find Category Error.", mysql_error(), 888 );
		$count = mysql_num_rows($result);

		if($count == 0) {
			return Array();
		} else {
			return mysql_fetch_assoc($result);
		}

	}

	function create_category($cid, $cname) {
		if(empty($cname) || empty($cid)) return false;
		$cname 		= mysql_real_escape_string($cname);

		$primary_id = md5($cid);
		$chk_woods = $this->find_category($primary_id);

		if(count($chk_woods) > 0) {
			$this->update_category($primary_id, $cname);
			return false;
		}

		$sql = "
			INSERT INTO `finder_category` (`fcuid`, `fcsid`, `fcname`, `fcupdate_time`) 
			VALUES ('{$primary_id}', '{$cid}', '{$cname}', CURRENT_TIMESTAMP);";

		mysql_query($sql) or $this->finder_error("Create Category Error.", mysql_error(), 888 );
	}

	function update_category($cid, $cname) {
		if(empty($cname)) return false;
		if($cname == '9999') return false;
		$sql = "
			UPDATE `finder_category` 
			SET `fcname` = '{$cname}'
			WHERE `fcuid` = '{$cid}';";

		mysql_query($sql) or $this->finder_error("Update Category Error.", mysql_error(), 888 );
	}

	// 紀錄每次抓取網頁的 網址 header 內容
	function finder_log($url, $post, $header, $data) {
		if(empty($url) || empty($header) || empty($data)) return false;
		$post = mysql_real_escape_string($post);
		$data = mysql_real_escape_string($data);
		$header = mysql_real_escape_string($header);
		$url = mysql_real_escape_string($url);

		$sql = "
			INSERT INTO `finder_log` (`fluid`, `flurl`, `flpost`, `flheader`, `fldata`, `fltime`) 
			VALUES (NULL, '{$url}', '{$post}', '{$header}', '{$data}', CURRENT_TIMESTAMP);";

		mysql_query($sql) or $this->finder_error("Recording Log Error.", mysql_error(), 888 );
	}

	function finder_error($msg, $error, $code) {
		if($code != 999)
			$this->finder_log($code, '', $msg, '');
		die("$msg ($code) - $error");
	}
}