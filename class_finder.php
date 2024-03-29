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
		header("Content-Type:text/html; charset=utf-8");
		ini_set('max_execution_time', 0);
		ini_set('memory_limit', '-1');
		set_time_limit(0);

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
		echo '<style>body{line-height: 22px;font-family: arial;} td {padding: 6px 4px;}</style><title>WAYNE FINDER 2.0</title>';
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
		echo date('Y/m/d H:i:s', $this->start_time).' Start Finder 2.0 [ '.$_SERVER["SCRIPT_FILENAME"].' ]<br />';
		echo '<table border="1" style="width: 100%;" cellspacing="0">
				<thead>
					<tr>
						<td> # </td>
						<td> 商品編號 </td>
						<td> 商品名稱 </td>
						<td> 商品價錢 </td>
						<td> 商品特價 </td>
					</tr>
				</thead>
				<tbody>
			';
	}

	function set_stop_time() {
		$this->stop_time = strtotime('now');
	}

	function show_time() {
		$diff = floor($this->stop_time-$this->start_time);
		echo '	</tbody>
			 </table>';
		echo '<hr />';
		echo date('Y/m/d H:i:s', $this->stop_time).' Stop <br />';
		echo 'Spend '.$diff.' s. <br />'; //Save '.$this->current_limit_woods.' Woods. <br />';
		exit('Process done.');
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
			'零阻力'
		);
	}

	// 由 $url 網址取得網頁原始碼， $post_data 為要傳送的 post_data
	function get_html_code($url = '', $post = false, $post_data = Array()) {
		if(empty($url)) return '';

		$options = array(
	        CURLOPT_SSL_VERIFYPEER => false,
	        CURLOPT_RETURNTRANSFER => true,
	        CURLOPT_HEADER         => true,
	        CURLOPT_FOLLOWLOCATION => true,
	        CURLOPT_VERBOSE		   => true,
	        CURLOPT_ENCODING       => '',
	        CURLOPT_USERAGENT      => 'Mozilla/5.0 (FINDER 2.0) Gecko/20160101 Firefox/34.0',
	        CURLOPT_AUTOREFERER    => true,
	        CURLOPT_CONNECTTIMEOUT => 120,
	        CURLOPT_TIMEOUT        => 180,
	        CURLOPT_COOKIEJAR	   => 'cookie/'.$this->current_type['ftname'].'_cookie.txt',
	        CURLOPT_BUFFERSIZE	   => 128,
	        CURLOPT_COOKIEFILE	   => 'cookie/'.$this->current_type['ftname'].'_cookie.txt',
	        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1
	    ); 

	    if($post == true && !empty($post_data)) {
			$options[CURLOPT_POST] = true;
			$options[CURLOPT_POSTFIELDS] = http_build_query($post_data);
	    }

	    $aCurlopt = curl_init($url);
	    curl_setopt_array($aCurlopt, $options);

	    $response_html_code  = curl_exec($aCurlopt);

	    if($response_html_code === false) $this->finder_error("Get Html Error.", '['.$url.'] '.curl_error($aCurlopt).'{'.$url.'}', 900 );

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
		//$chk_woods = $this->find_woods($primary_id);

		if(!empty($wcategory)) {
			$this->create_relation($wcategory, $primary_id);
		}

		echo '
			<tr>
				<td> '.($this->current_limit_woods+1).' </td>
				<td> '.$wid.' </td>
				<td> '.$wname.' </td>
				<td> '.$wprice.' </td>
				<td> '.$woffer.' </td>
			</tr>';

		/*if(count($chk_woods) > 0) {
			$this->update_woods($primary_id, $wname, $wprice, $woffer, $wpic);
			return true;
		}*/

		$sql = "
			INSERT INTO `finder_goods` (`fguid`, `fgsid`, `fgname`, `fgprice`, `fgoffer`, `fgpic_url`, `fgtype`, `fgupdate_time`, `fgcreate_time`) 
			VALUES ('{$primary_id}', '{$wid}', '{$wname}', '{$wprice}', '{$woffer}', '{$wpic}', '{$this->current_type['ftuid']}', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
			ON DUPLICATE KEY UPDATE `fgname`='{$wname}', `fgprice`='{$wprice}', `fgoffer`='{$woffer}', `fgpic_url`='{$wpic}', `fgupdate_time`=CURRENT_TIMESTAMP;";


		/*if($this->current_limit_woods >= $this->limit_woods) {
			$this->set_stop_time();
			$this->show_time();
			$this->finder_error("It's Out of limit.", '', 900 );
			exit();
		}*/
	    flush();
		ob_flush();
		
		mysql_query($sql) or $this->finder_error("Create Woods Error.", mysql_error(), 888 );
		// echo mysql_affected_rows().' <br />';
		$this->current_limit_woods += 1;
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
		$rid = (strpos($rid, $this->current_type['ftname']) === false) ? $this->current_type['ftname'].'_'.$rid : $rid;

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
		if($cname == '9999') return false;
		$cname 		= mysql_real_escape_string($cname);
		$cid = (strpos($cid, $this->current_type['ftname']) === false) ? $this->current_type['ftname'].'_'.$cid : $cid;

		$primary_id = md5($cid);
		/*$chk_woods = $this->find_category($primary_id);

		if(count($chk_woods) > 0) {
			$this->update_category($primary_id, $cname);
			return false;
		}*/

		$sql = "
			INSERT INTO `finder_category` (`fcuid`, `fcsid`, `fcname`, `fcupdate_time`) 
			VALUES ('{$primary_id}', '{$cid}', '{$cname}', CURRENT_TIMESTAMP)

			ON DUPLICATE KEY UPDATE `fcname`='{$cname}', `fcupdate_time`=CURRENT_TIMESTAMP;";

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

	function CheckWoodsIDInDB($WoodIDArray) {
		$sql = "
			SELECT `finder_goods`.`fgsid`
			FROM   `finder_goods`
	        LEFT JOIN `finder_relation`
	               ON ( `finder_relation`.`fcrgoods_uid` = `finder_goods`.`fguid` )
			WHERE  `finder_goods`.`fgsid` IN ( '".implode($WoodIDArray, "', '")."' )
			       AND `finder_relation`.`fcrcategory_uid` IS NULL
			       AND `finder_goods`.`fgtype` = '{$this->current_type['ftuid']}';";

		$result = mysql_query($sql) or $this->finder_error("Check WoodsID In DB Error.", mysql_error(), 888 );
		$count = mysql_num_rows($result);

		if($count == 0) {
			return Array();
		} else {
			$ReturnArray = Array();
			while($FetchRow=mysql_fetch_row($result)) {
				$ReturnArray[] = $FetchRow[0];
			}

			echo date('Y/m/d H:i:s')." 抓取商品類別預計花費 ".((count($ReturnArray) * (int)$this->current_type['ftsleep_time']) + 1)." 秒 <br />";
			
			return $ReturnArray;
		}
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