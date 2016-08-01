<?php require('class_finder.php');										// 引用 Finder

$Finder = new Finder('yahoo');											// 建立 Finder

$Finder->set_start_time();
if(count($Finder->current_keyword) > 0) foreach($Finder->current_keyword as $loop_value) {
																		// 取得 商品 開始
	$woods_url = strtr($Finder->current_type['ftgetwoods_url'], Array(
		'{$data}' => urlencode($loop_value),
		'{$page}' => 1
	));

	$str_woods_code = $Finder->get_html_code($woods_url);

	$decode_woods_code = get_category($Finder, $str_woods_code);

	//var_dump($decode_woods_code); die();

	//main_process($Finder, $loop_value, $decode_woods_code);
	process_parse($Finder, urlencode($loop_value), $decode_woods_code);
}
$Finder->set_stop_time();
$Finder->show_time();

function main_process ($aFinder, $keywords, $category_ray) {
	if(count($category_ray) == 0) {
		return;
	} else foreach($category_ray as $v) {
		$current_page = 1;
		$loop_page = round($v->category_num / 48);

		$woods_url = strtr($aFinder->current_type['ftgetcategory_url'], Array(
			'{$data}' => $keywords,
			'{$page}' => $current_page,
			'{$category}' => $v->category_id
		));

		$str_woods_code = $aFinder->get_html_code($woods_url);

		// echo $woods_url.'<br />';
		// echo $str_woods_code; die();
		$decode_woods_code = get_category($aFinder, $str_woods_code);

		if(count($decode_woods_code == 0)) {
			$ray_woods = get_woods($aFinder, $str_woods_code);
			save_woods($aFinder, $v->category_id, $ray_woods);
			return;
		} else {
			main_process($aFinder, $keywords, $decode_woods_code);
		}
	}
}

function process_parse ($aFinder, $keywords, $category_ray) {
	// echo 'process_parse start <br />';
	if(count($category_ray) == 0) {
		// echo '　count($category_ray) == 0 <br />';
		return; 
	} else foreach($category_ray as $v) {
		// echo '　count($category_ray) != 0 <br />';

		$str_woods_code = $aFinder->get_html_code($v->category_url);		
		// echo '　用 category_ray category_url 取得 html <br />';

		$decode_woods_code = get_category($aFinder, $str_woods_code);
		// echo '　用 get_category 取得商品類別 <br />';
		// echo '　用 process_parse start <br />';

		process_parse($aFinder, $keywords, $decode_woods_code);

		// echo '　用 process_parse end <br />';
		$ray_woods = get_woods($aFinder, $str_woods_code);
		// echo '　用 get_woods <br />';

		$loop_page = round($v->category_num / 48);							// 48 => 一頁顯示的頁數
		// echo '　取得頁數 ('.$loop_page.') <br />';
		echo $v->category_url.'<br />';
		save_woods($aFinder, $v->category_id, $ray_woods);
		// echo '　儲存 ('.count($ray_woods).') <br />';

		for($i = 1; $i <= $loop_page; $i++) {
			// echo '　　進入迴圈 ('.$i.') <br />';
			$woods_url = strtr($aFinder->current_type['ftgetcategory_url'], Array(
				'{$data}' => $keywords,
				'{$page}' => $i,
				'{$category}' => $v->category_id
			));
			// echo '　　建立網址 <br />';

			$str_woods_code = $aFinder->get_html_code($woods_url);
			// echo '　　取得 html <br />';
			$decode_woods_code = get_category($aFinder, $str_woods_code);
			// echo '　　取得 類別 <br />';
			$ray_woods = get_woods($aFinder, $str_woods_code);		
			// echo '　　取得 商品 <br />';
			// echo $woods_url.'<br />';
			save_woods($aFinder, $v->category_id, $ray_woods);	
			// echo '　　儲存 商品 <br />';
			// echo '　　用 process_parse start <br />';
			process_parse($aFinder, $keywords, $decode_woods_code);
			// echo '　　用 process_parse end <br />';
		}
	}
}

function get_category ($aFinder, $html_code) {
	$retrun_parse = array();
	preg_match_all('/<a class="btn" href="(.*)">\s+.*<div class="content">\s+(.*)\s+<span class="num">\((.*)\)<\/span>\s+<\/div>/', $html_code, $retrun_parse);

	if(empty($retrun_parse) || count($retrun_parse) != 4) return;
	if(empty($retrun_parse[0]) || empty($retrun_parse[1]) || empty($retrun_parse[2]) || empty($retrun_parse[3])) return;

	$array_finder_item = array();

	if(count($retrun_parse[0]) > 0) foreach($retrun_parse[0] as $k => $v) {
		$category_id = array();
		preg_match("/cid=(\d+)/", $retrun_parse[1][$k], $category_id);

		if(!in_array($category_id[1], $aFinder->repeat_category)) {
			array_push($aFinder->repeat_category, $category_id[1]);
		} else {
			continue;
		}

		$category_name = preg_replace('/\s+/', '', $retrun_parse[2][$k]);
		$array_finder_item[$k] = (object)Array(
			'category_id' => $category_id[1],
			'category_url' => strtr($aFinder->current_type['ftgetmaincategory_url'], Array(
				'{$data}' => $retrun_parse[1][$k]
			)),
			'category_name' => $category_name,
			'category_num' => $retrun_parse[3][$k]
		);

		$aFinder->create_category($category_id[1], $category_name);
	}
	return $array_finder_item;
}

function get_woods($aFinder, $html_code) {
	$retrun_parse = array();
	preg_match_all("/<div class=\\\"item yui3-.*\\\">[\\s\\S]{1,100}<a href=\\\".*gdid=(\\d+)\\\" title=\\\"(.*)\\\">[\\s\\S]{1,200}<img[\\s\\S]{1,200}src=\\\".*images\\/(.*)\\\">[\\s\\S]{1,1000}<span class=\\\"srp-promo.*\\\">[\\s\\S]{1,100}<em>(.*)<\\/em>/u", $html_code, $retrun_parse);
	$array_finder_item = array();

	if(count($retrun_parse[0]) > 0) foreach($retrun_parse[0] as $k => $v) {
		$array_finder_item[$k] = (object)Array(
			'woods_id' => $retrun_parse[1][$k],
			'woods_name' => $retrun_parse[2][$k],
			'woods_pic_url' => $retrun_parse[3][$k],
			'woods_offer' => $retrun_parse[4][$k]
		);
	}
	return $array_finder_item;
}

function save_woods ($aFinder, $cateid, $data_array) {
	if(count($data_array) > 0) foreach($data_array as $v) {
		$aFinder->create_woods(
			$v->woods_id, 
			$v->woods_name, 
			0, 
			$v->woods_offer, 
			$cateid, 
			$v->woods_pic_url);
		if($aFinder->current_limit_woods >= $aFinder->limit_woods && $aFinder->limit_woods != -1) return ;
	}
	// echo '＞儲存 ('.count($data_array).') 筆商品 <br />';
}
