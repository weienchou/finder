<?php require('class_finder.php');										// 引用 Finder

$Finder = new Finder('udn');											// 建立 Finder

$Finder->set_start_time();

if(count($Finder->current_keyword) > 0) foreach($Finder->current_keyword as $loop_value) {
	$per_page = 1000;
	$loop_page = 1;
	get_category($Finder, $loop_value);
	for($i = 1; $i <= $loop_page; $i++) {
		
		$woods_url = strtr($Finder->current_type['ftgetwoods_url'], Array(
			'{$data}' => urlencode($loop_value),
			'{$page}' => $i,
			'{$pageno}' => $per_page,
		));
		echo $woods_url.'<br />';

		$woods_html_code = $Finder->get_html_code($woods_url);

		// sort_label\">.*目前在.*>([0-9]+)<\/
		$woods_page_num_parse = array();
		preg_match_all("/sort_label\\\">.*目前在.*>([0-9]+)<\\//", $woods_html_code, $woods_page_num_parse);
		$woods_page_num = parse_search_item_num($woods_page_num_parse);

		if($i == 1) {
			$loop_page = $woods_page_num;
		}

		$woods_return_parse = array();
		preg_match_all("/<tr.*>[\\s\\S]*?<a href=\\\".*dc_cateid_0\\=([^\\&]*).*cargxuid.*\\=([0-9A-Z]+)\\&.*title=\\\"([^\\\"]*)\\\".*img src=\\\"([^\\\"]+)\\?.*\\\"[\\s\\S]*?pd_hlight\\\">([0-9]+)<\\/span>/", $woods_html_code, $woods_return_parse);

		save_woods($Finder, $woods_return_parse);
	}

}
$Finder->set_stop_time();
$Finder->show_time();

function save_woods($aFinder, $ray) {
	for($i = 0; $i < count($ray); $i++) {
		if(!isset($ray[$i+1])) break;
		if(count($ray[$i]) != count($ray[$i+1])) return ;
	}

	if(count($ray[0]) > 0) foreach($ray[0] as $k => $v) {
		//$ray[2][$k]
		//($wid, $wname, $wprice, $woffer, $wcategory = '', $wpic)
		$aFinder->create_woods($ray[2][$k], $ray[3][$k], 0, $ray[5][$k], '', 'http:'.$ray[4][$k]);

		if(count($aFinder->tmp_category) > 0) foreach($aFinder->tmp_category as $ka => $va) {
			if (preg_match("/^{$ka}/", $ray[1][$k]) === 1) {
				$aFinder->create_relation($ka, md5($ray[2][$k]));
				$aFinder->create_category($ka, $va);
			}
		}

		if($aFinder->current_limit_woods >= $aFinder->limit_woods && $aFinder->limit_woods != -1) return true;
	}
}

function get_category($aFinder, $loop_value, $category = '') {
	$category_url = strtr($aFinder->current_type['ftgetmaincategory_url'], Array(
		'{$data}' => urlencode($loop_value),
		'{$category}' => $category,
	));
		echo $category_url.'<br />';
	$category_html_code = $aFinder->get_html_code($category_url);

	$category_decode = json_decode($category_html_code);

	if(is_null($category_decode)) return;

	if(count($category_decode) == 0) return;

	if(count($category_decode) > 0) foreach($category_decode as $v) {
		if(!in_array($v->xuid, $aFinder->tmp_category)) {
			$aFinder->tmp_category[$v->xuid] = $v->nm2;
			get_category($aFinder, $loop_value, $v->xuid);
		} else {
			return;
		}
	}
	return;
}

function parse_search_item_num($ray) {
	if(count($ray) != 2) {
		return 0;
	}

	if(!isset($ray[1][0]) || count($ray[1]) != 1) {
		return 0;
	}

	if( (!is_int($ray[1][0]) ? (ctype_digit($ray[1][0])) : true) === false ) {
		return 0;
	}

	return intval($ray[1][0]);
}